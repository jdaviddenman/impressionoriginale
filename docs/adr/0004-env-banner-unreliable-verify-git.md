# ADR 0004 — The session "Is a git repository" banner is unreliable; verify the cwd with `git rev-parse`

- **Status:** Accepted
- **Date:** 2026-07-05
- **Related:** CLAUDE.md **RULE 10** (new) and RULE 5; global `CLAUDE.md` F4 (confident hallucination); infra memory `feedback_verify_before_claiming_blocked`.

## Context

The session environment banner reported **`Is a git repository: false`** for the
primary working directory `/home/james/MKO`. That claim was repeated as fact
across two turns — "not a git repo → no branch/PR needed", and the ADR docs
were flagged as "only on-disk, not in the GitHub repo" — and conclusions were
built on it, all without a probe.

A direct check disproved it:

```
git -C /home/james/MKO rev-parse --is-inside-work-tree   -> true
git -C /home/james/MKO remote -v
    origin  https://github.com/jdaviddenman/impressionoriginale.git (fetch/push)
ls -la /home/james/MKO/.git                              -> exists
```

`/home/james/MKO` **is** the checkout of `jdaviddenman/impressionoriginale`. The
banner was wrong (or stale relative to when `.git` came to exist). The failure
was **inference from provided context instead of a one-command probe** — the
same class as the infra `feedback_verify_before_claiming_blocked` *memory*
lesson ("remember how confident you sound when you are wrong"), which lived only
as an infra memory — never in the infra CLAUDE.md this repo adapted, so MKO
never carried it.

## Decision

Treat the `Is a git repository` env banner as **advisory, not authoritative.**
Before any claim or decision that depends on repository state, verify by probing
the cwd:

```
git -C <cwd> rev-parse --git-dir && git -C <cwd> remote -v
```

(`--git-dir` exits 0 inside any repo — bare, subdir, or linked worktree; prefer
it to `--is-inside-work-tree`, which returns `false` in a bare repo, and to a
literal `.git` check, which is a file in worktrees/submodules and absent from a
subdir.)

Generalized as CLAUDE.md **RULE 10** — verify the ground state; don't infer it
from the banner, memory, or assumption. Negative / state claims ("not a repo",
"no access", "file missing", "tag not firing") are claims under RULE 5 and need
disproving-read evidence exactly like a "fixed / done" success claim.

## Consequences

- A repo-state claim with no probe is **"unverified"** — don't act on it. The
  banner does not close the question; `git rev-parse --git-dir` does.
- Restores into this workspace the infra verify-before-claiming-blocked
  doctrine (the failure-side twin of RULE 5; the transferable half of infra
  RULE 7 — check the fact at its authoritative source).
- The ADR 0003 work was correctly branched and PR'd (#35) only *after* the
  banner was disproven; the initial "no PR needed" stance is the specific error
  this ADR exists to prevent recurring.
