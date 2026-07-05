# Client-References "Trusted by" Banner (E-E-A-T, luxury-appropriate)

Companion to the SEO audit. The site serves marquee luxury houses and lists them
on [`/our-references/`](https://www.impressionoriginale.com/our-references/). This
turns that roster into an **authority / E-E-A-T** signal — the luxury-brand
alternative to Trustpilot/Google star reviews.

**What this is and isn't (SEO reality):** a logo image strip is a *human* trust
signal; Google ranks **crawlable text + entities**, not the prestige of a PNG. So
the banner pairs the logos with a **visible sentence naming every client** — that
text is the SEO payload. Client logos build E-E-A-T/conversion; they do **not**
produce SERP star ratings (that is a separate review-schema lever, declined here on
brand grounds).

**Legal note (business call, not SEO):** displaying LVMH / Richemont-house marks
(Louis Vuitton, Moët Hennessy, Van Cleef & Arpels, TAG Heuer…) as clients carries
trademark risk. "Already on the site" is not authorization. Confirm you have the
right to display each mark before promoting it to the homepage.

## The 16 clients (corrected spellings)

Live `alt`/filenames contain **indexed typos** — fix these first (Google is
associating you with the wrong entity string):

| Client (correct) | Live alt on `/our-references/` | Action |
|---|---|---|
| Van Cleef & **Arpels** | `Van Cleef & Arples` | ❌ fix alt (+ optional file rename) |
| **Shiseido** | `Shisheido` | ❌ fix alt (+ optional file rename) |
| **TAG Heuer** | `Tag Heuer` | ⚠️ brand styles all-caps |
| Louis Vuitton, Tiffany & Co., Prada, Salvatore Ferragamo, Moët Hennessy, Faber-Castell, Anine Bing, Tata Harper, Charles Oudin, Musée Rodin, The Ritz Paris, Le Bristol Paris, Barrière Les Neiges | correct | ✅ leave |

**Alt text is the priority fix** (it is the crawlable string, edited in Media
Library → image → *Alternative Text*). Filename rename is optional polish — it
needs a re-upload or a rename plugin and risks 404s on hotlinks, so lower value.

## Homepage banner — paste into a Raw HTML / Text block

One block, one copy button. Names are real text (the SEO part); logos carry
correct `alt`; the strip links to `/our-references/`.

```html
<section class="io-trusted-by" aria-label="Luxury houses that trust Impression Originale">
  <h2>Trusted by the world&rsquo;s leading luxury houses</h2>
  <ul class="io-trusted-logos">
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Louis Vuitton" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Louis-Vuitton.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Van Cleef & Arpels" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Van-Cleef-Arples.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Tiffany & Co." src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Tiffany-Co.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Prada" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Prada.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Salvatore Ferragamo" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Ferragamo.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Moët Hennessy" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Moet-Hennessy.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — TAG Heuer" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Tag-Heuer.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Shiseido" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Shisheido.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Musée Rodin" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Musee-Rodin-1.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — The Ritz Paris" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Ritz.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Le Bristol Paris" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Bristol.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Barrière Les Neiges" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Barriere-les-Neiges.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Faber-Castell" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Faber-Castell.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Charles Oudin" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Charles-Oudin.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Anine Bing" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Anine-Bing.png"></li>
    <li><img loading="lazy" width="160" height="80" alt="Impression Originale — Tata Harper" src="https://www.impressionoriginale.com/wp-content/uploads/2026/03/Impression-Originale-Tata-Harper.png"></li>
  </ul>
  <p class="io-trusted-caption">
    Impression Originale creates bespoke luxury gift wrapping for
    <a href="https://www.impressionoriginale.com/our-references/">the world&rsquo;s leading houses</a> —
    Louis Vuitton, Van Cleef &amp; Arpels, Tiffany &amp; Co., Prada, Salvatore Ferragamo,
    Moët Hennessy, TAG Heuer, Shiseido, Musée Rodin, The Ritz Paris, Le Bristol Paris,
    Barrière Les Neiges, Faber-Castell, Charles Oudin, Anine Bing and Tata Harper.
  </p>
</section>
```

## Minimal styling (optional — paste into Customizer → Additional CSS)

```css
.io-trusted-by { text-align: center; padding: 2.5rem 1rem; }
.io-trusted-by h2 { font-size: 1.1rem; letter-spacing: .08em; text-transform: uppercase; opacity: .8; }
.io-trusted-logos { list-style: none; margin: 1.5rem auto; padding: 0; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 1.5rem 2.5rem; max-width: 1100px; }
.io-trusted-logos img { height: 48px; width: auto; object-fit: contain; filter: grayscale(1); opacity: .75; transition: opacity .2s, filter .2s; }
.io-trusted-logos img:hover { filter: grayscale(0); opacity: 1; }
.io-trusted-caption { max-width: 800px; margin: 0 auto; font-size: .9rem; line-height: 1.6; opacity: .85; }
```

## Where to place

- **Homepage:** add the block below the hero (WPBakery → Text/Raw HTML element).
  It links to `/our-references/`, passing authority to that page.
- **Keep `/our-references/`** as the deep roster — fix its alt-text typos there too.

## Verify (Done when)

Confirm the client **names render as crawlable body text** (not just alt) on the home page:

```
curl -s https://www.impressionoriginale.com/ | grep -o "Van Cleef &amp; Arpels" | head -1
```

Done when that returns `Van Cleef &amp; Arpels` (proves the caption text — with the
**corrected** spelling — is in the served HTML, not hidden in an image).
