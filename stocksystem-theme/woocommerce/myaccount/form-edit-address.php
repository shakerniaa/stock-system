<?php
/**
 * Edit one address (billing / shipping). Same field grid and controls as the
 * checkout form so the two feel like one system.
 *
 * @package StockSystem
 *
 * @var string $load_address 'billing' | 'shipping' | ''
 * @var array  $address      Field definitions.
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? __( 'ویرایش نشانی پیش‌فرض', 'stocksystem' ) : __( 'ویرایش نشانی گیرندهٔ دیگر', 'stocksystem' );

do_action( 'woocommerce_before_edit_account_address_form' );
?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>
	<div class="account-address-form">
		<div class="account-panel__header">
			<h2><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?></h2>
		</div>

		<form method="post" novalidate>
			<div class="woocommerce-address-fields">
				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields__field-wrapper">
					<?php
					foreach ( $address as $key => $field ) {
						woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					}
					?>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

				<div class="account-address-form__actions">
					<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
					<input type="hidden" name="action" value="edit_address">
					<button type="submit" class="btn btn--primary" name="save_address" value="<?php esc_attr_e( 'ذخیرهٔ نشانی', 'stocksystem' ); ?>"><?php esc_html_e( 'ذخیرهٔ نشانی', 'stocksystem' ); ?></button>
					<a class="btn btn--outline" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php esc_html_e( 'انصراف', 'stocksystem' ); ?></a>
				</div>
			</div>
		</form>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
