<?php
/**
 * Per-page SEO: a «سئو» box on every page / post / product and on category,
 * brand and tag screens (title, description, hide-from-Google, share image),
 * plus the meta / Open Graph output. Site-wide defaults live in
 * «استوک سیستم ← سئو و آمار». No SEO plugin is needed (or assumed).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Editing screens
 * ---------------------------------------------------------------------- */

function stocksystem_seo_fields( $get, $prefix = '' ) {
	$title   = $get( 'title' );
	$desc    = $get( 'desc' );
	$noindex = $get( 'noindex' );
	$image   = (int) $get( 'image' );
	?>
	<div class="ss-seo">
		<p>
			<label for="<?php echo esc_attr( $prefix ); ?>ss_seo_title"><strong><?php esc_html_e( 'عنوان برای گوگل', 'stocksystem' ); ?></strong></label><br>
			<input type="text" class="widefat" id="<?php echo esc_attr( $prefix ); ?>ss_seo_title" name="ss_seo[title]" value="<?php echo esc_attr( $title ); ?>" maxlength="120">
			<span class="description"><?php esc_html_e( 'خالی = عنوان خود صفحه. حدود ۵۰ تا ۶۰ نویسه بهتر است.', 'stocksystem' ); ?></span>
		</p>
		<p>
			<label for="<?php echo esc_attr( $prefix ); ?>ss_seo_desc"><strong><?php esc_html_e( 'توضیح برای گوگل', 'stocksystem' ); ?></strong></label><br>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $prefix ); ?>ss_seo_desc" name="ss_seo[desc]" maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea>
			<span class="description"><?php esc_html_e( 'خالی = خلاصهٔ خودکار. حدود ۱۲۰ تا ۱۶۰ نویسه.', 'stocksystem' ); ?></span>
		</p>
		<p>
			<label><input type="checkbox" name="ss_seo[noindex]" value="1" <?php checked( ! empty( $noindex ) ); ?>> <?php esc_html_e( 'این صفحه در گوگل نمایش داده نشود', 'stocksystem' ); ?></label>
		</p>
		<p>
			<strong><?php esc_html_e( 'تصویر اشتراک‌گذاری', 'stocksystem' ); ?></strong>
			<span class="ss-media" style="display:flex;align-items:center;gap:10px;margin-top:6px">
				<span class="ss-media__preview" style="width:96px;height:64px;background:#f6f7f7;display:flex;align-items:center;justify-content:center;overflow:hidden"><?php echo $image ? wp_get_attachment_image( $image, 'thumbnail' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<input type="hidden" name="ss_seo[image]" value="<?php echo esc_attr( $image ); ?>">
				<button type="button" class="button ss-media__pick"><?php echo $image ? esc_html__( 'تغییر تصویر', 'stocksystem' ) : esc_html__( 'انتخاب تصویر', 'stocksystem' ); ?></button>
				<button type="button" class="button-link ss-media__clear"<?php echo $image ? '' : ' hidden'; ?>><?php esc_html_e( 'حذف', 'stocksystem' ); ?></button>
			</span>
			<span class="description"><?php esc_html_e( 'خالی = تصویر شاخص صفحه، یا تصویر پیش‌فرض سایت.', 'stocksystem' ); ?></span>
		</p>
	</div>
	<?php
}

function stocksystem_seo_add_meta_box() {
	foreach ( array( 'post', 'page', 'product' ) as $type ) {
		add_meta_box( 'stocksystem_seo', __( 'سئو (گوگل و اشتراک‌گذاری)', 'stocksystem' ), 'stocksystem_seo_meta_box', $type, 'normal', 'low' );
	}
}
add_action( 'add_meta_boxes', 'stocksystem_seo_add_meta_box' );

function stocksystem_seo_meta_box( $post ) {
	wp_nonce_field( 'stocksystem_seo_save', 'stocksystem_seo_nonce' );
	stocksystem_seo_fields(
		function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, '_ss_seo_' . $key, true );
		}
	);
}

function stocksystem_seo_clean( $raw ) {
	$raw = is_array( $raw ) ? wp_unslash( $raw ) : array();

	return array(
		'title'   => isset( $raw['title'] ) ? sanitize_text_field( $raw['title'] ) : '',
		'desc'    => isset( $raw['desc'] ) ? sanitize_textarea_field( $raw['desc'] ) : '',
		'noindex' => empty( $raw['noindex'] ) ? '' : '1',
		'image'   => ( isset( $raw['image'] ) && wp_attachment_is_image( absint( $raw['image'] ) ) ) ? absint( $raw['image'] ) : '',
	);
}

function stocksystem_seo_save_post( $post_id ) {
	if ( ! isset( $_POST['stocksystem_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stocksystem_seo_nonce'] ) ), 'stocksystem_seo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$clean = stocksystem_seo_clean( isset( $_POST['ss_seo'] ) ? $_POST['ss_seo'] : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $clean as $key => $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, '_ss_seo_' . $key );
		} else {
			update_post_meta( $post_id, '_ss_seo_' . $key, $value );
		}
	}
}
add_action( 'save_post', 'stocksystem_seo_save_post' );

// Taxonomy screens.
function stocksystem_seo_term_edit_form( $term ) {
	wp_nonce_field( 'stocksystem_seo_save', 'stocksystem_seo_nonce' );
	echo '<tr class="form-field"><th scope="row">' . esc_html__( 'سئو', 'stocksystem' ) . '</th><td>';
	stocksystem_seo_fields(
		function ( $key ) use ( $term ) {
			return get_term_meta( $term->term_id, '_ss_seo_' . $key, true );
		},
		'term_'
	);
	echo '</td></tr>';
}

function stocksystem_seo_save_term( $term_id ) {
	if ( ! isset( $_POST['stocksystem_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stocksystem_seo_nonce'] ) ), 'stocksystem_seo_save' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$clean = stocksystem_seo_clean( isset( $_POST['ss_seo'] ) ? $_POST['ss_seo'] : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	foreach ( $clean as $key => $value ) {
		if ( '' === $value ) {
			delete_term_meta( $term_id, '_ss_seo_' . $key );
		} else {
			update_term_meta( $term_id, '_ss_seo_' . $key, $value );
		}
	}
}

function stocksystem_seo_register_term_hooks() {
	foreach ( array( 'category', 'post_tag', 'product_cat', 'product_tag', 'product_brand' ) as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			add_action( $taxonomy . '_edit_form_fields', 'stocksystem_seo_term_edit_form' );
			add_action( 'edited_' . $taxonomy, 'stocksystem_seo_save_term' );
		}
	}
}
add_action( 'init', 'stocksystem_seo_register_term_hooks', 20 );

function stocksystem_seo_admin_assets( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php', 'term.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_script( 'stocksystem-kit-admin', STOCKSYSTEM_URI . '/assets/js/admin-kit.js', array( 'jquery' ), STOCKSYSTEM_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'stocksystem_seo_admin_assets' );

/* -------------------------------------------------------------------------
 * Front-end output
 * ---------------------------------------------------------------------- */

/** Values for the current request: title, desc, image url, url, type, noindex. */
function stocksystem_seo_context() {
	static $ctx = null;

	if ( null !== $ctx ) {
		return $ctx;
	}

	$seo = stocksystem_opt( 'seo' );
	$ctx = array(
		'title'   => '',
		'desc'    => '',
		'image'   => '',
		'url'     => '',
		'type'    => 'website',
		'noindex' => false,
	);

	$meta = function ( $type, $id, $key ) {
		return 'term' === $type ? get_term_meta( $id, '_ss_seo_' . $key, true ) : get_post_meta( $id, '_ss_seo_' . $key, true );
	};

	if ( is_singular() ) {
		$id            = get_queried_object_id();
		$ctx['title']  = $meta( 'post', $id, 'title' );
		$ctx['desc']   = $meta( 'post', $id, 'desc' );
		$ctx['noindex'] = (bool) $meta( 'post', $id, 'noindex' );
		$image_id      = (int) $meta( 'post', $id, 'image' );
		$image_id      = $image_id ? $image_id : (int) get_post_thumbnail_id( $id );
		$ctx['image']  = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
		$ctx['url']    = get_permalink( $id );
		$ctx['type']   = is_singular( 'product' ) ? 'product' : ( is_singular( 'post' ) ? 'article' : 'website' );

		if ( '' === $ctx['desc'] ) {
			$post = get_post( $id );
			$text = $post ? ( has_excerpt( $id ) ? $post->post_excerpt : $post->post_content ) : '';
			$ctx['desc'] = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $text ) ), 28, '…' );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) && isset( $term->term_id ) ) {
			$ctx['title']   = $meta( 'term', $term->term_id, 'title' );
			$ctx['desc']    = $meta( 'term', $term->term_id, 'desc' );
			$ctx['noindex'] = (bool) $meta( 'term', $term->term_id, 'noindex' );
			$image_id       = (int) $meta( 'term', $term->term_id, 'image' );
			$image_id       = $image_id ? $image_id : (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			$ctx['image']   = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
			$link           = get_term_link( $term );
			$ctx['url']     = is_wp_error( $link ) ? '' : $link;

			if ( '' === $ctx['desc'] ) {
				$ctx['desc'] = wp_trim_words( wp_strip_all_tags( term_description( $term->term_id ) ), 28, '…' );
			}
		}
	}

	if ( '' === $ctx['desc'] ) {
		$ctx['desc'] = $seo['home_desc'];
	}
	if ( '' === $ctx['image'] ) {
		$ctx['image'] = stocksystem_opt_image( 'seo', 'og_image', 'large' );
	}
	if ( '' === $ctx['url'] ) {
		$ctx['url'] = is_front_page() ? home_url( '/' ) : '';
	}

	return $ctx;
}

function stocksystem_seo_document_title( $title ) {
	$ctx = stocksystem_seo_context();

	return '' !== $ctx['title'] ? $ctx['title'] : $title;
}
add_filter( 'pre_get_document_title', function ( $title ) {
	$custom = stocksystem_seo_document_title( '' );

	return '' !== $custom ? $custom : $title;
} );

function stocksystem_seo_robots( $robots ) {
	if ( stocksystem_seo_context()['noindex'] ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['max-image-preview'] );
	}

	return $robots;
}
add_filter( 'wp_robots', 'stocksystem_seo_robots' );

function stocksystem_seo_print_meta() {
	if ( is_admin() || is_404() || is_feed() ) {
		return;
	}

	$ctx   = stocksystem_seo_context();
	$title = '' !== $ctx['title'] ? $ctx['title'] : wp_get_document_title();

	if ( '' !== $ctx['desc'] ) {
		echo '<meta name="description" content="' . esc_attr( $ctx['desc'] ) . '">' . "\n";
	}

	echo '<meta property="og:locale" content="fa_IR">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $ctx['type'] ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( '' !== $ctx['desc'] ) {
		echo '<meta property="og:description" content="' . esc_attr( $ctx['desc'] ) . '">' . "\n";
	}
	if ( '' !== $ctx['url'] ) {
		echo '<meta property="og:url" content="' . esc_url( $ctx['url'] ) . '">' . "\n";
	}
	if ( '' !== $ctx['image'] ) {
		echo '<meta property="og:image" content="' . esc_url( $ctx['image'] ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="' . ( '' !== $ctx['image'] ? 'summary_large_image' : 'summary' ) . '">' . "\n";
}
add_action( 'wp_head', 'stocksystem_seo_print_meta', 4 );
