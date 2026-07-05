# ADR 0001 — No clone / test bench exists

- **Status:** Accepted
- **Date:** 2026-07-05
- **Supersedes the clone-dependent parts of:** CLAUDE.md RULE 1, RULE 3, RULE 4; the `ssh -i clone_key …` and `fingerprint.sh https://<clone>.updraftclone.com` commands.

## Context

CLAUDE.md and the project doctrine repeatedly assume an isolated UpdraftPlus / UpdraftClone matched to live (PHP 8.2 / WP 7.0 / WC 10.7) as the test bench for risky changes. **No such clone exists.** None is provisioned, and there is no `clone_key` / SSH access to one. Because CLAUDE.md is loaded every session and states the clone as present, agents keep re-asserting it and proposing "prove it on the clone first" steps that cannot be executed.

## Decision

Record as a standing fact: **there is no clone.** Any instruction that routes a change "through the clone" is currently unexecutable. Until a clone is deliberately stood up, treat the clone step as **unavailable**, not assumed-present.

## Consequences

- RULE 1's clone-first gate cannot be satisfied. High-blast-radius changes (plugin / core / theme updates, anything with layout or checkout blast radius) have **no test bench** and must be either **(a)** deferred until a clone is provisioned, or **(b)** explicitly risk-accepted by the operator for that specific change — never done silently.
- Reversible, externally-verifiable, low-blast-radius changes (title/meta, a tag ID, a Yoast toggle) remain safe to do directly on live — exactly RULE 1's existing exception.
- The `ssh -i clone_key <user>@<host> …` and `fingerprint.sh https://<clone>.updraftclone.com` commands are inert until a clone exists.
- **CLAUDE.md should be reconciled** to stop stating the clone as present. Pending that edit, **this ADR is the source of truth**; where CLAUDE.md and this ADR disagree about the clone, this ADR wins.

## Open question

If a genuinely risky change becomes necessary, is the plan to stand up a fresh UpdraftClone on demand, or to avoid such changes entirely? Not decided here.
