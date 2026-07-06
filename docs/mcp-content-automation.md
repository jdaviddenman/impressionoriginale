# MCP Content Automation — Research & Decision

**Date:** 2026-07-05
**Status:** researched, not yet tested
**Issue:** #37 / follow-up to `docs/rest-content-edits.md` (edge-blocked)

Goal: allow Claude to perform test modifications in the site's content — spelling fixes, meta edits, SEO toggles — without manual copy-paste into wp-admin.

## The `Authorization` header problem

The REST API path (Application Passwords) and all HTTP-transport MCP servers authenticate via the `Authorization` header. A live dry run on 2026-07-05 proved Cloudflare / WP Engine **strips this header** before it reaches PHP:

> identical `401 rest_not_logged_in` for correct password, deliberately wrong password, and explicit `Authorization: Basic` header

WordPress never sees the credential, so no Application Password can authenticate over HTTP here. This kills **every** MCP server that sends an `Authorization` header — Basic or Bearer, npm-based or plugin-based.

| MCP Server | Auth mechanism | Status |
|---|---|---|
| `@cmsmcp/wordpress` | `Authorization: Basic` (App Password) | Dead |
| `@respira/wordpress-mcp-server` | `Authorization: Basic` (App Password) | Dead |
| `@instawp/mcp-wp` | `Authorization: Basic` (App Password) | Dead |
| `@cochatai/mcp-wordpress` | `Authorization: Basic` (App Password) | Dead |
| `@node2flow/wordpress-mcp` | `Authorization: Basic` (App Password) | Dead |
| NIBWP (plugin) | `Authorization: Basic` (App Password) | Dead |
| StifLi Flex MCP (plugin) | `Authorization: Bearer` (OAuth 2.1) | Dead |
| Cowboy MCP (plugin) | `Authorization: Bearer` (API key) | Dead |
| Block MCP / GravityKit (plugin) | WordPress auth flow (likely Authorization header) | Probably dead |

The operator does not have WP Engine portal access, so the Cloudflare edge is not operator-fixable.

## What survives: two paths

### Path 1: Playwright MCP (browser automation) — recommended

Microsoft-maintained, Apache-2.0, free. Claude drives a real Chrome/Chromium browser. The browser logs into wp-admin normally (cookie auth). Cloudflare sees a regular browser session — the `Authorization` header is never involved.

**Why it fits:**
- Cookie auth = Cloudflare forwards everything
- 23 core tools + 6 optional capability packs (`storage`, `vision`, `devtools`, `testing`, `network`, `pdf`)
- `--storage-state` persists wp-admin login across sessions — login once, reuse indefinitely
- Version-locked: `@playwright/mcp@0.0.76` (pin to avoid tool disappearance mid-session)
- Proven: 2026 case study used it for wp-admin content pruning (133 posts, Yoast toggles, Redirection plugin)

**Setup (one-time):**
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
```bash
claude mcp add playwright npx @playwright/mcp@0.0.76 --isolated --caps=storage --storage-state=./wp-auth.json
```

**Login workflow (run once):**
1. Claude navigates to `https://www.impressionoriginale.com/wp-admin`
2. Fills wp-admin credentials, clicks Log In
3. Calls `browser_storage_state` → saves cookies + localStorage to `wp-auth.json`
4. All subsequent sessions auto-load `wp-auth.json` → already logged in

**Cost:** ~$0.34/edit task in MCP mode (~114K tokens), ~$0.08/task in CLI mode (~27K tokens). MCP for exploration/one-off edits; CLI for repeated execution.

**Coverage:** any wp-admin UI — posts, pages, products (WooCommerce), Yoast SEO settings, WPML translations, menus, Redirection plugin, Search Regex. Screenshots as audit proof (RULE 5).

**Risk:** full wp-admin access — Claude has the same capabilities as the WordPress user whose cookies are saved. Mitigation: create a dedicated Editor-scoped user (content only, no plugin/theme/settings access) — same principle as the `docs/rest-content-edits.md` security model.

### Path 2: Search Regex plugin (already proven)

Working path from `docs/spelling-fix-runbook.md`. Claude generates the regex pattern, operator pastes it into wp-admin → Search Regex → Replace. Cookie auth, zero edge issues.

**Scope:** post/page content text only. Not product descriptions, meta fields, or Yoast settings.

## Decision

**Playwright MCP** for autonomous content modifications. Setup is ~5 minutes one-time (plugin install not required — `npx` fetches everything). After auth-state is saved, Claude can navigate wp-admin, edit content, toggle Yoast settings, and take screenshots as evidence.

**Search Regex** remains the fallback for simple text sweeps if browser automation burns too many tokens.

**Next step:** smoke-test Playwright MCP with a read-only wp-admin action (list pages, no edits). If reads work, proceed to a draft-only write test.

## Candidates ranked (pre-research)

| Rank | Solution | Reason |
|---|---|---|
| 1 | **Respira** (plugin) | WPBakery support, duplicate-before-edit, snapshot rollback — but dead (Authorization header) |
| 2 | **AI Engine** (plugin) | 100K+ installs, 4.9★, 25 WooCommerce tools — but dead (Authorization header) |
| 3 | **Playwright MCP** | Works, cookie auth bypasses Cloudflare, any wp-admin page |
| 4 | **Search Regex** | Works, already proven, manual step only |

All plugin-based MCPs are blocked by the same edge header strip. Only cookie-auth paths survive.

## Sources

- [Playwright MCP](https://www.npmjs.com/package/@playwright/mcp) — Microsoft, Apache-2.0
- [Playwright MCP + Claude Code guide](https://site.builder.io/blog/playwright-mcp-server-claude-code)
- [Block MCP (GravityKit)](https://www.gravitykit.com/block-mcp-wordpress-plugin/)
- [Cowboy MCP](https://himcp.ai/server/cowboy-mcp)
- [StifLi Flex MCP](https://wordpress.org/plugins/stifli-flex-mcp/)
- [AICOM](https://wordpress.org/plugins/aicom/)
- [NIBWP](https://wordpress.org/plugins/nibwp/)
- [Respira WordPress MCP](https://himcp.ai/server/respira-for-wordpress-rse)
- [AI Engine (Meow Apps)](https://wordpress.org/plugins/ai-engine/)
- [Browser automation MCP comparison](https://dev.to/manja316/i-tested-5-browser-mcp-servers-heres-which-one-to-use-for-your-project-4gc6)
- [2026 content pruning case study with Playwright MCP + Claude Code](https://elite-strategies.com/content-pruning-case-study-2026/)
