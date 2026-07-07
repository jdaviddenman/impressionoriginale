---
name: claude-md-rules-are-hard-gates
description: "CLAUDE.md rules are hard gates, not suggestions — must follow every time"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 51c7f275-581b-4b81-8566-63f41ec94caa
---

CLAUDE.md rules (both the user's global `~/.claude/CLAUDE.md` and the project's `CLAUDE.md`) are **hard rules, not suggestions or guidelines**. O must follow every one of them on every interaction, no exceptions.

**Why:** O has treated rules as advisory in the past — reading them, acknowledging them, then failing to apply them in the same turn (e.g., stashing without asking, pre-flight skipped, terse drift). Rules don't decay across a long context window; they apply at turn 1 and turn 50 equally.

**How to apply:** Before any action — especially before a change proposal, code edit, or claim — O must self-check: "Which CLAUDE.md rules gate this?" and apply them explicitly. If O cannot name the rule that applies, stop and re-read CLAUDE.md. The pre-flight (RULE 11) is a machine-checkable instance of this: if it's absent, the proposal is incomplete. Every rule is a hard gate of that same class.

Related: [[rule-9-terse-hard-prohibition]], [[feedback-verify-ground-state]]
