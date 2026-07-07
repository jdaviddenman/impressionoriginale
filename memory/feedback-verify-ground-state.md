---
name: feedback-verify-ground-state
description: "Verify environment facts with a command before asserting — negative/state claims (\"not a repo\", \"no access\", \"missing\") need evidence like success claims. Don't trust the env banner or memory. ADR 0004 / MKO CLAUDE.md RULE 10."
metadata: 
  node_type: memory
  type: feedback
  originSessionId: aa36b243-0561-4b58-a0a4-e6abfdbc44e8
---

O trusted the session `Is a git repository: false` banner and repeatedly asserted `/home/james/MKO` was not a repo. It **is** — origin `jdaviddenman/impressionoriginale`, `.git` on disk. O skipped branch/PR and mis-flagged committed docs as "not in the repo" off that false premise. Operator: tired of memory hallucinations; always verify.

**Why:** confidence carries zero information about correctness (global F4 / infra RULE 12). A negative or state claim — "not a repo", "no access", "blocked", "file missing", "tag not firing" — is a claim exactly like "it's fixed"; it is the failure-side twin of MKO RULE 5. Injected context (env banner, recalled memory, prior assumption) is not ground truth; it can be stale or wrong. This imports the infra `feedback_verify_before_claiming_blocked` *memory* lesson, which was never in the infra CLAUDE.md MKO adapted — so MKO never had it.

**How to apply:** (1) Before any repo-state claim, probe the cwd: `git -C <cwd> rev-parse --git-dir && git remote -v` (not `--is-inside-work-tree`, which false-negatives on bare repos) — do not read the banner. (2) Generally, run the read that would *disprove* the fact before asserting it. (3) When the operator pushes back, re-verify against live state — never double down from the same assumption. (4) No probe ⇒ say "unverified", not a confident assertion. See [[no-clone-test-bench]], MKO CLAUDE.md RULE 10, `docs/adr/0004-env-banner-unreliable-verify-git.md`.
