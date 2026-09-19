<?php
/**
 * Plugin Name: Stock System — dev mock gateway
 * Description: LOCAL DEVELOPMENT ONLY. A fake "online bank gateway" that marks the order paid immediately, so the checkout can be tested end to end without a real Iranian gateway. Installed by dev-tools/seed-checkout.php; never deploy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		class Stocksystem_Dev_Gateway extends WC_Payment_Gateway {
			public function __construct() {
				$this->id                 = 'stocksystem_dev';
				$this->method_title       = 'Stock System dev gateway';
				$this->method_description = 'Local testing only — pays every order instantly.';
				$this->has_fields         = false;
				$this->order_button_text  = 'انتقال به درگاه بانکی';

				$this->init_form_fields();
				$this->init_settings();

				$this->title       = 'پرداخت اینترنتی — درگاه بانکی';
				$this->description = 'انتقال به صفحهٔ امن بانک. اطلاعات کارت هرگز روی سرور استوک سیستم ذخیره نمی‌شود. (درگاه آزمایشی محیط توسعه)';
			}

			public function init_form_fields() {
				$this->form_fields = array(
					'enabled' => array(
						'title'   => 'Enable',
						'type'    => 'checkbox',
						'label'   => 'Enable the dev gateway',
						'default' => 'yes',
					),
				);
			}

			public function process_payment( $order_id ) {
				$order = wc_get_order( $order_id );
				$order->payment_complete();
				WC()->cart->empty_cart();

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}
		}

		add_filter(
			'woocommerce_payment_gateways',
			function ( $gateways ) {
				$gateways[] = 'Stocksystem_Dev_Gateway';
				return $gateways;
			}
		);
	}
);
