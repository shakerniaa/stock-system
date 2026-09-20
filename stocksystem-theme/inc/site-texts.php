<?php
/**
 * Site texts — every sentence, label and button of the theme editable from
 * Appearance → «متن‌های سایت», without touching code.
 *
 * How it works: all theme copy is written as translatable strings
 * (`__( 'متن', 'stocksystem' )`). This file
 *   1. scans the theme's PHP files for those strings (cached, admin only),
 *   2. lists them grouped by page/section with a search box,
 *   3. stores the edited ones in one option, keyed by the original text, and
 *   4. swaps them in through the `gettext` filter on the front end — so any
 *      template, including ones added later, is covered automatically.
 *
 * Placeholders (%s, %d, %1$s) must stay: an edit that drops or adds one is
 * rejected. Texts that already carry HTML keep a safe subset of it.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Front end: apply the edits
 * ---------------------------------------------------------------------- */

function stocksystem_text_overrides() {
	static $map = null;

	if ( null === $map ) {
		$saved = get_option( 'stocksystem_texts', array() );
		$map   = is_array( $saved ) ? $saved : array();
	}

	return $map;
}

function stocksystem_text_filter( $translation, $text, $domain ) {
	if ( 'stocksystem' !== $domain ) {
		return $translation;
	}

	$map = stocksystem_text_overrides();

	return isset( $map[ $text ] ) ? $map[ $text ] : $translation;
}
add_filter( 'gettext', 'stocksystem_text_filter', 10, 3 );

function stocksystem_text_filter_context( $translation, $text, $context, $domain ) {
	return stocksystem_text_filter( $translation, $text, $domain );
}
add_filter( 'gettext_with_context', 'stocksystem_text_filter_context', 10, 4 );

/* -------------------------------------------------------------------------
 * Scanner
 * ---------------------------------------------------------------------- */

/** Files that hold admin-only labels (or this system itself) — not site copy. */
function stocksystem_texts_excluded( $relative ) {
	return 0 === strpos( $relative, 'dev-tools/' )
		|| 0 === strpos( $relative, 'node_modules/' )
		|| in_array( $relative, array( 'inc/home-settings.php', 'inc/site-texts.php', 'inc/nav-settings.php', 'inc/admin-kit.php', 'inc/admin-pages.php', 'inc/admin-pages-site.php', 'inc/support-pages.php', 'inc/site-extras.php', 'inc/seo-meta.php' ), true );
}

/** Persian group title for a template file. */
function stocksystem_texts_group( $relative ) {
	$map = array(
		'header.php'                        => 'هدر، منوها و جست‌وجو',
		'template-parts/header/'            => 'هدر، منوها و جست‌وجو',
		'footer.php'                        => 'فوتر',
		'template-parts/footer/'            => 'فوتر',
		'front-page.php'                    => 'صفحهٔ اصلی',
		'template-parts/home/'              => 'صفحهٔ اصلی',
		'page-templates/about-contact.php'  => 'درباره ما و تماس',
		'page-templates/repair.php'         => 'تعمیرات تخصصی',
		'page-templates/stock-condition.php' => 'وضعیت کالای استوک (درجه‌بندی)',
		'page-templates/faq.php'            => 'سوالات متداول و صفحه‌های حقوقی',
		'page-templates/terms.php'          => 'سوالات متداول و صفحه‌های حقوقی',
		'page-templates/privacy.php'        => 'سوالات متداول و صفحه‌های حقوقی',
		'inc/support-pages.php'             => 'سوالات متداول و صفحه‌های حقوقی',
		'page-templates/order-tracking.php' => 'پیگیری سفارش',
		'woocommerce/cart/'                 => 'سبد خرید',
		'template-parts/cart/'              => 'سبد خرید',
		'woocommerce/checkout/'             => 'تسویه‌حساب و پرداخت',
		'template-parts/checkout/'          => 'تسویه‌حساب و پرداخت',
		'inc/checkout-fields.php'           => 'تسویه‌حساب و پرداخت',
		'woocommerce/myaccount/'            => 'حساب کاربری و ورود',
		'template-parts/account/'           => 'حساب کاربری و ورود',
		'inc/otp-auth.php'                  => 'حساب کاربری و ورود',
		'inc/account-endpoints.php'         => 'حساب کاربری و ورود',
		'inc/wallet.php'                    => 'حساب کاربری و ورود',
		'inc/wishlist.php'                  => 'حساب کاربری و ورود',
		'template-parts/product/'           => 'صفحهٔ محصول و کارت محصول',
		'woocommerce/single-product'        => 'صفحهٔ محصول و کارت محصول',
		'woocommerce/content-single-product.php' => 'صفحهٔ محصول و کارت محصول',
		'inc/product-'                      => 'صفحهٔ محصول و کارت محصول',
		'woocommerce/archive-product.php'   => 'فروشگاه، دسته‌ها و جست‌وجو',
		'woocommerce/taxonomy-'             => 'فروشگاه، دسته‌ها و جست‌وجو',
		'template-parts/archive/'           => 'فروشگاه، دسته‌ها و جست‌وجو',
		'inc/archive-filters.php'           => 'فروشگاه، دسته‌ها و جست‌وجو',
		'search.php'                        => 'فروشگاه، دسته‌ها و جست‌وجو',
		'searchform.php'                    => 'فروشگاه، دسته‌ها و جست‌وجو',
		'home.php'                          => 'بلاگ',
		'single.php'                        => 'بلاگ',
		'archive.php'                       => 'بلاگ',
		'template-parts/blog/'              => 'بلاگ',
		'inc/blog.php'                      => 'بلاگ',
		'inc/repair.php'                    => 'تعمیرات تخصصی',
		'404.php'                           => 'صفحهٔ ۴۰۴',
		'inc/js-strings.php'                => 'پیام‌های لحظه‌ای (اعلان‌ها و خطاهای فرم)',
	);

	foreach ( $map as $prefix => $title ) {
		if ( 0 === strpos( $relative, $prefix ) ) {
			return $title;
		}
	}

	return 'سایر پیام‌ها (ایمیل‌ها، وضعیت‌ها، ...)';
}

/**
 * All `__( '…', 'stocksystem' )` strings, grouped:
 * array( group title => array( text, text, … ) ). Cached until a theme file changes.
 */
function stocksystem_texts_index( $force = false ) {
	$dir   = get_template_directory();
	$files = array();
	$sig   = '';

	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $file ) {
		if ( 'php' !== strtolower( $file->getExtension() ) ) {
			continue;
		}
		$relative = ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dir ) ) ), '/' );
		if ( stocksystem_texts_excluded( $relative ) ) {
			continue;
		}
		$files[ $relative ] = $file->getPathname();
		$sig               .= $relative . $file->getMTime();
	}
	$sig = md5( $sig );

	$cached = get_transient( 'stocksystem_texts_index' );
	if ( ! $force && is_array( $cached ) && isset( $cached['sig'], $cached['groups'] ) && $sig === $cached['sig'] ) {
		return $cached['groups'];
	}

	$pattern = '/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_x|esc_html_x|esc_attr_x)\(\s*(\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")\s*,\s*(?:(?:\'[^\']*\'|"[^"]*")\s*,\s*)?\'stocksystem\'\s*\)/us';
	$seen    = array();
	$groups  = array();

	ksort( $files );
	foreach ( $files as $relative => $path ) {
		$source = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $source || ! preg_match_all( $pattern, $source, $matches, PREG_SET_ORDER ) ) {
			continue;
		}

		foreach ( $matches as $match ) {
			if ( "'" === $match[1][0] ) {
				$text = str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $match[2] );
			} else {
				$text = stripcslashes( isset( $match[3] ) ? $match[3] : '' );
			}

			// Only real sentences: skip empty and pure-symbol/number strings.
			if ( '' === trim( $text ) || ! preg_match( '/[\x{0600}-\x{06FF}A-Za-z]/u', $text ) || isset( $seen[ $text ] ) ) {
				continue;
			}

			$seen[ $text ]                                    = true;
			$groups[ stocksystem_texts_group( $relative ) ][] = $text;
		}
	}

	ksort( $groups );
	set_transient( 'stocksystem_texts_index', array( 'sig' => $sig, 'groups' => $groups ), DAY_IN_SECONDS );

	return $groups;
}

/* -------------------------------------------------------------------------
 * Saving
 * ---------------------------------------------------------------------- */

/** Placeholders (%s, %d, %1$s) of a string, sorted — edits must keep the same set. */
function stocksystem_text_placeholders( $text ) {
	preg_match_all( '/%(?:\d+\$)?[sdf]/', $text, $m );
	sort( $m[0] );

	return $m[0];
}

function stocksystem_texts_handle_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}
	check_admin_referer( 'stocksystem_texts_save' );

	$raw     = isset( $_POST['texts_json'] ) ? json_decode( wp_unslash( $_POST['texts_json'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded and sanitised per item below.
	$raw     = is_array( $raw ) ? $raw : array();
	$known   = array();
	foreach ( stocksystem_texts_index() as $group ) {
		foreach ( $group as $text ) {
			$known[ $text ] = true;
		}
	}

	$saved   = get_option( 'stocksystem_texts', array() );
	$saved   = is_array( $saved ) ? $saved : array();
	$rejected = 0;
	$changed  = 0;

	foreach ( $raw as $original => $value ) {
		$original = (string) $original;
		if ( ! isset( $known[ $original ] ) || ! is_string( $value ) ) {
			continue; // Only strings that really exist in the theme.
		}

		$value = trim( $value );

		// Empty, or the same as the shipped text = back to default.
		if ( '' === $value || $value === $original ) {
			if ( isset( $saved[ $original ] ) ) {
				unset( $saved[ $original ] );
				$changed++;
			}
			continue;
		}

		$clean = false !== strpos( $original, '<' )
			? wp_kses( $value, array( 'span' => array( 'class' => true ), 'strong' => array(), 'em' => array(), 'br' => array(), 'a' => array( 'href' => true ) ) )
			: sanitize_text_field( $value );

		if ( stocksystem_text_placeholders( $clean ) !== stocksystem_text_placeholders( $original ) ) {
			$rejected++;
			continue;
		}

		if ( ! isset( $saved[ $original ] ) || $saved[ $original ] !== $clean ) {
			$saved[ $original ] = $clean;
			$changed++;
		}
	}

	update_option( 'stocksystem_texts', $saved );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'     => 'stocksystem-texts',
				'saved'    => $changed,
				'rejected' => $rejected,
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_stocksystem_texts_save', 'stocksystem_texts_handle_save' );

function stocksystem_texts_handle_reset() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}
	check_admin_referer( 'stocksystem_texts_reset' );
	delete_option( 'stocksystem_texts' );
	wp_safe_redirect( add_query_arg( array( 'page' => 'stocksystem-texts', 'reset' => '1' ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_stocksystem_texts_reset', 'stocksystem_texts_handle_reset' );

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */

function stocksystem_texts_menu() {
	add_theme_page(
		__( 'متن‌های سایت', 'stocksystem' ),
		__( 'متن‌های سایت', 'stocksystem' ),
		'manage_options',
		'stocksystem-texts',
		'stocksystem_texts_render_page'
	);
}
add_action( 'admin_menu', 'stocksystem_texts_menu' );

function stocksystem_texts_assets( $hook ) {
	if ( 'appearance_page_stocksystem-texts' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'stocksystem-texts-admin', STOCKSYSTEM_URI . '/assets/css/admin-texts.css', array(), STOCKSYSTEM_VERSION );
	wp_enqueue_script( 'stocksystem-texts-admin', STOCKSYSTEM_URI . '/assets/js/admin-texts.js', array(), STOCKSYSTEM_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'stocksystem_texts_assets' );

function stocksystem_texts_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$groups    = stocksystem_texts_index( isset( $_GET['rescan'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$overrides = stocksystem_text_overrides();
	$total     = 0;
	foreach ( $groups as $items ) {
		$total += count( $items );
	}
	?>
	<div class="wrap ss-texts">
		<h1><?php esc_html_e( 'متن‌های سایت', 'stocksystem' ); ?></h1>
		<p class="description">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: number of texts */
					__( 'همهٔ جمله‌ها، عنوان‌ها و دکمه‌های قالب (%s متن) در یک‌جا. متن را عوض کنید و ذخیره بزنید؛ خالی‌کردن یک کادر آن را به متن اولیه برمی‌گرداند.', 'stocksystem' ),
					stocksystem_to_persian_digits( $total )
				)
			);
			?>
			<?php esc_html_e( 'اگر داخل متن علامتی مثل', 'stocksystem' ); ?>
			<code dir="ltr">%s</code> <?php esc_html_e( 'یا', 'stocksystem' ); ?> <code dir="ltr">%d</code>
			<?php esc_html_e( 'دیدید، آن را نگه دارید؛ سایت به‌جایش عدد، نام یا مبلغ می‌گذارد.', 'stocksystem' ); ?>
		</p>
		<p class="description"><?php esc_html_e( 'متن صفحهٔ اصلی از «نمایش ← صفحهٔ اصلی»، و شماره تماس، آدرس و گارانتی از «نمایش ← سفارشی‌سازی» عوض می‌شود.', 'stocksystem' ); ?></p>

		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: number of changed texts */
						__( 'ذخیره شد (%s تغییر).', 'stocksystem' ),
						stocksystem_to_persian_digits( absint( $_GET['saved'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
					)
				);
				?>
			</p></div>
			<?php if ( ! empty( $_GET['rejected'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-warning is-dismissible"><p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: number of rejected texts */
							__( '%s متن ذخیره نشد چون علامت‌های ویژهٔ داخل آن‌ها (همان‌ها که موقع نمایش به عدد یا نام تبدیل می‌شوند) تغییر کرده بود. متن اولیه را نگاه کنید و همان علامت‌ها را نگه دارید.', 'stocksystem' ),
							stocksystem_to_persian_digits( absint( $_GET['rejected'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
						)
					);
					?>
				</p></div>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( isset( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'همهٔ متن‌ها به حالت اولیه برگشتند.', 'stocksystem' ); ?></p></div>
		<?php endif; ?>

		<div class="ss-texts__bar">
			<input type="search" id="ss-texts-search" class="regular-text" placeholder="<?php esc_attr_e( 'جست‌وجو در متن‌ها…', 'stocksystem' ); ?>" autocomplete="off">
			<label><input type="checkbox" id="ss-texts-only-changed"> <?php esc_html_e( 'فقط متن‌های تغییرداده‌شده', 'stocksystem' ); ?></label>
			<span class="ss-texts__count" id="ss-texts-count" aria-live="polite"></span>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ss-texts-form">
			<input type="hidden" name="action" value="stocksystem_texts_save">
			<input type="hidden" name="texts_json" id="ss-texts-json" value="">
			<?php wp_nonce_field( 'stocksystem_texts_save' ); ?>

			<?php foreach ( $groups as $title => $items ) : ?>
				<details class="ss-group" <?php echo count( $groups ) <= 1 ? 'open' : ''; ?>>
					<summary>
						<?php echo esc_html( $title ); ?>
						<span class="ss-group__n"><?php echo esc_html( stocksystem_to_persian_digits( count( $items ) ) ); ?></span>
					</summary>
					<div class="ss-group__rows">
						<?php foreach ( $items as $text ) : ?>
							<?php $current = isset( $overrides[ $text ] ) ? $overrides[ $text ] : ''; ?>
							<div class="ss-row<?php echo '' !== $current ? ' is-changed' : ''; ?>" data-original="<?php echo esc_attr( $text ); ?>">
								<div class="ss-row__orig"><?php echo esc_html( wp_strip_all_tags( $text ) ); ?></div>
								<textarea rows="<?php echo (int) max( 1, min( 5, ceil( mb_strlen( $text ) / 70 ) ) ); ?>" placeholder="<?php echo esc_attr( wp_strip_all_tags( $text ) ); ?>" data-saved="<?php echo esc_attr( $current ); ?>"><?php echo esc_textarea( $current ); ?></textarea>
							</div>
						<?php endforeach; ?>
					</div>
				</details>
			<?php endforeach; ?>

			<p class="submit">
				<?php submit_button( __( 'ذخیرهٔ تغییرات', 'stocksystem' ), 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'دیدن سایت', 'stocksystem' ); ?></a>
				<a class="button-link" href="<?php echo esc_url( add_query_arg( array( 'page' => 'stocksystem-texts', 'rescan' => '1' ), admin_url( 'themes.php' ) ) ); ?>"><?php esc_html_e( 'به‌روزرسانی فهرست متن‌ها', 'stocksystem' ); ?></a>
			</p>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ss-texts__reset" onsubmit="return confirm('<?php echo esc_js( __( 'همهٔ متن‌های ویرایش‌شده به حالت اولیه برگردند؟', 'stocksystem' ) ); ?>');">
			<input type="hidden" name="action" value="stocksystem_texts_reset">
			<?php wp_nonce_field( 'stocksystem_texts_reset' ); ?>
			<button type="submit" class="button-link button-link-delete"><?php esc_html_e( 'بازگشت همهٔ متن‌ها به حالت اولیه', 'stocksystem' ); ?></button>
		</form>
	</div>
	<?php
}
