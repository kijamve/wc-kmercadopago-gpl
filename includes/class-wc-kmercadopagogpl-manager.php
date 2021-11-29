<?php
/**
 * MercadoPago Tools GPL - Manager
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

if ( ! class_exists( 'WC_KMercadoPagoGPL_Manager' ) ) :
	/**
	 * WC_KMercadoPagoGPL_Manager Gateway Class.
	 *
	 * Manager Class of MercadoPago.
	 */
	class WC_KMercadoPagoGPL_Manager extends WC_Integration {
		/**
		 * Endpoint of MercadoPago
		 *
		 * @var string
		 */
		private static $api_url = 'https://api.mercadopago.com';

		/**
		 * Cache
		 *
		 * @var array
		 */
		private static $cache_metadata = array();

		/**
		 * Instance of current class
		 *
		 * @var WC_KMercadoPagoGPL_Manager
		 */
		private static $is_load = null;

		/**
		 * Access Token of MercadoPago
		 *
		 * @var string
		 */
		private static $token = null;

		/**
		 * Public Key of MercadoPago
		 *
		 * @var string
		 */
		private static $publickey = null;

		/**
		 * Currency Rate
		 *
		 * @var float
		 */
		private static $currency_rate = null;

		/**
		 * Check if log is active
		 *
		 * @var bool
		 */
		private static $show_debug = false;

		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {
			$this->id           = 'kmercadopagogpl-manager';
			$this->has_fields   = false;
			$this->method_title = __( 'MercadoPago Tools GPL', 'wc-kmp-gpl' );

			self::check_database();

			$this->mp_countries = include 'data-mp-countries.php';
			$base_location      = wc_get_base_location();
			switch ( strtolower( $base_location['country'] ) ) {
				case 'ar':
					$this->mp_name = 'MLA';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 'mx':
					$this->mp_name = 'MLM';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 'br':
					$this->mp_name = 'MLB';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 've':
					$this->mp_name = 'MLV';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 'co':
					$this->mp_name = 'MCO';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 'cl':
					$this->mp_name = 'MLC';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 'pe':
					$this->mp_name = 'MPE';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				case 'uy':
					$this->mp_name = 'MLU';
					$this->setting = $this->mp_countries[ $this->mp_name ];
					break;
				default:
					$this->mp_name = null;
					$this->setting = null;
					break;
			}

			$this->test_user       = json_decode( (string) get_option( $this->id . 'test_user', 'false' ), true );
			$this->test_user_retry = (int) get_option( $this->id . 'test_user_retry', '0' );

			// Load the settings.
			$this->init_settings();
			// Load the form fields.
			$this->init_form_fields();

			$this->title             = $this->get_option( 'title', 'MercadoPago' );
			$this->description       = $this->get_option( 'description', '' );
			$this->convertion_option = $this->get_option( 'convertion_option', 'off' );
			$this->convertion_rate   = json_decode( (string) get_option( $this->id . 'convertion_rate', 'false' ), true );
			$this->invoice_prefix    = sanitize_key( $this->get_option( 'invoice_prefix', 'wc-' ) );
			$this->mp_completed      = 'yes' === $this->get_option( 'mp_completed' );
			$this->mp_request_dni    = 'yes' === $this->get_option( 'mp_request_dni' );
			$this->mp_onhold         = 'yes' === $this->get_option( 'mp_onhold', 'no' );
			$this->cancel_in         = $this->get_option( 'cancel_in', '120' );
			$this->cancel_hold_in    = $this->get_option( 'cancel_hold_in', '72' );
			if ( empty( $this->convertion_option ) ) {
				$this->convertion_option = 'off';
			}

			self::$token         = (string) $this->get_option( 'token' );
			self::$publickey     = (string) $this->get_option( 'publickey' );
			self::$currency_rate = (string) $this->get_option( 'convertion_rate', '1.0' );
			self::$show_debug    = 'yes' === $this->get_option( 'debug' );
			self::$is_load       = $this;

			// Actions.
			add_action( 'wp_loaded', array( $this, 'woocommerce_kmercadopagogpl_check_ipn_response' ) );
			add_action( 'wp_head', array( $this, 'frontend_alerts' ) );
			add_action( 'woocommerce_kmercadopagogpl_check_ipn_response', array( $this, 'woocommerce_kmercadopagogpl_check_ipn_response' ), 10000 );
			add_action( 'valid_mercadopago_ipn_request', array( $this, 'successful_request' ) );
			add_action( 'woocommerce_kmercadopagogpl_metabox', array( $this, 'woocommerce_kmercadopagogpl_metabox' ) );
			add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitized_admin_options' ) );
			add_action( 'woocommerce_order_status_changed', array( $this, 'status_changed' ), 10, 3 );
			add_action( 'woocommerce_review_order_after_submit', array( $this, 'js_add_fee_mercadopago' ) );
			add_action( 'wp_head', array( $this, 'hook_js_head' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'hook_js' ) );

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( is_admin() && isset( $_GET['section'] ) && $this->id === $_GET['section'] ) {
				$dt1 = dirname( __FILE__ ) . '/.test.txt';
				$dt3 = dirname( __FILE__ ) . '/../logs/.test.txt';
				if ( ! class_exists( 'WP_Filesystem_Direct' ) ) {
					require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
					require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
				}
				if ( ! defined( 'FS_CHMOD_FILE' ) ) {
					define( 'FS_CHMOD_FILE', 0644 );
				}
				$fs = new WP_Filesystem_Direct( array() );
				$fs->put_contents( $dt1, 'testing' );
				$fs->put_contents( $dt3, 'testing' );
				$t1 = $fs->get_contents( $dt1 );
				$t3 = $fs->get_contents( $dt3 );
				unlink( $dt1 );
				unlink( $dt3 );
				if ( 'testing' !== $t1 ) {
					add_action( 'admin_notices', array( $this, 'directory_includes_nowrite' ) );
				}
				if ( 'testing' !== $t3 ) {
					add_action( 'admin_notices', array( $this, 'directory_logs_nowrite' ) );
				}
				if ( empty( self::$token ) ) {
					add_action( 'admin_notices', array( $this, 'client_secret_missing_message' ) );
				}
				if ( ! empty( self::$token ) && ! $this->get_mercadopago_me() ) {
					add_action( 'admin_notices', array( $this, 'client_secret_invalid_message' ) );
				}
				if ( ! $this->using_supported_currency() ) {
					add_action( 'admin_notices', array( $this, 'currency_not_supported_message' ) );
				}
			}
		}

		/**
		 * Sanitized admin options.
		 *
		 * @param array $setting Current setting.
		 * @return array
		 */
		public function sanitized_admin_options( $setting ) {
			if ( isset( $setting['invoice_prefix'] ) ) {
				$setting['invoice_prefix'] = sanitize_key( $setting['invoice_prefix'] );
			}
			return $setting;
		}

		/**
		 * Add JS to check Fee on change method payment.
		 *
		 * @return void
		 */
		public function js_add_fee_mercadopago() {
			?><script type="text/javascript">
			jQuery(document).ready(function($){
				$(document.body).on('change', 'input[name="payment_method"]', function() {
					$('body').trigger('update_checkout');
					if (typeof $fragment_refresh !== 'undefined') {
						$.ajax($fragment_refresh);
					}
				});
			});
			</script>
			<?php
		}

		/**
		 * Check if request DNI is true
		 *
		 * @return bool
		 */
		public function get_mp_request_dni() {
			return $this->mp_request_dni;
		}

		/**
		 * Check refunded order
		 *
		 * @param int $order_id Order ID.
		 * @return void
		 */
		public function status_refunded( $order_id ) {
			self::debug( 'status_refunded: ' . self::pL( $order_id, true ) );

		}

		/**
		 * Load JS for MercadoPago.
		 *
		 * @return void.
		 */
		public function hook_js_head() {
		}

		/**
		 * Check if completed order setting is true
		 *
		 * @return bool
		 */
		public function get_mp_completed() {
			return $this->mp_completed;
		}

		/**
		 * Add javascript file to front.
		 *
		 * @return void
		 */
		public function hook_js() {
			add_filter( 'script_loader_tag', array( $this, 'add_data_attribute' ), 10, 2 );
			wp_enqueue_script( 'wc-kmercadopagogpl-security-js', 'https://www.mercadopago.com/v2/security.js', array( 'jquery' ), WC_KMercadoPagoGPL::VERSION, true );
		}

		/**
		 * Display alert from MercadoPago in front-office.
		 *
		 * @return void
		 */
		public function frontend_alerts() {
			$wc = WC_KMercadoPagoGPL::woocommerce_instance();
			if ( ! isset( $wc->session ) || is_null( $wc->session ) ) {
				$wc->session = new WC_Session_Handler();
				$wc->session->init();
			}
			$device_id = $wc->session->get( 'woocommere-kmercadopagogpl-device-id' );
			if ( ! $device_id ) {
				$device_id = '';
			}
			$alert = $wc->session->get( 'woocommere-kmercadopagogpl-alert' );
			if ( $alert && ! empty( $alert ) ) {
				$wc->session->set( 'woocommere-kmercadopagogpl-alert', '' );
				?>
				<style>
					.woocommere-kmercadopagogpl-alert {
						background-color: #ff0000;
						color: #FFFFFF;
						display: block;
						line-height: 45px;
						height: 50px;
						position: relative;
						text-align: center;
						text-decoration: none;
						top: 0px;
						width: 100%;
						z-index: 100;
					}
				</style>
				<div class="woocommere-kmercadopagogpl-alert"><?php echo esc_html( $alert ); ?></div>
				<?php
			}
			?>
			<input type="hidden" id="deviceMpId" />
			<script>
				var mp_device_id = <?php echo wp_json_encode( $device_id ); ?>;
				setInterval(function() {
					if (typeof jQuery == 'undefined' || typeof window.MP_DEVICE_SESSION_ID == 'undefined') return;
					var $ = jQuery;
					var check_device_id = window.MP_DEVICE_SESSION_ID;
					if (check_device_id && check_device_id != mp_device_id) {
						mp_device_id = check_device_id;
						$.post('<?php echo esc_url( rtrim( home_url(), '/' ) ) . '/?kmercadopagogpl_set_device_id'; ?>', {
							kmercadopagogpl_device_id: mp_device_id,
							action: 'kmercadopagogpl_set_device_id',
							nonce: '<?php echo esc_html( wp_create_nonce( 'kmercadopagogpl_set_device_id' ) ); ?>'
						});
					}
				}, 1000);
			</script>
			<?php
		}

		/**
		 * Add security section page in javascript of MercadoPago.
		 *
		 * @param string $tag All attributes.
		 * @param string $handle Handle by.
		 * @return string
		 */
		public function add_data_attribute( $tag, $handle ) {
			if ( 'wc-kmercadopagogpl-security-js' !== $handle ) {
				return $tag;
			}

			if ( is_product() ) {
				return str_replace( ' src', '  output="deviceMpId" defer view="item" src', $tag );
			}

			if ( is_home() || is_front_page() ) {
				return str_replace( ' src', ' output="deviceMpId" defer view="home" src', $tag );
			}

			if ( is_category() || is_product_category() ) {
				return str_replace( ' src', ' output="deviceMpId" defer view="search" src', $tag );
			}

			if ( is_checkout() ) {
				return str_replace( ' src', ' output="deviceMpId" defer view="checkout" src', $tag );
			}

			return str_replace( ' src', ' output="deviceMpId" defer src', $tag );
		}

		/**
		 * Show Metabox in Order Admin Page.
		 *
		 * @return void
		 */
		public function woocommerce_kmercadopagogpl_metabox() {
			$theorder = WC_KMercadoPagoGPL::woocommerce_theorder();
			$order_id = method_exists( $theorder, 'get_id' ) ? $theorder->get_id() : $theorder->id;
			$vat_type = $theorder->get_meta( '_billing_kmercadopagogpl_vat_type' );
			$vat      = trim( $theorder->get_meta( '_billing_kmercadopagogpl_vat' ) );
			if ( $vat && $vat_type ) {
				?>
				<table width="70%" style="width:70%">
					<tr>
						<td><strong><?php echo esc_html( __( 'DNI', 'wc-kmp-gpl' ) ); ?>:</strong></td><td><?php echo esc_html( $vat_type . ' ' . $vat ); ?></td>
					<tr>
				</table>
				<?php
			}
			$data = $this->validate_mercadopago( 'payment', self::get_metadata( $order_id, 'mp_op_id' ), $order_id );
			if ( ! $data ) {
				$data = $this->validate_mercadopago( 'payment', self::get_metadata( $order_id, __( 'Payment Number in MercadoPago', 'wc-kmp-gpl' ) ), $order_id );
			}
			if ( ! $data ) {
				$data = $this->validate_mercadopago( 'merchant_order', self::get_metadata( $order_id, 'mp_order_id' ), $order_id );
			}
			if ( ! $data ) {
				$data = $this->validate_mercadopago( 'merchant_order', self::get_metadata( $order_id, __( 'Order Number in MercadoPago', 'wc-kmp-gpl' ) ), $order_id );
			}

			if ( $data ) {
				$this->successful_request( $data, false );
			}
			$status = self::get_metadata( $order_id, 'last_mp_status' );
			if ( ! $status || empty( $status ) ) {
				echo esc_html( __( 'This order was not processed by MercadoPago.', 'wc-kmp-gpl' ) );
				return;
			}
			?>
			<table width="70%" style="width:70%">
			<?php
			self::showLabelMetabox( $order_id, 'last_mp_status', __( 'Actual Status' ) );
			self::showLabelMetabox( $order_id, 'Amount Paid', __( 'Amount Paid', 'wc-kmp-gpl' ), true );
			self::showLabelMetabox( $order_id, 'Amount of Shipping', __( 'Amount of Shipping', 'wc-kmp-gpl' ), true );
			self::showLabelMetabox( $order_id, 'Amount of Fee', __( 'Amount of Fee', 'wc-kmp-gpl' ), true );
			self::showLabelMetabox( $order_id, 'Order Number in MercadoPago', __( 'Order Number in MercadoPago', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Payment Number in MercadoPago', __( 'Payment Number in MercadoPago', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Payer Name', __( 'Payer Name', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Payer Identification Number', __( 'Payer Identification Number', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Method of payment', __( 'Method of payment', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Credit / Debit Card', __( 'Credit / Debit Card', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Name printed on Credit / Debit Card', __( 'Name printed on Credit / Debit Card', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Payer e-mail', __( 'Payer e-mail', 'wc-kmp-gpl' ) );
			self::showLabelMetabox( $order_id, 'Payer phone', __( 'Payer phone', 'wc-kmp-gpl' ) );
			?>
			</table>
			<?php
		}

		/**
		 * Display attribute in MetaBox.
		 *
		 * @param int    $order_id Order ID.
		 * @param string $id ID of attributes.
		 * @param string $text Label of attribute.
		 * @param bool   $is_price If this attribute is a price send true.
		 * @return void
		 */
		public static function showLabelMetabox( $order_id, $id, $text, $is_price = false ) {
			$data = self::get_metadata( $order_id, $id );
			if ( false === $data || empty( $data ) ) {
				return;
			}
			?>
			<tr>
				<td><strong><?php echo esc_html( $text ); ?>:</strong></td><td><?php echo esc_html( $is_price ? self::showPrice( $data ) : $data ); ?></td>
			<tr>
			<?php
		}

		/**
		 * Return float in price format.
		 *
		 * @param float $price Float price.
		 * @return string
		 */
		private static function showPrice( $price ) {
			return number_format( (float) $price, 2, ',', '.' ) . ' ' . self::get_instance()->setting['CURRENCY'];
		}

		/**
		 * Get instance of current class.
		 *
		 * @return WC_KMercadoPagoGPL_Manager
		 */
		public static function get_instance() {
			if ( is_null( self::$is_load ) ) {
				self::$is_load = new self();
			}
			return self::$is_load;
		}

		/**
		 * Get invoice prefix
		 *
		 * @return string
		 */
		public function get_invoice_prefix() {
			return $this->invoice_prefix;
		}

		/**
		 * Get API endpoint
		 *
		 * @return string
		 */
		public static function get_api_url() {
			return self::$api_url;
		}

		/**
		 * Get if order pending change to on-hold status
		 *
		 * @return bool
		 */
		public function get_on_hold() {
			return $this->mp_onhold;
		}

		/**
		 * Get timeout for without payment orders
		 *
		 * @return int
		 */
		public function get_cancel_in() {
			return $this->cancel_in;
		}

		/**
		 * Get timeout for pending orders
		 *
		 * @return int
		 */
		public function get_cancel_hold_in() {
			return $this->cancel_hold_in;
		}

		/**
		 * Get public key
		 *
		 * @return string
		 */
		public function get_public_key() {
			return self::$publickey;
		}

		/**
		 * Get setting
		 *
		 * @return array
		 */
		public function get_setting() {
			return $this->setting;
		}

		/**
		 * Get Site ID of MercadoPago
		 *
		 * @return string
		 */
		public function get_mp_name() {
			return $this->mp_name;
		}

		/**
		 * Returns a bool that indicates if currency is amongst the supported ones.
		 *
		 * @return bool
		 */
		protected function using_supported_currency() {
			if ( ! $this->setting ) {
				return false;
			}
			return get_woocommerce_currency() === $this->setting['CURRENCY'] || 'off' !== $this->convertion_option;
		}

		/**
		 * Returns currency rate
		 *
		 * @param string $currency_org ISO Currency Origin.
		 * @param string $currency_dst ISO Currency Destination.
		 * @return float
		 */
		public function get_convertion_rate( $currency_org, $currency_dst ) {
			if ( $currency_org === $currency_dst || 'off' === $this->convertion_option ) {
				return 1.0;
			}
			if ( 'custom' === $this->convertion_option ) {
				if ( $this->setting['CURRENCY'] === $currency_dst ) {
					return self::$currency_rate;
				} else {
					return 1.0 / self::$currency_rate;
				}
			}
			return 1.0;
		}

		/**
		 * Check if MercadoPago is available.
		 *
		 * @return bool
		 */
		public function is_available() {
			// Test if is valid for use.
			$available = ( 'yes' === $this->settings['enabled'] ) &&
					! empty( self::$token ) &&
					$this->using_supported_currency() &&
					$this->get_mercadopago_me();

			self::debug( 'Manager: is_available - v: ' . self::pL( $available, true ) );

			return $available;
		}

		/**
		 * Set metadata of MercadoPago.
		 *
		 * @param int    $order_id Order ID.
		 * @param string $key Key of Meta Data.
		 * @param mixed  $value Data.
		 * @return bool
		 */
		public static function set_metadata( $order_id, $key, $value ) {
			$wpdb = WC_KMercadoPagoGPL::woocommerce_wpdb();
			self::$cache_metadata[ $order_id . '-' . $key ] = $value;
			if ( ! property_exists( $wpdb, 'woo_kmercadopagogpl' ) ) {
				$table_name = $wpdb->prefix . 'woo_kmercadopagogpl';
				$wpdb->woo_kmercadopagogpl = sanitize_key( $table_name );
			}
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT `id` FROM `{$wpdb->woo_kmercadopagogpl}` WHERE `order_id` = %d AND `key` = %s LIMIT 1", (int) $order_id, $key ) );
			if ( $exists ) {
				$result = $wpdb->update(
					$wpdb->woo_kmercadopagogpl,
					array( 'data' => wp_json_encode( $value ) ),
					array(
						'order_id' => $order_id,
						'key'      => $key,
					),
					array( '%s' ),
					array( '%d', '%s' )
				);
			} else {
				$result = $wpdb->insert(
					$wpdb->woo_kmercadopagogpl,
					array(
						'order_id' => $order_id,
						'key'      => $key,
						'data'     => wp_json_encode( $value ),
					),
					array( '%d', '%s', '%s' )
				);
			}
			self::debug( "set_metadata [order:$order_id]: [$key]=>" . self::pL( $value, true ) . ' Result: ' . self::pL( $result, true ) );
			return $result;
		}

		/**
		 * Get metadata of MercadoPago.
		 *
		 * @param int    $order_id Order ID.
		 * @param string $key Key of Meta Data.
		 * @return mixed
		 */
		public static function get_metadata( $order_id, $key ) {
			$wpdb = WC_KMercadoPagoGPL::woocommerce_wpdb();
			if ( isset( self::$cache_metadata[ $order_id . '-' . $key ] ) ) {
				return self::$cache_metadata[ $order_id . '-' . $key ];
			}
			if ( ! property_exists( $wpdb, 'woo_kmercadopagogpl' ) ) {
				$table_name = $wpdb->prefix . 'woo_kmercadopagogpl';
				$wpdb->woo_kmercadopagogpl = sanitize_key( $table_name );
			}
			$data       = $wpdb->get_var( $wpdb->prepare( "SELECT `data` FROM `{$wpdb->woo_kmercadopagogpl}` WHERE `order_id` = %d AND `key` = %s LIMIT 1", (int) $order_id, $key ) );
			self::debug( "get_metadata [order:$order_id]: [$key] | Result: " . self::pL( $data, true ) );
			self::$cache_metadata[ $order_id . '-' . $key ] = $data ? json_decode( $data, true ) : false;
			return self::$cache_metadata[ $order_id . '-' . $key ];
		}

		/**
		 * Create table of MercadoPago.
		 *
		 * @return void
		 */
		public static function check_database() {
			$wpdb       = WC_KMercadoPagoGPL::woocommerce_wpdb();
			$table_name = sanitize_key( $wpdb->prefix . 'woo_kmercadopagogpl' );
			if ( ! property_exists( $wpdb, 'woo_kmercadopagogpl' ) ) {
				$wpdb->woo_kmercadopagogpl = $table_name;
			}
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->woo_kmercadopagogpl}'" ) !== $table_name ) {
				$charset_collate = $wpdb->get_charset_collate();
				$sql             = "CREATE TABLE $table_name (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`order_id` bigint NOT NULL,
						`key` varchar(255) NOT NULL,
						`data` longtext NOT NULL,
						PRIMARY KEY  (`id`),
						INDEX (`order_id`),
						INDEX (`key`)
					) $charset_collate;";
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
			}
		}

		/**
		 * Gets the admin url.
		 *
		 * @return string
		 */
		protected function admin_url() {
			return admin_url( 'admin.php?page=wc-settings&tab=integration&section=kmercadopagogpl-manager' );
		}

		/**
		 * Display invalid access token
		 *
		 * @return void
		 */
		public function client_secret_invalid_message() {
			echo '<div class="error"><p><strong>' . esc_html( __( 'MercadoPago Disable', 'wc-kmp-gpl' ) ) . '</strong>: ' . esc_html( __( 'You must enter a valid Access Token.', 'wc-kmp-gpl' ) ) . ' <a href="' . esc_url( $this->admin_url() ) . '">' . esc_html( __( 'Click here to configure it!', 'wc-kmp-gpl' ) ) . '</a></p></div>';
		}

		/**
		 * Display empty access token
		 *
		 * @return void
		 */
		public function client_secret_missing_message() {
			echo '<div class="error"><p><strong>' . esc_html( __( 'MercadoPago Disable', 'wc-kmp-gpl' ) ) . '</strong>: ' . esc_html( __( 'You must enter a valid Access Token.', 'wc-kmp-gpl' ) ) . ' <a href="' . esc_url( $this->admin_url() ) . '">' . esc_html( __( 'Click here to configure it!', 'wc-kmp-gpl' ) ) . '</a></p></div>';
		}
		/**
		 * Display directory error to write
		 *
		 * @param string $name Name of Directory.
		 * @return void
		 */
		public function directory_nowrite( $name ) {
			// translators: %s: PATH of Invalid Directory.
			echo '<div class="error"><p><strong>' . esc_html( sprintf( __( 'The directory /wp-content/plugins/wc-kmp-gpl/%s of MercadoPago Tools GPL module is not writable, change it to chmod 777.', 'wc-kmp-gpl' ), $name ) ) . '</strong></p></div>';
		}

		/**
		 * Display directory log error to write
		 *
		 * @return void
		 */
		public function directory_logs_nowrite() {
			$this->directory_nowrite( 'logs' );
		}

		/**
		 * Display directory pdf error to write
		 *
		 * @return void
		 */
		public function directory_pdf_nowrite() {
			$this->directory_nowrite( 'pdf' );
		}

		/**
		 * Display directory includes error to write
		 *
		 * @return void
		 */
		public function directory_includes_nowrite() {
			$this->directory_nowrite( 'includes' );
		}

		/**
		 * Display directory invalid currency
		 *
		 * @return void
		 */
		public function currency_not_supported_message() {
			// translators: %s: ISO of invalid Corrency.
			echo '<div class="error"><p><strong>' . esc_html( __( 'MercadoPago Disable', 'wc-kmp-gpl' ) ) . '</strong>: ' . esc_html( sprintf( __( 'The currency "%s" is not supported. Please activate a conversion rate in the module configuration or use one of the following currencies (depending on your country): ARS, BRL, CLP, COP, MXN, UYU or PEN.', 'wc-kmp-gpl' ), get_woocommerce_currency() ) ) . '</p></div>';
		}

		/**
		 * Successful Payment!
		 *
		 * @param array $posted MercadoPago post data.
		 * @param bool  $redirect is redirect.
		 */
		public function successful_request( $posted, $redirect = true ) {
			if ( ! is_array( $posted ) ) {
				$posted = self::stdclass_to_array( $posted );
			}
			if ( ! class_exists( 'WC_KMP_MpMutex' ) ) {
				include_once 'class-WC_KMP_MpMutex.php';
			}
			$mutex = new WC_KMP_MpMutex( dirname( __FILE__ ) . '/.mercadopago' );
			while ( ! $mutex->lock() ) {
				sleep( .5 );
			}
			$order_key = $posted && isset( $posted['order_id'] ) ? $posted['order_id'] : false;
			if ( ! empty( $order_key ) ) {
				$order    = false;
				$order_id = (int) str_replace( $this->invoice_prefix, '', $order_key );
				self::debug( 'Buscando WC_Order: ' . self::pL( $this->invoice_prefix, true ) . ' <> ' . self::pL( $order_key, true ) . ' <> ' . self::pL( $order_id, true ) );
				$order       = new WC_Order( $order_id );
				$total_price = self::get_metadata( $order_id, 'expected_total_order' );
				if ( abs( $posted['total'] - $total_price ) > 0.05 ) {
					self::debug( "Precio NO esperado: Esperado {$total_price} - Pagado: " . self::pL( $posted, true ) );
				}
				$last_status = self::get_metadata( $order_id, 'last_mp_status' );
				if ( (int) $order_id === (int) $order->get_id() && $posted['status'] !== $last_status ) {
					self::debug( 'Estado del pago ' . $order->get_order_number() . ': ' . $posted['status'] );
					self::set_metadata(
						$order_id,
						'last_mp_status',
						$posted['status']
					);
					if ( ! empty( $posted['mp_op_id'] ) ) {
						self::set_metadata(
							$order_id,
							__( 'Payment Number in MercadoPago', 'wc-kmp-gpl' ),
							$posted['mp_op_id']
						);
						self::set_metadata(
							$order_id,
							'mp_op_id',
							$posted['mp_op_id']
						);
					}
					if ( ! empty( $posted['mp_order_id'] ) ) {
						self::set_metadata(
							$order_id,
							__( 'Order Number in MercadoPago', 'wc-kmp-gpl' ),
							$posted['mp_order_id']
						);
						self::set_metadata(
							$order_id,
							'mp_order_id',
							$posted['mp_order_id']
						);
					}
					switch ( $posted['status'] ) {
						case 'approved':
							wp_clear_scheduled_hook( 'do_kmercadopagogpl_hourly_check', array( (int) $order_id ) );
							self::set_metadata(
								$order_id,
								'Amount Paid',
								$posted['price']
							);
							self::set_metadata(
								$order_id,
								'Amount of Shipping',
								$posted['shipping']
							);
							$fee = (float) self::get_metadata( $order_id, 'total_fee' );
							if ( $fee > 0.005 ) {
								self::set_metadata(
									$order_id,
									'Amount of Fee',
									$fee
								);
							}
							if ( ! empty( $posted['payment_info']['payment_type'] ) ) {
								self::set_metadata(
									$order_id,
									'Method of payment',
									strtoupper( str_replace( '_', ' ', $posted['payment_info']['payment_type'] ) )
								);
							}
							if ( ! empty( $posted['client_name'] ) ) {
								self::set_metadata(
									$order_id,
									'Payer Name',
									$posted['client_name']
								);
							}
							if ( ! empty( $posted['identification'] ) ) {
								self::set_metadata(
									$order_id,
									'Payer Identification Number',
									$posted['identification']
								);
							}
							if ( isset( $posted['last_four_digits'] ) && false !== $posted['last_four_digits'] ) {
								self::set_metadata(
									$order_id,
									'Credit / Debit Card',
									$posted['last_four_digits']
								);
							}
							if ( isset( $posted['cardholder_name'] ) && false !== $posted['cardholder_name'] ) {
								self::set_metadata(
									$order_id,
									'Name printed on Credit / Debit Card',
									$posted['cardholder_name']
								);
							}
							if ( ! empty( $posted['payment_info']['payer']['email'] ) ) {
								self::set_metadata(
									$order_id,
									'Payer e-mail',
									$posted['payment_info']['payer']['email']
								);
							}
							if ( ! empty( $posted['payment_info']['payer']['phone'] ) ) {
								self::set_metadata(
									$order_id,
									'Payer phone',
									implode( '-', $posted['payment_info']['payer']['phone'] )
								);
							}

							$order->add_order_note( __( 'MercadoPago: Payment approved.', 'wc-kmp-gpl' ) );
							$order->payment_complete();
							if ( $this->mp_completed ) {
								self::debug( 'Estado mp_completed' );
								$order->update_status( 'completed', __( 'MercadoPago: Payment approved.', 'wc-kmp-gpl' ) );
							} else {
								self::debug( 'Estado not mp_completed' );
							}
							break;
						case 'pending':
						case 'in_process':
							if ( ! empty( $posted['mp_op_id'] ) ) {
								$order->update_status( 'on-hold', __( 'MercadoPago: The payment is in review.', 'wc-kmp-gpl' ) );
							}
							break;
						case 'rejected':
							$order->add_order_note( __( 'MercadoPago: payment rejected, user must try again.', 'wc-kmp-gpl' ) );
							$order->update_status( 'failed', __( 'MercadoPago: Payment rejected.', 'wc-kmp-gpl' ) );
							break;
						case 'refunded':
							$order->update_status( 'refunded', __( 'MercadoPago: The payment was returned to the customer.', 'wc-kmp-gpl' ) );
							do_action( 'woocommerce_order_fully_refunded_notification', $order->get_id() );
							break;
						case 'hacking':
						case 'cancelled':
							$order->update_status( 'cancelled', __( 'MercadoPago: The payment was cancelled.', 'wc-kmp-gpl' ) );
							break;
						case 'in_mediation':
							$order->add_order_note( __( 'MercadoPago: A dispute has started over the payment.', 'wc-kmp-gpl' ) );
							$order->update_status( 'on-hold', __( 'MercadoPago: A dispute has started.', 'wc-kmp-gpl' ) );
							break;
					}
				}
			}
			$mutex->unlock();
			if ( $redirect && isset( $order ) && $order && ! is_admin() ) {
				$payment_id = self::get_metadata(
					$order_id,
					'payment_id'
				);
				$url        = false;
				if ( 'mercadopagogpl-basic' === $payment_id ) {
					$url = str_replace( '&#038;', '&', str_replace( '&amp;', '&', WC_KMercadoPagoGPL_Basic::get_instance()->get_return_url( $order ) ) );
					if ( $url && strlen( $url ) > 5 ) {
						die( '<script>top.location.href = "' . esc_url( $url ) . '";</script>' );
					}
				}
			}
		}

		/**
		 * Hook for new order status
		 *
		 * @param int    $order_id Order ID.
		 * @param string $old_status Old Status.
		 * @param string $new_status New Status.
		 * @return void
		 */
		public function status_changed( $order_id, $old_status = false, $new_status = false ) {
			self::debug( 'new status: ' . self::pL( $new_status, true ) );
			if ( 'refunded' !== $new_status ) {
				return;
			}
			$order = wc_get_order( $order_id );
			if ( ! $order || ! isset( $order->order_date ) ) {
				return;
			}
			$is_refunded = (bool) self::get_metadata( $order_id, 'is_refunded' );
			if ( $is_refunded ) {
				self::debug( 'Refund error is_refunded: ' . self::pL( $is_refunded, true ) );
				return;
			}
			$all_op_id = self::get_metadata( $order_id, 'mp_op_id' );
			if ( ! $all_op_id ) {
				self::debug( 'Refund error mp_op_id: ' . self::pL( $all_op_id, true ) );
				return;
			}
			$token = self::get_metadata( $order_id, 'mp_access_token' );
			foreach ( explode( ',', $all_op_id ) as $mp_op_id ) {
				$mp_op_id = trim( $mp_op_id );
				$url      = self::get_api_url() . '/v1/payments/' . $mp_op_id . '/refunds?access_token=' . ( $token ? $token : self::get_client_credentials() );
				$response = wp_remote_request(
					$url,
					array(
						'method'    => 'POST',
						'body'      => '{}',
						'sslverify' => false,
						'timeout'   => 60,
						'headers'   => array(
							'Accept'          => 'application/json',
							'Content-Type'    => 'application/json;charset=UTF-8',
							'X-integrator-id' => 'dev_ea5f644448e411ec8c840242ac130004',
						),
					)
				);
				if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
					$data = json_decode( $response['body'], true );
					self::set_metadata( $order_id, 'is_refunded', true );
					self::debug( 'Refund response: ' . self::pL( $response, true ) );
				} else {
					self::debug( 'Refund error response: ' . self::pL( $response, true ) );
				}
			}
		}

		/**
		 * Dump var
		 *
		 * @param mixed $data Var to dump.
		 * @param bool  $return_log Return or Dump.
		 * @return string
		 */
		public static function pL( &$data, $return_log = false ) {
			if ( ! self::$is_load ) {
				return print_r( $data, $return_log );
			}
			if ( ! self::$show_debug ) {
				return '';
			}
			return print_r( $data, $return_log );
		}

		/**
		 * Dump message in log
		 *
		 * @param string $message Message.
		 * @param mixed  $data Var to dump.
		 * @return void
		 */
		public static function debug( $message, $data = false ) {
			if ( ! self::$is_load ) {
				self::get_instance();
			}
			if ( self::$show_debug && ! empty( $message ) ) {
				$path = dirname( __FILE__ ) . '/..';
				if ( ! is_dir( $path . '/logs' ) ) {
					mkdir( $path . '/logs' );
				}

				if ( ! is_dir( $path . '/logs/' . gmdate( 'Y-m' ) ) ) {
					mkdir( $path . '/logs/' . gmdate( 'Y-m' ) );
				}

				$fp = fopen( $path . '/logs/' . gmdate( 'Y-m' ) . '/log-' . gmdate( 'Y-m-d' ) . '.log', 'a' );

				fwrite( $fp, "\n----- " . gmdate( 'Y-m-d H:i:s' ) . " -----\n" );
				fwrite( $fp, $message . self::pL( $data, true ) );
				fclose( $fp );
			}
		}

		/**
		 * Get cliente token.
		 *
		 * @return mixed Sucesse return the token and error return null.
		 */
		public static function get_client_credentials() {
			if ( ! self::$is_load ) {
				self::get_instance();
			}
			if ( ! is_null( self::$token ) && ! empty( self::$token ) ) {
				return self::$token;
			}
			return null;
		}

		/**
		 * Validate order in MercadoPago.
		 *
		 * @param string $topic Type of request.
		 * @param string $mp_op_id Payment ID.
		 * @param string $order_id Order ID.
		 * @param string $is_qr Payment is by QR.
		 *
		 * @return array
		 */
		public static function validate_mercadopago( $topic, $mp_op_id, $order_id = false, $is_qr = false ) {
			if ( ! $mp_op_id && ! $order_id ) {
				return false;
			}
			$org_mp_op_id    = $mp_op_id;
			$mp_op_id_arr    = explode( ',', $mp_op_id );
			$mp_op_id        = trim( $mp_op_id_arr[0] );
			$status_act      = false;
			$paid_amount     = 0;
			$refunded_amount = 0;
			$payment_pending = false;
			$payment_refund  = false;
			$payments        = false;
			$payment_info    = false;
			$ret             = array();
			$token           = false;
			if ( $order_id ) {
				$token = self::get_metadata( $order_id, 'mp_access_token' );
			}
			$external_reference = false;
			if ( ! $is_qr && $order_id && ! empty( $order_id ) ) {
				$payments           = self::get_search_payments( self::get_instance()->invoice_prefix . $order_id, $token );
				$external_reference = self::get_instance()->invoice_prefix . $order_id;
			}
			if ( ! $payments ) {
				if ( ! $mp_op_id ) {
					return false;
				}
				$is_sub = false;
				if ( null === $topic || ! $topic || 1 > strlen( $topic ) || 'payment' === $topic ) {
					$payment_info = self::get_payment_info( $mp_op_id, $token );
					if ( ! $payment_info ) {
						return false;
					}
					$external_reference = $payment_info['external_reference'];
					if ( isset( $payment_info['metadata'] ) && isset( $payment_info['metadata']['preapproval_id'] ) && ! empty( $payment_info['metadata']['preapproval_id'] ) ) {
						$is_sub   = true;
						$payments = array( self::fixPaymentInfo( $payment_info ) );

						$ret['preapproval_id'] = $payment_info['metadata']['preapproval_id'];
						self::set_metadata( str_replace( self::get_instance()->invoice_prefix, '', $external_reference ), 'preapproval_id', $payment_info['metadata']['preapproval_id'] );
					}
				} else {
					$merchant_order_info = self::get_merchant_order( $mp_op_id, $token );
					if ( ! $merchant_order_info ) {
						return false;
					}
					$external_reference = $merchant_order_info['external_reference'];
				}
				if ( $is_qr ) {
					$external_reference = self::get_instance()->invoice_prefix . $order_id;
				}
				if ( ! $external_reference || empty( $external_reference ) || stristr( $external_reference, 'validation' ) !== false ) {
					return false;
				}
				$order_id = (int) str_replace( self::get_instance()->invoice_prefix, '', $external_reference );
				$i        = 0;
				while ( ! $is_sub && ! $is_qr && $i < 5 && ! $payments ) {
					sleep( 3 );
					++$i;
					self::debug( 'Retry search nro: ' . $i );
					$payments = self::get_search_payments( $external_reference, $token );
				}
				if ( ! $payments && $payment_info ) {
					self::debug( 'Faied retry, used topic/payment' );
					$payments = array();
					foreach ( $mp_op_id_arr as $id ) {
						$p = self::get_payment_info( $id, $token );
						if ( $p ) {
							$payments[] = self::fixPaymentInfo( $p );
						}
					}
				}
			}
			if ( ! $payments || ! count( $payments ) ) {
				return false;
			}
			$total_price = self::get_metadata( $order_id, 'expected_total_order' );
			$ids         = array();

			$total_shipping          = 0;
			$ret['identification']   = '';
			$ret['last_four_digits'] = '';
			$ret['cardholder_name']  = '';
			$ret['client_name']      = '';
			if ( count( $payments ) > 0 ) {
				foreach ( $payments as $payment ) {
					if ( in_array( $payment['status'], array( 'approved', 'authorized', 'in_process', 'pending', 'refunded' ), true ) ) {
						$paid_amount       += $payment['transaction_amount'] + $payment['shipping_cost'];
						$total_shipping    += $payment['shipping_cost'];
						$ids[]              = $payment['id'];
						$ret['currency_id'] = $payment['currency_id'];
						if ( isset( $payment['payer'] ) && isset( $payment['payer']['identification'] ) && isset( $payment['payer']['identification']['number'] ) ) {
							$ret['identification'] .= ' - ' . $payment['payer']['identification']['type'];
							$ret['identification'] .= ' ' . $payment['payer']['identification']['number'];
						} else {
							if ( isset( $payment['cardholder'] ) &&
							isset( $payment['cardholder']['identification'] ) &&
							isset( $payment['cardholder']['identification']['number'] ) &&
							! empty( $payment['cardholder']['identification']['number'] )
							) {
								$ret['identification'] .= ' - ' . $payment['cardholder']['identification']['type'];
								$ret['identification'] .= ' ' . $payment['cardholder']['identification']['number'];
							} else {
								if ( isset( $payment['card'] ) &&
								isset( $payment['card']['cardholder'] ) &&
								isset( $payment['card']['cardholder']['identification'] ) &&
								isset( $payment['card']['cardholder']['identification']['number'] ) &&
								! empty( $paymen['card']['cardholder']['identification']['number'] )
								) {
									$ret['identification'] .= ' - ' . $payment['card']['cardholder']['identification']['type'];
									$ret['identification'] .= ' ' . $payment['card']['cardholder']['identification']['number'];
								} else {
									$ret['identification'] .= ' - ' . __( 'Not Available', 'wc-kmp-gpl' );
								}
							}
						}
						if ( isset( $payment['payment_method_id'] ) && isset( $payment['last_four_digits'] ) && ! empty( $payment['last_four_digits'] ) ) {
							$ret['last_four_digits'] .= ' - ' . strtoupper( str_replace( '_', ' ', $payment['payment_method_id'] ) ) . ' ' . $payment['first_six_digits'] . '-****-' . $payment['last_four_digits'];
						} else {
							if ( isset( $payment['card'] ) && isset( $payment['card']['last_four_digits'] ) && ! empty( $payment['card']['last_four_digits'] ) ) {
								$ret['last_four_digits'] .= ' - ' . strtoupper( str_replace( '_', ' ', $payment['payment_method_id'] ) ) . ' ' . $payment['card']['first_six_digits'] . '-****-' . $payment['card']['last_four_digits'];
							}
						}
						if ( isset( $payment['cardholder'] ) && isset( $payment['cardholder']['name'] ) && ! empty( $payment['cardholder']['name'] ) ) {
							$ret['cardholder_name'] .= ' - ' . strtoupper( $payment['cardholder']['name'] );
						} else {
							if ( isset( $payment['card'] ) && isset( $payment['card']['cardholder'] ) && isset( $payment['card']['cardholder']['name'] ) && ! empty( $payment['card']['cardholder']['name'] ) ) {
								$ret['cardholder_name'] .= ' - ' . strtoupper( $payment['card']['cardholder']['name'] );
							}
						}
						if ( isset( $payment_info['payer'] ) && isset( $payment_info['payer']['first_name'] ) ) {
							$ret['client_name'] .= ' - ' . trim( $payment['payer']['first_name'] . ' ' . $payment['payer']['last_name'] );
						} else {
							$ret['client_name'] .= ' - ' . __( 'Not Available', 'wc-kmp-gpl' );
						}
					}
					if ( 'pending' === $payment['status'] || 'in_process' === $payment['status'] ) {
						$payment_pending = true;
					}
					if ( 'refunded' === $payment['status'] ) {
						$payment_refund   = true;
						$refunded_amount += $payment['transaction_amount'] + $payment['shipping_cost'];
					}
				}
			}
			$diff = $total_price - $paid_amount;
			if ( $payment_refund ) {
				$status_act      = 'refunded';
				$ret['refunded'] = $refunded_amount;
			} elseif ( ! $payment_pending && $diff < 0.005 ) {
				$status_act = 'approved';
			} elseif ( ! $payment_pending && $paid_amount < 0.001 ) {
				self::debug( 'ERROR --> paid_amount' );
				$status_act = 'rejected';
			} else {
				self::debug( 'ERROR --> paid pending --> ' . $total_price . ' <-> ' . $paid_amount );
				$status_act = 'pending';
			}
			$ret['order_id']     = (int) str_replace( self::get_instance()->invoice_prefix, '', $external_reference );
			$ret['price']        = (float) $paid_amount - $total_shipping;
			$ret['shipping']     = $total_shipping;
			$ret['mp_op_id']     = implode( ',', $ids );
			$ret['total']        = $ret['price'] + $ret['shipping'];
			$ret['wc_order_id']  = $order_id;
			$ret['status']       = $status_act;
			$ret['payment_info'] = $payments;
			self::debug( 'validateMercadoPago[' . $mp_op_id . ']->return: ' . self::pL( $ret, true ) );
			return $ret;
		}

		/**
		 * Cross compatibility by Country
		 *
		 * @param array $payment_info Payment Info.
		 *
		 * @return array
		 */
		private static function fixPaymentInfo( $payment_info ) {
			if ( isset( $payment_info['collection'] ) ) {
				$payment_info = $payment_info['collection'];
			}
			if ( isset( $payment_info['id'] ) ) {
				if ( isset( $payment_info['transaction_details'] ) ) {
					$payment_info = array_merge( $payment_info, $payment_info['transaction_details'] );
				}
				if ( ! isset( $payment_info['shipping_cost'] ) && isset( $payment_info['shipping_amount'] ) ) {
					$payment_info['shipping_cost'] = $payment_info['shipping_amount'];
				}
				if ( ! isset( $payment_info['order_id'] ) && isset( $payment_info['order'] ) && isset( $payment_info['order']['id'] ) ) {
					$payment_info['merchant_order_id'] = $payment_info['order']['id'];
					$payment_info['order_id']          = $payment_info['order']['id'];
				}
			}
			return $payment_info;
		}

		/**
		 * Get all identification type of MercadoPago
		 *
		 * @param string $token Access Token.
		 *
		 * @return array
		 */
		public static function get_identification_types( $token = false ) {
			$url      = self::$api_url . '/v1/identification_types?access_token=' . ( $token ? $token : self::get_client_credentials() );
			$cache_id = 'get_identification_types_' . md5( $url );
			$data     = self::get_metadata( '0', $cache_id );
			if ( $data ) {
				return $data;
			}
			$params   = array(
				'sslverify' => false,
				'timeout'   => 60,
				'headers'   => array(
					'Accept'          => 'application/json',
					'Content-Type'    => 'application/json;charset=UTF-8',
					'X-integrator-id' => 'dev_ea5f644448e411ec8c840242ac130004',
				),
			);
			$response = wp_remote_get( $url, $params );
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				$body = json_decode( $response['body'], true );
				self::debug( 'get_identification_types valido: ' . self::pL( $body, true ) );
				if ( $body && count( $body ) > 0 ) {
					self::set_metadata( '0', $cache_id, $body );
					return $body;
				}
				return false;
			} else {
				self::debug( 'get_identification_types invalido: ' . self::pL( $url, true ) . self::pL( $response, true ) );
			}
			return false;
		}

		/**
		 * Get all identification type of MercadoPago
		 *
		 * @param string $external_reference External Reference.
		 * @param string $token Access Token.
		 *
		 * @return array
		 */
		private static function get_search_payments( $external_reference, $token = false ) {
			$url      = self::$api_url . "/v1/payments/search?external_reference=$external_reference&access_token=" . ( $token ? $token : self::get_client_credentials() );
			$params   = array(
				'sslverify' => false,
				'timeout'   => 60,
				'headers'   => array(
					'Accept'          => 'application/json',
					'Content-Type'    => 'application/json;charset=UTF-8',
					'X-integrator-id' => 'dev_ea5f644448e411ec8c840242ac130004',
				),
			);
			$response = wp_remote_get( $url, $params );
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				$body = json_decode( $response['body'], true );
				self::debug( 'get_search_payments valido: ' . self::pL( $body, true ) );
				if ( isset( $body['results'] ) && count( $body['results'] ) > 0 ) {
					$payments = array();
					foreach ( $body['results'] as &$payment ) {
						$payments[] = self::fixPaymentInfo( $payment );
					}
					return $payments;
				}
				return false;
			} else {
				self::debug( 'get_payment_info invalido: ' . self::pL( $url, true ) . self::pL( $response, true ) );
			}
			return false;
		}

		/**
		 * Get payment info of MercadoPago
		 *
		 * @param string $mp_op_id Payment ID.
		 * @param string $token Access Token.
		 *
		 * @return array
		 */
		private static function get_payment_info( $mp_op_id, $token = false ) {
			$url      = self::$api_url . "/v1/payments/$mp_op_id/?access_token=" . ( $token ? $token : self::get_client_credentials() );
			$params   = array(
				'sslverify' => false,
				'timeout'   => 60,
				'headers'   => array(
					'Accept'          => 'application/json',
					'Content-Type'    => 'application/json;charset=UTF-8',
					'X-integrator-id' => 'dev_ea5f644448e411ec8c840242ac130004',
				),
			);
			$response = wp_remote_get( $url, $params );
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				$body = json_decode( $response['body'], true );
				self::debug( 'get_payment_info valido: ' . self::pL( $body, true ) );
				return $body;
			} else {
				self::debug( 'get_payment_info invalido: ' . self::pL( $url, true ) . self::pL( $response, true ) );
			}
			return false;
		}

		/**
		 * Get order info of MercadoPago
		 *
		 * @param string $mp_op_id Order ID.
		 * @param string $token Access Token.
		 *
		 * @return array
		 */
		private static function get_merchant_order( $mp_op_id, $token = false ) {
			$url      = self::$api_url . "/merchant_orders/$mp_op_id?access_token=" . ( $token ? $token : self::get_client_credentials() );
			$params   = array(
				'sslverify' => false,
				'timeout'   => 60,
				'headers'   => array(
					'Accept'          => 'application/json',
					'Content-Type'    => 'application/json;charset=UTF-8',
					'X-integrator-id' => 'dev_ea5f644448e411ec8c840242ac130004',
				),
			);
			$response = wp_remote_get( $url, $params );
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				$body = json_decode( $response['body'], true );
				self::debug( 'get_merchant_order valido: ' . self::pL( $body, true ) );
				return $body;
			} else {
				self::debug( 'get_merchant_order invalido: ' . self::pL( $url, true ) . self::pL( $response, true ) );
			}
			return false;
		}

		/**
		 * Get user vendor data
		 *
		 * @return array
		 */
		public function get_mercadopago_me() {
			$me = self::get_metadata( 0, 'get_mercadopago_me_' . md5( $this->id . self::get_client_credentials() ) );
			if ( $me ) {
				return $me;
			}
			$token = self::get_client_credentials();
			if ( ! $token ) {
				return false;
			}
			$params = array(
				'sslverify' => false,
				'timeout'   => 60,
				'headers'   => array(
					'Accept'          => 'application/json',
					'Content-Type'    => 'application/json;charset=UTF-8',
					'X-integrator-id' => 'dev_ea5f644448e411ec8c840242ac130004',
				),
			);

			$url = self::get_api_url() . '/users/me?access_token=' . $token;

			$response = wp_remote_get( $url, $params );
			if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
				$data = json_decode( $response['body'], true );
				self::debug( 'ME data response: ' . self::pL( $response, true ) );
				self::set_metadata( 0, 'get_mercadopago_me_' . md5( $this->id . self::get_client_credentials() ), $data );
				return $data;
			} else {
				self::debug( 'Generate ME error response: ' . self::pL( $response, true ) );
			}

			return false;
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
			$redirect_uri      = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'mercadopago-marketplace/';
			$api_secret_locale = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$this->setting['SECRET_URL'],
				$this->setting['NAME']
			);
			$panel_mp          = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				'https://www.mercadopago.com/developers/panel/',
				__( 'aquí', 'wc-kmp-gpl' )
			);
			if ( ! isset( $this->setting['PUBLICKEY_URL'] ) ) {
				$this->setting['PUBLICKEY_URL'] = '';
			}
			$api_publickey_locale = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$this->setting['PUBLICKEY_URL'],
				$this->setting['NAME']
			);
			$currency_org         = get_woocommerce_currency();
			$currency_dst         = $this->setting['CURRENCY'];
			$this->form_fields    = include 'data-settings-mercadopagogpl-manager.php';
			if ( $currency_org === $currency_dst ) {
				unset( $this->form_fields['convertion_option'] );
				unset( $this->form_fields['convertion_rate'] );
			}
		}

		/**
		 * Check API Response.
		 *
		 * @return void
		 */
		public function woocommerce_kmercadopagogpl_check_ipn_response() {
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( ! isset( $_GET['topic'] ) && ! isset( $_GET['id'] ) ) {
				return;
			}
			// phpcs:ignore WordPress.Security.NonceVerification
			$topic = strtolower( sanitize_key( $_GET['topic'] ) );
			// phpcs:ignore WordPress.Security.NonceVerification
			$id = (int) $_GET['id'];
			if ( empty( $topic ) || $id < 1 ) {
				return;
			}
			if ( ! in_array(
				$topic,
				array(
					'chargebacks',
					'merchant_order',
					'payment',
				),
				true
			) ) {
				return;
			}
			ob_clean();
			// phpcs:ignore WordPress.Security.NonceVerification
			$order_id = isset( $_GET['external_reference'] ) ? (int) str_replace( $this->invoice_prefix, '', sanitize_key( $_GET['external_reference'] ) ) : false;
			$data     = $this->validate_mercadopago( $topic, $id, $order_id );
			if ( $data ) {
				header( 'HTTP/1.1 200 OK' );
				$this->successful_request( $data );
				echo 'IPN FOUND';
				exit;
			}
		}

		/**
		 * Add HTML type in setting page
		 *
		 * @param string $key Object.
		 * @param string $data HTML to Display.
		 * @return string
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
					<?php echo wp_kses( $data['description'], wp_kses_allowed_html( 'post' ) ); ?>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}

		/**
		 * STD Class to Array
		 *
		 * @param object $object Object.
		 * @return array
		 */
		public static function stdclass_to_array( $object ) {
			if ( is_object( $object ) ) {
				$object = get_object_vars( $object );
			}
			if ( is_array( $object ) ) {
				return array_map( array( 'WC_KMercadoPagoGPL_Manager', 'stdclass_to_array' ), $object ); // recursive.
			} else {
				return $object;
			}
		}
	}
endif;
