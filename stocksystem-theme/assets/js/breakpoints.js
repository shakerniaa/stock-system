/**
 * Numeric breakpoints for JS consumers (matchMedia, resize logic).
 * Mirrors assets/css/tokens.css — CSS custom properties can't be read
 * inside @media conditions, so these are duplicated here. Keep in sync
 * with DECISIONS-v1.1.md #8 if the client ever revises them.
 */
window.StockSystem = window.StockSystem || {};
window.StockSystem.breakpoints = {
	sm: 480,
	md: 768,
	lg: 1024,
	xl: 1280,
};
