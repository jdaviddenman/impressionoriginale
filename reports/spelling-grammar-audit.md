# English Site — Spelling & Grammar Audit

**Method:** LanguageTool 6.9 (self-hosted, en-GB) over **521 EN pages** (115 content + 406 products, `/fr/` excluded), then an LLM adjudication pass over all **960 distinct flags** dropping proper-noun / brand / artwork / intentional-French false positives → **348 confirmed errors**.

## 🔴 Fix first — structural

- **Live lorem-ipsum placeholder** on `/about-us-2/` and `/services/` — replace with real copy.
- **`lang="en-US"` but British house style** — set the site language to en-GB.
- **Duplicated template strings:** `Description Description` (66 product pages) and `Mathilde Habert Mathilde Habert` byline (24 blog pages) — template bugs, see batch fixes.

## Sub-pages

| Section | Count |
|---|---|
| [Batch fixes (site-wide, ≥8 pages)](spelling-grammar/00-batch-fixes.md) | 17 |
| [Untranslated French running text](spelling-grammar/01-untranslated-french.md) | 17 |
| [Spelling typos](spelling-grammar/02-typos.md) | 114 |
| [Franglais](spelling-grammar/03-franglais.md) | 19 |
| [British / American spelling](spelling-grammar/04-british-american.md) | 28 |
| [Grammar](spelling-grammar/05-grammar.md) | 37 |
| [Punctuation](spelling-grammar/06-punctuation.md) | 19 |

_Start with **Batch fixes** (biggest per-page reach) and **Untranslated French** (worst UX/SEO). Each sub-page has a copy-paste `find → replace` block plus a context table._
