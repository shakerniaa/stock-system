/**
 * Phone account nav is a horizontally scrolling pill row; bring the active
 * pill into view so a deep endpoint (addresses, wishlist…) doesn't open
 * with its own tab hidden off-screen.
 */
( function () {
	'use strict';

	var active = document.querySelector( '.account-nav__item.is-active' );
	var list = active && active.parentElement;

	if ( ! list || list.scrollWidth <= list.clientWidth ) {
		return;
	}

	list.scrollLeft += active.getBoundingClientRect().left - list.getBoundingClientRect().left
		- ( list.clientWidth - active.offsetWidth ) / 2;
} )();
