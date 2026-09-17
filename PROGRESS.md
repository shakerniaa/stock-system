# Stock System — Build Progress & Continuation Notes

This file exists so a fresh Claude Code session (on another machine, or after a context reset) can pick up this project without re-deriving everything from scratch. Read this, then `stocksystem-dev-kit/CLAUDE.md` and `stocksystem-dev-kit/DECISIONS-v1.1.md` (the binding spec — overrides the `.dc.html` design files wherever they conflict).

## Repo layout

- `design_handoff_stocksystem_wordpress/` — **older** design handoff (superseded, kept only so nothing was discarded).
- `stocksystem-dev-kit/` — **current** design handoff. Has `CLAUDE.md` + `DECISIONS-v1.1.md` on top of the same `.dc.html` files. Always work from this one.
- `stocksystem-theme/` — the actual WordPress/WooCommerce theme being built. This is the deliverable.
- `Stock-System.zip` / `stocksystem-dev-kit.zip` — original zips the handoff/dev-kit folders were extracted from. Redundant with the extracted folders; kept per an explicit "don't leave anything out" request.

## What's built so far (in build order)

1. **Scaffold** — tokens/typography/spacing as CSS custom properties (`assets/css/tokens.css`), Peyda fonts, RTL base (`assets/css/base.css`), theme bootstrap (`functions.php` + `inc/*.php`).
2. **Header/footer/nav** — desktop chrome (topbar/masthead/nav + mega menu + mini-cart + search-suggestions shell), mobile chrome (hamburger + drawer), footer. Switches to mobile chrome below 1024px (tablet has no dedicated design — treated as mobile for now, flagged as a later responsive pass).
3. **Product card component** (`template-parts/product/card.php` + `buy-box-*.php`) — all 14 states from `15 Product States.dc.html §15-B`, reused by every listing on the site (archives, home, related products, mega menu).
4. **Archive templates** — `woocommerce/archive-product.php` (shop root hub), `taxonomy-product_cat.php` (category + subcategory), `taxonomy-product_brand.php`, `search.php`. Shared filter sidebar (`template-parts/archive/`), FAQ accordion component (`template-parts/global/faq-accordion.php`, FAQPage schema, reusable).
5. **Single product + configurator** — `woocommerce/content-single-product.php`, and the RAM/storage configurator (`woocommerce/single-product/add-to-cart/variable.php`): custom pill-tile UI wired to WooCommerce's native hidden `<select>`s and `found_variation` event, so pricing/stock/SKU stay 100% server-authoritative. Add-ons (Windows licence, case+mouse, warranty extension) are a separate flat-surcharge layer per the README, not variations (`inc/product-addons.php`).
6. **Homepage** (`front-page.php` + `template-parts/home/*`) — hero, categories, "deal of the week" (on-sale products, hides itself if none exist), trust band, blog teaser.

### Not built yet (next up per the recommended build order)
7. Cart → checkout → confirmation (4-step wizard per decision #2; `04 Cart Checkout.dc.html` is largely obsolete — only its cart line-item/order-summary patterns survive; `12 Checkout Flow.dc.html` is the real reference).
8. My Account cluster: OTP login (decision #3 — needs a custom OTP solution, WP core is password-based), orders, order detail, tracking, wishlist (`08`, `16-C`, `16-D`).
9. Content templates: blog (`05`), repair (`06`), grading page (`07` — the grading taxonomy already exists with A/B/C seeded, this page is its home), about/contact (`09`), support pages incl. FAQ/terms/installment guide (`14` — **do not build the installment calculator**, disabled per decision #6).
10. Missing UI states pass: empty cart, empty search (done), empty category, payment failed, skeletons, network error, 404, toasts.
11. Tablet/condensed-desktop responsive pass (no design reference — build from existing component logic).

## Decisions-doc corrections already applied (don't re-introduce the design's originals)

- CTA fill is **teal + white text always** (add to cart, buy, pay). Orange is never a filled primary action and never has white text (contrast). Applied throughout buttons/badges/configurator.
- "In stock" status is **green** (`--c-status-success`), not teal — teal is brand/CTA only.
- Warranty is **1 month**, not the design's 18/6/3-month placeholders — always pulled from `stocksystem_business('warranty_text')`, never hardcoded.
- Address/phone are Neyshabur (`نیشابور، بین بعثت ۳۰ و ۳۲` / `۰۹۰۳۴۵۳۵۰۲۵`), not Tehran — via Customizer (`inc/customizer-settings.php`).
- Shipping copy is "free pickup in Neyshabur + nationwide post/Tipax", not "Tehran same-day/48h".
- Installment/BNPL UI is **out of scope for v1** — don't build it if you see it in a design file.
- Saved credit-card storage is **out of scope** — wallet balance only, if/when the wallet is built.
- Thousands separator is `٬` (U+066C), Persian-Indic digits everywhere user-facing. `inc/persian-numerals.php` has the helpers; `inc/woocommerce.php` also hooks `formatted_woocommerce_price` so every native `wc_price()` call gets this for free.

## Bugs found and fixed along the way

- **Customizer defaults never reached the front end.** `stocksystem_business()` called `get_theme_mod($key)` without a `$default` argument — a Customizer `'default'` only applies inside the Customizer preview, never on the real site. Fixed by centralizing key/default/label in `stocksystem_business_settings()` and passing the default through. This was invisible until we actually had a live site to look at (see below) — worth remembering that Customizer defaults always need the explicit second argument.

## Live preview environment (this machine only — see note below)

There's a Local by Flywheel site called **`stock-system`** (domain `stock-system.local`) already set up with WordPress + WooCommerce + this theme, active and working. Key facts if you need to touch it again:

- **Don't rely on the `.local` domain resolving.** Windows hosts-file writes are blocked here (Kaspersky, centrally managed — settings are locked too, not just the file). Worked around this by pointing WordPress at itself directly: `siteurl`/`home` options are set to `http://127.0.0.1:10004` (the site's actual nginx port, found in `%APPDATA%\Local\sites.json`). **Preview URL: `http://127.0.0.1:10004/`.**
- Site locale is `fa_IR` for both WordPress core and WooCommerce (language packs downloaded and installed) — needed because a lot of native strings (breadcrumbs, stock status text, tab labels) come from WP/WC core translations, not the theme.
- WooCommerce's auto-created pages were renamed from their English defaults: Shop→فروشگاه, Cart→سبد خرید, Checkout→تسویه‌حساب, My account→حساب کاربری (slugs left as `/shop/` `/cart/` etc. so URLs stay stable).
- `product_grading` (A/B/C, seeded) and `product_brand` taxonomies are registered by the theme itself (`inc/taxonomies.php`) — no plugin provides these.
- Catalog is currently **empty** (no real products) — that's why "deal of the week" and most category tiles look sparse. Not a bug.
- To run one-off WP-context PHP (like I did to activate plugins/switch themes/seed data without wp-admin access), the working recipe on this machine was:
  ```
  "<Local's PHP CLI binary>" -c "<Local's rendered php.ini for this site>" script.php
  ```
  found via `%APPDATA%\Local\lightning-services\php-*\bin\win64\php.exe` and `%APPDATA%\Local\run\<siteId>\conf\php\php.ini` (the *rendered* ini, not the `.hbs` template in the site's own `conf/` folder — the rendered one has the real MySQL port). This bypasses needing wp-admin or wp-cli entirely; useful if wp-cli isn't installed.

### This environment is NOT portable to the other machine

The Local site (WordPress database, uploaded media, installed plugins) lives only on this computer — it is not part of this git repo and can't be. On the other machine you'll need your own local WordPress+WooCommerce install to preview against; only the **theme code** (`stocksystem-theme/`) is shared via this repo. If you want the two environments to actually converge (same catalog/content), that needs either a periodic DB export/import between the two Local sites, or moving to a shared staging server — ask if you want help setting either of those up.

## Working agreement across two machines

This repo is the sync mechanism for code — not real-time, standard git (`pull` before starting, `commit`/`push` when done). Whoever picks this up next should `git pull` first and skim this file plus recent `git log` before making changes, to stay consistent with the patterns above (variant-detection helpers in `inc/product-card.php`, the term-meta pattern for admin-editable content in `inc/term-meta-fields.php`, etc.) rather than re-deriving a parallel approach.
