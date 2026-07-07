---
name: never-derive-connection-strings
description: "Never infer SSH/API hostnames from public domains — probe memory first. Upstream of 5 WPE SSH timeouts."
metadata:
  type: feedback
---

Before any external connection (SSH, API, DB), grep the memory directory for stored hostnames and credentials. Never infer a connection string from the public domain name.

**Why:** Hosting providers routinely use install identifiers that differ from the public domain. The WPE SSH hostname is `impressionor.ssh.wpengine.net`, not `impressionoriginale.ssh.wpengine.com`. Five SSH timeouts were wasted deriving the hostname from the domain instead of reading [[wpe-ssh-slow-handshake]], which had the correct connection string from the prior day's session.

**How to apply:** Before any SSH attempt: `grep -r 'ssh.wpengine\|@.*\.ssh\.' /home/james/.claude/projects/-home-james-MKO/memory/`. If that returns nothing, widen to `find ~/.ssh/` and `ssh-add -l`. Do not fire the first SSH attempt blind.

Encoded as CLAUDE.md RULE 14. Related: [[wpe-ssh-slow-handshake]], [[feedback-verify-ground-state]].
