<?php
/**
 * "کیف پول و اعتبار" — My Account → Wallet. Decision #10: internal
 * wallet stays (top-up, view balance, withdraw); card storage is
 * removed entirely (nothing here ever asks for a card number).
 *
 * No online top-up flow yet — no payment gateway has been chosen
 * (README open item), so "شارژ" is a request-based form the admin
 * fulfils manually and then credits via inc/wallet.php's
 * stocksystem_adjust_wallet_balance(), same honest pattern as the
 * notify-me / quote-request forms elsewhere in the theme.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id  = get_current_user_id();
$balance  = stocksystem_get_wallet_balance( $user_id );
$ledger   = get_user_meta( $user_id, '_wallet_ledger', true );
$ledger   = is_array( $ledger ) ? array_reverse( $ledger ) : array();
$topup_requested = isset( $_GET['topup_requested'] );
?>
<div class="account-wallet">
	<div class="account-panel__header">
		<h2><?php esc_html_e( 'کیف پول و اعتبار', 'stocksystem' ); ?></h2>
	</div>

	<div class="account-wallet__balance-card">
		<span class="account-wallet__balance-label"><?php esc_html_e( 'موجودی فعلی', 'stocksystem' ); ?></span>
		<span class="account-wallet__balance-amount">
			<?php echo esc_html( stocksystem_format_number( $balance ) ); ?> <span><?php esc_html_e( 'تومان', 'stocksystem' ); ?></span>
		</span>
	</div>

	<?php if ( $topup_requested ) : ?>
		<p class="account-panel__notice"><?php esc_html_e( 'درخواست شارژ ثبت شد — پس از تأیید واریز، موجودی شما به‌روزرسانی می‌شود.', 'stocksystem' ); ?></p>
	<?php endif; ?>

	<form class="account-wallet__topup-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="stocksystem_wallet_topup">
		<?php wp_nonce_field( 'stocksystem_wallet_topup', 'stocksystem_wallet_nonce' ); ?>
		<label for="wallet-topup-amount"><?php esc_html_e( 'مبلغ درخواستی برای شارژ (تومان)', 'stocksystem' ); ?></label>
		<div class="account-wallet__topup-row">
			<input type="number" id="wallet-topup-amount" name="amount" min="10000" step="10000" placeholder="۵۰۰۰۰۰" required>
			<button type="submit" class="btn btn--primary"><?php esc_html_e( 'درخواست شارژ', 'stocksystem' ); ?></button>
		</div>
		<p class="account-wallet__topup-note"><?php esc_html_e( 'پس از ثبت درخواست، همکاران ما برای هماهنگی واریز با شما تماس می‌گیرند.', 'stocksystem' ); ?></p>
	</form>

	<?php if ( ! empty( $ledger ) ) : ?>
		<div class="account-wallet__ledger">
			<span class="account-wallet__ledger-title"><?php esc_html_e( 'تراکنش‌های اخیر', 'stocksystem' ); ?></span>
			<?php foreach ( array_slice( $ledger, 0, 10 ) as $entry ) : ?>
				<div class="account-wallet__ledger-row">
					<span class="account-wallet__ledger-note"><?php echo esc_html( $entry['note'] ); ?></span>
					<span class="account-wallet__ledger-date"><?php echo esc_html( stocksystem_to_persian_digits( mysql2date( 'j F', $entry['date'] ) ) ); ?></span>
					<span class="account-wallet__ledger-amount<?php echo $entry['delta'] < 0 ? ' is-negative' : ' is-positive'; ?>">
						<?php echo esc_html( ( $entry['delta'] >= 0 ? '+' : '−' ) . stocksystem_format_number( abs( $entry['delta'] ) ) ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
