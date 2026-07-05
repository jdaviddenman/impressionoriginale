# English Site — Spelling & Grammar Audit

**Method:** LanguageTool 6.9 (self-hosted, en-GB dictionary) over **521 EN pages** (115 content/editorial + 406 products), then an LLM adjudication pass over all **960 distinct flags** to drop proper-noun / brand / artwork / intentional-French false positives. **348 confirmed as real errors.** Typography/casing/whitespace and en-GB↔US variants excluded upstream.

**Confirmed:** 17 pages with untranslated French · 114 spelling typos · 19 franglais · 37 grammar · 19 punctuation · 28 British/American & misc spelling.

## 🔴 Fix first — structural

- **Live lorem-ipsum placeholder** on `/about-us-2/` and `/services/` — dummy Latin text is published. Replace with real copy.
- **`lang="en-US"` but British house style** — pages declare US English yet write British (colour/realise). If British is intended, set the site language to en-GB.
- **Duplicated author byline** `Mathilde Habert Mathilde Habert` renders across ~24 blog pages — a template bug, not a typo.

## ⚡ Quick batch fixes (site-wide, ≥8 pages)

Highest-frequency corrections — search-and-replace across the site (check case/context before blind replace):

```text
COLOR  →  colour   (364 pages)
colors  →  colours   (79 pages)
Description Description  →  Description   (66 pages)
favorite  →  favourite   (51 pages)
quadri-color  →  four-colour   (41 pages)
ornates  →  adorns   (31 pages)
gros  →  grosgrain   (21 pages)
personnalised  →  personalised   (20 pages)
currated  →  curated   (20 pages)
beautifuly  →  beautifully   (20 pages)
personnalise  →  personalise   (20 pages)
colorful  →  colourful   (14 pages)
g raduated  →  graduated   (12 pages)
Recylced  →  Recycled   (12 pages)
traveling  →  travelling   (10 pages)
Velvelt  →  Velvet   (10 pages)
Artic  →  Arctic   (8 pages)
```

## 🔴 Untranslated French running text — 17 pages

English URLs whose body copy is still (partly) in French — high SEO + UX cost. Ranked by count of French fragments detected:

| Page | French fragments |
|---|---|
| `/e-shop/l-oracle-de-dodone-papier-cadeau/` | 86 |
| `/e-shop/la-vie-sous-le-1er-empire-papier-cadeau/` | 81 |
| `/e-shop/by-design-evergreen-wreaths/` | 9 |
| `/our-designers/genevieve-despres/` | 6 |
| `/services/` | 5 |
| `/e-shop/sticky-gift-tag-evergreen-wreaths-butterflies/` | 5 |
| `/e-shop/sticky-gift-tag-evergreen-wreaths-owl/` | 5 |
| `/e-shop/sticky-gift-tag-evergreen-wreaths-tit/` | 5 |
| `/furoshiki-an-ancestral-tradition-of-gift-wrapping/` | 3 |
| `/our-designers/claire-de-quenetain/` | 3 |
| `/workshop-eva-magill-oliver/` | 3 |
| `/e-shop/look-mexicana/` | 3 |
| `/e-shop/table-name-cards-watercolour-blue/` | 3 |
| `/e-shop/table-name-cards-watercolour-light-blue/` | 3 |
| `/e-shop/table-name-cards-watercolour-pink/` | 3 |
| `/e-shop/table-name-cards-watercolour-purple/` | 3 |
| `/e-shop/table-name-cards-watercolour-yellow/` | 3 |

## Spelling typos — 114

_Site-wide (≥8 pages) — fix once in shared template/copy:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `currated` | **curated** | 20 | `/e-shop/gift-tags-black/` | …...2 cm DENSITY 240g ORIGIN From France We currated Gift Tags that converse beau… |
| `beautifuly` | **beautifully** | 20 | `/e-shop/gift-tags-black/` | …...nce We currated Gift Tags that converse beautifuly with our ribbons and colou… |
| `g raduated` | **graduated** | 12 | `/e-shop/24-december-gift-bag-m/` | …... dealing with paper items in Japan. She g raduated from Tokyo National Univer… |
| `Recylced` | **Recycled** | 12 | `/e-shop/gift-tags-watercolour-blue/` | …...n. Specifications: x6 gift tags 5×12 cm Recylced Paper Cut in reversed V for … |
| `Velvelt` | **Velvet** | 10 | `/e-shop/almond-green-velvet-tuxedo-bow-n436-l/` | …...COLOR Almond Green ORIGIN France COLOUR Velvelt Tuxedo Bow ready-to-use SIZE … |
| `Artic` | **Arctic** | 8 | `/./gift-bags/` | …... Bag (S) 4,17 € Add to cart Add to cart Artic Blue Leather Gift Bag (S) 106,6… |

_Page-level:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `stationnary` | **stationery** | 7 | `/e-shop/an-afternoon-at-the-zoo-in-paris-wrap/` | …... us on the 2nd floor at the hear of the stationnary and bookstore section. Al… |
| `Monteral` | **Montreal** | 7 | `/e-shop/24-december-gift-bag-m/` | …... Industrial Design at the university of Monteral, Geneviève decides to dedica… |
| `complets` | **completes** | 7 | `/e-shop/24-december-gift-bag-m/` | …...g. She concentrates on illustration and complets several projects. In 2003, s… |
| `designor` | **designer** | 6 | `/e-shop/an-afternoon-at-the-zoo-in-paris-wrap/` | …...is a children illustrator and a graphic designor. After studying graphic desi… |
| `reknown` | **renowned** | 6 | `/e-shop/by-design-on-the-birds-wings/` | …... the incredible work of Eugène Séguy, a reknown French entomologist, whose in… |
| `Compagnion` | **Companion** | 4 | `/diary/` | …... Read more Workshop: Damien the leather Compagnion Read more… |
| `Montmarte` | **Montmartre** | 4 | `/e-shop/24-december-gift-bag-m/` | …...lain, swept by the northern wind, walks Montmarte, a reindeer with crazy antl… |
| `Gingko` | **Ginkgo** | 4 | `/e-shop/by-design-the-ginkgo-bilobas-spell/` | …...will convey the elegant strength of the Gingko. Note: Our gift bags and gift … |
| `Gingko biloba` | **Ginkgo biloba** | 4 | `/e-shop/the-ginkgo-bilobas-spell-gift-bag-m/` | …...paired ribbons and bows in the Look the Gingko biloba’s Spell. Specifications… |
| `handcrafter` | **handcrafted** | 3 | `/e-shop/artic-blue-leather-gift-bag-s/` | …...-designed with L’ouvreuse and carefully handcrafter in Paris, France. Our IMP… |
| `innovent` | **innocent** | 3 | `/e-shop/by-design-childrens-rhymes/` | …...r by the heart ». Tara invites us in an innovent children’s rhyme, candid and… |
| `Bowen-Johntson` | **Bowen-Johnson** | 3 | `/e-shop/by-design-evergreen-wreaths/` | …...0 € By Design Evergreen Wreaths Gillian Bowen-Johntson aka The Singing Cricke… |
| `Todays` | **Today's** | 3 | `/e-shop/by-design-the-island-of-the-sealions/` | …...ions, hidden away in the Southern seas. Todays schedule for the adults: fresh… |
| `G ift` | **Gift** | 3 | `/e-shop/evergreen-wreaths-gift-bag-s/` | …...a The Singing Cricket Evergreen Wreaths G ift Bag Size S [12,5 (h) x 9 (w) x … |
| `Do-it-Youself` | **Do-it-Yourself** | 2 | `/furoshiki-8-ways-to-master-it/` | …...-how and our easy-to-follow techniques. Do-it-Youself with simple folding gif… |
| `Lefeveuvre` | **Lefeuvre** | 2 | `/e-shop/winter-wonderland-gift-wrap/` | …...on Originale Close Chloé Lefeuvre Chloé Lefeveuvre is a French free-lance tex… |
| `Beautific` | **Beatific** | 2 | `/e-shop/can-you-hear-the-music-wrap/` | …...s Can you hear the Music wrapping paper Beautific dischord, conducted to cres… |
| `dischord` | **discord** | 2 | `/e-shop/can-you-hear-the-music-wrap/` | …...hear the Music wrapping paper Beautific dischord, conducted to crescendo, sil… |
| `delicatly` | **delicately** | 2 | `/e-shop/celebration-wraps-x3/` | …... go wrong as long as it is presented so delicatly, with subtle touches of met… |
| `Rio de Janero` | **Rio de Janeiro** | 2 | `/e-shop/giddy-jungle-wrap/` | …...ting the house of Roberto Burle Marx in Rio de Janero, Jeanne Boyer was inspi… |
| `inbetween` | **in between** | 2 | `/e-shop/look-pop-art-petals/` | …...ition technique. He takes us to a place inbetween two worlds, where the viewe… |
| `Lillies` | **Lilies** | 2 | `/e-shop/look-swimming-in-the-water-lilies/` | …...s Monika Forsberg Swimming in the Water Lillies wrapping paper Tip to toe, pa… |
| `armor` | **armour** | 2 | `/e-shop/look-the-battle-of-the-xi-an-warriors/` | …... one could almost perceive the crash of armor and the whinnying of horses. Tr… |
| `Ucello` | **Uccello** | 2 | `/e-shop/look-the-battle-of-the-xi-an-warriors/` | …... to the famous Florentine painter Paolo Ucello who took battles to lyrical ar… |
| `Andy Wharol` | **Andy Warhol** | 2 | `/e-shop/look-where-is-the-bird-hiding/` | …...tic list of miscellaneous items with an Andy Wharol twist. This wrapping pape… |
| `favorites` | **favourites** | 1 | `/` | …...n France. Eco-responsible. The Season's favorites By Design 24 December From:… |
| `RABIT` | **RABBIT** | 1 | `/3d-modeling-surgeon-paper/` | …...indow décor, or private orders.” MISTER RABIT AND A BOW [Originals by Surgeon… |
| `poeple` | **people** | 1 | `/3d-modeling-surgeon-paper/` | …...y pleased with the result I got and how poeple receive it. However I would sa… |
| `spectaular` | **spectacular** | 1 | `/bespoke/` | …...make your ideas come to life and create spectaular wrapping paper in whatever… |
| `embroisery` | **embroidery** | 1 | `/bespoke-services/` | …...sculpted paper (3D), drawing, feathers, embroisery, fabric weaving… SET DESIN… |
| `DESING` | **DESIGN** | 1 | `/bespoke-services/` | …...athers, embroisery, fabric weaving… SET DESING A set to be experienced, alive… |
| `diairy` | **diary** | 1 | `/./bows/` | …... bows with our dedicated article in our diairy… |
| `Ethymology` | **Etymology** | 1 | `/decipher-quadrichromia-printing-process/` | …...dgQ?t=4″ align=”center”] Back to Greece Ethymology “ Quadrichromia ” emerged … |
| `reffered` | **referred** | 1 | `/decipher-quadrichromia-printing-process/` | …... contrasts and to print the text and is reffered as key (“K”). The four-color… |
| `supperposing` | **superposing** | 1 | `/decipher-quadrichromia-printing-process/` | …...olours are printed one after the other, supperposing the layers of colours to… |
| `developping` | **developing** | 1 | `/diary/` | …...r e f o r a t h o u g h t . Our team is developping original content in order… |
| `dehibd` | **behind** | 1 | `/diary/` | …...lented artists and the expertise hiding dehibd the scenes. We bring you in th… |
| `papr` | **paper** | 1 | `/faqs/` | …...or quality grammage: 115g/m 2 . All our papr is 100% recycled. WHAT IS THE SI… |
| `knoting` | **knotting** | 1 | `/./gift-tags/` | …...-how of folding gift-wrapping paper and knoting our ribbons, all the way unti… |
| `gratuded` | **graduated** | 1 | `/historical-reissue-gift-wrap-of-the-bnf/` | …...you specialize in a period of study? “I gratuded with an art historian degree… |
| `faciliting` | **facilitating** | 1 | `/historical-reissue-gift-wrap-of-the-bnf/` | …... known by increasing its visibility and faciliting its accessibility. The pub… |
| `freetime` | **free time** | 1 | `/how-to-a-commission-for-musee-rodin/` | …...ig ones like building our studio! In my freetime, I like to play the ukulele … |
| `Somedays` | **Some days** | 1 | `/how-to-a-commission-for-musee-rodin/` | …...elf a designer but also an illustrator. Somedays I do more textile design tha… |
| `LATERN ON` | **LANTERN ON** | 1 | `/illustrated-interview-kim-heeguym-aka-mr-fox/` | …...t naughty invention of your dog, Fry? A LATERN ON MY HEAD If you could escape… |
| `apart form` | **apart from** | 1 | `/interview-meet-the-founders-of-impression-originale/` | …...signs IDEALIST: What sets your products apart form other gift wrap? “Daring t… |
| `KEEPSAFE` | **KEEPSAKE** | 1 | `/know-how-the-perfect-gift/inspirations/` | …...S IT INTO A GIFT” “THE EMOTIONS ARE THE KEEPSAFE OF MEMORY” “THE RIBBON BENDS… |
| `the hear` | **the heart** | 1 | `/know-how-the-perfect-gift/wrapping-service/` | …...e in Paris. Find us on the 2nd floor at the hear of the stationnary and books… |
| `behing` | **behind** | 1 | `/meet-an-expert-the-art-of-colours/` | …...a good share of professional experience behing me, including in the luxury de… |
| `wonderfull` | **wonderful** | 1 | `/our-designers/agnes-denat/` | …...colas Barome, I’m also in love with the wonderfull pastel color ranges of Mar… |
| `facinated` | **fascinated** | 1 | `/our-designers/camille-laugie/` | …...urce of inspiration. I have always been facinated by them, especially birds a… |
| `sattle` | **saddle** | 1 | `/our-designers/camille-laugie/` | …... most amazing gift is a gorgeous riding sattle offered by my family for my 30… |
| `creativy` | **creativity** | 1 | `/our-designers/camille-laugie/` | …...he day than at night where I can let my creativy free rolling. At night every… |
| `Cissors` | **Scissors** | 1 | `/our-designers/camille-laugie/` | …...er » what is first coming to your mind? Cissors. Immediatly. Mention a quote … |
| `Immediatly` | **Immediately** | 1 | `/our-designers/camille-laugie/` | …... is first coming to your mind? Cissors. Immediatly. Mention a quote you feel … |
| `environnent` | **environment** | 1 | `/our-designers/camille-laugie/` | …... Animal care, and the protection of the environnent. The access to art, cultu… |
| `attrack` | **attract** | 1 | `/our-designers/clairecolin/` | …... extremely inspiring with them, flowers attrack me, inspire me. I spend a lon… |
| `administrive` | **administrative** | 1 | `/our-designers/clairecolin/` | …... Mornings are dedicated to prospection, administrive and commercial activitie… |
| `aftertoon` | **afternoon** | 1 | `/our-designers/clairecolin/` | …...trive and commercial activities. In the aftertoon I am more creative in my wo… |
| `dont` | **don't** | 1 | `/our-designers/clairecolin/` | …...ding it, cutting it, creating volume. I dont see myself live without paper. M… |
| `québecquois` | **Québécois** | 1 | `/our-designers/clairecolin/` | …...y inspiring DARE! I viscerally love the québecquois proverb « Nothing is impo… |
| `unberable` | **unbearable** | 1 | `/our-designers/frederic-bonnin-minakani/` | …...and their exploitation and suffering is unberable, let us promote vegan. Huma… |
| `Eureopan` | **European** | 1 | `/our-designers/genevieve-despres/` | …...ely on children’s illustration from her Eureopan retreat. Since her repatriat… |
| `Déco` | **Deco** | 1 | `/our-designers/maxime-massole/` | …...inspired by the Art Nouveau and the Art Déco. His style stems from the intric… |
| `erea` | **era** | 1 | `/our-designers/maxime-massole/` | …...stems from the intricate styles of that erea, where he started in a black and… |
| `Internatioal` | **International** | 1 | `/our-designers/maxime-massole/` | …...for (at the moment) After I visited the Internatioal City of Tapestry located… |
| `sirup` | **syrup** | 1 | `/our-designers/maxime-massole/` | …...f Fine Arts of Belgium. A violet flower sirup mixed with water. If I say « pa… |
| `a please` | **pleasure** | 1 | `/our-designers/mr-oneteas/` | …... Vence, in south of France. Always such a please to wander in a place where a… |
| `redtape` | **red tape** | 1 | `/our-designers/mr-oneteas/` | …...INE called « les petits papiers ». Then redtape will come to mind, and I’ll c… |
| `founf` | **fond** | 1 | `/our-designers/sophie-truant/` | …...mostly inspired by Nature. I am totally founf of the vegetal world. What is t… |
| `Anthropolgie` | **Anthropologie** | 1 | `/our-designers/tara-lilly/` | …...rotection. Tara Lilly CLIENTS FIND MORE Anthropolgie, American Greetings, Flo… |
| `Gslison` | **Galison** | 1 | `/our-designers/tara-lilly/` | …...ng, 180 Degrees, Hasbro, Mudpuppy Kids, Gslison Gift, Midwest CBK, TAG Instag… |
| `palett` | **palette** | 1 | `/our-designers/violaine-auzuech/` | …...e of dreams and compose with her colour palett extravageant flowers bouquets.… |
| `extravageant` | **extravagant** | 1 | `/our-designers/violaine-auzuech/` | …...eams and compose with her colour palett extravageant flowers bouquets. If you… |
| `ARPLES` | **Arpels** | 1 | `/our-references/` | …...r craft for maisons such as Van Cleef & Arples, Louis Vuitton, Tiffany & Co.,… |
| `TTIFFANY` | **TIFFANY** | 1 | `/our-references/` | …...ns embodying the excellence of gesture. TTIFFANY & CO. Crafting and live deli… |
| `SHISHEIDO` | **SHISEIDO** | 1 | `/our-references/` | …...are demonstration of technical mastery. SHISHEIDO Design and execution of bes… |
| `REQUETS` | **REQUESTS** | 1 | `/submit-an-inquiry/` | …...T US BRING YOUR MOST EXCLUSIVE AND RARE REQUETS TO LIFE, IN KEEPING WITH YOUR… |
| `crystalizes` | **crystallizes** | 1 | `/talk-with-our-founder-the-wrapping-ceremony/` | …...the lucky recipient. A well formed gift crystalizes these universal emotions … |
| `Tapei` | **Taipei** | 1 | `/where-to-find-us/` | …... du Docteur Lescour Nouméa 98800 TAIWAN Tapei Eslite B1. No. 196, Songde Road… |
| `traning` | **training** | 1 | `/workshop-damien-the-leather-compagnion/` | …... vocational training. At the end of the traning, you are required to submit y… |
| `Companons` | **Compagnons** | 1 | `/workshop-damien-the-leather-compagnion/` | …... them today. I was 20 when I joined the Companons, which is very late. Usuall… |
| `cliens` | **clients** | 1 | `/workshop-damien-the-leather-compagnion/` | …...hing is the smile of appreciation on my cliens’ face when they see what I hav… |
| `Alcantra` | **Alcantara** | 1 | `/workshop-damien-the-leather-compagnion/` | …...eads, with crushed raspberries coloured Alcantra panels. On the outside, ther… |
| `yoks` | **yokes** | 1 | `/workshop-damien-the-leather-compagnion/` | …...panels. On the outside, there were some yoks at the corners, stitched in whit… |
| `Beskpoke` | **Bespoke** | 1 | `/workshop-damien-the-leather-compagnion/` | …...re? Join the world of Damien Press .com Beskpoke Post Tags: artisan Damien in… |
| `personnal` | **personal** | 1 | `/workshop-eva-magill-oliver/` | …...has come up with an incredible and very personnal interpretation of the “ A t… |
| `developped` | **developed** | 1 | `/workshop-pippa-dyrlaga/` | …...BY STEP Walk us through the artwork you developped with us Step #1 So I chose… |
| `yummi` | **yummy** | 1 | `/workshop-the-pineapple-chef/` | …...omments <>Stylist & food photographer A yummi job Introducing Nice to meet yo… |
| `Whithout` | **Without** | 1 | `/workshop-the-pineapple-chef/` | …...s, granola… Make a wish Save the bees ! Whithout them, we won’t be here for l… |
| `everytime` | **every time** | 1 | `/./wrap/` | ….... Do it all, or don’t do it. We hear it everytime we hesitate between two col… |
| `blue shinny` | **blue shiny** | 1 | `/e-shop/a-perfect-birthday-party-gift-box-s/` | …...gift wrap and best matched with a small blue shinny satin ribbon . Note: our … |
| `Rasberry` | **Raspberry** | 1 | `/e-shop/crushed-raspberry-velvet-ribbon-n250-xl/` | …...ONS 2.5 m MATERIAL Velvet COLOR Crushed Rasberry pink ORIGIN France COLOUR Ve… |
| `pointilist` | **pointillist** | 1 | `/e-shop/iconic-decoding-wrap/` | …...g of the source a foreign word to most, pointilist movement, Impressionist ta… |
| `Pointilism` | **Pointillism** | 1 | `/e-shop/look-iconic-decoding-wrap/` | …...rapping paper Elizabeth was inspired by Pointilism, one of the treatments of … |
| `casuality` | **casualness** | 1 | `/e-shop/look-rodeo-of-toucan/` | …...he tropics that Quentin embarks us with casuality and a touch of farnienté. I… |
| `Ruxedo` | **Tuxedo** | 1 | `/e-shop/look-the-maharaja-and-his-pet/` | …...information Weight 0,08 kg Black Velvet Ruxedo Bow n°233 (L) Colour Navy I Re… |
| `Gatsy` | **Gatsby** | 1 | `/e-shop/look-the-unbearable-lightness-of-the-feather/` | …... paper, which calls for hosting a Great Gatsy’s unforgettable party in the he… |
| `Cheetha` | **Cheetah** | 1 | `/e-shop/menagerie-cheeta-gift-bag-s/` | …...e – Cheeta Gift Bag (S) BNF Menagerie – Cheetha Gift Bag Size S [12,5 (h) x 9… |
| `Bas` | **Bag** | 1 | `/e-shop/mexicana-furoshiki/` | …...ion. This design is available in a Gift Bas (S) , Gift Bag (M) and a Gift Wra… |
| `MoMa` | **MoMA** | 1 | `/e-shop/modern-art-wraps-x3/` | …...isit to Centre Pompidou, Tate Modern or MoMa. Whichever, just come indulge yo… |
| `squirel` | **squirrel** | 1 | `/e-shop/once-upon-a-christmas-night-wraps-x3/` | …...cal night of preparations with a little squirel stirring hot chocolate for Sa… |
| `Playgroud` | **Playground** | 1 | `/e-shop/playground-wraps-x3/` | …...Close From: 12,51 € Playground Wraps x3 Playgroud – Wraps x3 We propose a sel… |
| `joyfull` | **joyful** | 1 | `/e-shop/silver-moon-shine-sheen-ribbon-n02-s/` | …...d the silver moon sheen Ribbon with the joyfull minty wrapping paper Selfies … |
| `snak` | **snack** | 1 | `/e-shop/snack-time-wraps-x3/` | …... of the biscuit in your mouth when it’s snak time… a set of porcelaine tea an… |
| `preceeds` | **precedes** | 1 | `/e-shop/the-artists-workshop-wraps-x3/` | …...istic impulse, the movement of the hand preceeds the thought. The shallow sha… |
| `burried` | **buried** | 1 | `/e-shop/the-artists-workshop-wraps-x3/` | …...and start dancing on the screen and the burried warriors of the Xi-An Emperor… |
| `Wowan` | **Woman** | 1 | `/e-shop/the-woman-with-the-apron-gift-bag-s/` | …... design is available in a gift wrap The Wowan with the Apron . Specifications… |
| `babyshower` | **baby shower** | 1 | `/e-shop/yellow-satin-ribbon-n220-xs/` | …...r Before the Pixel a perfect pick for a babyshower celebration. Available in … |

## Franglais (French-formed English) — 19

_Site-wide (≥8 pages) — fix once in shared template/copy:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `quadri-color` | **four-colour** | 41 | `/e-shop/arabesque-wraps-x3/` | …...PAPER Cyclus – 100% recycled INK Offset quadri-color, UV drying process, Doub… |
| `ornates` | **adorns** | 31 | `/e-shop/black-tuxedo-bow-n233-20mm/` | …...k tuxedo Bow with its flat single loops ornates exceptional presents. Simply … |
| `gros` | **grosgrain** | 21 | `/e-shop/black-gros-grain-ribbon-n233-l/` | …...wet cloth for a perfect result (velvet, gros grain, suit, satin). HOW IS THE … |
| `personnalised` | **personalised** | 20 | `/e-shop/gift-tags-black/` | …...rk to your present. The gift tag can be personnalised with a hand-written cal… |
| `personnalise` | **personalise** | 20 | `/e-shop/gift-tags-black/` | …...th our ribbons and colour hues. You can personnalise your gift tag with an el… |

_Page-level:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `Quadrichromia` | **Four-Colour** | 3 | `/decipher-quadrichromia-printing-process/` | …Decipher Quadrichromia Printing Process - Impression Originale...… |
| `mesure` | **measure** | 2 | `/bespoke/` | …...s and designers Adaptable sizes, cut to mesure MADE TO MEASURE FOR EACH SPECI… |
| `modelisation` | **modelling** | 1 | `/3d-modeling-surgeon-paper/` | …... on paper sculptures, based on computer modelisation. I sell online my “anima… |
| `modelised` | **modelled** | 1 | `/3d-modeling-surgeon-paper/` | …... cutters; The first time, I used a deer modelised model I found online. I was… |
| `Parallely` | **In parallel** | 1 | `/historical-reissue-gift-wrap-of-the-bnf/` | …...cessible to as many people as possible. Parallely, I study and promote my wor… |
| `memoire` | **memoir** | 1 | `/meet-founders-impression-originale/` | …...”. This book actually led me to write a memoire on the topic “How to give a s… |
| `week-end` | **weekend** | 1 | `/our-designers/agnes-denat/` | …...me to Amsterdam for a surprise birthday week-end with all of them. Do you wor… |
| `graphism` | **graphics** | 1 | `/our-designers/clairecolin/` | …... them, trying to later transcribe their graphism, their strength and delicacy… |
| `proctologue` | **proctologist** | 1 | `/our-designers/francois-ruyer/` | …...weren’t an artist, what would you be? A proctologue. Topics and causes that m… |
| `traited` | **treated** | 1 | `/our-designers/frederic-bonnin-minakani/` | …...beings of animals : they should be well traited and their exploitation and su… |
| `Fleurist` | **Florist** | 1 | `/our-designers/sophie-truant/` | …...artist, what would you be? I would be a Fleurist. Topics and causes that matt… |
| `combinaison` | **combination** | 1 | `/e-shop/burgundy-striped-ribbon-n209-xs/` | …...mmend the see-through striped Ribbon in combinaison with the medium gold gros… |
| `carnaval` | **carnival** | 1 | `/e-shop/carnival-furoshiki/` | …...s fill the Spring air. It’s the biggest carnaval of the year, where everyone … |
| `correspondance` | **correspondence** | 1 | `/e-shop/hot-wax-seals-i-1h30/` | …...erent techniques to seal beautiful your correspondance. Course level suitable… |

## British / American & misc spelling — 28

_Site-wide (≥8 pages) — fix once in shared template/copy:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `COLOR` | **colour** | 364 | `/./gift-tags/` | …... the Greek word, chromia , which means “color”. The technical foundations wer… |
| `colors` | **colours** | 79 | `/./bows/` | …...mend selecting the bow that matches the colors of the design of the gift-wrap… |
| `Description Description` | **Description** | 66 | `/e-shop/arabesque-wraps-x3/` | …...ed rolled in a protective tube Designer Description Description WEIGHT 115 g … |
| `favorite` | **favourite** | 51 | `/./bows/` | …...r high technology and movie stars. YOUR FAVORITE What is your favorite artwor… |
| `colorful` | **colourful** | 14 | `/e-shop/deep-dive-coral-gift-wrap/` | …...rque in the Marais in the 1840s are now colorful. In 1852, when the Encyclopé… |
| `traveling` | **travelling** | 10 | `/./wrap/` | …... to pack their personal belongings when traveling for religious gatherings or… |

_Page-level:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `clamor` | **clamour** | 6 | `/e-shop/carnaval-gift-bag-m/` | …...le bursts of laughter rise in a festive clamor. Violaine’s charcoals make the… |
| `colored` | **coloured** | 5 | `/e-shop/carnival-furoshiki/` | …...cales of the objects I create and using colored papers or with pretty pattern… |
| `neighbors` | **neighbours** | 5 | `/e-shop/24-december-gift-bag-m/` | …...g in the playground, my dogs and lovely neighbors’ horses and cows. I hear na… |
| `Modeling` | **Modelling** | 4 | `/3d-modeling-surgeon-paper/` | …3D Modeling: Surgeon Paper - Impression Originale C...… |
| `Youtube` | **YouTube** | 4 | `/know-how-the-perfect-gift/` | …...asy step-by-step video tutorials on our youtube channel to learn how to make … |
| `Paypal` | **PayPal** | 2 | `/` | …...r the World Secured Payment MasterCard, Paypal, American Express, Carte Bleue… |
| `Gold gold` | **Gold** | 2 | `/e-shop/scissors-65-gold/` | …Scissors 6'5 Gold gold - Impression Originale Close 24,17 € Sc...… |
| `CORPORATE GIFTS CORPORATE GIFTS` | **CORPORATE GIFTS** | 1 | `/corporate-gifts-order-form-online/` | …...ion Originale - Order Form online Close CORPORATE GIFTS CORPORATE GIFTS FILL … |
| `Linkedin` | **LinkedIn** | 1 | `/our-designers/aurore-de-la-morinerie/` | …...IND MORE Instagram @auroredelamorinerie Linkedin https://www.linkedin.com/in/… |
| `Alexander Mcqueen` | **Alexander McQueen** | 1 | `/our-designers/ayako-furness/` | …...ands including Louis Vuitton, Givenchy, Alexander Mcqueen, Burberry as a text… |
| `nut shell` | **nutshell** | 1 | `/our-designers/mr-oneteas/` | …...ged with humor and originality. In in a nut shell, Anthony is a multi-faceted… |
| `instagram` | **Instagram** | 1 | `/workshop-sarah-matthews/` | …...ker for fluff. I follow so many dogs on instagram! VERSATILE MATERIAL Paper i… |
| `Green GREEN` | **Green** | 1 | `/e-shop/gift-tags-green/` | …...sion Originale Close 4,33 € Gift Tags – Green GREEN – x6 Six large gift tags … |
| `Navy NAVY` | **Navy** | 1 | `/e-shop/gift-tags-navy/` | …...sion Originale Close 4,33 € Gift Tags – Navy NAVY – x6 Six large gift tags in… |
| `Red RED` | **Red** | 1 | `/e-shop/gift-tags-red/` | …...sion Originale Close 4,33 € Gift Tags – Red RED – x6 Six large gift tags in t… |
| `Blue BLUE` | **Blue** | 1 | `/e-shop/gift-tags-watercolour-blue/` | …...le Close 4,33 € Gift Tags – Watercolour Blue BLUE Watercolour – x6 Six large … |
| `Light Blue LIGHT BLUE` | **Light Blue** | 1 | `/e-shop/gift-tags-watercolour-light-blue/` | …...le Close 4,33 € Gift Tags – Watercolour Light Blue LIGHT BLUE Watercolour – x… |
| `Pink PINK` | **Pink** | 1 | `/e-shop/gift-tags-watercolour-pink/` | …...le Close 4,33 € Gift Tags – Watercolour Pink PINK Watercolour – x6 Six large … |
| `Purple PURPLE` | **Purple** | 1 | `/e-shop/gift-tags-watercolour-purple/` | …...le Close 4,33 € Gift Tags – Watercolour Purple PURPLE Watercolour – x6 Six la… |
| `Teal TEAL` | **Teal** | 1 | `/e-shop/gift-tags-watercolour-teal/` | …...le Close 4,33 € Gift Tags – Watercolour Teal TEAL Watercolour – x6 Six large … |
| `Yellow YELLOW` | **Yellow** | 1 | `/e-shop/gift-tags-watercolour-yellow/` | …...le Close 4,33 € Gift Tags – Watercolour Yellow YELLOW Watercolour – x6 Six la… |
| `White WHITE` | **White** | 1 | `/e-shop/gift-tags-white/` | …...sion Originale Close 4,33 € Gift Tags – White WHITE – x6 Six large gift tags … |

## Grammar — 37

_Site-wide (≥8 pages) — fix once in shared template/copy:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `for` | **crave** | 34 | `/our-designers/agnes-denat/` | …...ose to a deadline! The artist you crave for (at the moment) I’m love the work… |
| `hard working` | **hard-working** | 8 | `/e-shop/by-the-christmas-fireplace-wraps-x3/` | …...ie Brabbins Sophie Brabbins is a happy, hard working illustrator and surface … |

_Page-level:_

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| `everyday` | **every day** | 4 | `/interview-meet-the-founders-of-impression-originale/` | …...s about the different decisions we make everyday, such as where we source our… |
| `lines` | **line** | 4 | `/e-shop/by-design-northern-beauties/` | …...to see. Agnes exquisitely painted every lines with a folk Russian song playin… |
| `award winning` | **award-winning** | 3 | `/e-shop/look-synchronised-swimming/` | …...e Close Jeannie Phan Jeannie Phan is an award winning freelance illustrator b… |
| `i` | **I** | 2 | `/our-designers/monika-forsberg/` | …... received? A giant cuddly toy Lion when i was about 5. If I say « paper » wha… |
| `double sided` | **double-sided** | 2 | `/e-shop/heavenly-conservatory-gift-wrap/` | …...in Yerevan. This gift wrapping paper is double sided. One side cream / one si… |
| `lives` | **live** | 2 | `/e-shop/look-swimming-in-the-water-lilies/` | …... by the shore, a wonder: “does a Prince lives beneath the leaves?” Prelude to… |
| `to control` | **one to control** | 1 | `/decipher-quadrichromia-printing-process/` | …... The four-color printing process allows to control the colour dosage and repr… |
| `allow` | **allows** | 1 | `/faqs/` | …...d sold around (50x70cm). The maxi sheet allow you to wrap a very large square… |
| `recommend to iron` | **recommend ironing** | 1 | `/faqs/` | …...hipment, as they are made of fabric. We recommend to iron them under a wet cl… |
| `login` | **log in** | 1 | `/faqs/` | …...IND OUT THE STATUS OF MY ORDER ? Please login to your customer page in order … |
| `discuss about our` | **discuss our** | 1 | `/faqs/` | …...fill in the form on our CONTACT PAGE to discuss about our exceptional designs… |
| `to slip` | **allows you to slip** | 1 | `/./gift-tags/` | …... slit in circumflex accent which allows to slip a ribbon. Our gift tags alway… |
| `the our` | **our** | 1 | `/./gift-tags/` | …...ft tags with one of our dry stamps from the our Christmas collection. We are … |
| `is was` | **it was** | 1 | `/how-to-a-commission-for-musee-rodin/` | …...ed the museum. Last time I was in Paris is was next on our list but we didn’t… |
| `months` | **month** | 1 | `/know-how-the-perfect-gift/tutorials-gift-wraps-do-it-yourself/` | …...d, we are releasing new tutorials every months. Subscribe to our youtube chan… |
| `I’m love` | **I love** | 1 | `/our-designers/agnes-denat/` | …...he artist you crave for (at the moment) I’m love the work of the french illus… |
| `In in` | **In** | 1 | `/our-designers/mr-oneteas/` | …...iety tinged with humor and originality. In in a nut shell, Anthony is a multi… |
| `Everyday I` | **Every day** | 1 | `/our-designers/mr-oneteas/` | …... your source of inspiration? The world. Everyday I get inspired by nature, pe… |
| `Henri Matisse Henri Matisse` | **Henri Matisse** | 1 | `/our-designers/sophie-truant/` | …...e than one! Louise Bourgeois Kiki Smith Henri Matisse Henri Matisse Elsa Mora… |
| `mid century` | **mid-century** | 1 | `/our-designers/tara-lilly/` | …...inspired by folk art, antique textiles, mid century design, and her family. S… |
| `Every steps` | **Every step counts** | 1 | `/our-philosophy/` | …...ly in France with top quality supplies. Every steps count We try to minimise … |
| `makes` | **make** | 1 | `/./scissors/` | …... Add to cart Add to cart The tools that makes the maker Scissors are central … |
| `is` | **are** | 1 | `/./scissors/` | …...cise as they are designed. The criteria is twofold: the object must be beauti… |
| `is enters` | **enters** | 1 | `/workshop-cut-fold/` | …...n, a personalized label … Gift wrapping is enters in the larger understanding… |
| `day dreaming` | **daydreaming** | 1 | `/workshop-damien-the-leather-compagnion/` | …...pecialise in Leather? As a child, I was day dreaming that later I will become… |
| `a a` | **a** | 1 | `/workshop-damien-the-leather-compagnion/` | …...car designer… So I naturally sought for a a vocational training, which propos… |
| `have` | **has** | 1 | `/workshop-damien-the-leather-compagnion/` | …...orking with the real material. It often have “carte blanche” and I skip the p… |
| `there is only hills` | **there are only hills** | 1 | `/workshop-miriam-fitzgerald-juskova/` | …...e mountains The High Tatras. In Ireland there is only hills (which they call … |
| `lead` | **leads** | 1 | `/workshop-miriam-fitzgerald-juskova/` | …... I realize they are white inside and it lead me to whole new body of work. Ph… |
| `well know` | **well-known** | 1 | `/workshop-pippa-dyrlaga/` | …...y, the draw of a beautiful place with a well know creative community was real… |
| `its` | **it's** | 1 | `/workshop-pippa-dyrlaga/` | …...#5 After all details have been cut out, its time to cut it free from the pape… |
| `two dimensional` | **two-dimensional** | 1 | `/workshop-sarah-matthews/` | …...a significant amount of my work is also two dimensional, layered papercuts. I… |
| `a beautiful personalized labels` | **beautiful personalized labels** | 1 | `/./wrap/` | …...e finishing details on the gift such as a beautiful personalized labels on we… |
| `a several hues` | **several hues** | 1 | `/e-shop/deep-dive-night-blue-furoshiki/` | …...ls section. This design is available in a several hues ( rose gold and coral … |
| `a` | **the** | 1 | `/e-shop/farandole-of-pine-cones-gift-bag-s/` | …...le, by Sophie. Check the same design in a best paired gift wrap + ribbons and… |

## Punctuation — 19

| Flagged | Fix | Pages | Example | Context |
|---|---|---|---|---|
| ` so` | **, so** | 6 | `/how-to-a-commission-for-musee-rodin/` | …...f my best friends lives outside of Paris so I travel to France about once a y… |
| `‘` | **‘world of sumptuous forms and colours’** | 5 | `/e-shop/by-design-on-the-birds-wings/` | …...uveau styles, creating what he called a ‘world of sumptuous forms and colours… |
| `,` | **—** | 3 | `/illustrated-interview-aiko-fukawa/` | …...s far as I can remember, I always drew! , Aiko Fukawa. A CALLING Why did you … |
| ` but` | **, but** | 3 | `/e-shop/by-design-evergreen-wreaths/` | …... crowns by the wealthy. Centuries passed but the tradition remained, and Gill… |
| `’` | **Harmony.** | 2 | `/our-designers/agnes-denat/` | …...mes down to a very simple idea: Harmony ’. If you weren’t an artist, what wou… |
| `However` | **However,** | 1 | `/3d-modeling-surgeon-paper/` | …...result I got and how poeple receive it. However I would say that my favorite … |
| `double check` | **double-check** | 1 | `/faqs/` | …... tied to your card. Please make sure to double check that the billing address… |
| `three story` | **three-story** | 1 | `/how-to-a-commission-for-musee-rodin/` | …... City. I’m in the process of building a three story studio with my husband, P… |
| ` so` | **, so** | 1 | `/how-to-a-commission-for-musee-rodin/` | …... work all day. My work is very enjoyable so this isn’t a bad thing for me. In… |
| `Paul&Joe` | **Paul & Joe** | 1 | `/our-designers/clairecolin/` | …...Freedom. Claire Colin CLIENTS FIND MORE Paul&Joe, Christian Lacroix, Léonard,… |
| `..` | **.** | 1 | `/our-designers/kim-heegyum/` | …...eryday life. Conversations, my dog, food.. It’s always full of stories. What … |
| `well formed` | **well-formed** | 1 | `/talk-with-our-founder-the-wrapping-ceremony/` | …...ion intended for the lucky recipient. A well formed gift crystalizes these un… |
| `carbon neutral` | **carbon-neutral** | 1 | `/talk-with-our-founder-the-wrapping-ceremony/` | …...n to running their IT infrastructure on carbon neutral cloud providers. Compa… |
| `do it yourself` | **do-it-yourself** | 1 | `/talk-with-our-founder-the-wrapping-ceremony/` | …... Impression Originale From step-by-step do it yourself tutorials, to beautifu… |
| `brother in law` | **brother-in-law** | 1 | `/workshop-miriam-fitzgerald-juskova/` | …...in Ireland. At first, we stayed with my brother in law but not long we found … |
| `labour intensive` | **labour-intensive** | 1 | `/workshop-pippa-dyrlaga/` | …...orking on current projects. It is quite labour intensive with long periods of… |
| `left hand` | **left-hand** | 1 | `/workshop-pippa-dyrlaga/` | …... smell? The window is right next to the left hand side of my desk and outside… |
| `you` | **you.** | 1 | `/workshop-pippa-dyrlaga/` | …...a commission that was really special to you It wasn’t technically a commissio… |
| `ingredients` | **ingredients'** | 1 | `/workshop-the-pineapple-chef/` | …... I shoot, the more I am in awe with the ingredients beauty. Colors and textur… |
