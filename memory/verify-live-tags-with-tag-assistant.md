---
name: verify-live-tags-with-tag-assistant
description: "To check which analytics/GTM/marketing tags actually fire on a live site, start with Tag Assistant (stable URL), not GA4/GTM UI click-paths."
metadata: 
  node_type: memory
  type: feedback
  originSessionId: db75cdd6-e3fe-422a-b199-b518131a8b99
---

When verifying which analytics/GA4/GTM/marketing tags actually fire on a live website (e.g. "is UA gone? is GA4 double-counting? how many page_views?"), go straight to **Tag Assistant**: <https://tagassistant.google.com/> → **Add domain** → enter the site URL → **Connect**. It lists every tag firing on the page from one stable, URL-addressable entry point.

**Why:** GA4/GTM UI click-paths (Reports → Realtime, Admin → DebugView, container → Preview) churn constantly, are easy to describe from a stale menu layout, and are **gated** — a normal visit shows nothing in Realtime/DebugView until consent is accepted and debug-mode is on, which produced repeated false "0 / empty screen" confusion this session. Websites and vendor consoles change frequently, so click-paths go outdated fast; a tool's root URL is durable, a menu route is not.

**How to apply:** For any "which tags fire / how many times" question, recommend Tag Assistant (or the site's own HTML via `curl`) **first**, before walking anyone through a vendor's current menus. Generalize: prefer stable URL-based tools and direct evidence over UI click-paths; when a click-path is unavoidable, give the direct deep-link URL too and flag that menu labels may have moved. Pairs with the evidence-is-a-claim discipline — verify from a source that won't have silently moved.
