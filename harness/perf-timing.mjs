#!/usr/bin/env node
// Runtime perf fingerprint via headless Chrome + CDP. Zero npm deps (needs google-chrome + node >=18).
// The JS-blind twin of fingerprint.sh: captures what curl CANNOT — the actual "slow load" symptom.
//   LCP, FCP, TTFB, DOMContentLoaded, load, main-thread long-tasks (TBT proxy),
//   per-resource transfer bytes grouped by initiator, top-10 heaviest resources, RevSlider presence.
//
// Usage: ./perf-timing.mjs <BASE_URL> <OUTDIR> [path ...]
//   BASE_URL  https://www.impressionoriginale.com     (no trailing slash)
//   OUTDIR    e.g. perf-baseline    (writes OUTDIR/SUMMARY.txt + OUTDIR/<slug>.json)
//   [path...] default: /  /fr/      (the RevSlider hero pages — the perf break-zone)
// Env:
//   THROTTLE=1   4x CPU + ~Fast-3G net (approximates Lighthouse-mobile; sharpens the signal)
//   RUNS=<n>     median of n loads per page for the timing fields (default 1)
//
// Diff rounds like fingerprint:  diff perf-baseline/SUMMARY.txt perf-after/SUMMARY.txt
// NOTE: wall-clock timing is noisy (network + CDN cache). Run before/after back-to-back on the
//   same host/network. Structural fields (revslider, resource count, bytes-by-type) are stable.
// Cache: browser cache is disabled per load for determinism; origin/Cloudflare cache is NOT — a
//   cf-cache MISS on the first hit inflates TTFB. Warm the page once, then measure.

import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync, readFileSync, existsSync, writeFileSync, mkdirSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = process.argv[2];
const OUT = process.argv[3];
if (!BASE || !OUT) { console.error('usage: ./perf-timing.mjs <BASE_URL> <OUTDIR> [path ...]'); process.exit(2); }
const PATHS = process.argv.slice(4).length ? process.argv.slice(4) : ['/', '/fr/'];
const RUNS = Math.max(1, parseInt(process.env.RUNS || '1', 10));
const THROTTLE = process.env.THROTTLE === '1';

const CHROME = process.env.CHROME || 'google-chrome';
const sleep = ms => new Promise(r => setTimeout(r, ms));
const median = a => { const s = [...a].sort((x, y) => x - y); const m = s.length >> 1; return s.length % 2 ? s[m] : Math.round((s[m - 1] + s[m]) / 2); };

// Probe runs in the page after load: fills LCP + longtask buffers, then snapshots resource timing.
const PROBE = `new Promise((resolve)=>{
  const nav = performance.getEntriesByType('navigation')[0]||{};
  let lcp=0, lcpEl='';
  try{ new PerformanceObserver(l=>{for(const e of l.getEntries()){ lcp=e.startTime;
      lcpEl=(e.element&&(e.element.tagName+((e.element.currentSrc||e.element.src)?(' '+String(e.element.currentSrc||e.element.src).split('/').pop()):'')))||e.url||''; }})
    .observe({type:'largest-contentful-paint',buffered:true}); }catch(e){}
  let tbt=0,lc=0,lmax=0;
  try{ new PerformanceObserver(l=>{for(const e of l.getEntries()){ tbt+=Math.max(0,e.duration-50); lc++; lmax=Math.max(lmax,e.duration);}})
    .observe({type:'longtask',buffered:true}); }catch(e){}
  const fcp=(performance.getEntriesByType('paint').find(p=>p.name==='first-contentful-paint')||{}).startTime||0;
  setTimeout(()=>{
    const res=performance.getEntriesByType('resource');
    let total=0, by={};
    for(const r of res){ total+=r.transferSize||0; const t=r.initiatorType||'other'; by[t]=(by[t]||0)+(r.transferSize||0); }
    const top=res.map(r=>({n:String(r.name).split('/').pop().slice(0,42),init:r.initiatorType,k:Math.round((r.transferSize||0)/1024)}))
      .sort((a,b)=>b.k-a.k).slice(0,10);
    const rs=res.filter(r=>/rs6|rbtools|revslider/i.test(r.name)).map(r=>String(r.name).split('/').pop());
    resolve({ status:nav.responseStatus||0, ttfb:Math.round(nav.responseStart||0), fcp:Math.round(fcp),
      dcl:Math.round(nav.domContentLoadedEventEnd||0), load:Math.round(nav.loadEventEnd||0),
      lcp:Math.round(lcp), lcpEl:String(lcpEl).slice(0,50),
      longtasks:lc, longMax:Math.round(lmax), tbtProxy:Math.round(tbt),
      resources:res.length, kb:Math.round(total/1024),
      byKB:Object.fromEntries(Object.entries(by).map(([k,v])=>[k,Math.round(v/1024)])),
      revslider:rs, top });
  }, 900);
})`;

class CDP {
  constructor(ws) {
    this.ws = ws; this.id = 0; this.pending = new Map(); this.listeners = new Set();
    ws.addEventListener('message', ev => {
      const m = JSON.parse(ev.data);
      if (m.id !== undefined) { const p = this.pending.get(m.id); if (p) { this.pending.delete(m.id); m.error ? p.rej(new Error(m.error.message)) : p.res(m.result); } }
      else for (const l of [...this.listeners]) l(m);
    });
  }
  send(method, params = {}, sessionId) {
    const id = ++this.id; const msg = { id, method, params }; if (sessionId) msg.sessionId = sessionId;
    return new Promise((res, rej) => { this.pending.set(id, { res, rej }); this.ws.send(JSON.stringify(msg)); });
  }
  // Cancelable one-shot event wait. Returns { promise, cancel } so the loser of a race
  // can be torn down (listener + timer removed) — otherwise a pending 45s timer keeps node alive.
  waitFor(method, sessionId, timeoutMs) {
    let to, l, done = false;
    const promise = new Promise((res, rej) => {
      to = setTimeout(() => { if (done) return; done = true; this.listeners.delete(l); rej(new Error('timeout ' + method)); }, timeoutMs);
      l = m => { if (done) return; if (m.method === method && (!sessionId || m.sessionId === sessionId)) { done = true; clearTimeout(to); this.listeners.delete(l); res(m.params); } };
      this.listeners.add(l);
    });
    const cancel = () => { if (done) return; done = true; clearTimeout(to); this.listeners.delete(l); };
    return { promise, cancel };
  }
}

async function launchChrome() {
  const dir = mkdtempSync(join(tmpdir(), 'perf-chrome-'));
  const child = spawn(CHROME, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--no-first-run', '--no-default-browser-check',
    '--disable-extensions', '--hide-scrollbars', '--window-size=1350,940',
    '--disable-dev-shm-usage', // heavy pages crash the renderer when /dev/shm is small; write shm to /tmp instead
    '--remote-debugging-port=0', `--user-data-dir=${dir}`, 'about:blank',
  ], { stdio: 'ignore' });
  const portFile = join(dir, 'DevToolsActivePort');
  for (let i = 0; i < 100; i++) { if (existsSync(portFile)) break; await sleep(100); }
  if (!existsSync(portFile)) { child.kill('SIGKILL'); rmSync(dir, { recursive: true, force: true }); throw new Error('chrome did not start'); }
  const port = readFileSync(portFile, 'utf8').split('\n')[0].trim();
  const ver = await (await fetch(`http://127.0.0.1:${port}/json/version`)).json();
  const ws = new WebSocket(ver.webSocketDebuggerUrl);
  await new Promise((res, rej) => { ws.addEventListener('open', res, { once: true }); ws.addEventListener('error', rej, { once: true }); });
  return { cdp: new CDP(ws), stop: () => { try { ws.close(); } catch {} child.kill('SIGKILL'); rmSync(dir, { recursive: true, force: true }); } };
}

async function measure(cdp, url) {
  const { targetId } = await cdp.send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await cdp.send('Target.attachToTarget', { targetId, flatten: true });
  await cdp.send('Page.enable', {}, sessionId);
  await cdp.send('Runtime.enable', {}, sessionId);
  await cdp.send('Network.enable', {}, sessionId);
  await cdp.send('Network.setCacheDisabled', { cacheDisabled: true }, sessionId);
  if (THROTTLE) {
    await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 }, sessionId);
    await cdp.send('Network.emulateNetworkConditions',
      { offline: false, latency: 150, downloadThroughput: 1.6 * 1024 * 1024 / 8, uploadThroughput: 750 * 1024 / 8 }, sessionId);
  }
  const loaded = cdp.waitFor('Page.loadEventFired', sessionId, 45000);
  const crashed = cdp.waitFor('Inspector.targetCrashed', sessionId, 45000);
  await cdp.send('Page.navigate', { url }, sessionId);
  let crash = false;
  try {
    await Promise.race([
      loaded.promise,
      crashed.promise.then(() => { crash = true; throw new Error('renderer crashed'); }),
    ]);
  } catch (e) { if (crash) { loaded.cancel(); throw e; } /* load timeout: probe still reads whatever painted */ }
  finally { loaded.cancel(); crashed.cancel(); }
  const r = await cdp.send('Runtime.evaluate', { expression: PROBE, awaitPromise: true, returnByValue: true }, sessionId);
  await cdp.send('Target.closeTarget', { targetId });
  return r.result.value;
}

function summary(p, url, m) {
  const b = m.byKB || {};
  const kb = k => (b[k] || 0);
  const rs = m.revslider && m.revslider.length ? 'YES (' + m.revslider.length + ': ' + m.revslider.slice(0, 3).join(',') + ')' : 'no';
  const top = (m.top || []).map(t => `      ${String(t.k).padStart(4)}KB ${t.init.padEnd(6)} ${t.n}`).join('\n');
  return `=== ${p}
url    : ${url}   http=${m.status}
timing : ttfb=${m.ttfb}ms  fcp=${m.fcp}ms  lcp=${m.lcp}ms  dcl=${m.dcl}ms  load=${m.load}ms
lcp_el : ${m.lcpEl}
mainthr: longtasks=${m.longtasks}  longest=${m.longMax}ms  tbt_proxy=${m.tbtProxy}ms
payload: total=${m.kb}KB  res=${m.resources}   css=${kb('css')}KB img=${kb('img')}KB script=${kb('script')}KB link=${kb('link')}KB
revslid: ${rs}
top10:
${top}
`;
}

(async () => {
  mkdirSync(OUT, { recursive: true });
  // Heartbeat: some runners kill a child that produces no output for a while. Page loads are
  // silent (~15s), so tick stderr to keep the pipe warm. Harmless — never touches SUMMARY/stdout.
  const beat = setInterval(() => process.stderr.write('.'), 1000);
  const { cdp, stop } = await launchChrome();
  const lines = [];
  try {
    for (const p of PATHS) {
      const url = BASE + p;
      const slug = p.replace(/\//g, '_').replace(/^_|_$/g, '') || 'home';
      const runs = [];
      for (let i = 0; i < RUNS; i++) runs.push(await measure(cdp, url));
      // median the noisy timing fields; keep last run's structural fields
      const m = { ...runs[runs.length - 1] };
      for (const f of ['ttfb', 'fcp', 'lcp', 'dcl', 'load', 'tbtProxy', 'longMax']) m[f] = median(runs.map(r => r[f] || 0));
      writeFileSync(join(OUT, slug + '.json'), JSON.stringify({ url, runs, median: m }, null, 2));
      const s = summary(p, url, m);
      lines.push(s);
      process.stdout.write(s + '\n');
    }
  } finally { clearInterval(beat); stop(); }
  writeFileSync(join(OUT, 'SUMMARY.txt'), lines.join('\n'));
  process.stdout.write(`\nwrote ${join(OUT, 'SUMMARY.txt')} (${PATHS.length} pages, RUNS=${RUNS}, THROTTLE=${THROTTLE ? 'on' : 'off'})\n`);
  process.exit(0);
})().catch(e => { console.error(e); process.exit(1); });
