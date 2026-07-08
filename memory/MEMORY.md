# Memory Index

- [CLAUDE.md rules are hard gates, not suggestions](claude-md-rules-are-hard-gates.md) — every rule applies every turn; self-check before acting. Feedback.
- [No clone / test bench](no-clone-test-bench.md) — there is NO clone; CLAUDE.md's clone-first gate is unexecutable; never propose "prove it on the clone". ADR 0001.
- [Verify live tags with Tag Assistant](verify-live-tags-with-tag-assistant.md) — check firing tags via the stable tagassistant.google.com URL, not churn-prone GA4/GTM click-paths.
- [i18n: EN default at root](i18n-en-default-at-root.md) — no /en/ URLs is by design (WPML EN-default-at-root), not a defect; don't re-audit. ADR 0003.
- [Verify ground state, don't infer](feedback-verify-ground-state.md) — probe cwd for `.git` / run the disproving read before any env/state claim; the env banner + memory aren't ground truth. RULE 10 / ADR 0004.
- [RULE 9 — terse is a hard prohibition](rule-9-terse-hard-prohibition.md) — prose, fluff, fake emotion, tables-when-lines-would-do all banned; self-check every response.
- [obflink nav obfuscation is intentional](obflink-nav-obfuscation-intentional.md) — "uncrawlable links" = deliberate obflink crawl-budget/PageRank sculpting + noindex, not a defect; don't strip. #48 closed.
- [WPE SSH slow handshake](wpe-ssh-slow-handshake.md) — WP-CLI prod access WORKS; gateway handshake ~20-30s, short ConnectTimeout false-negatives as "unauthorized". Use ConnectTimeout=30+.
- [Sandbox kills headless Chrome](sandbox-kills-headless-chrome.md) — Bash sandbox kills node-spawned/bg headless Chrome (exit 144); use Playwright MCP for live perf/render measurement. harness/perf-timing.mjs runs only in a normal shell.
- [WooCommerce outdated template overrides](woocommerce-outdated-template-overrides.md) — Engic theme has 10 flagged-stale WC template overrides (+ unflagged checkout ones); live WC is >=10.9 (CLAUDE.md 10.7 stale).
- [Yoast title/meta write mechanism](yoast-titlemeta-write-mechanism.md) — render source = indexable not postmeta; naive clear API fatals (delete row instead); empty metadesc = NO tag; templates shared EN+FR. Reference.
