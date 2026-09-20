# Hosting & speed checklist

What the theme already does for speed (no action needed): WOFF2 fonts (~19 KB per weight, three preloaded), minified/bundled CSS and JS in `stocksystem-theme/assets/dist`, deferred jQuery, no jQuery Migrate / emoji / block-editor CSS on shop pages, responsive `srcset` and lazy-loading on product/blog images, WebP for newly uploaded images, a priority hint on the hero image, a small optimised logo.

What has to be done on the host — these matter more than anything in the theme:

## 1. Server
- **PHP 8.2 or newer with OPcache on** (`opcache.enable=1`, `opcache.memory_consumption=128`, `opcache.validate_timestamps=0` in production, then reload PHP-FPM after each deploy).
- **MySQL/MariaDB** (not SQLite — the local preview uses SQLite only for convenience). `innodb_buffer_pool_size` sized to the database.
- **HTTPS + HTTP/2 (or 3).**
- **Brotli or gzip** for `text/html, text/css, application/javascript, application/json, image/svg+xml`. (The CSS+JS above shrink to roughly a third when compressed.)
- **Long cache headers for static files** — the build gives changed files a new `?ver=` (file time), so they can be cached for a year:

  Nginx:
  ```nginx
  location ~* \.(?:css|js|woff2|webp|png|jpg|jpeg|gif|svg|ico)$ {
      expires 1y;
      add_header Cache-Control "public, immutable";
      access_log off;
  }
  ```
  Apache (`.htaccess`):
  ```apache
  <IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
  </IfModule>
  ```

## 2. WordPress
- **Page cache** for visitors who are not logged in (LiteSpeed Cache on LiteSpeed hosts, otherwise WP Rocket / FlyingPress / W3 Total Cache). **Never cache:** `/cart/`, `/checkout/`, `/my-account/`, order-tracking, and anything with `wc-ajax` or a WooCommerce cart cookie (these plugins do that by default for WooCommerce).
- **Persistent object cache** (Redis or Memcached + the matching plugin) — removes most repeated database reads.
- **Image optimisation** on upload (the host's optimiser, ShortPixel, Imagify…) — product photos are the biggest files a shop serves. Upload product photos at about 1200 px on the long side.
- Keep `WP_DEBUG` **off** and don't define `SCRIPT_DEBUG` (with either on, the theme serves the unminified sources).
- Remove plugins you don't use; every active plugin adds work to every request.
- Set a real cron (`wp-cron` triggered by the system every few minutes) instead of running it on visits.

## 3. When you change CSS/JS in the theme
The site serves `assets/dist`. After editing anything in `assets/css` or `assets/js`:
```bash
cd dev-tools && npm install && npm run build
```
and upload `assets/dist` with the change. (On a machine with `WP_DEBUG` on, the theme notices a newer source file and serves the sources instead, so you see edits without building.)

Fonts: `dev-tools/build-fonts.sh` rebuilds the WOFF2 files from the original TTFs in `stocksystem-dev-kit/assets/fonts` (needs `pip install fonttools brotli`).

## 4. Measuring
Test the deployed **home**, a **category**, a **product** page and the **cart** with PageSpeed Insights / WebPageTest (mobile profile) from a location near the users. On a good host, with page caching, expect first byte under ~300 ms and LCP under ~2.5 s on mobile.
