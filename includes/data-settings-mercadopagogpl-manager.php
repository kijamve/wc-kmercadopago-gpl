<?php
/**
 * MercadoPago Tools GPL - Setting for Manager
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

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

return array(
	'other_setting'     => array(
		'title'       => __( 'Payment Methods', 'wc-kmp-gpl' ),
		'type'        => 'html',
		'description' => __( 'Configure the following methods after saving changes in this section', 'wc-kmp-gpl' ) . '<ul>
				<li><a href="admin.php?page=wc-settings&tab=checkout&section=mercadopagogpl-basic">' . __( 'Configure Basic Checkout', 'wc-kmp-gpl' ) . '</a></li>
				<li><a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">' . __( 'Configure Checkout by Tokenize', 'wc-kmp-gpl' ) . '</a> (' . __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ')</li>
				<li><a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">' . __( 'Configure Checkout by QR', 'wc-kmp-gpl' ) . '</a> (' . __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ')</li>
			</ul>',
	),
	'enabled'           => array(
		'title'   => __( 'Enable / Disable', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Activate MercadoPago', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'token'             => array(
		'title'       => __( 'Access Token', 'wc-kmp-gpl' ),
		'type'        => 'text',
		// translators: %s: URL to get the Token.
		'description' => __( 'Enter MercadoPago Access Token.', 'wc-kmp-gpl' ) . ' ' . sprintf( __( 'Look for this information in your MercadoPago account to %s.', 'wc-kmp-gpl' ), $api_publickey_locale ),
		'default'     => '',
	),
	'publickey'         => array(
		'title'       => __( 'Public Key', 'wc-kmp-gpl' ),
		'type'        => 'text',
		// translators: %s: URL to get the Token.
		'description' => __( 'Enter MercadoPago Public Key.', 'wc-kmp-gpl' ) . ' ' . sprintf( __( 'Look for this information in your MercadoPago account to %s.', 'wc-kmp-gpl' ), $api_publickey_locale ),
		'default'     => '',
	),
	'invoice_prefix'    => array(
		'title'       => __( 'Prefix for Invoices', 'wc-kmp-gpl' ),
		'type'        => 'text',
		'description' => __( 'Please enter a prefix for your invoice numbers. If you use your MercadoPago account for several stores, you must ensure that this prefix is unique since MercadoPago will not allow orders with the same invoice number.', 'wc-kmp-gpl' ),
		'default'     => 'WC-',
	),
	'cancel_in'         => array(
		'title'   => __( 'Time in minutes to cancel an order without payment', 'wc-kmp-gpl' ),
		'type'    => 'text',
		'default' => '120',
	),
	'cancel_hold_in'    => array(
		'title'   => __( 'Time in Hours to cancel an order with pending payment', 'wc-kmp-gpl' ),
		'type'    => 'text',
		'default' => '72',
	),
	'mp_request_dni'    => array(
		'title'   => __( 'Request DNI from buyers', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'It will help you minimize rejected payments due to the risk of fraud.', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'mp_onhold'         => array(
		'title'   => __( 'Leave unpaid orders in "on-hold" status', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Block the inventory when the customer is in the payment process, recommended for high traffic and low inventory.', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'mp_completed'      => array(
		'title'       => __( 'Leave orders with payment Accepted in Completed', 'wc-kmp-gpl' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable', 'wc-kmp-gpl' ),
		'default'     => 'no',
		'description' => __( 'When the payment is approved, the order in WooCommerce will not be in Processing but in Completed. ', 'wc-kmp-gpl' ),
	),
	'convertion_option' => array(
		// translators: %1$s: Origin Currency %2$s: Destination Currency.
		'title'   => sprintf( __( 'Activate conversion from %1$s to %2$s', 'wc-kmp-gpl' ), $currency_org, $currency_dst ),
		'type'    => 'select',
		'label'   => __( 'Activate the plugin by converting the amounts to MercadoPago currency', 'wc-kmp-gpl' ),
		'default' => '',
		'options' => array(
			'off'    => __( 'Disable Module', 'wc-kmp-gpl' ),
			'custom' => __( 'Use a Manual conversion rate', 'wc-kmp-gpl' ),
		),
	),
	'convertion_rate'   => array(
		// translators: %1$s: Origin Currency %2$s: Destination Currency.
		'title'   => sprintf( __( 'Convert using Manual Rate %1$s a %2$s', 'wc-kmp-gpl' ), $currency_org, $currency_dst ),
		'type'    => 'text',
		'label'   => __( 'Use a manual conversion rate', 'wc-kmp-gpl' ),
		'default' => '1.0',
	),
	'debug'             => array(
		'title'       => __( 'Debug', 'wc-kmp-gpl' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable log', 'wc-kmp-gpl' ),
		'default'     => 'no',
		// translators: %s: Path of log file.
		'description' => sprintf( __( 'To review the MercadoPago Log, download the file: %s', 'wc-kmp-gpl' ), '<code>/wp-content/plugins/wc-kmp-gpl/logs/</code>' ),
	),
);
