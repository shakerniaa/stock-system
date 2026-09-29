<?php
/**
 * Front-end side of the site-wide edit pages (inc/admin-pages-site.php):
 * logo & favicon, colour / radius overrides, announcement bar, cookie notice,
 * floating WhatsApp button, pop-up, tracking codes, extra homepage sections,
 * and the notification e-mail / OTP text helpers.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Logo, favicon
 * ---------------------------------------------------------------------- */

/** Logo for dark backgrounds: the uploaded one, else the bundled lockup. */
function stocksystem_logo_url() {
	$custom = stocksystem_opt_image( 'look', 'logo', 'full' );

	return $custom ? $custom : STOCKSYSTEM_URI . '/assets/images/logo-lockup-dark.webp';
}

/**
 * Browser-tab icon. Three sources, most specific first:
 *   1. WordPress' own Site Icon (تنظیمات ← عمومی) — if that is set,
 *      WordPress prints a full, correct icon set itself and we stay out
 *      of the way entirely.
 *   2. The «آیکون تب مرورگر» upload in استوک سیستم ← رنگ‌ها و لوگو.
 *   3. The bundled brand mark, so a fresh install has a real favicon
 *      instead of the browser's blank page glyph.
 *
 * The bundled set is generated from stocksystem-dev-kit/assets/
 * logo-symbol-dark.png: the mark trimmed to its own bounds (the source
 * has uneven padding, which renders it visibly off-centre at 16px) and
 * centred on the brand's ink tile, so the teal stays legible against
 * both light and dark browser chrome.
 */
function stocksystem_print_favicon() {
	if ( has_site_icon() ) {
		return;
	}

	$custom = stocksystem_opt_image( 'look', 'favicon', 'full' );

	if ( $custom ) {
		printf( '<link rel="icon" href="%s">' . "\n", esc_url( $custom ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $custom ) );
		return;
	}

	$base = STOCKSYSTEM_URI . '/assets/icons/';

	printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( $base . 'icon-32.png' ) );
	printf( '<link rel="icon" href="%s" sizes="192x192">' . "\n", esc_url( $base . 'icon-192.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'apple-touch-icon.png' ) );
	printf( '<link rel="shortcut icon" href="%s">' . "\n", esc_url( $base . 'favicon.ico' ) );
}
add_action( 'wp_head', 'stocksystem_print_favicon', 2 );

/* -------------------------------------------------------------------------
 * Colours and shape → CSS custom properties (only what differs from the defaults)
 * ---------------------------------------------------------------------- */

function stocksystem_color_mix( $hex, $with, $percent ) {
	$hex  = ltrim( $hex, '#' );
	$with = ltrim( $with, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$out = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$a    = hexdec( substr( $hex, $i * 2, 2 ) );
		$b    = hexdec( substr( $with, $i * 2, 2 ) );
		$out .= str_pad( dechex( (int) round( $a + ( $b - $a ) * $percent ) ), 2, '0', STR_PAD_LEFT );
	}

	return $out;
}

function stocksystem_print_look_css() {
	$look = stocksystem_opt( 'look' );
	$vars = array();
	$same = function ( $a, $b ) {
		return 0 === strcasecmp( (string) $a, (string) $b );
	};

	if ( ! $same( $look['cta'], '#0EBAAF' ) ) {
		$vars['--c-cta'] = $look['cta'];
	}
	if ( ! $same( $look['cta_hover'], '#0A8F87' ) ) {
		$vars['--c-cta-hover'] = $look['cta_hover'];
	}
	if ( ! $same( $look['brand'], '#0EBAAF' ) ) {
		$vars['--c-teal-500'] = $look['brand'];
	}
	if ( ! $same( $look['accent'], '#F58220' ) ) {
		$vars['--c-orange-500'] = $look['accent'];
	}
	if ( ! $same( $look['dark'], '#04211F' ) ) {
		$vars['--c-ink-900'] = $look['dark'];
		$vars['--c-ink-800'] = stocksystem_color_mix( $look['dark'], '#ffffff', .05 );
		$vars['--c-ink-700'] = stocksystem_color_mix( $look['dark'], '#ffffff', .09 );
	}
	if ( ! $same( $look['dark_soft'], '#0B3A38' ) ) {
		$vars['--c-ink-600'] = $look['dark_soft'];
	}

	$radii = array(
		'sharp' => array( 4, 4, 5, 6, 6, 8, 9, 10 ),
		'round' => array( 12, 12, 14, 16, 20, 22, 24, 26 ),
	);
	if ( isset( $radii[ $look['radius'] ] ) ) {
		$names = array( '--radius-chip', '--radius-input', '--radius-button', '--radius-button-lg', '--radius-card', '--radius-panel', '--radius-panel-lg', '--radius-panel-xl' );
		foreach ( $names as $i => $name ) {
			$vars[ $name ] = $radii[ $look['radius'] ][ $i ] . 'px';
		}
	}

	if ( empty( $vars ) ) {
		return;
	}

	$css = '';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	echo '<style id="stocksystem-look">:root{' . esc_html( $css ) . '}</style>' . "\n";
}
add_action( 'wp_head', 'stocksystem_print_look_css', 30 );

/* -------------------------------------------------------------------------
 * Announcement bar, cookie notice, WhatsApp, pop-up
 * ---------------------------------------------------------------------- */

function stocksystem_announcement_active() {
	$bars = stocksystem_opt( 'bars' );

	if ( empty( $bars['ann_on'] ) || '' === trim( $bars['ann_text'] ) ) {
		return false;
	}

	$now = current_time( 'Y-m-d\TH:i' );

	if ( ! empty( $bars['ann_start'] ) && $now < $bars['ann_start'] ) {
		return false;
	}
	if ( ! empty( $bars['ann_end'] ) && $now > $bars['ann_end'] ) {
		return false;
	}

	return true;
}

function stocksystem_render_announcement() {
	if ( ! stocksystem_announcement_active() ) {
		return;
	}

	$bars = stocksystem_opt( 'bars' );
	$link = stocksystem_home_link( $bars['ann_link'] );
	$id   = substr( md5( $bars['ann_text'] . $bars['ann_link'] ), 0, 8 );
	?>
	<div class="announce announce--<?php echo esc_attr( $bars['ann_style'] ); ?>" data-announce="<?php echo esc_attr( $id ); ?>" role="region" aria-label="<?php esc_attr_e( 'اعلان', 'stocksystem' ); ?>">
		<div class="container announce__inner">
			<span class="announce__text"><?php echo esc_html( $bars['ann_text'] ); ?></span>
			<?php if ( $link ) : ?>
				<a class="announce__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $bars['ann_label'] ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $bars['ann_close'] ) ) : ?>
				<button type="button" class="announce__close" data-announce-close aria-label="<?php esc_attr_e( 'بستن اعلان', 'stocksystem' ); ?>">×</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'stocksystem_before_header', 'stocksystem_render_announcement' );

function stocksystem_render_floating_extras() {
	$bars = stocksystem_opt( 'bars' );

	// Cookie notice.
	if ( ! empty( $bars['cookie_on'] ) ) :
		$privacy = stocksystem_home_link( $bars['cookie_link'] );
		?>
		<div class="cookie-notice" data-cookie-notice hidden role="dialog" aria-label="<?php esc_attr_e( 'کوکی‌ها', 'stocksystem' ); ?>">
			<p>
				<?php echo esc_html( $bars['cookie_text'] ); ?>
				<?php if ( $privacy ) : ?>
					<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'حریم خصوصی', 'stocksystem' ); ?></a>
				<?php endif; ?>
			</p>
			<button type="button" class="btn btn--primary" data-cookie-accept><?php echo esc_html( $bars['cookie_btn'] ); ?></button>
		</div>
		<?php
	endif;

	// WhatsApp.
	if ( ! empty( $bars['wa_on'] ) ) :
		$phone = '' !== trim( (string) $bars['wa_phone'] ) ? $bars['wa_phone'] : stocksystem_business( 'phone' );
		$digits = preg_replace( '/\D/', '', stocksystem_to_latin_digits( $phone ) );
		if ( $digits && 0 === strpos( $digits, '0' ) ) {
			$digits = '98' . substr( $digits, 1 );
		}
		if ( $digits ) :
			$url = 'https://wa.me/' . $digits . ( '' !== trim( $bars['wa_msg'] ) ? '?text=' . rawurlencode( $bars['wa_msg'] ) : '' );
			?>
			<a class="wa-float wa-float--<?php echo esc_attr( $bars['wa_side'] ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
				<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21l1.6-4.6A8.5 8.5 0 1 1 8 19.5z"></path><path d="M9 9c.5 2 2 3.5 4 4l1-1 2 1c-.5 1.5-2 2-3.5 1.5C10 13.500 8 11.500 7.500 9.500 7.500 8.500 8.200 8 9 9z"></path></svg>
				<span class="wa-float__label"><?php echo esc_html( $bars['wa_label'] ); ?></span>
			</a>
			<?php
		endif;
	endif;

	// Pop-up.
	if ( ! empty( $bars['pop_on'] ) && ( ! empty( $bars['pop_title'] ) || ! empty( $bars['pop_text'] ) || ! empty( $bars['pop_image'] ) ) && ( empty( $bars['pop_home'] ) || is_front_page() ) ) :
		$image = stocksystem_opt_image( 'bars', 'pop_image', 'large' );
		$link  = stocksystem_home_link( $bars['pop_link'] );
		$key   = substr( md5( $bars['pop_title'] . $bars['pop_text'] . $bars['pop_link'] ), 0, 8 );
		?>
		<div class="promo-pop" data-promo="<?php echo esc_attr( $key ); ?>" data-delay="<?php echo esc_attr( (int) $bars['pop_delay'] ); ?>" data-days="<?php echo esc_attr( (int) $bars['pop_days'] ); ?>" hidden>
			<div class="promo-pop__backdrop" data-promo-close></div>
			<div class="promo-pop__box" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $bars['pop_title'] ? $bars['pop_title'] : __( 'پیشنهاد', 'stocksystem' ) ); ?>">
				<button type="button" class="promo-pop__close" data-promo-close aria-label="<?php esc_attr_e( 'بستن', 'stocksystem' ); ?>">×</button>
				<?php if ( $image ) : ?>
					<img class="promo-pop__image" src="<?php echo esc_url( $image ); ?>" alt="">
				<?php endif; ?>
				<?php if ( $bars['pop_title'] ) : ?>
					<h2 class="promo-pop__title"><?php echo esc_html( $bars['pop_title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $bars['pop_text'] ) : ?>
					<p class="promo-pop__text"><?php echo esc_html( $bars['pop_text'] ); ?></p>
				<?php endif; ?>
				<?php if ( $link ) : ?>
					<a class="btn btn--primary promo-pop__cta" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $bars['pop_btn'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	endif;
}
add_action( 'wp_footer', 'stocksystem_render_floating_extras', 5 );

/* -------------------------------------------------------------------------
 * Tracking / verification
 * ---------------------------------------------------------------------- */

function stocksystem_tracking_enabled() {
	// Never count the owner's own visits.
	return ! is_user_logged_in() || ! current_user_can( 'edit_posts' );
}

function stocksystem_print_head_codes() {
	$seo = stocksystem_opt( 'seo' );

	if ( '' !== trim( $seo['google_verify'] ) ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $seo['google_verify'] ) . '">' . "\n";
	}
	if ( '' !== trim( $seo['bing_verify'] ) ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( $seo['bing_verify'] ) . '">' . "\n";
	}

	if ( ! stocksystem_tracking_enabled() ) {
		return;
	}

	$gtm = preg_replace( '/[^A-Za-z0-9\-]/', '', $seo['gtm'] );
	$ga4 = preg_replace( '/[^A-Za-z0-9\-]/', '', $seo['ga4'] );

	if ( $gtm ) {
		echo "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . esc_js( $gtm ) . "');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	} elseif ( $ga4 ) {
		echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $ga4 ) . '"></script>' . "\n";
		echo "<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . esc_js( $ga4 ) . "');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	if ( '' !== trim( $seo['head_code'] ) ) {
		echo $seo['head_code'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin code (unfiltered_html).
	}
}
add_action( 'wp_head', 'stocksystem_print_head_codes', 3 );

function stocksystem_print_gtm_noscript() {
	$gtm = preg_replace( '/[^A-Za-z0-9\-]/', '', stocksystem_opt( 'seo', 'gtm' ) );

	if ( $gtm && stocksystem_tracking_enabled() ) {
		echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $gtm ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
	}
}
add_action( 'wp_body_open', 'stocksystem_print_gtm_noscript' );

function stocksystem_print_footer_code() {
	$code = stocksystem_opt( 'seo', 'footer_code' );

	if ( '' !== trim( $code ) && stocksystem_tracking_enabled() ) {
		echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin code (unfiltered_html).
	}
}
add_action( 'wp_footer', 'stocksystem_print_footer_code', 99 );

/* -------------------------------------------------------------------------
 * Notification e-mail / OTP text
 * ---------------------------------------------------------------------- */

/** Where requests (repair, notify-me, quotes, wallet top-ups, OTP fallback) are e-mailed. */
function stocksystem_notify_email( $context = '' ) {
	$email = stocksystem_opt( 'messages', 'notify_email' );

	return apply_filters( 'stocksystem_notify_email', is_email( $email ) ? $email : get_option( 'admin_email' ), $context );
}

/** The OTP message with {code}, {phone}, {minutes} filled in. */
function stocksystem_otp_message( $phone, $code ) {
	return strtr(
		stocksystem_opt( 'messages', 'otp_text' ),
		array(
			'{code}'    => $code,
			'{phone}'   => $phone,
			'{minutes}' => stocksystem_to_persian_digits( (int) ( STOCKSYSTEM_OTP_TTL / MINUTE_IN_SECONDS ) ),
		)
	);
}

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

function stocksystem_enqueue_extras() {
	stocksystem_enqueue_style( 'stocksystem-extras', 'components/extras.css', array( 'stocksystem-base' ) );
	stocksystem_enqueue_script( 'stocksystem-extras', 'extras.js' );
}
add_action( 'wp_enqueue_scripts', 'stocksystem_enqueue_extras', 30 );
