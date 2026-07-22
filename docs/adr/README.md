# Architecture Decision Records (ADRs)

Durable decisions and standing facts for this workspace — the **running tally** so they aren't forgotten between sessions. CLAUDE.md is injected every session and is treated as ground truth; when reality diverges from it, an ADR here records the correction and is the source of truth until CLAUDE.md is reconciled.

Rules:

- One decision/fact per ADR.
- When a decision changes, add a **new** ADR that supersedes the old one and mark the old one `Superseded` — don't silently rewrite history (mirrors CLAUDE.md RULE 2: corrections are first-class).
- Mirror each ADR's one-line takeaway into the auto-memory index (`~/.claude/projects/-home-james-MKO/memory/MEMORY.md`) so it surfaces at session start.

## Log

| ADR | Title | Status | Date |
|-----|-------|--------|------|
| [0001](0001-no-clone-test-bench.md) | No clone / test bench exists | Accepted | 2026-07-05 |
| [0002](0002-risky-changes-on-demand-clone.md) | Risky changes: default-avoid + on-demand clone | Accepted | 2026-07-05 |
| [0003](0003-i18n-url-strategy-en-default-at-root.md) | i18n URL strategy: EN-default-at-root is by design | Accepted | 2026-07-05 |
| [0004](0004-env-banner-unreliable-verify-git.md) | Env "is a git repo" banner unreliable — verify cwd with `git rev-parse` | Accepted | 2026-07-05 |
| [0014](0014-indexnow-setup.md) | IndexNow auto-submit for instant search engine indexing | Accepted | 2026-07-21 |
| [0016](0016-font-preloads-broke-site.md) | CDN font preloads broke the site, permanently ruled out | Accepted | 2026-07-22 |

## Format

Minimal [MADR](https://adr.github.io/madr/). Sections: **Context**, **Decision**, **Consequences**. Status ∈ `Proposed` / `Accepted` / `Superseded`.
