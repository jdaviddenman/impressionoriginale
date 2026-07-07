---
name: wpe-ssh-slow-handshake
description: "WPE SSH/WP-CLI works; the gateway handshake takes ~20-30s — short ConnectTimeout false-negatives as \"key unauthorized\". Give it 30s+."
metadata: 
  node_type: memory
  type: reference
  originSessionId: b556a2b5-30b6-4b06-b55f-000b286ca894
---

WP-CLI write path to prod IS available.

```
ssh -i ~/.ssh/id_ed25519 -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net 'wp ...'
```

Key: `~/.ssh/id_ed25519` (SHA256:nmo2Pj3RlA65NqHSRZ2HQyE46LsfJd20DZ871JB8VZk, MD5: `e6:86:6d:38:cb:22:0c:ee:87:ef:a2:af:bf:14:42:48`), authorized on `impressionor` install.

**GOTCHA 1 (hostname):** `impressionor.ssh.wpengine.net` — NOT `impressionoriginale.ssh.wpengine.com`. Never derive the SSH hostname from the public domain. See [[never-derive-connection-strings]] and CLAUDE.md RULE 14.

**GOTCHA 2 (latency):** The WPE SSH gateway handshake takes ~20–30s to complete. A tight bound (`ConnectTimeout=8`, outer `timeout 15`) kills it mid-negotiation → `exit 124`/`Terminated`, which looks identical to auth failure. O twice concluded "key not authorized / no write access" from this before probing correctly — a RULE 10 false-negative-capability miss (see [[feedback-verify-ground-state]]).

**Do:** `ConnectTimeout=60`, outer `timeout 120`. Verify with `wp option get siteurl` before claiming blocked. TCP 22 open ≠ working session; a completed `wp` round-trip is.

Editing WPBakery pages via CLI: `wp post get <ID> --field=content > backup.txt` (rollback artifact), edit `post_content`, `wp post update <ID> -` from STDIN, then `wp cache flush` + `wp page-cache flush` (WP Rocket CLI not installed — it auto-purges the edited URL on save_post). where-to-find-us = post 3910.

Editing WPBakery pages via CLI: `wp post get <ID> --field=content > backup.txt` (rollback artifact), edit `post_content`, `wp post update <ID> -` from STDIN, then `wp cache flush` + `wp page-cache flush` (WP Rocket CLI not installed — it auto-purges the edited URL on save_post). where-to-find-us = post 3910.
