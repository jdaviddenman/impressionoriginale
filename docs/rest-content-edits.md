# Runbook — Fix text/spelling in live posts & pages via WP REST API (Tier 1)

**Issue #37.** Task class: **spelling / small copy corrections in existing content.** Not a new feature, not a layout change.

Chosen path: **plain WordPress REST API, no MCP server.** Rationale is in the research thread — a spelling sweep is a handful of targeted, reversible edits; a full MCP admin bridge is an always-on admin surface this task doesn't need (RULE 8, simplicity). If content editing becomes a recurring workflow, escalate to Tier 2 (official `WordPress/mcp-adapter` + `wordpress-mcp` plugin, HTTP transport, Editor-scoped token). This runbook is Tier 1 only.

Blast radius: **low, reversible.** A body-text spelling fix is content, externally verifiable, instantly revertible → per **RULE 1** it MAY go **direct to live**; no clone required (none exists — ADR 0001). It does **not** touch layout, cart, or checkout.

---

## Security & credentials (read first — plain sentences)

The REST calls below authenticate as a WordPress user with an **Application Password**. Do **not** use the primary administrator account for this.

- Create a **dedicated user with the Editor role** (capabilities: `edit_posts`, `edit_pages`, `edit_others_posts` — content only, no plugin/theme/settings access). This caps the blast radius to content if the credential leaks.
- Generate its **Application Password** at wp-admin → Users → that user → *Application Passwords*. Application Passwords are WP core (5.6+) and work over HTTPS only.
- The username and app password live in the **private note** and your local shell env — **never** in this public repo, an issue, or a commit (**RULE 7**). Below they are referenced as `$WP_USER` and `$WP_APP_PASS`.

```bash
# set once per shell session, from the private note — do not echo, do not commit
read -rp 'WP_USER: ' WP_USER
read -rsp 'WP_APP_PASS: ' WP_APP_PASS; echo
SITE=https://www.impressionoriginale.com
AUTH="Authorization: Basic $(printf '%s:%s' "$WP_USER" "$WP_APP_PASS" | base64 -w0)"
```

---

## The loop (per corrected page)

Do one page at a time. Each step verifies before moving on (**RULE 4**).

### 1. Find the ID and post type

Body copy lives in a **post** or **page**. Find the ID by search (or read it from the wp-admin edit URL `…post=<ID>`):

```bash
curl -s "$SITE/wp-json/wp/v2/pages?search=<distinctive%20words>&per_page=5" \
  -H "$AUTH" | grep -oE '"id":[0-9]+|"link":"[^"]+"'
# swap 'pages' -> 'posts' for blog posts
```

- **WooCommerce products / category descriptions are NOT `wp/v2/posts`.** Product descriptions edit via the WC REST API (`/wp-json/wc/v3/products/{id}`) with a read/write WooCommerce key, or via wp-admin. Category/term descriptions: `/wp-json/wc/v3/products/categories/{id}`. Those are out of scope for this runbook — edit them in wp-admin or open a follow-up.

### 2. Read the RAW block markup — `context=edit` is mandatory

```bash
ID=<id>; TYPE=pages   # or posts
curl -s "$SITE/wp-json/wp/v2/$TYPE/$ID?context=edit" -H "$AUTH" \
  > "reports/rest-edits/$TYPE-$ID.orig.json"
```

`context=edit` returns `content.raw` — the real Gutenberg block markup (`<!-- wp:… -->` delimiters). **Without it the API returns `content.rendered` (HTML with the block delimiters stripped); saving that back corrupts every block on the page.** This `.orig.json` file is also the rollback artifact (step 5).

### 3. Make the SURGICAL fix

Change **only** the misspelled token; preserve all block markup, attributes, and whitespace exactly (**RULE 8**, surgical). Extract raw → replace only the typo → keep everything else byte-for-byte.

```bash
# example: single misspelling, unique in the doc. Inspect the match first.
jq -r '.content.raw' "reports/rest-edits/$TYPE-$ID.orig.json" | grep -n 'realyl'   # confirm the typo + its context
jq -r '.content.raw' "reports/rest-edits/$TYPE-$ID.orig.json" \
  | sed 's/realyl/really/g' > "reports/rest-edits/$TYPE-$ID.fixed.txt"
diff <(jq -r '.content.raw' "reports/rest-edits/$TYPE-$ID.orig.json") \
     "reports/rest-edits/$TYPE-$ID.fixed.txt"   # MUST show only the intended line(s)
```

If the misspelled string is not unique (appears in an unrelated word), do not blind-`sed` — target the exact phrase or edit in the block editor instead.

### 4. Write it back

```bash
jq -n --rawfile c "reports/rest-edits/$TYPE-$ID.fixed.txt" '{content:$c}' \
  | curl -s -X POST "$SITE/wp-json/wp/v2/$TYPE/$ID" \
      -H "$AUTH" -H 'Content-Type: application/json' --data @- \
  | jq '{id, modified, status:.status}'
```

A `200` with an updated `modified` timestamp is **necessary but not sufficient** — REST can return `200` and silently no-op (caching, capability, or a nonce-gated field). Proof is the external fetch in step 5, not this response.

### 5. Purge both caches, then verify externally (RULE 5)

WP Engine page cache **and** WP Rocket both sit in front of the HTML — purge both or the re-check reads a stale copy.

```bash
# after purging WPE cache (wp-admin bar / WPE portal) AND WP Rocket (Settings → WP Rocket → Clear cache)
URL="$SITE/<the-page-path>/"
curl -sL "$URL" | grep -ci 'realyl'   # EXPECT 0  (old spelling gone)
curl -sL "$URL" | grep -ci 'really'   # EXPECT >=1 (correction rendered)
curl -sL -o /dev/null -w '%{http_code}\n' "$URL"   # EXPECT 200
```

### 6. WPML — the French copy is a SEPARATE post

WPML stores each language as its own post with its own ID. **Editing the EN post does not change the FR page.** If the same typo (or its FR equivalent) exists on `/fr/…`, repeat steps 1–5 against the **FR post's ID** (find it in wp-admin on the FR translation, or add `&lang=fr` to the step-1 search). Verify the FR URL separately.

---

## Rollback (RULE 3)

Content edits are captured by WP's post revisions (wp-admin → edit → Revisions → restore). Independently, the `reports/rest-edits/$TYPE-$ID.orig.json` from step 2 is a full snapshot — revert by POSTing its `content.raw` back:

```bash
jq '{content:.content.raw}' "reports/rest-edits/$TYPE-$ID.orig.json" \
  | curl -s -X POST "$SITE/wp-json/wp/v2/$TYPE/$ID" -H "$AUTH" \
      -H 'Content-Type: application/json' --data @-
```

If a change ever degrades a page, **restore first, diagnose second.**

---

## Acceptance criteria (machine-checkable)

Per corrected URL, after both caches are purged:

- **Done when:** `curl -sL <URL> | grep -ci '<misspelling>'` outputs `0` **and** `curl -sL <URL> | grep -ci '<correction>'` outputs `≥1`.
- **No regression:** `curl -sL -o /dev/null -w '%{http_code}' <URL>` outputs `200`; `./harness/fingerprint.sh` diff shows same 200s, no new PHP errors, no shortcode leakage, headings intact.
- **FR parity:** if the page has an FR translation with the same defect, its `/fr/…` URL passes the same two greps.

A green "updated" from the REST response or the block editor is **not** acceptance — only the external re-fetch is.

---

## Known gotchas (site-specific)

- **`context=edit` or you break blocks** — see step 2. The single most important line in this runbook.
- **Two caches** (WPE + WP Rocket) — purge both before re-checking or you read stale HTML.
- **Cloudflare** fronts live and may 403/challenge scripted requests on deeper pages. The REST API and homepage/category pages usually pass; if a product-tier path challenges, fall back to editing in the wp-admin block editor. (Gotcha carried from CLAUDE.md.)
- **Products ≠ posts** — WooCommerce product/category text is not on `wp/v2/posts`; use the WC REST API or wp-admin (see step 1).
- **Not the admin account** — Editor-scoped Application Password only (see Security).
