<?php
/**
 * Admin kit — one small engine that turns a declarative field list into an
 * edit page (text, long text, link, image, colour, switch, choice, code and
 * repeating rows), with saving, sanitising and defaults handled once.
 *
 * Pages are declared in inc/admin-pages.php as
 *
 *   'about' => array(
 *       'title'    => 'درباره ما و تماس',
 *       'sections' => array( array( 'title' => …, 'fields' => array( 'hero_title' => array( 'type' => 'text', … ) ) ) ),
 *   )
 *
 * and read anywhere with stocksystem_opt( 'about', 'hero_title' ). The shipped
 * copy is the default: an emptied text falls back to it unless the field says
 * 'allow_empty' (blank = hidden), and a repeater the owner has saved is theirs
 * even if it is empty.
 *
 * All pages live under one top-level «استوک سیستم» menu with a hub page that
 * links every editable area of the site (including the older Appearance pages).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Schema access + option getter
 * ---------------------------------------------------------------------- */

function stocksystem_kit_pages() {
	static $pages = null;

	if ( null === $pages ) {
		$pages = function_exists( 'stocksystem_admin_pages_schema' ) ? stocksystem_admin_pages_schema() : array();
	}

	return $pages;
}

function stocksystem_kit_fields( $slug ) {
	$pages  = stocksystem_kit_pages();
	$fields = array();

	if ( isset( $pages[ $slug ] ) ) {
		foreach ( $pages[ $slug ]['sections'] as $section ) {
			$fields = array_merge( $fields, $section['fields'] );
		}
	}

	return $fields;
}

/** Text-like types where an emptied box means "use the shipped copy". */
function stocksystem_kit_falls_back( $field ) {
	return in_array( $field['type'], array( 'text', 'textarea', 'html', 'url', 'color' ), true ) && empty( $field['allow_empty'] );
}

/**
 * Value of one setting (or the whole page when $key is null), merged over defaults.
 *
 * @param string      $slug Page slug ('about', 'repair', …).
 * @param string|null $key  Field key.
 */
function stocksystem_opt( $slug, $key = null ) {
	static $cache = array();

	if ( ! isset( $cache[ $slug ] ) ) {
		$saved  = stocksystem_get_option( 'stocksystem_' . $slug );
		$saved  = is_array( $saved ) ? $saved : array();
		$values = array();

		foreach ( stocksystem_kit_fields( $slug ) as $name => $field ) {
			$default = isset( $field['default'] ) ? $field['default'] : ( 'repeater' === $field['type'] ? array() : ( 'checkbox' === $field['type'] ? 0 : '' ) );

			if ( ! array_key_exists( $name, $saved ) ) {
				$values[ $name ] = $default;
				continue;
			}

			$value = $saved[ $name ];

			if ( stocksystem_kit_falls_back( $field ) && ( '' === trim( (string) $value ) ) ) {
				$value = $default;
			}

			$values[ $name ] = $value;
		}

		$cache[ $slug ] = $values;
	}

	if ( null === $key ) {
		return $cache[ $slug ];
	}

	return isset( $cache[ $slug ][ $key ] ) ? $cache[ $slug ][ $key ] : '';
}

/** URL of an image field (attachment id) at a size, or '' . */
function stocksystem_opt_image( $slug, $key, $size = 'large' ) {
	$id = (int) stocksystem_opt( $slug, $key );

	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/* -------------------------------------------------------------------------
 * Sanitising
 * ---------------------------------------------------------------------- */

function stocksystem_kit_clean_value( $field, $raw ) {
	switch ( $field['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $raw );

		case 'html':
			return wp_kses_post( (string) $raw );

		case 'code':
			return current_user_can( 'unfiltered_html' ) ? (string) $raw : wp_kses_post( (string) $raw );

		case 'url':
			return esc_url_raw( trim( (string) $raw ) );

		case 'image':
			$id = absint( $raw );
			return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;

		case 'checkbox':
			return empty( $raw ) ? 0 : 1;

		case 'number':
			$value = trim( str_replace( array( ',', '،', ' ' ), '', stocksystem_to_latin_digits( (string) $raw ) ) );
			if ( '' === $value || ! is_numeric( $value ) ) {
				return '';
			}
			$value = 0 + $value;
			if ( isset( $field['min'] ) ) {
				$value = max( $field['min'], $value );
			}
			if ( isset( $field['max'] ) ) {
				$value = min( $field['max'], $value );
			}
			return $value;

		case 'color':
			$color = sanitize_hex_color( trim( (string) $raw ) );
			return $color ? $color : '';

		case 'select':
			$options = isset( $field['options_cb'] ) ? call_user_func( $field['options_cb'] ) : ( isset( $field['options'] ) ? $field['options'] : array() );
			$raw     = (string) $raw;
			return isset( $options[ $raw ] ) ? $raw : ( isset( $field['default'] ) ? $field['default'] : '' );

		case 'datetime':
			$raw = trim( (string) $raw );
			return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $raw ) ? $raw : '';

		case 'text':
		default:
			return sanitize_text_field( (string) $raw );
	}
}

function stocksystem_kit_sanitize( $slug, $input ) {
	$in     = is_array( $input ) ? wp_unslash( $input ) : array();
	$out    = array();
	$fields = stocksystem_kit_fields( $slug );

	foreach ( $fields as $name => $field ) {
		$raw = array_key_exists( $name, $in ) ? $in[ $name ] : null;

		if ( 'repeater' === $field['type'] ) {
			$rows  = array();
			$first = key( $field['fields'] );

			foreach ( array_slice( is_array( $raw ) ? $raw : array(), 0, isset( $field['max'] ) ? (int) $field['max'] : 60 ) as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$clean = array();
				foreach ( $field['fields'] as $sub => $sub_field ) {
					$clean[ $sub ] = stocksystem_kit_clean_value( $sub_field, isset( $row[ $sub ] ) ? $row[ $sub ] : '' );
				}
				// A row needs its main (first) field, else it is an empty leftover.
				$main = isset( $clean[ $first ] ) ? $clean[ $first ] : '';
				if ( '' !== $main && 0 !== $main ) {
					$rows[] = $clean;
				}
			}

			$out[ $name ] = $rows;
			continue;
		}

		if ( 'checkbox' === $field['type'] ) {
			$out[ $name ] = empty( $raw ) ? 0 : 1;
			continue;
		}

		if ( null === $raw ) {
			continue; // Not on the form: leave the default in force.
		}

		$out[ $name ] = stocksystem_kit_clean_value( $field, $raw );
	}

	return $out;
}

function stocksystem_kit_register_settings() {
	foreach ( array_keys( stocksystem_kit_pages() ) as $slug ) {
		register_setting(
			'stocksystem_' . $slug . '_group',
			'stocksystem_' . $slug,
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $input ) use ( $slug ) {
					return stocksystem_kit_sanitize( $slug, $input );
				},
				'default'           => array(),
			)
		);
	}
}
add_action( 'admin_init', 'stocksystem_kit_register_settings' );

/* -------------------------------------------------------------------------
 * Menus
 * ---------------------------------------------------------------------- */

function stocksystem_kit_menu() {
	add_menu_page(
		__( 'استوک سیستم', 'stocksystem' ),
		__( 'استوک سیستم', 'stocksystem' ),
		'manage_options',
		'stocksystem',
		'stocksystem_kit_hub_page',
		'dashicons-store',
		3
	);

	add_submenu_page( 'stocksystem', __( 'مرکز ویرایش سایت', 'stocksystem' ), __( 'مرکز ویرایش', 'stocksystem' ), 'manage_options', 'stocksystem', 'stocksystem_kit_hub_page' );

	foreach ( stocksystem_kit_pages() as $slug => $page ) {
		add_submenu_page(
			'stocksystem',
			$page['title'],
			isset( $page['menu'] ) ? $page['menu'] : $page['title'],
			'manage_options',
			'stocksystem-' . $slug,
			function () use ( $slug ) {
				stocksystem_kit_render_page( $slug );
			}
		);
	}

	// The older Appearance pages, reachable from here too.
	foreach ( stocksystem_kit_hub_links() as $link ) {
		if ( ! empty( $link['legacy'] ) ) {
			add_submenu_page( 'stocksystem', $link['title'], $link['title'], 'manage_options', $link['url'] );
		}
	}
}
add_action( 'admin_menu', 'stocksystem_kit_menu' );

function stocksystem_kit_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'stocksystem' ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'stocksystem-kit-admin', STOCKSYSTEM_URI . '/assets/css/admin-kit.css', array(), STOCKSYSTEM_VERSION );
	wp_enqueue_script( 'stocksystem-kit-admin', STOCKSYSTEM_URI . '/assets/js/admin-kit.js', array( 'jquery' ), STOCKSYSTEM_VERSION, true );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'stocksystem_kit_assets' );

/* -------------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------- */

/** One input, for a top-level field ($name = stocksystem_about[key]) or a repeater cell. */
function stocksystem_kit_input( $name, $field, $value ) {
	$type = $field['type'];
	$ph   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

	switch ( $type ) {
		case 'textarea':
		case 'html':
		case 'code':
			$rows = isset( $field['rows'] ) ? (int) $field['rows'] : ( 'textarea' === $type ? 3 : 5 );
			printf(
				'<textarea name="%1$s" rows="%2$d" placeholder="%3$s"%4$s>%5$s</textarea>',
				esc_attr( $name ),
				(int) $rows,
				esc_attr( $ph ),
				'code' === $type ? ' dir="ltr" class="ss-code"' : '',
				esc_textarea( (string) $value )
			);
			break;

		case 'checkbox':
			printf( '<input type="hidden" name="%1$s" value="0"><input type="checkbox" name="%1$s" value="1" %2$s>', esc_attr( $name ), checked( ! empty( $value ), true, false ) );
			break;

		case 'select':
			$options = isset( $field['options_cb'] ) ? call_user_func( $field['options_cb'] ) : $field['options'];
			printf( '<select name="%s">', esc_attr( $name ) );
			foreach ( $options as $key => $label ) {
				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), selected( (string) $value, (string) $key, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'image':
			$id = (int) $value;
			echo '<div class="ss-media">';
			echo '<div class="ss-media__preview">' . ( $id ? wp_get_attachment_image( $id, 'thumbnail' ) : '' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( $id ) );
			printf( '<button type="button" class="button ss-media__pick">%s</button> ', $id ? esc_html__( 'تغییر تصویر', 'stocksystem' ) : esc_html__( 'انتخاب تصویر', 'stocksystem' ) );
			printf( '<button type="button" class="button-link ss-media__clear"%s>%s</button>', $id ? '' : ' hidden', esc_html__( 'حذف', 'stocksystem' ) );
			echo '</div>';
			break;

		case 'color':
			printf( '<input type="text" name="%1$s" value="%2$s" class="ss-color" data-default-color="%3$s" dir="ltr">', esc_attr( $name ), esc_attr( (string) $value ), esc_attr( isset( $field['default'] ) ? $field['default'] : '' ) );
			break;

		case 'datetime':
			printf( '<input type="datetime-local" name="%s" value="%s" dir="ltr">', esc_attr( $name ), esc_attr( (string) $value ) );
			break;

		case 'number':
			printf( '<input type="text" inputmode="numeric" name="%1$s" value="%2$s" placeholder="%3$s" dir="ltr">', esc_attr( $name ), esc_attr( (string) $value ), esc_attr( $ph ) );
			break;

		case 'url':
			printf( '<input type="text" name="%1$s" value="%2$s" placeholder="%3$s" dir="ltr" class="ss-url">', esc_attr( $name ), esc_attr( (string) $value ), esc_attr( $ph ) );
			break;

		default:
			printf( '<input type="text" name="%1$s" value="%2$s" placeholder="%3$s">', esc_attr( $name ), esc_attr( (string) $value ), esc_attr( $ph ) );
	}
}

function stocksystem_kit_repeater( $name, $field, $rows ) {
	$row_html = function ( $index, $row ) use ( $name, $field ) {
		?>
		<div class="ss-rep__row ss-rep__row--card">
			<div class="ss-rep__cells">
				<?php foreach ( $field['fields'] as $sub => $sub_field ) : ?>
					<label class="ss-rep__cell ss-rep__cell--<?php echo esc_attr( $sub_field['type'] ); ?><?php echo isset( $sub_field['wide'] ) ? ' is-wide' : ''; ?>">
						<span class="ss-field__label"><?php echo esc_html( $sub_field['label'] ); ?></span>
						<?php stocksystem_kit_input( $name . '[' . $index . '][' . $sub . ']', $sub_field, isset( $row[ $sub ] ) ? $row[ $sub ] : ( isset( $sub_field['default'] ) ? $sub_field['default'] : '' ) ); ?>
					</label>
				<?php endforeach; ?>
			</div>
			<span class="ss-rep__tools">
				<button type="button" class="button-link ss-rep__up" aria-label="<?php esc_attr_e( 'بالاتر', 'stocksystem' ); ?>">↑</button>
				<button type="button" class="button-link ss-rep__down" aria-label="<?php esc_attr_e( 'پایین‌تر', 'stocksystem' ); ?>">↓</button>
				<button type="button" class="button-link button-link-delete ss-rep__remove"><?php esc_html_e( 'حذف', 'stocksystem' ); ?></button>
			</span>
		</div>
		<?php
	};
	?>
	<div class="ss-rep" data-name="<?php echo esc_attr( $name ); ?>">
		<div class="ss-rep__rows">
			<?php foreach ( array_values( (array) $rows ) as $i => $row ) { $row_html( $i, $row ); } ?>
		</div>
		<template class="ss-rep__tpl"><?php $row_html( '__i__', array() ); ?></template>
		<button type="button" class="button ss-rep__add"><?php echo esc_html( isset( $field['add_label'] ) ? $field['add_label'] : __( '+ افزودن', 'stocksystem' ) ); ?></button>
	</div>
	<?php
}

function stocksystem_kit_render_page( $slug ) {
	$pages = stocksystem_kit_pages();

	if ( ! current_user_can( 'manage_options' ) || ! isset( $pages[ $slug ] ) ) {
		return;
	}

	$page   = $pages[ $slug ];
	$values = stocksystem_opt( $slug );
	$option = 'stocksystem_' . $slug;
	?>
	<div class="wrap ss-home ss-kit">
		<h1><?php echo esc_html( $page['title'] ); ?></h1>
		<?php if ( ! empty( $page['intro'] ) ) : ?>
			<p class="description"><?php echo esc_html( $page['intro'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $page['view'] ) ) : ?>
			<p><a class="button" href="<?php echo esc_url( stocksystem_home_link( $page['view'], home_url( '/' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'دیدن این صفحه در سایت', 'stocksystem' ); ?></a></p>
		<?php endif; ?>

		<?php if ( isset( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'به متن‌های پیش‌فرض برگشت.', 'stocksystem' ); ?></p></div>
		<?php endif; ?>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'stocksystem_' . $slug . '_group' ); ?>

			<?php foreach ( $page['sections'] as $section ) : ?>
				<section class="ss-card">
					<div class="ss-card__head"><h2><?php echo esc_html( $section['title'] ); ?></h2></div>
					<?php if ( ! empty( $section['help'] ) ) : ?>
						<p class="description"><?php echo esc_html( $section['help'] ); ?></p>
					<?php endif; ?>
					<div class="ss-grid">
						<?php foreach ( $section['fields'] as $name => $field ) : ?>
							<?php
							$full  = 'repeater' === $field['type'] || in_array( $field['type'], array( 'textarea', 'html', 'code' ), true ) || ! empty( $field['wide'] );
							$input = $option . '[' . $name . ']';
							?>
							<div class="ss-field<?php echo $full ? ' ss-field--wide' : ''; ?>">
								<?php if ( 'checkbox' === $field['type'] ) : ?>
									<label class="ss-check">
										<?php stocksystem_kit_input( $input, $field, $values[ $name ] ); ?>
										<span><?php echo esc_html( $field['label'] ); ?></span>
									</label>
								<?php elseif ( 'repeater' === $field['type'] ) : ?>
									<span class="ss-field__label"><?php echo esc_html( $field['label'] ); ?></span>
									<?php stocksystem_kit_repeater( $input, $field, $values[ $name ] ); ?>
								<?php elseif ( 'color' === $field['type'] ) : ?>
									<?php // The colour picker script wraps its input, so the label must not contain it. ?>
									<span class="ss-field__label"><?php echo esc_html( $field['label'] ); ?></span>
									<?php stocksystem_kit_input( $input, $field, $values[ $name ] ); ?>
								<?php else : ?>
									<label>
										<span class="ss-field__label"><?php echo esc_html( $field['label'] ); ?></span>
										<?php stocksystem_kit_input( $input, $field, $values[ $name ] ); ?>
									</label>
								<?php endif; ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<span class="ss-field__help"><?php echo esc_html( $field['help'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>

			<p class="submit">
				<?php submit_button( __( 'ذخیرهٔ تغییرات', 'stocksystem' ), 'primary', 'submit', false ); ?>
			</p>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ss-reset" onsubmit="return confirm('<?php echo esc_js( __( 'همهٔ تغییرات این صفحه پاک شود و متن‌های پیش‌فرض برگردد؟', 'stocksystem' ) ); ?>');">
			<input type="hidden" name="action" value="stocksystem_kit_reset">
			<input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>">
			<?php wp_nonce_field( 'stocksystem_kit_reset_' . $slug ); ?>
			<button type="submit" class="button-link button-link-delete"><?php esc_html_e( 'بازگشت به حالت پیش‌فرض', 'stocksystem' ); ?></button>
		</form>
	</div>
	<?php
}

function stocksystem_kit_handle_reset() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}

	$slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
	check_admin_referer( 'stocksystem_kit_reset_' . $slug );

	if ( isset( stocksystem_kit_pages()[ $slug ] ) ) {
		delete_option( 'stocksystem_' . $slug );
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'stocksystem-' . $slug, 'reset' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_stocksystem_kit_reset', 'stocksystem_kit_handle_reset' );

/* -------------------------------------------------------------------------
 * Hub
 * ---------------------------------------------------------------------- */

/** Every editable area, for the hub page and the menu. */
function stocksystem_kit_hub_links() {
	$links = array(
		array( 'group' => 'محتوای صفحه‌ها', 'title' => __( 'صفحهٔ اصلی', 'stocksystem' ), 'desc' => __( 'تیتر، دکمه‌ها، تصاویر، دسته‌ها، کالاهای پیشنهادی، مزیت‌ها، مقالات و بخش‌های تازه (بنر، نظرات، برندها، آمار).', 'stocksystem' ), 'url' => 'themes.php?page=stocksystem-home', 'legacy' => true ),
	);

	foreach ( stocksystem_kit_pages() as $slug => $page ) {
		$links[] = array(
			'group' => isset( $page['group'] ) ? $page['group'] : 'محتوای صفحه‌ها',
			'title' => isset( $page['menu'] ) ? $page['menu'] : $page['title'],
			'desc'  => isset( $page['intro'] ) ? $page['intro'] : '',
			'url'   => 'admin.php?page=stocksystem-' . $slug,
		);
	}

	$links[] = array( 'group' => 'منو، فوتر و متن‌ها', 'title' => __( 'منو و فوتر', 'stocksystem' ), 'desc' => __( 'دسته‌ها، مگامنو، بازه‌های قیمت، شبکه‌های اجتماعی، نمادها و خبرنامه.', 'stocksystem' ), 'url' => 'themes.php?page=stocksystem-nav', 'legacy' => true );
	$links[] = array( 'group' => 'منو، فوتر و متن‌ها', 'title' => __( 'متن‌های سایت', 'stocksystem' ), 'desc' => __( 'همهٔ جمله‌ها، عنوان‌ها و دکمه‌های قالب در یک فهرست جست‌وجوپذیر.', 'stocksystem' ), 'url' => 'themes.php?page=stocksystem-texts', 'legacy' => true );
	$links[] = array( 'group' => 'منو، فوتر و متن‌ها', 'title' => __( 'لینک‌های هدر و فوتر (فهرست‌ها)', 'stocksystem' ), 'desc' => __( 'لینک‌های نوار بالا، نوار اصلی و ستون خدمات فوتر.', 'stocksystem' ), 'url' => 'nav-menus.php' );
	$links[] = array( 'group' => 'منو، فوتر و متن‌ها', 'title' => __( 'شماره تماس، آدرس و گارانتی', 'stocksystem' ), 'desc' => __( 'اطلاعات پایه‌ای که در هدر، فوتر و صفحه‌ها تکرار می‌شود.', 'stocksystem' ), 'url' => 'customize.php?autofocus[section]=stocksystem_business' );

	return $links;
}

function stocksystem_kit_hub_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$grouped = array();
	foreach ( stocksystem_kit_hub_links() as $link ) {
		$grouped[ $link['group'] ][] = $link;
	}
	?>
	<div class="wrap ss-home ss-hub">
		<h1><?php esc_html_e( 'مرکز ویرایش سایت', 'stocksystem' ); ?></h1>
		<p class="description"><?php esc_html_e( 'هرچه در سایت می‌بینید از یکی از این‌جاها قابل تغییر است. هر صفحه یک دکمهٔ «بازگشت به حالت پیش‌فرض» هم دارد.', 'stocksystem' ); ?></p>
		<?php foreach ( $grouped as $group => $links ) : ?>
			<h2 class="ss-hub__group"><?php echo esc_html( $group ); ?></h2>
			<div class="ss-hub__grid">
				<?php foreach ( $links as $link ) : ?>
					<a class="ss-hub__card" href="<?php echo esc_url( admin_url( $link['url'] ) ); ?>">
						<strong><?php echo esc_html( $link['title'] ); ?></strong>
						<span><?php echo esc_html( $link['desc'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
