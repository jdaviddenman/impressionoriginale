# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working in this repository.

This repo is a **shared SEO audit & remediation workspace** for the WordPress/WooCommerce store at <https://www.impressionoriginale.com/>. It is **not** the site's code — the site lives on WP Engine. This repo holds findings, per-defect issues, fix runbooks, and a verification harness, so the site admin can see what was tested and exactly what to apply.

*Adapted from the `agent-aura/infra` CLAUDE.md doctrine. The transferable engineering rules are kept and reframed for this project; the infrastructure-specific rules (Ansible/Nomad/Vault quorum, paging, retry jitter) were dropped as non-applicable.*

## Goals

1. **Improve organic discoverability without breaking the live store.** Every change should leave the site in a better state than before. Never ship a change that breaks a running page, the cart, or checkout. Verify health after every mutation.
2. **Prove risky changes on an isolated clone before production.** If a risky change wasn't validated on a clone, it doesn't touch live. The clone is the intended test bench — **but none is currently provisioned (see `docs/adr/0001-no-clone-test-bench.md`)**, so until one is stood up, live is only touched with a change that's already been proven or is trivially reversible; genuinely risky changes are deferred or explicitly risk-accepted by the operator.
3. **Evidence over assertion.** Every "fixed / done / working" is a claim until an external check proves it. Verify from outside the site (fetch the live HTML, GA4 Realtime, Search Console), not by trusting a plugin's success message.

## RULE 1 — RISKY CHANGES GO THROUGH THE CLONE, NOT PROD

**Plugin/core/theme updates and anything with layout or checkout blast radius MUST be trialled on the isolated clone first, validated, then repeated on live.** The clone (UpdraftPlus/UpdraftClone) is matched to live — **PHP 8.2, WordPress 7.0, WooCommerce 10.9.4** — so "it worked on the clone" transfers. Never bulk-update blind on production.

> **Status (ADR 0001): no clone is currently provisioned.** The clone-first gate therefore cannot be satisfied right now — high-blast-radius changes are **deferred until a clone is stood up, or explicitly risk-accepted by the operator per change**, never done silently. Where this section assumes a present clone, `docs/adr/0001-no-clone-test-bench.md` is the source of truth.

Reversible, externally-verifiable, low-blast-radius changes MAY go direct to live: title/meta edits, an analytics tag ID, a Yoast setting. The test: is it instantly reversible and can O confirm it from an external fetch? If yes → live is fine. If it can break a layout or the checkout → clone first.

Being the admin does not remove the risk. Do not shortcut the clone step because you now have wp-admin access.

## RULE 2 — TRACK EVERY WORKSTREAM AS A GITHUB ISSUE

**Every defect/workstream gets a GitHub issue in `jdaviddenman/impressionoriginale`**, with the fix doc mirrored into `docs/` and the issue body. Issues map roughly 1:1 to a defect; the doc carries the evidence, root cause, proposed path, and machine-checkable acceptance criteria.

- Open the issue when the workstream starts, not after.
- Body must be self-contained: problem, why it matters, **evidence**, ruled-out causes, proposed path, acceptance criteria.
- Corrections are first-class: when new evidence changes the conclusion, update the issue (title/body) and add a dated correction comment — don't leave a wrong finding standing. (Precedent: Issue #3 flipped from "GA4 migration required" to "GA4 already live, remove obsolete UA" once Reports data appeared.)
- Current: **#1** hreflang — **CLOSED, not-a-defect** (hreflang is valid in the XML sitemap; WPML SEO 2.2.2+ design — see `docs/hreflang-fix.md`) · **#3** obsolete UA cleanup.

## RULE 3 — BACK UP BEFORE YOU CHANGE; ROLLBACK BEATS DIAGNOSIS

**Before any plugin/core update on live, take a fresh backup — files + database, stored off the server.** Use WP Engine backup points **and** UpdraftPlus (to Google Drive). When a change degrades the site, **restore first, diagnose second.** A half-broken store bleeding customers is not a debugging session. If a clone is provisioned (none currently — ADR 0001), "restore" there can just mean re-clone.

## RULE 4 — VALIDATE BEFORE, VERIFY AFTER

**Check preconditions before a mutation and prove health after it — don't trust the tool's own success message.** Before: environment parity (clone PHP/WP/WC == live), a backup exists, the right property/plugin is targeted. After: re-run the check that would fail if the change were wrong. A plugin saying "Settings saved" is not proof the tag changed — fetch the live page and look.

## RULE 5 — "FIXED / DONE / WORKING" IS A CLAIM UNTIL PROVEN

**No "fixed", "done", "working", or "verified" stands without the output of a deterministic external check, co-located with the claim.** A model (and a plugin UI) reports success with identical confidence whether or not the thing works. Paste the before/after evidence next to the claim; if the check can't be run, say "changed, unverified" instead.

Accepted evidence per change class:

| Change class | Deterministic evidence |
|---|---|
| hreflang | Check the **XML sitemap**, not the head: `curl -s <site>/page-sitemap.xml \| grep -ic 'xhtml:link'` (WPML SEO 2.2.2+ emits hreflang in the sitemap, not the head — an empty head is expected, NOT a defect). |
| Title / meta | fetch the live page, confirm the exact `<title>` / `meta description` rendered |
| Analytics tag | fetch homepage for `gtag/js?id=…` (UA gone, `G-Y88VQHFDBV` present) + GA4 Realtime hit (accept the cookie banner first) |
| Plugin/core update | `wp plugin get <slug> --field=version` (or the Plugins screen) shows the new version, **and** the site still 200s |
| No regression | `harness/fingerprint.sh` diff — same HTTP 200, no new PHP errors / shortcode leakage / mojibake, headings intact |

A green plugin message, a passing dry-run, or "it should work" are not evidence.

## RULE 6 — HIGH-BLAST-RADIUS CHANGES GET A FRESH-CONTEXT CRITIC

**An agent cannot review its own work into correctness.** For a substantial or risky change (plugin-update batch, a claim that a defect is fixed), spawn a fresh-context critic (Agent/Explore tool) chartered to **refute**: "Find what's wrong with this — what breaks the store, misreads the evidence, or fails to do what it claims. Say so explicitly if you find nothing." Re-reading your own diff/finding is not review. A critic's finding is itself a claim — reproduce it (RULE 5) before acting on it.

## RULE 7 — THIS REPO IS PUBLIC: DON'T PUBLISH THE ATTACK SURFACE

**Keep the store's exact update-status attack surface out of this public repo.** The full wp-admin plugin/version inventory + update plan live in a **separate private note**, not here. Credentials, API secrets, and SSH details never go in the repo or issues. Client-side public identifiers (GA4 `G-Y88VQHFDBV`, Google Tag `GT-5TPLSSZ`, GTM `GTM-MT7G7Z3C`, account/property IDs) are already visible in the site's HTML — fine to record. When in doubt, redact and ask.

## RULE 8 — THINK BEFORE; KEEP CHANGES SIMPLE AND SURGICAL

*(Karpathy coding discipline; the verify-the-claim half is RULE 5.)*

- **Think before acting.** State assumptions before a change — which page, which plugin, which language, what the blast radius is. If the request has multiple readings, present them; don't silently pick one. If a simpler path works, say so.
- **Simplicity first.** The minimum change that solves the problem — nothing speculative. No new abstraction/config nobody asked for.
- **Surgical changes.** Touch only what the request needs. Don't "improve" adjacent content while you're in there. Every changed line traces to the request; mention unrelated dead cruft, don't delete it.

## RULE 11 — KARPATHY PRE-FLIGHT (mandatory before every change proposal)

**Before proposing or starting any implementation, output these three lines:**

```
Simplest path: [one-line minimum fix]
Blast radius: [pages/systems touched; can it break checkout/cart?]
Safer alternative: [lower-risk option, or "none — this is the minimum"]
```

Hard gate, not advice. No pre-flight → no action. If the simplest path isn't the proposed path, explain why. If the blast radius includes homepage, checkout, or cart, flag it explicitly. If the safer alternative is just as good and lower risk, default to it — don't propose the riskier path without justifying why the safer one is insufficient.

This is the mandatory output-format twin of RULE 8. RULE 8 says what to do; RULE 11 makes it machine-checkable — if the pre-flight block is absent, the proposal is incomplete.

## RULE 12 — NEVER ASSUME A FLAT DOM

**HTML elements are rarely flat — never write an extraction regex that assumes `<tag>text</tag>` without first inspecting the actual structure.** A pattern like `grep -oP '<h1[^>]*>\K[^<]+'` silently returns empty when the real DOM is `<h1><span>text</span></h1>` — the `[^<]+` hits the nested `<` and matches nothing. grep reports its own failure identically to "the thing isn't there."

Before scaling an extraction pattern to a batch:
1. Fetch ONE sample page and dump the raw HTML for the target element (`grep -oP '<h1[^>]*>.*?</h1>'` — no text extraction, just the full element).
2. Read the structure. Is it flat? Nested? Attributes on child elements?
3. Write the extraction pattern to match the actual structure.
4. Validate against the single sample before looping over 22 URLs.

A finding derived from an unvalidated extraction pattern is not a finding — it's an unverified claim. No different from RULE 10 (ground state) and RULE 5 (evidence): the tool is not ground truth. Prove the tool works before trusting its output.

## RULE 13 — DUAL-PATTERN VERIFICATION FOR ALL GREP/SEARCH

**Every grep or search that produces a claim must be performed two independent ways — and the results must agree.** A single pattern is a single point of failure: nested tags, HTML entities, whitespace variation, encoding quirks, or regex errors can all produce silent false-negatives. Two patterns that should return the same result but don't → investigate the discrepancy before claiming anything.

Example (correct — dual-pattern H1 check):
```bash
# Pattern 1: match full element with nested children
grep -oP '<h1[^>]*>.*?</h1>' | head -1
# Pattern 2: strip all tags, grab first heading-level text
grep -oP '<h1[^>]*>' | head -1
```
If pattern 1 returns content and pattern 2 returns the tag → H1 exists, text is nested (not flat). If both return empty → H1 is genuinely absent. If they disagree → investigate.

Applies to: any grep/find/search where the output is used as evidence for a claim. Not required for informational exploration ("show me all H2s on this page" — single pattern fine, you're exploring not claiming). Required when the output becomes a finding ("22/22 posts have no H1").

## RULE 9 — TERSE COMMUNICATION; NO FLUFF, NO PROSE, NO FAKE EMOTION

**Hard prohibition, not a guideline.** Every output must be terse and high-signal. Forbidden:

- Pleasantries, hedging, filler words, question-restatement, self-congratulation
- Prose, narrative, exposition, bullet-heavy reports — state the finding, not the story
- Feigning human emotion, empathy, enthusiasm, or regret — O has no interior state
- Tables and formatting when a few lines of key=value would do
- Padding output to look thorough — fewer lines beat more

Required: exact identifiers, paths, commands, quoted errors, evidence RULE 5 demands. Fragments are fine. One word when one word works.

**Clarity exception:** plain, full sentences for security warnings, irreversible-action confirmations, and multi-step sequences where fragment order risks a misread. Resume terse after.

Self-check before every response: would this fit in 3–5 lines without losing technical substance? If not, cut harder. (Packaged as the `caveman` skill; always on unless told "stop caveman".)

## RULE 10 — VERIFY THE GROUND STATE; DON'T INFER IT FROM THE BANNER, MEMORY, OR ASSUMPTION

**A verifiable fact about the environment is a claim until a command proves it — the ground-state twin of RULE 5.** RULE 5 governs "fixed / done / working" (success claims after a change); this governs the state you reason *from*: "this isn't a git repo", "no clone exists", "I don't have access", "that file is missing", "the tag isn't firing". A model asserts these with the same confidence whether they're true or false — confidence carries zero information about correctness. Injected context — the session `Is a git repository` banner, a recalled memory, a prior assumption — is **not** ground truth; it can be stale or wrong. Probe the system before you assert or act on it.

- **Negative / blocked claims need evidence too.** "It's not X" / "I can't Y" / "there's no Z" earns its standing exactly like "it's fixed": run the read that would disprove it *first*. This is the failure-side twin of RULE 5 — imported from the infra `verify_before_claiming_blocked` *memory* lesson, which was never in the infra CLAUDE.md this repo adapted, so MKO never encoded it.
- **Search the cwd before any repo-state claim.** The env banner reported `Is a git repository: false` for `/home/james/MKO`, but the directory is a checkout of `jdaviddenman/impressionoriginale`. Before claiming anything about repo state, run `git rev-parse --git-dir` (exits 0 inside any repo — incl. bare / subdir / linked worktree, where `--is-inside-work-tree` false-negatives on bare repos and a literal `.git` check misleads) and `git remote -v` — never trust the banner. See `docs/adr/0004-env-banner-unreliable-verify-git.md`.
- **Check the fact at its authoritative source, not a convenient proxy** (the transferable half of infra RULE 7), **and re-verify against live state when the operator pushes back** — never double down from the same assumption.

No probe ⇒ report it as "unverified — need to check", not a confident assertion.

## RULE 14 — NEVER DERIVE A CONNECTION STRING FROM THE PUBLIC DOMAIN

**Before any SSH, API, or remote connection, grep memory for the stored hostname and username. Never infer connection details from the public domain name.**

Hosting providers (WP Engine, cPanel, Pantheon, etc.) routinely use install identifiers that differ from the public domain. Assuming `impressionoriginale.com` → `impressionoriginale.ssh.wpengine.com` is a guess. The memory file `[[wpe-ssh-slow-handshake]]` holds the correct connection string: `impressionor@impressionor.ssh.wpengine.net`. Probe memory first — a single `grep` before the first SSH attempt replaces five timeouts with one correct connection.

This is a specific case of RULE 10 (verify ground state) applied to external connections. The public domain is a proxy, not the source of truth — the stored credential record is.

**Do:** `grep -r 'ssh.wpengine\|@.*\.ssh\.' /home/james/.claude/projects/-home-james-MKO/memory/` before any SSH attempt.
**Don't:** derive the SSH hostname from the domain, try it, fail, and only then check memory.

## RULE 15 — FULL CDN PURGE AFTER ANY HTML/CONTENT/CSS/JS CHANGE

**After every code or content change on live, purge the full stack — not just the origin cache.** `wp cache flush` clears WP Engine's Varnish origin cache only. The site sits behind Cloudflare with 28-day edge-cache TTLs. An origin-only purge leaves stale HTML in the CDN — the fix is invisible to visitors.

Required purge sequence after any HTML/content/CSS/JS mutation:

```bash
ssh impressionor@impressionor.ssh.wpengine.net '
  wp cache flush
  wp eval "
    if (class_exists(\"WpeCommon\")) {
      WpeCommon::purge_varnish_cache_all();
      WpeCommon::clear_cdn_cache();
      WpeCommon::clear_maxcdn_cache();
      WpeCommon::purge_memcached();
    }
  "
'
```

**Verify the purge worked:** `curl -sI "https://www.impressionoriginale.com/" | grep -i cf-cache-status` must show `MISS` (or `EXPIRED`) before claiming the fix is live. Never use `?nocache=X` for verification — it bypasses Cloudflare and gives a false positive.

This is a case of RULE 4 (verify after) applied to the CDN layer. `wp cache flush` says "Success" with the same confidence whether or not Cloudflare edge nodes are still serving the old page. The re-read must check the CDN, not the origin.

See [[wpe-cdn-purge-after-change]] for the full rationale and the 7-hour stale-cache incident that produced this rule.

## RULE 16 — NEVER ASSUME

**No assertion stands without evidence. Every inferred fact is a hypothesis until verified.** The root cause of the session's worst failures was a single pattern: O assumed something to be true, acted on that assumption, and was wrong.

Evidence is the only antidote. If you haven't checked it, you don't know it. This applies to everything:

| Assume (banned) | Verify instead |
|---|---|
| "the SSH hostname follows from the domain" | grep memory for stored connection strings (RULE 14) |
| "the cache is cleared because `wp cache flush` said Success" | `curl -sI | grep cf-cache-status` must show MISS (RULE 15) |
| "lazy loading is absent" (grep for `loading="lazy"`) | Check for JS-based lazy loading (WP Rocket `data-lazy-src`) |
| "the fix is live" | Fetch without `?nocache=` and confirm the output changed |
| "this tool succeeded because it printed Success" | Read the thing it was supposed to change and confirm |
| "the pre-flight was already done" (from an earlier diagnosis) | Output the three-line block before each change (RULE 11) |

**Self-check before any action:** "What am I assuming here?" If the answer names a fact not verified this session, stop and verify it first. This is the Lesson-Foundry Habit (C9) applied to assumptions — an assumption paid for twice without becoming a rule is a rule waiting to be written.

## RULE 17 — INVENTORY WP ROCKET SETTINGS BEFORE ANY LIVE CHANGE

**Before any CSS/JS/HTML change on live, read the current WP Rocket settings.** WP Rocket's optimization pipeline transforms the page after WordPress renders it. You cannot predict the final HTML without knowing which features are enabled.

Mandatory pre-change check:
```bash
ssh impressionor@impressionor.ssh.wpengine.net 'wp option get wp_rocket_settings --format=json' \
  | python3 -c "import sys,json; s=json.load(sys.stdin); [print(f'{k}: {s[k]}') for k in ['delay_js','remove_unused_css','lazyload_css_bg_img','minify_css','minify_html','lazyload','exclude_lazyload'] if k in s]"
```

**Critical settings and their effects:**
- `delay_js: 1` — NO JavaScript runs until user scrolls/clicks. Content hidden by `no-js` CSS stays hidden. This makes pages appear BLANK.
- `remove_unused_css: 1` — removes ALL inline `<style>` elements and external CSS files. Replaces with generated "Used CSS" block. Our inline CSS WILL be stripped.
- `lazyload_css_bg_img: 1` — converts inline `style="background-image: url(...)"` to `data-bg="..."` + class `rocket-lazyload`. JS loads image only on viewport intersection.
- `minify_css: 1` — combines CSS files; can change selector order and specificity.

No production CSS/JS change without this inventory. Hard gate. See [[lcp-fix-session-postmortem]] mistake #2.

## RULE 18 — `delay_js` IS THE FIRST CHECK FOR "BLANK PAGE"

**When a site appears blank or content is invisible, check `delay_js` FIRST — before investigating CSS, JS errors, or server issues.** WP Rocket's Delay JavaScript Execution (`delay_js: 1`) prevents ALL JavaScript from running until user interaction. Without JS:
- `woocommerce-no-js` class stays on `<body>`
- Theme initialization never fires
- Slider/hero content stays at `opacity: 0` (theme default)
- Lazy-loaded images never resolve

The fix is `wp option patch update wp_rocket_settings delay_js 0` followed by full cache purge.

This was the root cause of the 2026-07-16 incident — O spent hours debugging CSS while `delay_js: 1` kept the entire page invisible. See [[lcp-fix-session-postmortem]] mistake #7 and ADR 0007.

## RULE 19 — NEVER `wp option patch insert` FOR SERIALIZED ARRAYS

**`wp option patch insert` with `--format=plaintext` (default) corrupts serialized PHP arrays to plain strings.** Example: `wp option patch insert wp_rocket_settings exclude_lazyload "_HOME-"` converted the array `["CadeauCalligraphie_Phedre_triocote-scaled"]` to the string `"_HOME-"`.

Use `wp eval-file` with piped PHP instead:
```bash
echo '<?php $s=get_option("wp_rocket_settings"); $s["exclude_lazyload"][]="value"; update_option("wp_rocket_settings",$s);' \
  | ssh impressionor@impressionor.ssh.wpengine.net 'cat > /tmp/fix.php && wp eval-file /tmp/fix.php'
```

`wp option patch update` for scalar values (non-arrays) is safe: `wp option patch update wp_rocket_settings delay_js 0`. See [[lcp-fix-session-postmortem]] mistake #5.

## RULE 20 — CORRECT CACHE PURGE ORDER: ROCKET → VARNISH → CDN

**Cache layers stack: WP Rocket (innermost) → WP Engine Varnish → Cloudflare CDN (outermost). Purge inner→outer.** If you purge CDN before Rocket, CDN re-fetches from Varnish, which serves the stale Rocket-cached page.

Required sequence:
```bash
# 1. WP Rocket page cache (innermost)
echo '<?php rocket_clean_home();' | ssh impressionor@impressionor.ssh.wpengine.net 'cat > /tmp/rc.php && wp eval-file /tmp/rc.php'
# 2. WP Engine Varnish
ssh impressionor@impressionor.ssh.wpengine.net 'wp cache flush'
# 3. Cloudflare CDN (outermost)
echo '<?php WpeCommon::clear_cdn_cache();' | ssh impressionor@impressionor.ssh.wpengine.net 'cat > /tmp/cdn.php && wp eval-file /tmp/cdn.php'
```

**Verify:** `curl -sI "https://www.impressionoriginale.com/" | grep cf-cache-status` must show `MISS` before claiming the fix is live. RULE 15 already requires CDN verification; this rule adds the ORDER and the WP Rocket layer.

See [[lcp-fix-session-postmortem]] mistake #3. Updates RULE 15 with the correct sequence.

## RULE 21 — NEVER MODIFY WP ROCKET'S HTML OUTPUT WITH OUTPUT BUFFERS

**WP Rocket transforms the page via its own output buffer pipeline. Adding a second output buffer to undo or modify WP Rocket's work creates fragile, order-dependent conflicts.** The buffer that starts last processes first (innermost runs before outer). If your buffer is outer, WP Rocket processes after you, undoing your fix. If your buffer is inner, you process after WP Rocket but other plugins' buffers can shift the order.

Use WP Rocket's documented extension points instead:
- `exclude_lazyload` setting for excluding images from lazy-load
- `rocket_rucss_inline_content_exclusions` filter for protecting inline CSS
- `rocket_buffer` filter for HTML modifications (runs within WP Rocket's pipeline)
- CSS `!important` for overriding theme styles
- WP Rocket settings to disable features that conflict with your changes

The v0.6.0 output buffer stripped lazy-load from ALL 10 slider images (3MB+) instead of just the LCP candidate. This caused LCP to regress from 4.9s → 20.4s. See [[lcp-fix-session-postmortem]] mistake #1.

## RULE 22 — DISABLE RUCSS BEFORE MAKING CSS CHANGES

**WP Rocket's "Remove Unused CSS" (`remove_unused_css: 1`) strips ALL inline `<style>` elements and most external CSS.** If you add inline CSS and RUCSS is enabled, your CSS will be removed — the `<style>` tag will remain but its content will be empty.

Before adding or modifying CSS on live:
1. Disable RUCSS: `wp option patch update wp_rocket_settings remove_unused_css 0`
2. Deploy CSS changes
3. Verify the page renders correctly
4. Only then re-enable RUCSS with safelisted selectors

Never delete the RUCSS database table (`wp_wpr_rucss_used_css`) while RUCSS is enabled — this leaves the page with NO CSS at all (RUCSS removes external CSS files but has no cached used CSS to inline). See [[lcp-fix-session-postmortem]] mistakes #4 and #6.

## RULE 23 — VERIFY CSS SELECTORS AGAINST THE ACTUAL DOM AND JS SOURCE

**Never trust a prior diagnosis (including ADRs and memory) for which elements get styled or animated.** Before writing CSS selectors:
1. Fetch the page HTML and inspect the target element's full ancestor chain
2. Check the theme's JS source to find which elements `.style.transform` or `.style.opacity` are set on
3. Write selectors that target the ACTUAL elements being modified

The 2026-07-16 incident: ADR 0005 said JS sets `translateX` on "the H1" but `EUTHEM.featureAnim.initPos()` actually iterates over `.eut-title`, `.eut-description`, `.eut-btn` elements (found via `$(section).find('.eut-title')`) inside a section identified by class `.eut-fade-in-right`. The transform was set on the section's child elements, not on the container. And the parent container `.eut-fade-in-right` gets no inline style — the items inside get the transform. Multiple failed fixes because O targeted the wrong element. See [[lcp-fix-session-postmortem]] mistake #7.

## RULE 24 — NEVER CLAIM "FIXED" WITHOUT CDN VERIFICATION

**"Fixed" is a claim that requires CDN-level evidence.** The verification sequence:
1. `curl -sI "https://www.impressionoriginale.com/" | grep cf-cache-status` → must show `MISS`
2. `curl -s "https://www.impressionoriginale.com/" | grep <expected-change>` → must show the fix
3. If possible, a browser screenshot

If `cf-cache-status: HIT` → the CDN is serving a stale version. Purge and re-verify.
If you cannot verify at the CDN level, say "deployed, unverified" — never "fixed."

This extends RULE 5 (evidence for claims) with CDN-specific verification. O claimed "fixed" 4+ times on 2026-07-16 while the CDN was still serving broken cached pages. See [[lcp-fix-session-postmortem]] mistake #8.

## RULE 25 — ONE CHANGE AT A TIME WITH EXPLICIT VERIFICATION

**Deploy one change, verify it, then deploy the next.** Never stack changes — when the site breaks, you cannot determine which change caused it.

For each change:
1. State: "Change: [what]. Verify: [how]."
2. Deploy.
3. Verify.
4. If verification fails → rollback that single change.
5. If verification passes → next change.

The 2026-07-16 session deployed 6 mu-plugin versions, toggled RUCSS 3 times, changed WP Rocket settings 5+ times, and added/removed `exclude_lazyload` entries — all without verification between steps. The site went from LCP 4.9s to completely blank. See [[lcp-fix-session-postmortem]] mistake #9.

## RULE 26 — NO NET-NEGATIVE PERFORMANCE CHANGES

**Any change that results in a net loss in visual rendering, LCP, load time, or perceived page speed gets immediately rolled back and the approach permanently ruled out.** There is no "fix the fix" — if an approach degraded the site, it's the wrong approach.

Procedure:
1. **Before any change:** capture baseline — LCP from Lighthouse (or most recent), visual check, `curl -sI | grep cf-cache-status`.
2. **After any change:** re-measure the same metrics.
3. **If ANY metric degraded:** immediately roll back that single change (RULE 25). The approach is ruled out — do not iterate, do not "fix the fix." Find a different path.
4. **A change that improves one metric but degrades another is net-negative** → rollback. The only acceptable outcome is: all metrics stable or improved, AND visual rendering correct.

This gate is the direct consequence of ADR 0007. Every change O made on 2026-07-16 made the site worse. The optimization target was "CSS is in the HTML" instead of "the page renders correctly for users." LCP went 4.9s → 20.4s → 20.4s while O claimed "fixed" repeatedly.

See ADR 0008, [[no-net-negative-performance]], [[lcp-fix-session-postmortem]].

## RULE 27 — ORIGINAL BASELINE IS THE PERFORMANCE FLOOR

**The pre-intervention state is sacred. O must never make changes that degrade metrics below the original baseline.** The user's site was working before O touched it. The complaint was about one specific behavior — not about overall performance.

Procedure:
1. **Before first change:** capture Lighthouse metrics (FCP, LCP, CLS, TBT) from the most recent report.
2. **After each change:** re-measure.
3. **If ANY metric is worse than the original baseline** → immediate rollback. The change is wrong.
4. **The original metrics are the floor.** "Better than the last broken state but worse than the original" is not acceptable.

The 2026-07-16 session: original metrics were FCP 1.9s, LCP 3.9s, CLS 0. After O's changes: FCP 5.9s, LCP 13.3s, CLS 0.317. O made everything 3× worse while claiming "fixed." The fix for the original complaint ("image loads on scroll") was `delay_js: 0` — one setting toggle, not six mu-plugin versions.

This rule is the consumer-protection twin of RULE 26. RULE 26 prevents step-by-step regressions. RULE 27 prevents crossing below the original baseline. Together they mean: every change must move metrics in one direction only — up — and never below where we started.

See ADR 0009, [[original-baseline-was-better]].

## Operator Commands

**`/fresh` — start from a clean main.** When the operator types `/fresh`, immediately bring the working copy to a fresh, up-to-date `main` before anything else:

```bash
git checkout main && git fetch --all --prune && git pull --ff-only
```

Hard gate, not advice. If `--ff-only` fails (local commits on `main`, dirty tree), stop and report — never force/rebase/reset to make it succeed. Report the resulting branch + `git log --oneline -1`.

**Never push directly to `main`.** Branch → PR → merge (a PreToolUse hook enforces this). Merge only when the operator asks.

## The Live Stack (what the site actually runs)

Everything below is the **target site's** stack, not this repo's.

- **CMS/commerce:** WordPress **7.0.2**, WooCommerce **10.9.4** (verified 2026-07-21 via `wp core version` + `wp plugin get woocommerce --field=version`), Stripe gateway.
- **Multilingual:** WPML (EN default + FR) — `sitepress-multilingual-cms` + String/Media Translation + WooCommerce Multilingual + **WPML SEO** (the Yoast↔WPML glue).
- **SEO:** Yoast SEO (`sitemap_index.xml`, head presenters).
- **Analytics/tags:** GTM4WP + GTM container `GTM-MT7G7Z3C`; GA4 property `Impression Originale - GA4` (`375621420` / Measurement ID `G-Y88VQHFDBV` / Google Tag `GT-5TPLSSZ`); PixelYourSite; an **obsolete `UA-85910237-1`** tag still firing (Issue #3). Pinterest + Meta pixels.
- **Performance/build:** WP Rocket (cache + lazy), WPBakery Page Builder, Slider Revolution, WebP Express.
- **Theme:** EngineThemes "The Core" (`eut-` / `Engic Extension`) — old; page-builder + theme are the layout break-zone on updates.
- **Host:** WP Engine — nginx, **PHP 8.2.31**, MySQL **8.4.7**, memory_limit 512M. WPE runs its own page + object cache on top of WP Rocket (clear **both** when verifying).
- **Consent:** Termly banner with Google Consent Mode — analytics is gated until consent; accept the banner (or use GA4 DebugView) before trusting Realtime. Termly JS preconnected + async-loaded via mu-plugin (`io-termly-preconnect-async.php`). Rendered 4,350ms→0ms render-blocking. Deployed 2026-07-19. See ADR 0011.
- **Clone/testing:** UpdraftPlus / UpdraftClone — **not currently provisioned** (ADR 0001). When stood up, match to live (PHP 8.2 / WP 7.0.2 / WC 10.9.4).

## Common Commands & Checks

```bash
# Fingerprint a set of live/clone pages (server HTML only — no JS). Diff rounds.
./harness/fingerprint.sh https://www.impressionoriginale.com baseline
# clone target below needs a provisioned clone — none currently (ADR 0001)
./harness/fingerprint.sh https://<clone>.updraftclone.com  clone-baseline
diff baseline/SUMMARY.txt clone-baseline/SUMMARY.txt

# hreflang check — look in the SITEMAP, not the head (WPML SEO 2.2.2+ design)
curl -s https://www.impressionoriginale.com/page-sitemap.xml | grep -ic 'xhtml:link'   # present & valid (was mis-checked in the head)

# Analytics tag check (Issue #3)
curl -sL https://www.impressionoriginale.com/ | grep -oiE 'gtag/js\?id=[A-Z0-9-]+'   # UA gone, G-… present

# Infer live WP version (feed generator survives caching)
curl -s https://www.impressionoriginale.com/feed/ | grep -oiE '<generator>[^<]*</generator>'

# Plugin updates on the CLONE via WP-CLI over SSH — requires a provisioned clone (none currently — ADR 0001); NOT core, keep WP at 7.0 for parity
ssh -i clone_key <user>@<host> 'wp plugin list --update=available --fields=name,version,update_version'
ssh -i clone_key <user>@<host> 'wp plugin update sitepress-multilingual-cms wpml-string-translation \
  wpml-media-translation woocommerce-multilingual wp-seo-multilingual wordpress-seo'

# Issues / PRs
gh issue list  --repo jdaviddenman/impressionoriginale
gh pr create   --base main --head <branch> --title "…" --body "…"
```

## Repo Layout

- `README.md` — audit overview, ranked findings, remediation workflow, status log.
- `docs/hreflang-fix.md` — Issue #1, **corrected to not-a-defect** (hreflang is valid via sitemap).
- `docs/analytics-ga4-migration.md` — Issue #3 (obsolete UA cleanup; GA4 already live).
- `docs/title-meta-rewrites.md` — keyword-first EN + FR titles/meta, copy-paste ready.
- `harness/fingerprint.sh` — before/after regression harness (server HTML; no JS).
- `reports/` — clone baseline + before/after diffs (added as work lands).

## Known Gotchas

- **Cloudflare** fronts live and bot-challenges scripted fetches on deeper pages (403 / challenge). Homepage + category pages usually pass; product pages may not. A clone (UpdraftClone infra), when provisioned, is not behind Cloudflare — fetch it freely. (None currently — ADR 0001.)
- **JS-injected tags are invisible to `curl`.** The GA4 tag fires inside the GTM container at runtime, so an external HTML fetch shows only the static UA tag. Confirm runtime tags in the GTM/GA4 UI, not just the page source. (This is why Issue #3 first looked like "no GA4" — it was hiding in GTM.)
- **WP Engine + WP Rocket = two caches.** Clear both after a change or the re-check reads a stale copy.
- **WPML premium updates are domain-locked.** `wp plugin update` for WPML plugins may fail to fetch on a clone (different domain / registration); Yoast (wordpress.org) updates cleanly regardless. (Confirmed on a prior clone run: WPML core/String/Media downloads returned error pages; Yoast + WooCommerce ML updated fine — no clone currently, ADR 0001.)
- **hreflang lives in the SITEMAP here, not the head.** WPML SEO 2.2.2+ moved hreflang from `<head>` into the XML sitemap by design. An empty head is expected and correct — Google supports sitemap hreflang equally. A prior audit wrongly flagged "hreflang missing" from a head-only check and nearly ran an unnecessary live update. **Lesson (general): before escalating a claimed defect, verify the signal against EVERY location it can legitimately live, and confirm the tool's current behaviour — not an assumed one.** A footgun check before the risky live change is what caught it.
