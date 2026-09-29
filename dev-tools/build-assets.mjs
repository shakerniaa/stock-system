/**
 * Builds stocksystem-theme/assets/dist from the readable sources in assets/css
 * and assets/js:
 *
 *   dist/global.min.css   the CSS every page loads, as one file
 *   dist/global.min.js    the JS every page loads, as one file
 *   dist/css/*.min.css    page-specific CSS, minified one by one
 *   dist/js/*.min.js      page-specific JS, minified one by one
 *   dist/manifest.json    what went where (read by inc/enqueue.php)
 *
 * The theme uses dist automatically when the manifest exists, unless
 * SCRIPT_DEBUG is on; delete assets/dist (or set SCRIPT_DEBUG) to work on the
 * readable sources. Run again after editing any CSS/JS:
 *
 *   cd dev-tools && npm install && npm run build
 */
import { transformSync } from 'esbuild';
import { readFileSync, writeFileSync, mkdirSync, readdirSync, rmSync, statSync } from 'node:fs';
import { dirname, join, basename } from 'node:path';
import { fileURLToPath } from 'node:url';

const theme = join(dirname(fileURLToPath(import.meta.url)), '..', 'stocksystem-theme', 'assets');
const dist = join(theme, 'dist');

// Order matters (it is the order the files used to be enqueued in).
const GLOBAL_CSS = [
	'fonts.css', 'tokens.css', 'base.css',
	'components/buttons.css', 'components/toast.css', 'components/notices.css',
	'components/header.css', 'components/footer.css', 'components/product-card.css',
	'components/archive.css', 'components/blog.css', 'components/extras.css',
	'components/compare.css',
];
const GLOBAL_JS = [
	'breakpoints.js', 'navigation.js', 'typing-state.js', 'toast.js', 'product-card.js',
	'archive-filters.js', 'faq-accordion.js', 'wishlist.js', 'extras.js', 'footer.js', 'compare.js',
];

const css = (src) => transformSync(src, { loader: 'css', minify: true }).code;
const js = (src) => transformSync(src, { loader: 'js', minify: true, target: 'es2018' }).code;
const read = (kind, rel) => readFileSync(join(theme, kind, rel), 'utf8');

rmSync(dist, { recursive: true, force: true });
mkdirSync(join(dist, 'css'), { recursive: true });
mkdirSync(join(dist, 'js'), { recursive: true });

const manifest = { built: new Date().toISOString(), global_css: GLOBAL_CSS, global_js: GLOBAL_JS, css: {}, js: {} };
const sizes = { css_src: 0, css_min: 0, js_src: 0, js_min: 0 };

// Global bundles.
let out = GLOBAL_CSS.map((f) => { const s = read('css', f); sizes.css_src += s.length; return css(s); }).join('\n');
writeFileSync(join(dist, 'global.min.css'), out); sizes.css_min += out.length;
out = GLOBAL_JS.map((f) => { const s = read('js', f); sizes.js_src += s.length; return js(s); }).join(';\n');
writeFileSync(join(dist, 'global.min.js'), out); sizes.js_min += out.length;

// Everything else, one by one.
const walk = (kind, sub = '') => readdirSync(join(theme, kind, sub)).flatMap((name) => {
	const rel = sub ? `${sub}/${name}` : name;
	return statSync(join(theme, kind, rel)).isDirectory() ? walk(kind, rel) : [rel];
});

for (const rel of walk('css').filter((f) => f.endsWith('.css') && !GLOBAL_CSS.includes(f) && !basename(f).startsWith('admin-'))) {
	const s = read('css', rel); const min = css(s);
	writeFileSync(join(dist, 'css', basename(rel, '.css') + '.min.css'), min);
	manifest.css[rel] = 'css/' + basename(rel, '.css') + '.min.css';
	sizes.css_src += s.length; sizes.css_min += min.length;
}
for (const rel of walk('js').filter((f) => f.endsWith('.js') && !GLOBAL_JS.includes(f) && !basename(f).startsWith('admin-'))) {
	const s = read('js', rel); const min = js(s);
	writeFileSync(join(dist, 'js', basename(rel, '.js') + '.min.js'), min);
	manifest.js[rel] = 'js/' + basename(rel, '.js') + '.min.js';
	sizes.js_src += s.length; sizes.js_min += min.length;
}

writeFileSync(join(dist, 'manifest.json'), JSON.stringify(manifest, null, 1));
const kb = (n) => (n / 1024).toFixed(1) + ' KB';
console.log(`CSS ${kb(sizes.css_src)} → ${kb(sizes.css_min)} · JS ${kb(sizes.js_src)} → ${kb(sizes.js_min)}`);
console.log(`global.min.css ${kb(statSync(join(dist, 'global.min.css')).size)} · global.min.js ${kb(statSync(join(dist, 'global.min.js')).size)}`);
