# Stock System — WordPress theme

Custom WooCommerce theme for a Persian/RTL refurbished-computer store.

## Before you write code
Read `README.md` in full. Then open `00 Overview.dc.html` and `Stock System Design System.dc.html` in a browser.

## Ground rules
- The `.dc.html` files are **design references**, not code to port. Recreate them as WordPress/WooCommerce templates.
- Override WooCommerce templates in `woocommerce/` rather than building parallel pages.
- RTL is the base direction. Use CSS logical properties everywhere.
- All styling from the token table in README.md, as CSS custom properties. No new colors.
- Persian-Indic digits for all user-facing numbers. Latin strings (SKU, phone, model names) go in `dir="ltr"` spans.
- Body line-height stays 1.85–2.05 for Persian text.
- Never hardcode business numbers (warranty length, installment rate, thresholds) — theme settings.
- Product photos in `assets/products/` are placeholders.

## Build order
Scaffold + tokens + fonts → header/footer → product card (14 states) → archives → single product + configurator → cart/checkout → account cluster → content pages → UI states.

## Where things are specified
Each design file is numbered and its internal sections are labeled (15-A, 16-C, …). The coverage checklist at the end of `16 Shop Pages.dc.html` maps every WordPress template to its design source — use it as the build checklist.
