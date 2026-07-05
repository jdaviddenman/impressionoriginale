# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working in this repository.

This repo is a **shared SEO audit & remediation workspace** for the WordPress/WooCommerce store at <https://www.impressionoriginale.com/>. It is **not** the site's code — the site lives on WP Engine. This repo holds findings, per-defect issues, fix runbooks, and a verification harness, so the site admin can see what was tested and exactly what to apply.

*Adapted from the `agent-aura/infra` CLAUDE.md doctrine. The transferable engineering rules are kept and reframed for this project; the infrastructure-specific rules (Ansible/Nomad/Vault quorum, paging, retry jitter) were dropped as non-applicable.*

## Goals

1. **Improve organic discoverability without breaking the live store.** Every change should leave the site in a better state than before. Never ship a change that breaks a running page, the cart, or checkout. Verify health after every mutation.
2. **Prove risky changes on an isolated clone before production.** If a risky change wasn't validated on a clone, it doesn't touch live. The clone is the intended test bench — **but none is currently provisioned (see `docs/adr/0001-no-clone-test-bench.md`)**, so until one is stood up, live is only touched with a change that's already been proven or is trivially reversible; genuinely risky changes are deferred or explicitly risk-accepted by the operator.
3. **Evidence over assertion.** Every "fixed / done / working" is a claim until an external check proves it. Verify from outside the site (fetch the live HTML, GA4 Realtime, Search Console), not by trusting a plugin's success message.

## RULE 1 — RISKY CHANGES GO THROUGH THE CLONE, NOT PROD

**Plugin/core/theme updates and anything with layout or checkout blast radius MUST be trialled on the isolated clone first, validated, then repeated on live.** The clone (UpdraftPlus/UpdraftClone) is matched to live — **PHP 8.2, WordPress 7.0, WooCommerce 10.7** — so "it worked on the clone" transfers. Never bulk-update blind on production.

> **Status (ADR 0001): no clone is currently provisioned.** The clone-first gate therefore cannot be satisfied right now — high-blast-radius changes are **deferred until a clone is stood up, or explicitly risk-accepted by the operator per change**, never done silently. Where this section assumes a present clone, `docs/adr/0001-no-clone-test-bench.md` is the source of truth.

Reversible, externally-verifiable, low-blast-radius changes MAY go direct to live: title/meta edits, an analytics tag ID, a Yoast setting. The test: is it instantly reversible and can O confirm it from an external fetch? If yes → live is fine. If it can break a layout or the checkout → clone first.

Being the admin does not remove the risk. Do not shortcut the clone step because you now have wp-admin access.

## RULE 2 — TRACK EVERY WORKSTREAM AS A GITHUB ISSUE

**Every defect/workstream gets a GitHub issue in `jdaviddenman/impressionoriginale`**, with the fix doc mirrored into `docs/` and the issue body. Issues map roughly 1:1 to a defect; the doc carries the evidence, root cause, proposed path, and machine-checkable acceptance criteria.

- Open the issue when the workstream starts, not after.
- Body must be self-contained: problem, why it matters, **evidence**, ruled-out causes, proposed path, acceptance criteria.
- Corrections are first-class: when new evidence changes the conclusion, update the issue (title/body) and add a dated correction comment — don't leave a wrong finding standing. (Precedent: Issue #3 flipped from "GA4 migration required" to "GA4 already live, remove obsolete UA" once Reports data appeared.)
- Current: **#1** hreflang — **CLOSED, not-a-defect** (hreflang is valid in the XML sitemap; WPML SEO 2.2.2+ design — see `docs/hreflang-fix.md`) · **#3** obsolete UA cleanup.

## RULE 3 — BACK UP BEFORE YOU CHANGE; ROLLBACK BEATS DIAGNOSIS

**Before any plugin/core update on live, take a fresh backup — files + database, stored off the server.** Use WP Engine backup points **and** UpdraftPlus (to Google Drive). When a change degrades the site, **restore first, diagnose second.** A half-broken store bleeding customers is not a debugging session. If a clone is provisioned (none currently — ADR 0001), "restore" there can just mean re-clone.

## RULE 4 — VALIDATE BEFORE, VERIFY AFTER

**Check preconditions before a mutation and prove health after it — don't trust the tool's own success message.** Before: environment parity (clone PHP/WP/WC == live), a backup exists, the right property/plugin is targeted. After: re-run the check that would fail if the change were wrong. A plugin saying "Settings saved" is not proof the tag changed — fetch the live page and look.

## RULE 5 — "FIXED / DONE / WORKING" IS A CLAIM UNTIL PROVEN

**No "fixed", "done", "working", or "verified" stands without the output of a deterministic external check, co-located with the claim.** A model (and a plugin UI) reports success with identical confidence whether or not the thing works. Paste the before/after evidence next to the claim; if the check can't be run, say "changed, unverified" instead.

Accepted evidence per change class:

| Change class | Deterministic evidence |
|---|---|
| hreflang | Check the **XML sitemap**, not the head: `curl -s <site>/page-sitemap.xml \| grep -ic 'xhtml:link'` (WPML SEO 2.2.2+ emits hreflang in the sitemap, not the head — an empty head is expected, NOT a defect). |
| Title / meta | fetch the live page, confirm the exact `<title>` / `meta description` rendered |
| Analytics tag | fetch homepage for `gtag/js?id=…` (UA gone, `G-Y88VQHFDBV` present) + GA4 Realtime hit (accept the cookie banner first) |
| Plugin/core update | `wp plugin get <slug> --field=version` (or the Plugins screen) shows the new version, **and** the site still 200s |
| No regression | `harness/fingerprint.sh` diff — same HTTP 200, no new PHP errors / shortcode leakage / mojibake, headings intact |

A green plugin message, a passing dry-run, or "it should work" are not evidence.

## RULE 6 — HIGH-BLAST-RADIUS CHANGES GET A FRESH-CONTEXT CRITIC

**An agent cannot review its own work into correctness.** For a substantial or risky change (plugin-update batch, a claim that a defect is fixed), spawn a fresh-context critic (Agent/Explore tool) chartered to **refute**: "Find what's wrong with this — what breaks the store, misreads the evidence, or fails to do what it claims. Say so explicitly if you find nothing." Re-reading your own diff/finding is not review. A critic's finding is itself a claim — reproduce it (RULE 5) before acting on it.

## RULE 7 — THIS REPO IS PUBLIC: DON'T PUBLISH THE ATTACK SURFACE

**Keep the store's exact update-status attack surface out of this public repo.** The full wp-admin plugin/version inventory + update plan live in a **separate private note**, not here. Credentials, API secrets, and SSH details never go in the repo or issues. Client-side public identifiers (GA4 `G-Y88VQHFDBV`, Google Tag `GT-5TPLSSZ`, GTM `GTM-MT7G7Z3C`, account/property IDs) are already visible in the site's HTML — fine to record. When in doubt, redact and ask.

## RULE 8 — THINK BEFORE; KEEP CHANGES SIMPLE AND SURGICAL

*(Karpathy coding discipline; the verify-the-claim half is RULE 5.)*

- **Think before acting.** State assumptions before a change — which page, which plugin, which language, what the blast radius is. If the request has multiple readings, present them; don't silently pick one. If a simpler path works, say so.
- **Simplicity first.** The minimum change that solves the problem — nothing speculative. No new abstraction/config nobody asked for.
- **Surgical changes.** Touch only what the request needs. Don't "improve" adjacent content while you're in there. Every changed line traces to the request; mention unrelated dead cruft, don't delete it.

## RULE 11 — KARPATHY PRE-FLIGHT (mandatory before every change proposal)

**Before proposing or starting any implementation, output these three lines:**

```
Simplest path: [one-line minimum fix]
Blast radius: [pages/systems touched; can it break checkout/cart?]
Safer alternative: [lower-risk option, or "none — this is the minimum"]
```

Hard gate, not advice. No pre-flight → no action. If the simplest path isn't the proposed path, explain why. If the blast radius includes homepage, checkout, or cart, flag it explicitly. If the safer alternative is just as good and lower risk, default to it — don't propose the riskier path without justifying why the safer one is insufficient.

This is the mandatory output-format twin of RULE 8. RULE 8 says what to do; RULE 11 makes it machine-checkable — if the pre-flight block is absent, the proposal is incomplete.

## RULE 9 — TERSE COMMUNICATION; NO HR-STYLE FILLER

**Operator-facing prose is terse and high-signal.** Drop pleasantries, hedging, filler, question-restatement, self-congratulation. Keep all technical substance: exact IDs, URLs, commands, quoted output, and the evidence RULE 5 demands. Fragments are fine. Exception — plain, full sentences for security warnings, irreversible-action confirmations, and multi-step sequences where terseness risks a misread. (Packaged as the `caveman` skill; always on unless told "stop caveman".)

## RULE 10 — VERIFY THE GROUND STATE; DON'T INFER IT FROM THE BANNER, MEMORY, OR ASSUMPTION

**A verifiable fact about the environment is a claim until a command proves it — the ground-state twin of RULE 5.** RULE 5 governs "fixed / done / working" (success claims after a change); this governs the state you reason *from*: "this isn't a git repo", "no clone exists", "I don't have access", "that file is missing", "the tag isn't firing". A model asserts these with the same confidence whether they're true or false — confidence carries zero information about correctness. Injected context — the session `Is a git repository` banner, a recalled memory, a prior assumption — is **not** ground truth; it can be stale or wrong. Probe the system before you assert or act on it.

- **Negative / blocked claims need evidence too.** "It's not X" / "I can't Y" / "there's no Z" earns its standing exactly like "it's fixed": run the read that would disprove it *first*. This is the failure-side twin of RULE 5 — imported from the infra `verify_before_claiming_blocked` *memory* lesson, which was never in the infra CLAUDE.md this repo adapted, so MKO never encoded it.
- **Search the cwd before any repo-state claim.** The env banner reported `Is a git repository: false` for `/home/james/MKO`, but the directory is a checkout of `jdaviddenman/impressionoriginale`. Before claiming anything about repo state, run `git rev-parse --git-dir` (exits 0 inside any repo — incl. bare / subdir / linked worktree, where `--is-inside-work-tree` false-negatives on bare repos and a literal `.git` check misleads) and `git remote -v` — never trust the banner. See `docs/adr/0004-env-banner-unreliable-verify-git.md`.
- **Check the fact at its authoritative source, not a convenient proxy** (the transferable half of infra RULE 7), **and re-verify against live state when the operator pushes back** — never double down from the same assumption.

No probe ⇒ report it as "unverified — need to check", not a confident assertion.

## Operator Commands

**`/fresh` — start from a clean main.** When the operator types `/fresh`, immediately bring the working copy to a fresh, up-to-date `main` before anything else:

```bash
git checkout main && git fetch --all --prune && git pull --ff-only
```

Hard gate, not advice. If `--ff-only` fails (local commits on `main`, dirty tree), stop and report — never force/rebase/reset to make it succeed. Report the resulting branch + `git log --oneline -1`.

**Never push directly to `main`.** Branch → PR → merge (a PreToolUse hook enforces this). Merge only when the operator asks.

## The Live Stack (what the site actually runs)

Everything below is the **target site's** stack, not this repo's.

- **CMS/commerce:** WordPress **7.0**, WooCommerce **10.7.0**, Stripe gateway.
- **Multilingual:** WPML (EN default + FR) — `sitepress-multilingual-cms` + String/Media Translation + WooCommerce Multilingual + **WPML SEO** (the Yoast↔WPML glue).
- **SEO:** Yoast SEO (`sitemap_index.xml`, head presenters).
- **Analytics/tags:** GTM4WP + GTM container `GTM-MT7G7Z3C`; GA4 property `Impression Originale - GA4` (`375621420` / Measurement ID `G-Y88VQHFDBV` / Google Tag `GT-5TPLSSZ`); PixelYourSite; an **obsolete `UA-85910237-1`** tag still firing (Issue #3). Pinterest + Meta pixels.
- **Performance/build:** WP Rocket (cache + lazy), WPBakery Page Builder, Slider Revolution, WebP Express.
- **Theme:** EngineThemes "The Core" (`eut-` / `Engic Extension`) — old; page-builder + theme are the layout break-zone on updates.
- **Host:** WP Engine — nginx, **PHP 8.2.31**, MySQL **8.4.7**, memory_limit 512M. WPE runs its own page + object cache on top of WP Rocket (clear **both** when verifying).
- **Consent:** Termly banner with Google Consent Mode — analytics is gated until consent; accept the banner (or use GA4 DebugView) before trusting Realtime.
- **Clone/testing:** UpdraftPlus / UpdraftClone — **not currently provisioned** (ADR 0001). When stood up, match to live (PHP 8.2 / WP 7.0 / WC 10.7).

## Common Commands & Checks

```bash
# Fingerprint a set of live/clone pages (server HTML only — no JS). Diff rounds.
./harness/fingerprint.sh https://www.impressionoriginale.com baseline
# clone target below needs a provisioned clone — none currently (ADR 0001)
./harness/fingerprint.sh https://<clone>.updraftclone.com  clone-baseline
diff baseline/SUMMARY.txt clone-baseline/SUMMARY.txt

# hreflang check — look in the SITEMAP, not the head (WPML SEO 2.2.2+ design)
curl -s https://www.impressionoriginale.com/page-sitemap.xml | grep -ic 'xhtml:link'   # present & valid (was mis-checked in the head)

# Analytics tag check (Issue #3)
curl -sL https://www.impressionoriginale.com/ | grep -oiE 'gtag/js\?id=[A-Z0-9-]+'   # UA gone, G-… present

# Infer live WP version (feed generator survives caching)
curl -s https://www.impressionoriginale.com/feed/ | grep -oiE '<generator>[^<]*</generator>'

# Plugin updates on the CLONE via WP-CLI over SSH — requires a provisioned clone (none currently — ADR 0001); NOT core, keep WP at 7.0 for parity
ssh -i clone_key <user>@<host> 'wp plugin list --update=available --fields=name,version,update_version'
ssh -i clone_key <user>@<host> 'wp plugin update sitepress-multilingual-cms wpml-string-translation \
  wpml-media-translation woocommerce-multilingual wp-seo-multilingual wordpress-seo'

# Issues / PRs
gh issue list  --repo jdaviddenman/impressionoriginale
gh pr create   --base main --head <branch> --title "…" --body "…"
```

## Repo Layout

- `README.md` — audit overview, ranked findings, remediation workflow, status log.
- `docs/hreflang-fix.md` — Issue #1, **corrected to not-a-defect** (hreflang is valid via sitemap).
- `docs/analytics-ga4-migration.md` — Issue #3 (obsolete UA cleanup; GA4 already live).
- `docs/title-meta-rewrites.md` — keyword-first EN + FR titles/meta, copy-paste ready.
- `harness/fingerprint.sh` — before/after regression harness (server HTML; no JS).
- `reports/` — clone baseline + before/after diffs (added as work lands).

## Known Gotchas

- **Cloudflare** fronts live and bot-challenges scripted fetches on deeper pages (403 / challenge). Homepage + category pages usually pass; product pages may not. A clone (UpdraftClone infra), when provisioned, is not behind Cloudflare — fetch it freely. (None currently — ADR 0001.)
- **JS-injected tags are invisible to `curl`.** The GA4 tag fires inside the GTM container at runtime, so an external HTML fetch shows only the static UA tag. Confirm runtime tags in the GTM/GA4 UI, not just the page source. (This is why Issue #3 first looked like "no GA4" — it was hiding in GTM.)
- **WP Engine + WP Rocket = two caches.** Clear both after a change or the re-check reads a stale copy.
- **WPML premium updates are domain-locked.** `wp plugin update` for WPML plugins may fail to fetch on a clone (different domain / registration); Yoast (wordpress.org) updates cleanly regardless. (Confirmed on a prior clone run: WPML core/String/Media downloads returned error pages; Yoast + WooCommerce ML updated fine — no clone currently, ADR 0001.)
- **hreflang lives in the SITEMAP here, not the head.** WPML SEO 2.2.2+ moved hreflang from `<head>` into the XML sitemap by design. An empty head is expected and correct — Google supports sitemap hreflang equally. A prior audit wrongly flagged "hreflang missing" from a head-only check and nearly ran an unnecessary live update. **Lesson (general): before escalating a claimed defect, verify the signal against EVERY location it can legitimately live, and confirm the tool's current behaviour — not an assumed one.** A footgun check before the risky live change is what caught it.
