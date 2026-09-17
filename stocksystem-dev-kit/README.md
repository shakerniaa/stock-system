# Handoff: قالب وردپرس فروشگاه استوک سیستم

## Overview
A complete design set for **Stock System** (استوک سیستم) — a Persian/RTL e-commerce site selling refurbished ("stock") European laptops, all-in-ones, and PCs, plus a repair service. The target implementation is a **custom WordPress theme running WooCommerce**, fully RTL, in Persian.

17 design documents cover every template the site needs: storefront, catalog, product (including a live RAM/storage configurator), cart, a four-step checkout, search, account, order tracking, content pages, plus mobile flows and cross-cutting UI states.

## About the Design Files
**The HTML files in this bundle are design references, not production code.** They are prototypes that show intended look, copy, and behavior. Do not copy their markup into PHP templates.

The task is to **recreate these designs as a WordPress theme** using WordPress and WooCommerce's own patterns — template hierarchy, `woocommerce/` template overrides, hooks and filters, the block editor or ACF for editable content, and `wp_enqueue_*` for assets. Where a design shows a WooCommerce screen (cart, checkout, account, order), override the corresponding WooCommerce template rather than building a parallel page.

Each design file opens directly in a browser. Some contain small interactive demos (the product configurator, FAQ accordion, installment calculator); the interaction logic lives in a `<script>` block at the bottom of the file and is readable as a behavior spec.

## Fidelity
**High fidelity.** Colors, typography, spacing, radii, and copy are final and should be reproduced exactly. Every value is inline in the HTML, so computed styles in devtools are authoritative. Persian copy in the designs is real copy, not lorem — use it as the default content, but the client will edit business specifics (warranty periods, installment rates, address) later.

Two things are deliberately NOT final:
- **Product photography** — all product images are stand-ins. The client will supply real photos.
- **Payment gateway / brand logos** — placeholders; swap for real marks.

---

## Design Tokens

### Colors
| Token | Hex | Use |
|---|---|---|
| `--c-ink-900` | `#04211F` | Page background (dark surfaces), deepest green |
| `--c-ink-800` | `#06292A` | Site header, footer, dark section background |
| `--c-ink-700` | `#08302E` | Raised card on dark |
| `--c-ink-600` | `#0B3A38` | Main nav bar |
| `--c-teal-500` | `#0EBAAF` | Primary accent, links, active state, focus ring |
| `--c-teal-600` | `#0A8F87` | Accent text on light backgrounds (AA-safe) |
| `--c-teal-700` | `#0A6F69` | "In stock" badge text |
| `--c-teal-300` | `#5FD9D0` | Accent text on dark backgrounds |
| `--c-teal-050` | `#E6F7F5` | Accent surface tint |
| `--c-teal-025` | `#F7FDFC` | Selected-row tint |
| `--c-orange-500` | `#F58220` | CTA buttons, phone number, link hover |
| `--c-orange-700` | `#B25708` | Price emphasis on light |
| `--c-orange-800` | `#9A3412` | Orange text on light (AA-safe) |
| `--c-orange-050` | `#FDEEE0` | Orange surface tint |
| `--c-red-600` | `#C62828` | Sale price, clearance badge |
| `--c-red-700` | `#9B2222` | Out-of-stock / error text |
| `--c-red-050` | `#FBE9E9` | Error surface |
| `--c-amber-500` | `#F2C94C` | Low-stock border |
| `--c-amber-700` | `#7A5800` | Low-stock text |
| `--c-amber-050` | `#FCF3DA` | Low-stock surface |
| `--c-paper` | `#FFFFFF` | Content background |
| `--c-paper-alt` | `#F4F8F8` | Subtle section / image well |
| `--c-line` | `#DDE7E6` | Border on light |
| `--c-line-soft` | `#EFF5F4` | Divider inside cards |
| `--c-text` | `#06292A` | Body text on light |
| `--c-text-2` | `#4C6664` | Secondary text on light |
| `--c-text-3` | `#8FA5A3` | Tertiary / placeholder |
| `--c-text-disabled` | `#B7C7C6` | Disabled option |
| `--c-on-dark` | `#EBEDEC` | Primary text on dark |
| `--c-on-dark-2` | `#B9CBC9` | Secondary text on dark |
| `--c-on-dark-3` | `#9FB4B2` | Tertiary text on dark |

Alpha overlays on dark use `rgba(235,237,236,.06 / .08 / .1 / .12 / .14 / .16 / .2)` for surfaces and borders.

### Typography
- **Family:** Peyda (Persian, self-hosted), fallback Vazirmatn → `system-ui`, `sans-serif`.
- **Weights shipped:** 400, 500, 600, 700, 800/900.
- Fonts are in `assets/fonts/` as TTF. **Convert to WOFF2 with Persian subsetting before production** (Arabic + Persian + Latin + digits). Expect roughly a 70% size reduction. Load with `font-display: swap`.
- **Scale in use:** 11, 11.5, 12, 12.5, 13, 13.5, 14, 14.5, 15, 15.5, 16, 17, 18, 19, 20, 21, 22, 24, 26, 27, 28, 30, 32, 34, 38px. Page titles use `clamp(26px, 3.6vw, 40px)`.
- **Line heights:** 1.4–1.5 headings, 1.85–2.05 body. Persian text needs the generous body value — do not tighten it.
- **Numerals:** all user-facing numbers render as Persian-Indic digits (۰۱۲۳۴۵۶۷۸۹). Latin-only strings (SKU, phone, model names, sizes like `15.6"`) sit in `direction: ltr` spans inside the RTL flow.

### Spacing, radius, elevation
- Spacing steps used: 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 32, 34, 36, 40, 44, 48, 52, 56px.
- Section padding on desktop: `40–48px` horizontal inside the 1280px content frame.
- Radius: `6` chips/tiny, `8–9` inputs & small buttons, `10–11` buttons & option tiles, `12` cards, `14` panels, `16–18` large panels, `26` phone frames, `999px` pills.
- Borders: `1px` default, `1.5px` interactive/selected, `2px` strongly selected (config options, featured card).
- Shadow (used sparingly): `0 30px 60px -30px rgba(0,0,0,.6)` on dark-mode frames only.

### Layout
- Desktop content frame: **1280px** fixed, centered.
- Mobile frames: **390 × 844** (iPhone 14 reference); design docs render them at 320px wide.
- Grids: 4-column product grid at desktop, 2-column at tablet, 1–2 at mobile. Category page is a 9/3 split (products/filter sidebar).
- Breakpoints to implement: **≥1280** desktop, **1024–1279** condensed desktop, **768–1023** tablet, **<768** mobile.
- RTL is the base direction. `dir="rtl"` on `<html>`; use logical properties (`margin-inline-start`, `padding-inline`, `inset-inline`) throughout so an LTR variant stays possible.

### Accessibility
- Minimum touch target 44×44px (mobile buttons are 46–52px tall).
- Body text ≥13px; never below 12px, and 12px only for metadata.
- Text contrast ≥4.5:1. The AA-safe pairings are already chosen: `#0A8F87` (not `#0EBAAF`) for teal text on white; `#9A3412`/`#B25708` for orange text on white; `#0EBAAF` is for fills and borders, and for text only on the dark greens.
- Focus ring: 2px `#0EBAAF` with 2px offset.

---

## Screens / Views

Documents are numbered; each contains multiple labeled sections (e.g. `15-A`, `15-B`) marked in the page.

| # | File | What it specifies |
|---|---|---|
| 00 | `00 Overview.dc.html` | Index of the whole set. Start here. |
| — | `Stock System Design System.dc.html` | Brand analysis, tokens, components, modes, responsive rules, a11y. **Read before writing any CSS.** |
| — | `Stock System Commerce SEO.dc.html` | IA, URL structure, metadata, schema strategy. |
| 01 | `01 Home.dc.html` | Home: 7/5 hero, six categories, deal of the week, trust bar, blog strip, footer. |
| 02 | `02 Category.dc.html` | Product category archive: 9/3 grid + filter sidebar, CPU/RAM/price facets, SEO text, FAQ block. Desktop + mobile. |
| 03 | `03 Product.dc.html` | Single product: gallery, buy box, installment block, device test-report panel. |
| 04 | `04 Cart Checkout.dc.html` | Cart, shipping, payment method, tracking timeline. |
| 05 | `05 Blog.dc.html` | Blog index + single article, with in-article product card. |
| 06 | `06 Repair.dc.html` | Repair service: price table, process steps, request form. |
| 07 | `07 Stock Condition.dc.html` | Grading standard (A+/A/B/C), three condition indicators, FAQ, sample test sheet. |
| 08 | `08 Account.dc.html` | My Account: OTP login, order list, test sheet, repair-quote approval. |
| 09 | `09 About Contact.dc.html` | About + contact: workshop story, numbers, address & hours, map. |
| 10 | `10 Mobile Flows.dc.html` | Mobile: five-step purchase flow, drawer nav, filter sheet, mini-cart, empty/error/loading/404 states. |
| 11 | `11 Desktop States.dc.html` | Mega menu, mini-cart, search suggestions, form errors, skeleton loading, 404, toasts. |
| 12 | `12 Checkout Flow.dc.html` | Four-step checkout: address (personal/business), shipping (courier + time slot, post, in-person), payment (gateways, wallet, installments, coupon), confirmation with five-step timeline. |
| 13 | `13 Search Results.dc.html` | Search suggestions dropdown, results grid, stock-specific filters (battery health, runtime hours), zero-results with availability alert, mobile filter sheet. |
| 14 | `14 Support Pages.dc.html` | Shared long-form template (sticky TOC + 720px measure): terms, privacy, FAQ accordion, installment guide with live calculator. Mobile views included. |
| 15 | `15 Product States.dc.html` | **RAM/storage configurator** + all 14 product card states + edge-case buy boxes. |
| 16 | `16 Shop Pages.dc.html` | Shop root, subcategory, brand archive, wishlist, login/register/lost-password, order tracking, order detail. Ends with a **template coverage checklist**. |

---

## Interactions & Behavior

### Product configurator (15-A) — the most complex piece
A single product with two variation axes plus add-ons, priced live.

- **RAM options:** 8GB (+0), 16GB (+2,400,000), 32GB (+5,900,000), 64GB (+11,500,000 — *out of stock*).
- **Storage options:** 256GB (+0), 512GB (+1,900,000), 1TB (+4,300,000), 2TB (+9,800,000 — *out of stock*).
- **Add-ons (checkboxes):** Windows 11 Pro licence (+1,200,000), case + wireless mouse (+890,000), warranty extension to 24 months (+1,800,000).
- **Base price:** 32,900,000 IRR-toman. Strike-through "list" price is base + 5,000,000 plus the same deltas.
- **Live outputs:** total price, SKU (`SS-EB840G8-{ram}-{storage}`), stock line, lead-time line, monthly instalment (`total × 0.7 × 1.23 ÷ 12`).
- **Rule:** if either axis is above its base value, the item is treated as workshop-upgraded — stock line becomes "ارتقا در کارگاه انجام می‌شود · موجود" and lead time becomes 1–2 business days instead of same-day dispatch.
- **Out-of-stock options** render struck-through, 70% opacity, `cursor: not-allowed`, and are not clickable.

**WooCommerce mapping:** a variable product with two attributes (RAM, storage). Every combination needs its own SKU and stock. Add-ons are a separate add-on/extra-fields layer, not variations. Price recalculation must be server-authoritative; the JS is presentation only.

### Installment calculator (14-C)
Price slider (10M–90M, 1M steps) × term (6/9/12/18 months). Down payment 30%. Monthly = `(price × 0.7 × (1 + 0.23 × term/12)) ÷ term`. **The 23% annual rate must come from a theme setting, never hardcoded.**

### FAQ accordion (14-B)
Single-open accordion, 7 questions, category chips above. Answers must be **server-rendered** (present in the HTML even when collapsed) and marked up with **FAQPage schema** — collapsing is CSS/JS only.

### Other behaviors
- Search suggestions appear on input; see 13-A for the dropdown anatomy.
- Mobile filters open as a bottom sheet (10, 13-D), not a modal.
- Cart reservation: 30 minutes on low-stock items (shown in 15-C).
- Out-of-stock products swap the buy button for a "notify me" phone-number form.
- Zero-price products swap the buy button for a quote-request form.
- Hover: links go `#0EBAAF` → `#F58220`; transition `.16s ease-out`.

---

## The 14 product states (15-B)
Implement each as a card modifier. **Rule: at most two badges per card**, ordered right-to-left as stock status → discount → marketing label.

1. **In stock** — teal "موجود" badge, orange add-to-cart.
2. **Out of stock** — red badge, greyscale 50%-opacity image, "خبرم کن" outline button.
3. **Low stock** — amber card border, "تنها ۱ عدد" badge, urgency CTA.
4. **Clearance** — red badge + countdown chip, dual price.
5. **Percentage discount** — red `٪۱۵−` badge, dual price.
6. **Variable** — "۶ پیکربندی" badge, attribute chips, "select options" CTA.
7. **Colour choice** — swatch row with selected ring.
8. **Size choice** — size chips; unavailable size struck through and dashed.
9. **Model choice** — stacked model rows with per-model stock.
10. **Price range** — "from X to Y" instead of a single price.
11. **No price** — "قیمت با استعلام", dark quote-request CTA.
12. **Labelled** — corner ribbon + secondary pill.
13. **New arrival** — teal "تازه رسید" badge.
14. **Featured** — 2px orange border, warm card background, star badge.

"Low stock" is not a separate WooCommerce state — it is the low-stock threshold; set it to 2.

---

## WordPress / WooCommerce implementation notes

### Template coverage
Document 16 ends with a full checklist mapping 26 WordPress templates to their design source. Reproduce that table as your build checklist. Highlights:

```
front-page.php ................... 01
archive-product.php .............. 16-A   (shop root — entry paths, not a bare grid)
taxonomy-product_cat.php ......... 02, 16-B
taxonomy-product_brand.php ....... 16-B
single-product.php ............... 03, 15
search.php ....................... 13
home.php / single.php ............ 05
woocommerce/cart/ ................ 04
woocommerce/checkout/ ............ 04, 12
woocommerce/myaccount/ ........... 08, 16-C, 16-D
wishlist ......................... 16-D
order tracking ................... 16-D
page-terms / privacy / faq ....... 14
page-installments.php ............ 14-C
page-repair.php .................. 06
page-grading.php ................. 07
404.php .......................... 10, 11
```

### Custom data the theme needs
- **Product grading** (A+/A/B/C) — product attribute or taxonomy; shown on cards, product page, and the printed test sheet.
- **Battery health %** and **runtime hours** — numeric product meta; both are search filters (13-B).
- **Device test report** — repeatable meta group rendered on the product page and downloadable from the order detail.
- **Product brand** — a real taxonomy (`product_brand`) so brand archives exist.
- **Marketing labels** (new / featured / bestseller) — product **tags**, not categories, so each gets a filterable archive.
- **Installment eligibility + rate** — theme settings, with a per-product minimum threshold (10M toman in the designs).

### Recommended build order
1. Theme scaffold, RTL base, token CSS custom properties, Peyda WOFF2 with Persian subsetting.
2. Header / footer / nav (01) — every template depends on them.
3. Product card component with all 14 state modifiers (15-B) — reused by 6+ templates.
4. Archive templates: shop, category, subcategory, brand, search (16-A, 02, 16-B, 13).
5. Single product + configurator (03, 15-A) — the hardest piece; budget accordingly.
6. Cart → checkout → confirmation (04, 12).
7. My Account cluster: login/register/lost password, orders, order detail, tracking, wishlist (08, 16-C, 16-D).
8. Content templates (05, 06, 07, 09, 14).
9. States pass: loading skeletons, empty, error, 404, toasts (10, 11) — do not leave these to the end of QA; they are specified, so build them.

### Performance & SEO
- The 1280px frame is the design canvas, not a hard max — let content scale on larger viewports with a max-width container.
- Product images: serve WebP/AVIF with `srcset`; the design uses `mix-blend-mode: multiply` on transparent-background product PNGs over `#F4F8F8`.
- Schema: Product + Offer + AggregateRating on product pages, FAQPage on 14-B and category FAQ blocks, BreadcrumbList sitewide, LocalBusiness on 09.
- Show a "last revised" date on legal pages (both trust and SEO) — it is in the 14 design.

---

## Assets
- `assets/fonts/Peyda-{Regular,Medium,SemiBold,Bold,ExtraBold}.ttf` — Persian brand typeface. **Subset and convert to WOFF2.**
- `assets/logo-lockup-dark.png` — logo for dark backgrounds (site header/footer).
- `assets/logo-lockup-light.png` — logo for light backgrounds.
- `assets/logo-symbol-dark.png` — symbol only (favicon, mobile header, app icon).
- `assets/products/*.png` — **placeholder product photography.** Client will supply real images. Keep transparent backgrounds and the multiply blend treatment.
- Icons are inline SVG, 1.6–1.8px stroke, `stroke-linecap="round"`, sized 11–22px. Keep them inline or build a sprite; do not swap in an icon font (breaks the stroke weight and RTL mirroring control).

## Content the client still needs to confirm
These values appear in the designs as reasonable defaults and must be verified before launch:
warranty periods (18 months hardware / 6 months battery), 7-day return window and who pays return shipping, installment terms (23% annual, 30% down, 40M toman no-guarantor ceiling), store address and opening hours, and the payment gateways actually in use.

## Files
All design documents are in this folder alongside `assets/`. Open `00 Overview.dc.html` first, then `Stock System Design System.dc.html`.
