# ADR 0002 — Risky changes: default-avoid, on-demand clone when forced

- **Status:** Accepted
- **Date:** 2026-07-05
- **Answers the open question in** [ADR 0001](0001-no-clone-test-bench.md).

## Context

ADR 0001 recorded that no clone is provisioned and left open: when a genuinely risky change (plugin / core / theme update, or anything with layout or checkout blast radius) becomes necessary, is the plan to **stand up a fresh UpdraftClone on demand**, or to **avoid such changes entirely**?

"Avoid entirely" is not viable for a live commerce store. Security disclosures against WordPress, WooCommerce, WPML, the Stripe gateway, or PHP will eventually force an update. Freezing the stack converts a controlled-update risk into an unpatched-vulnerability risk on a system that handles payments — a higher-blast-radius exposure (data / payment compromise) than a recoverable layout break. A mechanism to apply risky changes safely must therefore exist, and the two paths are not mutually exclusive.

## Decision

Two-part policy.

1. **Default posture — minimize.** Don't make risky changes casually. Reversible, externally-verifiable, low-blast-radius changes (title/meta, tag IDs, Yoast toggles) continue direct to live under RULE 1's existing exception. Batch and defer non-urgent risky changes rather than trickling them one at a time.
2. **When a risky change is genuinely necessary** (security patch, a required compatibility bump, an unavoidable plugin/core/theme update): stand up a fresh, **ephemeral** UpdraftClone on demand, matched to live (PHP 8.2 / WP 7.0 / WC 10.7). Prove the change there — apply it, then `harness/fingerprint.sh` diff live vs clone and confirm no regression. Take RULE 3 backups (WP Engine point **and** UpdraftPlus). Apply to live, verify from outside (RULE 5). **Tear the clone down afterward** — it is disposable, not a standing environment.

**Trigger:** any change that fails RULE 1's reversibility / external-verifiability test requires the on-demand clone before it touches live.

## Consequences

- Restores RULE 1's clone-first gate as the intended path, but **ephemeral / on-demand** rather than assumed-standing — consistent with ADR 0001's "not provisioned" reality and RULE 7's keep-the-attack-surface-small posture.
- **Adds lead time.** An urgent security fix is not instant: provisioning and proving the clone is a prerequisite step. Plan timelines with that step in them; do not skip it because the fix "looks safe."
- **Depends on UpdraftClone provisioning actually being available** (Updraft account, target infra, SSH access). Those details live in the private note (RULE 7), not here. If provisioning turns out to be impossible when first needed, that is a blocker to record as a follow-up ADR — not a reason to push a risky change blind to live.
- ADR 0001's open question is resolved; ADR 0001 stays `Accepted`.
