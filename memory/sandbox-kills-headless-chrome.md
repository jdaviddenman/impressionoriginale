---
name: sandbox-kills-headless-chrome
description: "The Bash tool sandbox kills node-spawned/background headless Chrome (exit 144, signal 16); use the Playwright MCP for live perf/render measurement instead."
metadata: 
  node_type: memory
  type: reference
  originSessionId: 79701f7f-7f3b-4a44-b928-0427f4ba766b
---

This agent's Bash execution sandbox kills sustained headless Chrome processes:
- node spawning Chrome → process group killed, exit **144** (signal 16), often before any stdout flushes (empty output).
- Chrome launched directly as a **background** Bash task → killed immediately, empty output.
- Chrome in the **foreground** survives only while it streams output continuously and is short-lived.
- `--disable-dev-shm-usage` makes the kill fire faster/harder; without it, the renderer instead crashes (`Inspector.targetCrashed`) on heavy pages (small `/dev/shm`). Both are sandbox artifacts — the flag is standard/correct in normal envs.

Consequence: `harness/perf-timing.mjs` (self-contained CDP timing harness) is architecturally correct and its pieces are verified (parse OK, args OK, CDP round-trips in probes, Chrome launches standalone), but it **cannot be run end-to-end inside this sandbox**. It runs fine in a normal shell / CI.

**Working alternative in-session:** the **Playwright MCP** browser (persistent server outside the Bash sandbox) drives live Chrome fine — used it to get real LCP/FCP/TTFB/transfer/RevSlider numbers off the homepage. Reach for `mcp__playwright__*` for any live render/perf/JS-tag measurement, not a bash-spawned Chrome. Pairs with [[verify-live-tags-with-tag-assistant]].
