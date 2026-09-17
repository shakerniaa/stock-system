<?php
/**
 * Internal wallet — decision #10: "کیف پول داخلی می‌ماند (شارژ، مشاهدهٔ
 * موجودی، برداشت). ذخیرهٔ شمارهٔ کارت بانکی حذف می‌شود." No card storage
 * anywhere in this file or its DB usage — balance is a plain integer.
 *
 * Scope for this pass: the payment gateway that *spends* wallet balance
 * at checkout. Top-up / view-balance / withdraw UI belongs to the
 * My Account cluster (not built yet) — the helpers below
 * (stocksystem_get_wallet_balance, stocksystem_adjust_wallet_balance)
 * are what that phase will build on.
 *
 * Simplification: the design shows a partial-wallet-then-gateway-for-
 * the-remainder split payment. That needs a "pay balance, then redirect
 * to a real gateway for the rest" flow, which is a materially bigger
 * feature. For now the wallet option only appears when it can cover the
 * order in full — worth revisiting once a specific IPG plugin is chosen.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_get_wallet_balance( $user_id ) {
	return (int) get_user_meta( $user_id, '_wallet_balance', true );
}

/**
 * @param int    $user_id
 * @param int    $delta Positive to add, negative to deduct.
 * @param string $note  Stored in a simple ledger (post meta on nothing
 *                      yet — just a log via error_log-style user meta
 *                      array) so top-ups/spends are auditable later.
 */
function stocksystem_adjust_wallet_balance( $user_id, $delta, $note = '' ) {
	$balance = stocksystem_get_wallet_balance( $user_id ) + (int) $delta;
	update_user_meta( $user_id, '_wallet_balance', max( 0, $balance ) );

	$ledger   = get_user_meta( $user_id, '_wallet_ledger', true );
	$ledger   = is_array( $ledger ) ? $ledger : array();
	$ledger[] = array(
		'delta' => (int) $delta,
		'note'  => $note,
		'date'  => current_time( 'mysql' ),
	);
	update_user_meta( $user_id, '_wallet_ledger', array_slice( $ledger, -100 ) );

	return $balance;
}

function stocksystem_register_wallet_gateway( $gateways ) {
	$gateways[] = 'Stocksystem_Wallet_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'stocksystem_register_wallet_gateway' );

function stocksystem_init_wallet_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/**
	 * @phpstan-ignore-next-line class conditionally defined after WC loads.
	 */
	class Stocksystem_Wallet_Gateway extends WC_Payment_Gateway {

		public function __construct() {
			$this->id                 = 'stocksystem_wallet';
			$this->icon               = '';
			$this->has_fields         = false;
			$this->method_title       = __( 'کیف پول استوک سیستم', 'stocksystem' );
			$this->method_description = __( 'پرداخت از موجودی کیف پول داخلی مشتری. فقط وقتی موجودی کل مبلغ سفارش را پوشش دهد در دسترس است.', 'stocksystem' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title', __( 'کیف پول استوک سیستم', 'stocksystem' ) );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'   => __( 'فعال‌سازی', 'stocksystem' ),
					'type'    => 'checkbox',
					'label'   => __( 'فعال کردن پرداخت از کیف پول', 'stocksystem' ),
					'default' => 'yes',
				),
				'title'       => array(
					'title'       => __( 'عنوان', 'stocksystem' ),
					'type'        => 'text',
					'default'     => __( 'کیف پول استوک سیستم', 'stocksystem' ),
				),
				'description' => array(
					'title'   => __( 'توضیح', 'stocksystem' ),
					'type'    => 'textarea',
					'default' => __( 'مبلغ سفارش از موجودی کیف پول شما کسر می‌شود.', 'stocksystem' ),
				),
			);
		}

		public function is_available() {
			if ( 'yes' !== $this->enabled || ! is_user_logged_in() ) {
				return false;
			}

			if ( null === WC()->cart ) {
				return false;
			}

			$balance = stocksystem_get_wallet_balance( get_current_user_id() );
			return $balance >= WC()->cart->get_total( 'edit' );
		}

		public function get_wallet_balance_display() {
			return stocksystem_get_wallet_balance( get_current_user_id() );
		}

		public function process_payment( $order_id ) {
			$order   = wc_get_order( $order_id );
			$user_id = $order->get_customer_id();
			$total   = (int) $order->get_total();

			if ( ! $user_id || stocksystem_get_wallet_balance( $user_id ) < $total ) {
				wc_add_notice( __( 'موجودی کیف پول کافی نیست.', 'stocksystem' ), 'error' );
				return array( 'result' => 'failure' );
			}

			stocksystem_adjust_wallet_balance(
				$user_id,
				-$total,
				sprintf(
					/* translators: %s: order number */
					__( 'پرداخت سفارش #%s', 'stocksystem' ),
					$order->get_order_number()
				)
			);

			$order->payment_complete();
			$order->add_order_note( __( 'پرداخت با موفقیت از کیف پول انجام شد.', 'stocksystem' ) );

			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}
}
add_action( 'plugins_loaded', 'stocksystem_init_wallet_gateway', 20 );
