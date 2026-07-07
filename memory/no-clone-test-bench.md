---
name: no-clone-test-bench
description: "There is NO clone / test bench — CLAUDE.md's clone-first gate is unexecutable; never propose \"prove it on the clone first\"."
metadata: 
  node_type: memory
  type: project
  originSessionId: d5fc041d-a099-4380-9bd0-41e8113f9be0
---

There is **no clone / UpdraftClone test bench**, and no `clone_key` SSH access to one, despite CLAUDE.md RULE 1/3/4 and the "Common Commands" assuming one exists. Recorded in `docs/adr/0001-no-clone-test-bench.md` (this ADR is the source of truth where it disagrees with CLAUDE.md).

**Why:** CLAUDE.md is injected every session and states the clone as present, so agents keep re-asserting it and proposing unexecutable "test it on the clone first" steps.

**How to apply:** no *standing* clone. Policy (ADR 0002, `docs/adr/0002-risky-changes-on-demand-clone.md`): default-avoid risky changes; reversible, externally-verifiable changes go direct to live; when a risky change is *forced* (security patch, unavoidable update), stand up an **ephemeral on-demand UpdraftClone**, prove it there (fingerprint diff + RULE 3 backups), apply to live, then tear the clone down. Never route a change through a clone that isn't currently up, and never push a forced risky change blind to live because spinning one up adds lead time. See [[verify-live-tags-with-tag-assistant]].
