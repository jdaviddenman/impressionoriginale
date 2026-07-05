# Playwright MCP — wp-admin Setup Runbook

**Date:** 2026-07-05
**Prerequisite:** `.mcp.json` with Playwright MCP config exists in project root
**Goal:** Save a persistent wp-admin auth state so Claude can autonomously edit content behind Cloudflare/WPE (which strips `Authorization` headers)

## Why this works

Cloudflare/WPE strips the `Authorization` header (proven 2026-07-05 dry run). All REST API / plugin MCP servers authenticate via that header → dead.

Playwright MCP drives a real Chromium browser. It logs into wp-admin with cookie auth, exactly like a human. Cloudflare sees a normal browser session. The `Authorization` header is never involved.

## Setup (one-time, ~5 minutes)

### Pre-check

Playwright binaries must be installed. In the project root:

```bash
npx playwright --version   # should print Version 1.61.1
```

If missing: `npx playwright install chromium`

### Session 1: Save the auth state

Start a new Claude Code session (Playwright MCP loads from `.mcp.json` at session start). Then tell Claude:

> Using Playwright MCP, navigate to https://www.impressionoriginale.com/wp-admin and log in with the wp-admin credentials. Once logged in (you should see the Dashboard heading), call browser_storage_state to save the session. Confirm the file was written to wp-auth.json.

Expected flow:
1. `browser_navigate` to `/wp-admin`
2. `browser_snapshot` → sees login form (username, password, "Log In" button)
3. `browser_type` credentials into the fields
4. `browser_click` the "Log In" button
5. `browser_snapshot` → confirms "Dashboard" heading — logged in
6. `browser_storage_state` → writes `wp-auth.json` to project root

### Session 2+: Autonomous editing

Subsequent sessions auto-load `wp-auth.json` (the `--storage-state=./wp-auth.json` flag in `.mcp.json`). Claude is already logged in on startup. Example commands:

- "List all pages on the site via wp-admin"
- "Edit page 'About Us' — fix the spelling of 'recieve' → 'receive'"
- "Check the Yoast SEO title for the homepage"
- "Take a screenshot of the French homepage"

**Watch for:** WordPress auth cookie expiry. If Claude hits a login screen mid-session, the cookie expired — re-run the Session 1 login flow. WordPress auth cookies typically last 48 hours if "Remember Me" is checked, 2 hours otherwise.

### Security: dedicated Editor user

The saved cookies grant whatever capabilities the WordPress user has. Create a **dedicated Editor-scoped user** (content edit only, no plugin/theme/settings access) and log in with that account — same principle as the REST runbook's security model (`docs/rest-content-edits.md`).

This caps the blast radius if `wp-auth.json` leaks. The file is gitignored below.

### Gitignore

`wp-auth.json` contains session cookies. Never commit it.

```bash
echo "wp-auth.json" >> .gitignore
```

## Troubleshooting

| Symptom | Likely cause | Fix |
|---|---|---|
| Playwright tools not available | `.mcp.json` not loaded | Restart Claude Code session |
| `browser_navigate` hangs | Playwright binary missing | `npx playwright install chromium` |
| Login form submits but redirects to login again | Wrong credentials or Cloudflare challenge | Check credentials; try with `--headless` off to see the browser |
| `browser_storage_state` fails | `--caps=storage` not in config | Verify `.mcp.json` has `"--caps=storage"` |
| Claude sees login form after restart | `wp-auth.json` expired or missing | Re-run Session 1 login flow |
| Cloudflare challenge page | Bot detection on headless Chrome | Add `--browser=chromium` or try non-headless mode |

## MCP config reference

```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": [
        "@playwright/mcp@0.0.76",
        "--isolated",
        "--caps=storage",
        "--storage-state=./wp-auth.json"
      ]
    }
  }
}
```

- `--isolated` — fresh browser profile each session (auth state loaded from file, not disk profile)
- `--caps=storage` — enables `browser_storage_state`, `browser_cookie_*`, `browser_localstorage_*` tools
- `--storage-state=./wp-auth.json` — auto-loads this file at startup if it exists
- Version pin `@0.0.76` prevents tool disappearance from breaking changes
