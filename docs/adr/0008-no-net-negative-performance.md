# ADR 0008 — No Net-Negative Performance Changes

**Date:** 2026-07-16
**Status:** Active

## Decision

Any change that results in a net loss in visual rendering, LCP, load time, or perceived page speed is immediately rolled back and the approach is permanently ruled out.

## Rationale

The 2026-07-16 LCP fix session (ADR 0007) demonstrated a persistent failure mode: O optimized for technical metrics ("CSS is in the HTML") while user-perceived performance degraded with every change.

| Change | LCP Before | LCP After | Visual | Net Effect |
|---|---|---|---|---|
| Output buffer strip lazy-load | 4.9s | 20.4s | CLS 0.32 appeared | **Negative** |
| RUCSS enabled + filter | 4.9s | 20.4s | H1 invisible | **Negative** |
| Delete RUCSS DB table | 20.4s | 20.4s | Page blank (no CSS) | **Negative** |
| CSS targeting wrong element | 20.4s | 20.4s | Still broken | **No improvement** |

Each change was deployed without baseline measurement, without before/after comparison, and without a rollback trigger on regression.

## Consequences

### CLAUDE.md RULE 26 — hard gate

Before any change: capture baseline. After any change: re-measure. Regression → immediate rollback + approach ruled out.

### No iteration on failed approaches

If a change makes things worse, do not "fix the fix." Roll back and find a fundamentally different path. The output buffer was the wrong approach — iterating on it (better regex, scoped matching) would have been the wrong direction.

### User-perceived metrics are the only metrics

Curl showing correct HTML is not verification. The user sees the rendered page, not the source. If O cannot measure rendering (no browser available), the change is "unverified" and stays that way.

## Related

- ADR 0007 — full postmortem of the session that produced this rule
- [[lcp-fix-session-postmortem]] — 10 mistakes catalog
- [[no-net-negative-performance]] — memory entry
- RULE 26 in CLAUDE.md
- RULE 5 (evidence for claims), RULE 24 (CDN verification), RULE 25 (one change at a time)
