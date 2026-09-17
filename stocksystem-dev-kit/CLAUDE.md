# Stock System — WordPress theme

Custom WooCommerce theme for a Persian/RTL refurbished-computer store (Neyshabur, Iran — real business: «استوکی سیستم»).

## Before you write code
1. Read `DECISIONS-v1.1.md` **first, always** — it overrides anything in the `.dc.html` files where they conflict (CTA color, checkout model, login model, warranty length, address, shipping, installments, status colors, saved-card removal). This is not optional context, it is the spec.
2. Read `README.md` in full.
3. Then open `00 Overview.dc.html` and `Stock System Design System.dc.html` in a browser.

## Ground rules
- The `.dc.html` files are **design references**, not code to port. Recreate them as WordPress/WooCommerce templates — and where `DECISIONS-v1.1.md` contradicts a `.dc.html` file, follow the decisions file.
- Override WooCommerce templates in `woocommerce/` rather than building parallel pages.
- RTL is the base direction. Use CSS logical properties everywhere.
- All styling from the token table in README.md, as CSS custom properties. No new colors. Breakpoints and the CTA/status color assignments come from `DECISIONS-v1.1.md`, not from the raw token dump (the two disagree in a few places — decisions file wins).
- Persian-Indic digits for all user-facing numbers, with `٬` (U+066C) as the thousands separator — not `,` or `٫` (both appear inconsistently across the `.dc.html` files; neither is correct Persian).
- Latin strings (SKU, phone, model names) go in `dir="ltr"` spans.
- Body line-height stays 1.85–2.05 for Persian text.
- Never hardcode business numbers (warranty length, shipping thresholds, address, phone) — theme settings / customizer options, so the client can edit them without touching code. Default values must match `DECISIONS-v1.1.md`, not the placeholder copy in the `.dc.html` files.
- Every interactive element must be a real semantic element: real `<button>`/`<input>`/`<label for>`, not `<span>` with a click handler (the design files lean heavily on styled spans — do not carry that into markup).
- Add real `:hover`/`:focus-visible`/`:disabled` states to every interactive component; add `aria-expanded`/`aria-controls`/`aria-live` to accordion, tabs, mega-menu, modal, toast. The design files define almost none of this — build it as you go, don't wait for a spec that isn't coming.
- Installment/BNPL UI (badges, payment-method radio, the calculator in `14 Support Pages`) stays out of v1 per decision #6 — don't build it.
- Saved credit-card UI/storage stays out per decision #10 — wallet balance only.
- Product photos, logo files, and most page copy in the design are placeholders; the client will supply real assets and copy later. Build the templates so swapping them in is just a media-library upload / customizer edit, not a code change.

## Environment
Local development now, on the developer's own machine, with a plan to push to real hosting later — keep the theme host-agnostic (no environment-specific paths, no hardcoded local URLs) so that move is a plain files+DB migration.

## Build order
Scaffold + tokens (from `DECISIONS-v1.1.md` + README) + fonts (subset later, use the .ttf files as-is for now) → header/footer → product card (14 states) → archives → single product + configurator → cart/checkout wizard (4-step, per decision #2) → account cluster → content pages → missing UI states (empty cart, empty search, empty category, payment failed, skeleton for product/cart, network error, mini-cart drawer, mega-menu, toast) → tablet/condensed-desktop responsive pass (the design has none — build these breakpoints from the same component logic, don't wait for a design file).

## Where things are specified
Each design file is numbered and its internal sections are labeled (15-A, 16-C, …). The coverage checklist at the end of `16 Shop Pages.dc.html` maps every WordPress template to its design source — use it as the build checklist. Add to that checklist (not in the original file): `thankyou.php`, `taxonomy-product_tag.php`, `cart/cart-empty.php`, `myaccount/form-edit-address.php`, `myaccount/form-edit-account.php`, a real product review template, and `archive.php` for the blog (separate from `home.php`).
