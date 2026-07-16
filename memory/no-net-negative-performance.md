---
name: no-net-negative-performance
description: Any change causing visual/LCP/load-time/perceived-speed regression is immediately rolled back and the approach permanently ruled out. Hard gate from 2026-07-16 postmortem.
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 8fe8ee67-16da-45fb-a561-61a5d894254b
---

# No Net-Negative Performance Changes

**Rule:** Any change that results in a net loss in visual rendering, LCP, load time, or perceived speed of the page gets immediately canceled, rolled back, and ruled out as a potential path forward.

**Why:** The 2026-07-16 LCP fix session produced 10+ changes, each worse than the last. LCP went from 4.9s → 20.4s → 20.4s. The site went from "hero image loads on scroll" to "completely blank white page." Every "fix" was a regression.

O optimized for "does the HTML contain my CSS?" instead of "does the page render correctly for users?" The only metric that matters is user-perceived performance — not whether a curl shows the right bytes.

**How to apply:**
1. Before any change, capture baseline: LCP from Lighthouse, visual screenshot if possible, `curl -sI | cf-cache-status`.
2. After any change, re-measure same metrics.
3. If ANY metric degraded → immediate rollback of that specific change.
4. The rolled-back approach is permanently ruled out. Do not iterate on it. Do not "fix the fix." Find a different path.
5. A change that improves one metric but degrades another is a net-negative and gets rolled back.

**Examples of net-negative changes (from 2026-07-16):**
- Output buffer fixed lazy-load for LCP image but caused ALL 10 slider images to load eagerly → LCP regressed 4.9s→20.4s → ROLLBACK
- RUCSS enabled for performance but stripped inline CSS → H1 invisible → ROLLBACK
- `_HOME-`/`_HOME_` exclusion would have fixed lazy-load but excluded ALL slider images → same 5MB payload problem → RULED OUT
- Deleting RUCSS DB table cleared stale CSS but left page with zero CSS → completely blank → IMMEDIATE RUCSS DISABLE

**Related:** [[lcp-fix-session-postmortem]], [[lcp-31s-root-cause-opacity-translatex]], [[lcp-image-lazy-load-scroll-fix]], ADR 0007, RULE 26.
