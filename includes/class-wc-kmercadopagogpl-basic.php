<?php
/**
 * MercadoPago Tools GPL - Basic Payment Gateway
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

if ( ! class_exists( 'WC_KMercadoPagoGPL_Basic' ) ) :
	/**
	 * WC_KMercadoPagoGPL_Basic Gateway Class.
	 *
	 * Built the MercadoPago method.
	 */
	class WC_KMercadoPagoGPL_Basic extends WC_Payment_Gateway_MercadoPagoGPL {
		/**
		 * Instance of current class
		 *
		 * @var WC_KMercadoPagoGPL_Basic
		 */
		private static $is_load = null;

		/**
		 * Instance of WC_KMercadoPagoGPL_Manager class
		 *
		 * @var WC_KMercadoPagoGPL_Manager
		 */
		private $manager = null;


		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			$this->id           = 'mercadopagogpl-basic';
			$this->has_fields   = false;
			$this->method_title = __( 'MercadoPago', 'wc-kmp-gpl' );

			// Load the settings.
			$this->init_settings();

			$this->manager = WC_KMercadoPagoGPL_Manager::get_instance();
			$this->setting = $this->manager->get_setting();
			$this->mp_name = $this->manager->get_mp_name();

			$this->init_form_fields();
			$this->icon                                 = $this->get_option( 'mp_icon', plugins_url( 'images/mercadopago.png', plugin_dir_path( __FILE__ ) ) );
			$this->title                                = $this->get_option( 'title', 'MercadoPago' );
			$this->description                          = $this->get_option( 'description', '' );
			$this->mp_fee                               = (float) $this->get_option( 'mp_fee' );
			$this->mp_fee_amount                        = (float) $this->get_option( 'mp_fee_amount' );
			$this->mp_installments_msi                  = false;
			$this->marketplace                          = false;
			$this->mp_installments                      = (int) $this->get_option( 'mp_installments' );
			$this->enabled_only_with_me                 = 'yes' === $this->get_option( 'enabled_only_with_me' );
			$this->disable_bitcoin                      = 'yes' === $this->get_option( 'disable_bitcoin' );
			$this->disable_bank                         = 'yes' === $this->get_option( 'disable_bank' );
			$this->disable_prepaid                      = 'yes' === $this->get_option( 'disable_prepaid' );
			$this->disable_tickets                      = 'yes' === $this->get_option( 'disable_tickets' );
			$this->disable_credit_card                  = 'yes' === $this->get_option( 'disable_credit_card' );
			$this->disable_debit_card                   = 'yes' === $this->get_option( 'disable_debit_card' );
			$this->installment_paymentbutton_calculator = 'yes' === $this->get_option( 'installment_paymentbutton_calculator' );
			$this->installment_product_calculator       = 'yes' === $this->get_option( 'installment_product_calculator' );
			$this->mp_redirect                          = 'yes' === $this->get_option( 'mp_redirect' );
			$this->method                               = $this->get_option( 'method', 'redirect' );
			$this->mp_installments_msi_fee              = array();

			add_action( 'wp_enqueue_scripts', array( $this, 'hook_js' ) );

			add_action( 'woocommerce_customer_changed_subscription_to_cancelled', array( $this, 'status_refunded' ), 10, 1 );
			add_action( 'woocommerce_order_status_refunded', array( $this, 'status_refunded' ), 10, 1 );
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'status_refunded' ), 10, 1 );

			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			if ( $this->mp_fee > 0.0 && $this->mp_fee_amount > 0.0 ) {
				// translators: %1$s%: % of fee amount, %2$s%: Fixed fee amount.
				$this->description .= '<br />' . sprintf( __( 'This payment method has a <b>%1$s%% + %2$s of fee</b>.', 'wc-kmp-gpl' ), $this->mp_fee, wc_price( $this->mp_fee_amount ) );
			} elseif ( $this->mp_fee > 0.0 && $this->mp_fee_amount <= 0.0 ) {
				// translators: %1$s%: % of fee amount.
				$this->description .= '<br />' . sprintf( __( 'This payment method has a <b>%s% of fee</b>.', 'wc-kmp-gpl' ), $this->mp_fee );
			} elseif ( $this->mp_fee <= 0.0 && $this->mp_fee_amount > 0.0 ) {
				// translators: %2$s%: fee amount.
				$this->description .= '<br />' . sprintf( __( 'This payment method has a <b>%s of fee</b>.', 'wc-kmp-gpl' ), wc_price( $this->mp_fee_amount ) );
			}
		}

		/**
		 * Get cucrrent instance
		 *
		 * @return WC_KMercadoPagoGPL_Basic
		 */
		public static function get_instance() {
			if ( is_null( self::$is_load ) ) {
				self::$is_load = new WC_KMercadoPagoGPL_Basic();
			}
			return self::$is_load;
		}

		/**
		 * Determine if MercadoPago Basic is available
		 *
		 * @return bool
		 */
		public function is_available() {
			// Test if is valid for use.
			$available = ( 'yes' === $this->settings['enabled'] ) &&
					WC_KMercadoPagoGPL_Manager::get_instance()->is_available();
			self::debug( 'Basic: is_available: ' . self::pL( $available, true ) );
			return $available;
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 *
		 * @return void
		 */
		public function init_form_fields() {
			if ( ! $this->setting ) {
				return;
			}
			$currency_org      = get_woocommerce_currency();
			$currency_dst      = $this->setting['CURRENCY'];
			$this->form_fields = include 'data-settings-mercadopagogpl-basic.php';
			if ( ! $this->setting['ACCEPT_DIGITAL_CURRENCY'] ) {
				unset( $this->form_fields['disable_bitcoin'] );
			}
			if ( ! isset( $this->setting['INSTALLMENT_FEE'] ) ) {
				unset( $this->form_fields['mp_installments_msi'] );
				foreach ( array( 3, 6, 9, 12, 18 ) as $i ) {
					unset( $this->form_fields[ 'mp_installments_msi_' . $i ] );
				}
			} else {
				foreach ( array( 3, 6, 9, 12, 18 ) as $i ) {
					if ( ! in_array( $i, $this->setting['INSTALLMENT_FEE'], true ) ) {
						unset( $this->form_fields[ 'mp_installments_msi_' . $i ] );
					}
				}
			}
		}

		/**
		 * Determine if MercadoPago Basic is available
		 *
		 * @param int $order_id Order ID refunded.
		 */
		public function status_refunded( $order_id ) {
			self::debug( 'status_refunded: ' . self::pL( $order_id, true ) );
		}
		/**
		 * Generate the payment arguments.
		 *
		 * @param  object $order Order data.
		 *
		 * @return array         Payment arguments.
		 */
		public function get_payment_args( $order ) {
			$title                   = '';
			$first                   = true;
			$img_url                 = false;
			$token                   = false;
			$last_author             = false;
			$m_token                 = false;
			$marketplace_cost        = 0;
			$marketplace_fee_percent = false;
			foreach ( $order->get_items() as $key => $item ) {
				if ( ! $first ) {
					$title .= ' - ';
				}
				if ( ! $img_url ) {
					$img = wp_get_attachment_image_src( get_post_thumbnail_id( $item['product_id'] ), 'single-post-thumbnail' );
					if ( $img && isset( $img[0] ) && strlen( $img[0] ) > 1 ) {
						$img_url = $img[0];
					}
				}
				$title .= $item['qty'] . ' U. de ';
				if ( isset( $item['variation_id'] ) && (int) $item['variation_id'] > 0 ) {
					$variation            = class_exists( 'WC_Product_Variation' ) ? new WC_Product_Variation( (int) $item['variation_id'] ) : wc_get_product( (int) $item['variation_id'] );
					$formatted_attributes = function_exists( 'wc_get_formatted_variation' ) ? wc_get_formatted_variation( $variation, true ) : $variation->get_formatted_variation_attributes( true );
					$title               .= $variation->get_title() . ', ' . $formatted_attributes;
				} else {
					$title .= $item['name'];
				}
				$first = false;
			}
			if ( ! $token ) {
				$token = WC_KMercadoPagoGPL_Manager::get_client_credentials();
			}
			$order_total     = method_exists( $order, 'get_total' ) ? $order->get_total() : $order->order_total;
			$marketplace_fee = 0;
			$order_shipping  = method_exists( $order, 'get_shipping_total' ) ? $order->get_shipping_total() : $order->order_shipping;
			$price           = $order_total;
			$rate            = WC_KMercadoPagoGPL_Manager::get_instance()->get_convertion_rate( get_woocommerce_currency(), $this->setting['CURRENCY'] );
			$order_shipping  = $order_shipping * $rate;
			$order_id        = $order->get_id();
			WC_KMercadoPagoGPL_Manager::set_metadata(
				$order_id,
				'currency_convertion_rate',
				'' . $rate
			);
			$fee = 0.0;
			WC_KMercadoPagoGPL_Manager::set_metadata(
				$order_id,
				'payment_id',
				'' . $this->id
			);
			$marketplace_fee = 0;
			$installments    = false;
			$total_price     = round( $price * $rate + $fee, 2 );
			$unit_price      = round( $total_price - $order_shipping, 2 );
			if ( 'INTEGER' === $this->setting['CURRENCY_TYPE'] ) {
				$order_shipping  = (int) $order_shipping;
				$unit_price      = (int) $unit_price;
				$total_price     = (int) $total_price;
				$marketplace_fee = (int) $marketplace_fee;
			}
			WC_KMercadoPagoGPL_Manager::set_metadata(
				$order_id,
				'expected_total_order',
				'' . $total_price
			);
			$args     = array(
				'back_urls'          => array(
					'success' => str_replace( '&#038;', '&', str_replace( '&amp;', '&', $this->get_return_url( $order ) ) ),
					'failure' => str_replace( '&#038;', '&', str_replace( '&amp;', '&', $order->get_cancel_order_url() ) ),
					'pending' => str_replace( '&#038;', '&', str_replace( '&amp;', '&', $this->get_return_url( $order ) ) ),
				),
				'notification_url'   => get_home_url(),
				'payer'              => array(
					'name'    => method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name,
					'surname' => method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name,
					'email'   => method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email,
				),
				'external_reference' => WC_KMercadoPagoGPL_Manager::get_instance()->get_invoice_prefix() . ( $order->get_id() ),
				'items'              => array(
					array(
						'quantity'    => 1,
						'unit_price'  => $unit_price,
						'currency_id' => $this->setting['CURRENCY'],
					),
				),
				'shipments'          => array(
					'mode'             => 'not_specified',
					'cost'             => $order_shipping,
					'free_shipping'    => $order_shipping < 0.01,
					'receiver_address' => array(
						'zip_code'    => method_exists( $order, 'get_shipping_postcode' ) ? $order->get_shipping_postcode() : $order->shipping_postcode,
						'street_name' => method_exists( $order, 'get_shipping_address_1' ) ? $order->get_shipping_address_1() : $order->shipping_postcode,
						'apartment'   => method_exists( $order, 'get_shipping_address_2' ) ? $order->get_shipping_address_2() : $order->shipping_postcode,
					),
				),
			);
			$vat_type = $order->get_meta( '_billing_kmercadopagogpl_vat_type' );
			$vat      = preg_replace( '/[^0-9]/', '', $order->get_meta( '_billing_kmercadopagogpl_vat' ) );
			if ( 'BR' === $this->setting['ISO'] ) {
				$postcode = str_replace( array( '-', '.', ' ' ), '', method_exists( $order, 'get_billing_postcode' ) ? $order->get_billing_postcode() : $order->billing_postcode );
				$address1 = method_exists( $order, 'get_billing_address_1' ) ? $order->get_billing_address_1() : $order->billing_address_1;
				$doc_type = '';
				$dni      = '';
				$postcode = '';
				$sn       = 'SN';
				if ( method_exists( $order, 'get_meta' ) ) {
					$sn          = $order->get_meta( '_billing_number' );
					$person_type = intval( $order->get_meta( '_billing_persontype' ) );
					if ( $person_type > 0 ) {
						if ( 1 === (int) $person_type ) {
							$doc_type = 'CPF';
							$dni      = str_replace( array( '-', '.' ), '', $order->get_meta( '_billing_cpf' ) );
						} else {
							$doc_type = 'CNPJ';
							$dni      = str_replace( array( '-', '.' ), '', $order->get_meta( '_billing_cnpj' ) );
						}
					}
				} else {
					$sn = isset( $order->billing_number ) ? $order->billing_number : 'SN';
					if ( isset( $order->billing_persontype ) ) {
						$person_type = intval( $order->billing_persontype );
						if ( $person_type > 0 ) {
							if ( 1 === (int) $person_type ) {
								$doc_type = 'CPF';
								$dni      = str_replace( array( '-', '.' ), '', $order->billing_cpf );
							} else {
								$doc_type = 'CNPJ';
								$dni      = str_replace( array( '-', '.' ), '', $order->billing_cnpj );

							}
						}
					}
				}
				if ( strlen( $sn ) < 1 ) {
					$sn = 'SN';
				}
				if ( strlen( $doc_type ) < 1 ) {
					$doc_type = $vat_type ? $vat_type : $doc_type;
				}
				if ( strlen( $dni ) < 1 ) {
					$dni = $vat ? $vat : $dni;
				}
				$args['payer']['identification'] = array(
					'type'   => $doc_type,
					'number' => $dni,
				);
				$args['payer']['address']        = array(
					'zip_code'      => $postcode,
					'street_name'   => $address1,
					'street_number' => $sn,
				);
			} elseif ( $vat_type && $vat ) {
				$args['payer']['identification'] = array(
					'type'   => $vat_type,
					'number' => $vat,
				);
			}
			$args['payment_methods']['installments']         = max( 1, min( 48, $this->mp_installments ) );
			$args['payment_methods']['default_installments'] = 1;
			if ( $this->mp_redirect ) {
				$args['auto_return'] = 'approved';
			}
			if ( $this->disable_bank ) {
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'bank_transfer',
				);
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'atm',
				);
			}

			if ( $this->disable_prepaid ) {
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'prepaid_card',
				);
			}

			if ( $this->disable_tickets ) {
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'ticket',
				);
			}

			if ( $this->disable_bitcoin && $this->setting['ACCEPT_DIGITAL_CURRENCY'] ) {
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'digital_currency',
				);
			}

			if ( $this->disable_credit_card ) {
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'credit_card',
				);
			}

			if ( $this->disable_debit_card ) {
				$args['payment_methods']['excluded_payment_types'][] = array(
					'id' => 'debit_card',
				);
			}
			// translators: %s: order ID.
			$args['additional_info'] = sprintf( __( 'Order %s', 'wc-kmp-gpl' ), wp_strip_all_tags( $order->get_order_number() ) );
			if ( abs( $fee ) > 0.01 ) {
				// translators: %s: Fee Amount.
				$args['additional_info'] .= ' - ' . sprintf( __( 'Fee: %s', 'wc-kmp-gpl' ), wp_strip_all_tags( self::showPrice( round( $fee, 2 ) ) ) );
			}
			$args['additional_info']  .= ' => ' . $title;
			$args['items'][0]['title'] = substr( $args['additional_info'], 0, 115 );
			if ( strlen( $args['items'][0]['title'] ) < strlen( $args['additional_info'] ) ) {
				$args['items'][0]['title'] .= ' |HAY MAS|';
			}
			if ( $img_url ) {
				$args['items'][0]['picture_url'] = $img_url;
			}
			$args = apply_filters( 'woocommerce_kmercadopagogpl_args', $args, $order );

			return array(
				'token' => $token,
				'body'  => $args,
			);
		}

		/**
		 * Get max installment available
		 **/
		public function get_max_installment() {
			return $this->mp_installments;
		}

		/**
		 * Generate the MercadoPago payment url.
		 *
		 * @param  WC_Order $order Order Object.
		 *
		 * @return string        MercadoPago payment url.
		 */
		protected function get_mercadopago_url( $order ) {
			$id_card  = false;
			$id_payer = false;
			$result   = $this->get_payment_args( $order );
			if ( ! $result ) {
				$wc = WC_KMercadoPagoGPL::woocommerce_instance();
				if ( ! isset( $wc->session ) || is_null( $wc->session ) ) {
					$wc->session = new WC_Session_Handler();
					$wc->session->init();
				}
				$wc->session->set( 'woocommere-kmercadopagogpl-alert', __( 'The payment could not be processed, please try again later or use another payment method.', 'woocommere-kmercadopagogpl' ) );
				return $order->get_cancel_order_url();
			}
			WC_KMercadoPagoGPL_Manager::set_metadata(
				$order->get_order_number(),
				'mp_access_token',
				$result['token']
			);
			ini_set( 'precision', 14 );
			ini_set( 'serialize_precision', 14 );
			$args = wp_json_encode( $result['body'] );

			$url = WC_KMercadoPagoGPL_Manager::get_api_url() . '/checkout/preferences?access_token=' . $result['token'];
			$wc  = WC_KMercadoPagoGPL::woocommerce_instance();
			if ( ! isset( $wc->session ) || is_null( $wc->session ) ) {
				$wc->session = new WC_Session_Handler();
				$wc->session->init();
			}
			$params = array(
				'body'      => $args,
				'sslverify' => false,
				'timeout'   => 60,
				'headers'   => array(
					'Accept'            => 'application/json',
					'Content-Type'      => 'application/json;charset=UTF-8',
					'X-integrator-id'   => 'dev_ea5f644448e411ec8c840242ac130004',
					'X-meli-session-id' => $wc->session->get( 'woocommere-kmercadopagogpl-device-id' ),
				),
			);

			self::debug( 'Generando boton de pago: ' . $order->get_order_number() . ': ' . self::pL( $params, true ) );

			$response = wp_remote_post( $url, $params );

			if ( ! is_wp_error( $response ) && 201 === (int) $response['response']['code'] && ( 0 === strcmp( $response['response']['message'], 'Created' ) ) ) {
				$checkout_info = json_decode( $response['body'], true );
				$order_id      = $order->get_id();
				WC_KMercadoPagoGPL_Manager::set_metadata(
					$order_id,
					'mp_preferences',
					$checkout_info
				);
				wp_clear_scheduled_hook( 'do_kmercadopagogpl_hourly_check', array( (int) $order_id ) );
				wp_schedule_event( time() + WC_KMercadoPagoGPL_Manager::get_instance()->get_cancel_in() * 60, 'twicedaily', 'do_kmercadopagogpl_hourly_check', array( (int) $order_id ) );
				WC_KMercadoPagoGPL_Manager::set_metadata(
					$order_id,
					'first_hourly_check',
					time()
				);
				return esc_url( $checkout_info['init_point'] );
			} else {
				self::debug( 'Error generando boton de pago: ' . $url . ':' . self::pL( $params, true ) . self::pL( $response, true ) );
			}
			$wc = WC_KMercadoPagoGPL::woocommerce_instance();
			if ( ! isset( $wc->session ) || is_null( $wc->session ) ) {
				$wc->session = new WC_Session_Handler();
				$wc->session->init();
			}
			$wc->session->set( 'woocommere-kmercadopagogpl-alert', __( 'The payment could not be processed, please try again later or use another payment method.', 'woocommere-kmercadopagogpl' ) );
			return $order->get_cancel_order_url();
		}

		/**
		 * Generate the form.
		 *
		 * @param int $order_id Order ID.
		 */
		public function receipt_page( $order_id ) {
			$order = new WC_Order( $order_id );

			if ( WC_KMercadoPagoGPL_Manager::get_instance()->get_on_hold() ) {
				$order->update_status( 'on-hold', __( 'MercadoPago: Wait payment...', 'wc-kmp-gpl' ) );
			}

			$url = $this->get_mercadopago_url( $order );

			if ( $url ) {
				// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				echo '<script type="text/javascript" src="https://secure.mlstatic.com/mptools/render.js"></script>';
				echo '<script type="text/javascript">(function() { $MPC.openCheckout({ url: "' . esc_url( $url ) . '", mode: "modal" }); })();</script>';
				echo '<p>' . esc_html( __( 'Thank you for your order, please click the button below to pay with MercadoPago.', 'wc-kmp-gpl' ) ) . '</p>';
				echo '<a id="submit-payment" href="' . esc_url( $url ) . '" name="MP-Checkout" class="button alt" mp-mode="modal">' . esc_html( __( 'Pay on MercadoPago', 'wc-kmp-gpl' ) ) . '</a> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . esc_html( __( 'Cancel order &amp; restore cart', 'wc-kmp-gpl' ) ) . '</a>';
			} else {
				echo '<p>' . esc_html( __( 'There was a problem with MercadoPago, try again later or contact our team.', 'wc-kmp-gpl' ) ) . '</p>';
				echo '<a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . esc_html( __( 'Go to cart', 'wc-kmp-gpl' ) ) . '</a>';
			}
		}

		/**
		 * Show Payment Calculator
		 *
		 * @return void.
		 */
		public function payment_fields() {
			parent::payment_fields();
			if ( ! WC_KMercadoPagoGPL_Manager::get_instance()->get_public_key() || ! $this->installment_paymentbutton_calculator ) {
				return;
			}
			$woocommerce = WC_KMercadoPagoGPL::woocommerce_instance();
			$total       = $woocommerce->cart->total;
			$this->showCalculatorInstallment( $total, true );
		}

		/**
		 * Load JS for MercadoPago.
		 *
		 * @return void.
		 */
		public function hook_js() {
			wp_enqueue_script( 'wc-kmercadopagogpl-js', plugins_url( 'mercadopago_script.js', WC_KMercadoPagoGPL::PATH ), array( 'jquery' ), WC_KMercadoPagoGPL::VERSION, true );
			wp_localize_script(
				'wc-kmercadopagogpl-js',
				'wc_kmercadopagogpl_context',
				array(
					'token'           => wp_create_nonce( 'kmercadopagogpl_token' ),
					'ajax_url'        => WC_AJAX::get_endpoint( 'wc_kmercadopagogpl_generate_cart' ),
					'home_url'        => home_url(),
					'publickey'       => WC_KMercadoPagoGPL_Manager::get_instance()->get_public_key(),
					'max_installment' => (int) $this->get_max_installment(),
					'messages'        => array(
						'cc_invalid'                => __( 'Invalid Credit Card Number', 'wc-kmp-gpl' ),
						'installment_error'         => __( 'Error on MercadoPago', 'wc-kmp-gpl' ),
						'server_error'              => __( 'Internal server error', 'wc-kmp-gpl' ),
						'server_loading'            => __( 'Loading...', 'wc-kmp-gpl' ),
						'mercadopago_not_installed' => __( 'Invalid setting', 'wc-kmp-gpl' ),
					),
				)
			);
		}
		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id Order ID.
		 *
		 * @return array           Redirect.
		 */
		public function process_payment( $order_id ) {
			$order = new WC_Order( $order_id );
			if ( 'redirect' === $this->method ) {
				if ( WC_KMercadoPagoGPL_Manager::get_instance()->get_on_hold() ) {
					$order->update_status( 'on-hold', __( 'MercadoPago: Wait payment.', 'wc-kmp-gpl' ) );
				}
				return array(
					'result'   => 'success',
					'redirect' => $this->get_mercadopago_url( $order ),
				);
			} else {
				if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
					return array(
						'result'   => 'success',
						'redirect' => $order->get_checkout_payment_url( true ),
					);
				} else {
					return array(
						'result'   => 'success',
						'redirect' => add_query_arg( 'order', $order->get_id(), add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) ),
					);
				}
			}
		}

		/**
		 * Output for the order received page.
		 *
		 * @param float $price Current Price.
		 * @param bool  $echo Dump result or return.
		 * @return string
		 */
		public function showCalculatorInstallment( $price, $echo = false ) {
			$rate = WC_KMercadoPagoGPL_Manager::get_instance()->get_convertion_rate( get_woocommerce_currency(), $this->setting['CURRENCY'] );
			if ( ! $echo ) {
				ob_start();
			}
			?>
			<script>
				var product_price_mpgpl = <?php echo (float) $price; ?>;
			</script>
			<div class="row">
				<div class="col-md-12"><b><?php echo esc_html( __( 'Enter the first six digits of your credit card to calculate the fees:', 'wc-kmp-gpl' ) ); ?></b></div><br />
				<div class="col-md-12">
					<input type="text" class="input-text text" size="4" pattern="[0-9]*" inputmode="numeric" style="width: 100px;padding: 5px;" maxlength="6" id="inputSixTDCMPGPL"  />
					<button class="button alt" style="float:none;" id="btn_mpgpl_calc_instalments" type="button">
						<?php echo esc_html( __( 'Calculate', 'wc-kmp-gpl' ) ); ?>
					</button>
				</div>
				<br /><br />
				<div class="col-md-12" id="result_installments_mpgpl">
				</div>
			</div>
			<?php
			if ( ! $echo ) {
				$out = ob_get_contents();
				ob_end_clean();
				return $out;
			}
			return '';
		}

		/**
		 * Output for the installment calculator in product.
		 *
		 * @return string
		 */
		public function process_installment_product() {
			$product = WC_KMercadoPagoGPL::woocommerce_product();
			if ( ! WC_KMercadoPagoGPL_Manager::get_instance()->get_public_key() || ( ! $this->installment_product_calculator && ! $this->mp_installments_msi ) ) {
				return;
			}
			$price = 0;
			if ( function_exists( 'wc_get_price_to_display' ) ) {
				$price = wc_get_price_to_display( $product );
			} else {
				$display_price         = $product->get_display_price();
				$display_regular_price = $product->get_display_price( $product->get_regular_price() );
				if ( $display_regular_price > 0 ) {
					$price = $display_regular_price;
				} else {
					$price = $display_price;
				}
			}
			if ( (float) $price < 0.01 ) {
				return;
			}
			echo '<br /><br />';
			$this->showCalculatorInstallment( $price, true );
		}

		/**
		 * Output for the price with format
		 *
		 * @param float $price Float value to format.
		 * @return string
		 */
		private static function showPrice( $price ) {
			return number_format( (float) $price, 2, ',', '.' ) . ' ' . self::get_instance()->setting['CURRENCY'];
		}

		/**
		 * Dump var
		 *
		 * @param mixed $data Var to dump.
		 * @param bool  $return_log Return or Dump.
		 * @return string
		 */
		public static function pL( &$data, $return_log = false ) {
			return WC_KMercadoPagoGPL_Manager::pL( $data, $return_log );
		}

		/**
		 * Dump message in log
		 *
		 * @param string $message Message.
		 * @param mixed  $data Var to dump.
		 * @return void
		 */
		public static function debug( $message, $data = false ) {
			WC_KMercadoPagoGPL_Manager::debug( $message, $data );
		}
	}
endif;
