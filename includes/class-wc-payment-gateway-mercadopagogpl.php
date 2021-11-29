<?php
/**
 * MercadoPago Tools GPL - Override Payment Gateway
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

if ( ! class_exists( 'WC_Payment_Gateway_MercadoPagoGPL' ) ) :
	/**
	 * Class Override Payment Gateway.
	 */
	class WC_Payment_Gateway_MercadoPagoGPL extends WC_Payment_Gateway {
		/**
		 * Get current gateway payment.
		 */
		private function get_current_gateway() {
			$available_gateways = WC_KMercadoPagoGPL::woocommerce_instance()->payment_gateways->payment_gateways();
			$current_gateway    = null;
			$default_gateway    = get_option( 'woocommerce_default_gateway' );
			if ( ! empty( $available_gateways ) ) {
				$wc = WC_KMercadoPagoGPL::woocommerce_instance();
				if ( ! isset( $wc->session ) || is_null( $wc->session ) ) {
					$wc->session = new WC_Session_Handler();
					$wc->session->init();
				}
				// Chosen Method.
				if ( isset( $wc->session->chosen_payment_method ) && isset( $available_gateways[ $wc->session->chosen_payment_method ] ) ) {
					$current_gateway = $available_gateways[ $wc->session->chosen_payment_method ];
				} elseif ( isset( $available_gateways[ $default_gateway ] ) ) {
					$current_gateway = $available_gateways[ $default_gateway ];
				} else {
					$current_gateway = current( $available_gateways );
				}
			}
			if ( ! is_null( $current_gateway ) ) {
				return $current_gateway;
			} else {
				return false;
			}
		}
		/**
		 * Add fee to this payment method.
		 *
		 * @param WC_Cart $cart Cart instance.
		 */
		public function add_fee_mercadopago( $cart ) {
			$current = $this->get_current_gateway();
			if ( $current->id === $this->id && ( $this->mp_fee > 0 || $this->mp_fee_amount > 0 ) ) {
				$rate = WC_KMercadoPagoGPL_Manager::get_instance()->get_convertion_rate( $this->setting['CURRENCY'], get_woocommerce_currency() );

				$calculation_base  = $cart->subtotal_ex_tax;
				$calculation_base += $cart->shipping_total;
				$calculation_base -= $cart->get_total_discount() + $cart->discount_cart;
				$calculation_base += $cart->tax_total;
				$calculation_base += $cart->shipping_tax_total;

				$cost = $this->mp_fee_amount * $rate + $calculation_base * ( $this->mp_fee / 100 );
				$cart->add_fee( __( 'MercadoPago Fee', 'woocommere-kmercadopagogpl' ), $cost );
			}
		}

		/**
		 * Add custom html setting.
		 *
		 * @param string $key Key.
		 * @param string $data HTML Data.
		 */
		public function generate_html_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );
			$defaults  = array(
				'title'       => '',
				'type'        => 'html',
				'description' => '',
			);
			$data      = wp_parse_args( $data, $defaults );
			ob_start();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				</th>
				<td class="forminp">
					<?php
					// phpcs:ignore
					echo $data['description'];
					?>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		/**
		 * Get payment icon.
		 *
		 * @param string $icon Current icon.
		 * @param string $id Payment ID.
		 */
		public function payment_icon( $icon, $id ) {
			return $icon;
		}
	}
endif;
