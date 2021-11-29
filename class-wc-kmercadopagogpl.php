<?php
/**
 * Plugin Name: Tools for MercadoPago and WooCommerce
 * Plugin URI: https://github.com/kijamve/wc-kmp-gpl
 * Description: Tools for MercadoPago and WooCommerce by Kijam
 * Author: Kijam López
 * Author URI: https://kijam.com/
 * Version: 1.0.0
 * License: GPLv2
 * Text Domain: wc-kmp-gpl
 * Domain Path: /languages/
 *
 * @author    Kijam.com <info@kijam.com>
 * @copyright 2021 Kijam.com
 * @license   GPLv2
 * @package MercadoPago Tools GPL
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_KMercadoPagoGPL' ) ) :
	/**
	 * WooCommerce kmercadopagogpl main class.
	 */
	class WC_KMercadoPagoGPL {

		/**
		* Plugin version.
		*
		* @var string
		*/
		const VERSION = '1.0.0';

		/**
		* Plugin Path.
		*
		* @var string
		*/
		const PATH = __FILE__;

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			// Checks with WooCommerce is installed.
			if ( class_exists( 'WC_Payment_Gateway' ) ) {
				$path = dirname( __FILE__ );
				include_once $path . '/includes/class-wc-payment-gateway-mercadopagogpl.php';
				include_once $path . '/includes/class-wc-kmercadopagogpl-manager.php';
				include_once $path . '/includes/class-wc-kmercadopagogpl-basic.php';
				add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
				add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Add the gateway to WooCommerce.
		 *
		 * @param   array $methods WooCommerce payment methods.
		 *
		 * @return  array         Payment methods with MercadoPago.
		 */
		public function add_gateway( $methods ) {
			if ( version_compare( self::woocommerce_instance()->version, '2.3.0', '>=' ) ) {
				$methods[] = WC_KMercadoPagoGPL_Basic::get_instance();
			} else {
				$methods[] = 'WC_KMercadoPagoGPL_Basic';
			}
			return $methods;
		}

		/**
		 * Add a new integration to WooCommerce.
		 *
		 * @param   array $integrations All WooCommerce integrations.
		 *
		 * @return  array         Integrations with MercadoPago.
		 */
		public function add_integration( $integrations ) {
			$integrations[] = 'WC_KMercadoPagoGPL_Manager';
			return $integrations;
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return  void
		 */
		public function woocommerce_missing_notice() {
			echo '<div class="error"><p>' . esc_html( __( 'WooCommerce MercadoPago Tools GPL depends on the last version of WooCommerce to work!', 'wc-kmp-gpl' ) ) . '</p></div>';
		}

		/**
		 * Backwards compatibility
		 *
		 * @return object Returns the main instance of WooCommerce class.
		 */
		public static function woocommerce_instance() {
			if ( function_exists( 'WC' ) ) {
				return WC();
			} else {
				global $woocommerce;
				return $woocommerce;
			}
		}

		/**
		 * Backwards compatibility
		 *
		 * @return object Returns the main instance of Database class.
		 */
		public static function woocommerce_wpdb() {
			global $wpdb;
			return $wpdb;
		}

		/**
		 * Backwards compatibility
		 *
		 * @return object Returns the main instance of WC_Product class.
		 */
		public static function woocommerce_product() {
			global $product;
			return $product;
		}
		/**
		 * Backwards compatibility
		 *
		 * @return object Returns the main instance of WC_Order class.
		 */
		public static function woocommerce_theorder() {
			global $theorder;
			return $theorder;
		}
	}
	add_action( 'plugins_loaded', array( 'WC_KMercadoPagoGPL', 'get_instance' ), 0 );

	/**
	 * Add a links to Plugin in WordPress.
	 *
	 * @param   array $links All Plugin links.
	 *
	 * @return  array Plugin links with MercadoPago.
	 */
	function kmercadopagogpl_add_action_links( $links ) {
		$mylinks = array(
			'<a style="font-weight: bold;color: red" href="' . admin_url( 'admin.php?page=wc-settings&tab=integration&section=kmercadopagogpl-manager' ) . '">Conf. MercadoPago</a>',
		);
		return array_merge( $links, $mylinks );
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kmercadopagogpl_add_action_links' );

	$mp_gpl_locale = apply_filters( 'plugin_locale', get_locale(), 'wc-kmp-gpl' );
	load_textdomain( 'wc-kmp-gpl', trailingslashit( WP_LANG_DIR ) . 'wc-kmp-gpl/wc-kmp-gpl-' . $mp_gpl_locale . '.mo' );
	load_plugin_textdomain( 'wc-kmp-gpl', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	include_once dirname( __FILE__ ) . '/includes/functions.php';
endif;
