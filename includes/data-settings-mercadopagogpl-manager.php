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
	__( 'aquí', 'woocommerce-kmercadopagogpl' )
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
		'title'       => __( 'Payment Methods', 'woocommerce-kmercadopagogpl' ),
		'type'        => 'html',
		'description' => __( 'Configure the following methods after saving changes in this section', 'woocommerce-kmercadopagogpl' ) . '<ul>
				<li><a href="admin.php?page=wc-settings&tab=checkout&section=mercadopagogpl-basic">' . __( 'Configure Basic Checkout', 'woocommerce-kmercadopagogpl' ) . '</a></li>
				<li><a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">' . __( 'Configure Checkout by Tokenize', 'woocommerce-kmercadopagogpl' ) . '</a> (' . __( 'Only available in MercadoPago Tools Pro', 'woocommerce-kmercadopagogpl' ) . ')</li>
				<li><a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">' . __( 'Configure Checkout by QR', 'woocommerce-kmercadopagogpl' ) . '</a> (' . __( 'Only available in MercadoPago Tools Pro', 'woocommerce-kmercadopagogpl' ) . ')</li>
			</ul>',
	),
	'enabled'           => array(
		'title'   => __( 'Enable / Disable', 'woocommerce-kmercadopagogpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Activate MercadoPago', 'woocommerce-kmercadopagogpl' ),
		'default' => 'yes',
	),
	'token'             => array(
		'title'       => __( 'Access Token', 'woocommerce-kmercadopagogpl' ),
		'type'        => 'text',
		// translators: %s: URL to get the Token.
		'description' => __( 'Enter MercadoPago Access Token.', 'woocommerce-kmercadopagogpl' ) . ' ' . sprintf( __( 'Look for this information in your MercadoPago account to %s.', 'woocommerce-kmercadopagogpl' ), $api_publickey_locale ),
		'default'     => '',
	),
	'publickey'         => array(
		'title'       => __( 'Public Key', 'woocommerce-kmercadopagogpl' ),
		'type'        => 'text',
		// translators: %s: URL to get the Token.
		'description' => __( 'Enter MercadoPago Public Key.', 'woocommerce-kmercadopagogpl' ) . ' ' . sprintf( __( 'Look for this information in your MercadoPago account to %s.', 'woocommerce-kmercadopagogpl' ), $api_publickey_locale ),
		'default'     => '',
	),
	'invoice_prefix'    => array(
		'title'       => __( 'Prefix for Invoices', 'woocommerce-kmercadopagogpl' ),
		'type'        => 'text',
		'description' => __( 'Please enter a prefix for your invoice numbers. If you use your MercadoPago account for several stores, you must ensure that this prefix is unique since MercadoPago will not allow orders with the same invoice number.', 'woocommerce-kmercadopagogpl' ),
		'default'     => 'WC-',
	),
	'cancel_in'         => array(
		'title'   => __( 'Time in minutes to cancel an order without payment', 'woocommerce-kmercadopagogpl' ),
		'type'    => 'text',
		'default' => '120',
	),
	'cancel_hold_in'    => array(
		'title'   => __( 'Time in Hours to cancel an order with pending payment', 'woocommerce-kmercadopagogpl' ),
		'type'    => 'text',
		'default' => '72',
	),
	'mp_request_dni'    => array(
		'title'   => __( 'Request DNI from buyers', 'woocommerce-kmercadopagogpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'It will help you minimize rejected payments due to the risk of fraud.', 'woocommerce-kmercadopagogpl' ),
		'default' => 'yes',
	),
	'mp_onhold'         => array(
		'title'   => __( 'Leave unpaid orders in "on-hold" status', 'woocommerce-kmercadopagogpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Block the inventory when the customer is in the payment process, recommended for high traffic and low inventory.', 'woocommerce-kmercadopagogpl' ),
		'default' => 'yes',
	),
	'mp_completed'      => array(
		'title'       => __( 'Leave orders with payment Accepted in Completed', 'woocommerce-kmercadopagogpl' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable', 'woocommerce-kmercadopagogpl' ),
		'default'     => 'no',
		'description' => __( 'When the payment is approved, the order in WooCommerce will not be in Processing but in Completed. ', 'woocommerce-kmercadopagogpl' ),
	),
	'convertion_option' => array(
		// translators: %1$s: Origin Currency %2$s: Destination Currency.
		'title'   => sprintf( __( 'Activate conversion from %1$s to %2$s', 'woocommerce-kmercadopagogpl' ), $currency_org, $currency_dst ),
		'type'    => 'select',
		'label'   => __( 'Activate the plugin by converting the amounts to MercadoPago currency', 'woocommerce-kmercadopagogpl' ),
		'default' => '',
		'options' => array(
			'off'    => __( 'Disable Module', 'woocommerce-kmercadopagogpl' ),
			'custom' => __( 'Use a Manual conversion rate', 'woocommerce-kmercadopagogpl' ),
		),
	),
	'convertion_rate'   => array(
		// translators: %1$s: Origin Currency %2$s: Destination Currency.
		'title'   => sprintf( __( 'Convert using Manual Rate %1$s a %2$s', 'woocommerce-kmercadopagogpl' ), $currency_org, $currency_dst ),
		'type'    => 'text',
		'label'   => __( 'Use a manual conversion rate', 'woocommerce-kmercadopagogpl' ),
		'default' => '1.0',
	),
	'debug'             => array(
		'title'       => __( 'Debug', 'woocommerce-kmercadopagogpl' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable log', 'woocommerce-kmercadopagogpl' ),
		'default'     => 'no',
		// translators: %s: Path of log file.
		'description' => sprintf( __( 'To review the MercadoPago Log, download the file: %s', 'woocommerce-kmercadopagogpl' ), '<code>/wp-content/plugins/woocommerce-kmercadopago-gpl/logs/</code>' ),
	),
);
