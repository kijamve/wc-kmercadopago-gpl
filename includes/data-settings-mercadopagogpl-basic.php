<?php
/**
 * MercadoPago Tools GPL - Setting for Checkout Basic
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

$currency_org = get_woocommerce_currency();
$currency_dst = $this->setting['CURRENCY'];

return array(
	'enabled'                              => array(
		'title'   => __( 'Enable / Disable', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable MercadoPago', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'title'                                => array(
		'title'       => __( 'Title', 'wc-kmp-gpl' ),
		'type'        => 'text',
		'description' => __( 'Add the name to MercadoPago that will be shown to the customer.', 'wc-kmp-gpl' ),
		'default'     => __( 'MercadoPago', 'wc-kmp-gpl' ),
	),
	'description'                          => array(
		'title'       => __( 'Description', 'wc-kmp-gpl' ),
		'type'        => 'textarea',
		'description' => __( 'Add a description to this payment method.', 'wc-kmp-gpl' ),
		'default'     => __( 'Pay with MercadoPago', 'wc-kmp-gpl' ),
	),
	'mp_icon'                              => array(
		'title'       => __( 'Icon (Maximum recommended height: 21px)', 'wc-kmp-gpl' ),
		'type'        => 'text',
		'description' => __( 'To remove the icon use this url', 'wc-kmp-gpl' ) . ': ' . plugins_url( 'images/blank.gif', plugin_dir_path( __FILE__ ) ),
		'default'     => plugins_url( 'images/mercadopago.png', plugin_dir_path( __FILE__ ) ),
	),
	'installment_paymentbutton_calculator' => array(
		'title'   => __( 'Show Installment calculator in the payment button', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Show a Installment calculator on the payment button when you want to pay by credit card', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'installment_product_calculator'       => array(
		'title'   => __( 'Show Installment calculator on products', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Displays an installment calculator on the product when you want to pay by credit card', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'mp_fee'                               => array(
		'title'       => __( 'Commission percentage', 'wc-kmp-gpl' ),
		'type'        => 'text',
		'description' => __( 'Enter the percentage of commission you want to charge your clients.', 'wc-kmp-gpl' ),
		'default'     => '',
	),
	'mp_fee_amount'                        => array(
		// translators: %s: Fixed fee amount.
		'title'       => sprintf( __( 'Fixed amount of Commission in %s', 'wc-kmp-gpl' ), $currency_dst ),
		'type'        => 'text',
		'description' => __( 'Enter a commission amount that you want to charge your clients (It is additional to the percentage).', 'wc-kmp-gpl' ),
		'default'     => '',
	),
	'marketplace'                          => array(
		'title'             => __( 'Marketplace Mode', 'wc-kmp-gpl' ),
		'type'              => 'checkbox',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'label'             => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => 'no',
	),
	'subcription'                          => array(
		'title'             => __( 'Subscription Mode (Only work with WooCommerce Subscription plugin)', 'wc-kmp-gpl' ),
		'type'              => 'checkbox',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'label'             => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => 'no',
	),
	'mp_installments'                      => array(
		'title'       => __( 'Maximum number of installments', 'wc-kmp-gpl' ),
		'type'        => 'text',
		'description' => __( 'Indicate the maximum number of quotas available to your clients', 'wc-kmp-gpl' ),
		'default'     => '18',
	),
	'mp_installments_msi'                  => array(
		'title'             => __( 'Activate fixed commission to Months without Interest', 'wc-kmp-gpl' ),
		'type'              => 'checkbox',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'label'             => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => 'no',
	),
	'mp_installments_msi_3'                => array(
		'title'             => __( 'Commission Percentage for 3 Months without Interest', 'wc-kmp-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_6'                => array(
		'title'             => __( 'Commission Percentage for 6 Months without Interest', 'wc-kmp-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_9'                => array(
		'title'             => __( 'Commission Percentage for 9 Months without Interest', 'wc-kmp-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_12'               => array(
		'title'             => __( 'Commission Percentage for 12 Months without Interest', 'wc-kmp-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_18'               => array(
		'title'             => __( 'Commission Percentage for 18 Months without Interest', 'wc-kmp-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmp-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'disable_bank'                         => array(
		'title'   => __( 'Deactivate Deposits / Bank Transfers', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your clients from paying with Deposits / Bank Transfers', 'wc-kmp-gpl' ),
		'default' => 'no',
	),
	'disable_prepaid'                      => array(
		'title'   => __( 'Deactivate Prepaid Cards', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Prepaid Cards', 'wc-kmp-gpl' ),
		'default' => 'no',
	),
	'disable_credit_card'                  => array(
		'title'   => __( 'Deactivate Credit Card', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Credit Cards', 'wc-kmp-gpl' ),
		'default' => 'no',
	),
	'disable_debit_card'                   => array(
		'title'   => __( 'Deactivate Debit Card', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Debit Cards', 'wc-kmp-gpl' ),
		'default' => 'no',
	),
	'disable_tickets'                      => array(
		'title'   => __( 'Deactivate Coupon', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with coupons', 'wc-kmp-gpl' ),
		'default' => 'no',
	),
	'disable_bitcoin'                      => array(
		'title'   => __( 'Deactivate Cryptocurrencies (Bitcoin, etc ...)', 'wc-kmp-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Cryptocurrencies (Bitcoin, etc...)', 'wc-kmp-gpl' ),
		'default' => 'yes',
	),
	'mp_redirect'                          => array(
		'title'       => __( 'Redirect automatically', 'wc-kmp-gpl' ),
		'type'        => 'checkbox',
		'label'       => __( 'Activate', 'wc-kmp-gpl' ),
		'default'     => 'yes',
		'description' => __( 'When the payment is approved, the client will be automatically redirected to your website.', 'wc-kmp-gpl' ),
	),
	'method'                               => array(
		'title'   => __( 'Checkout Method', 'wc-kmp-gpl' ),
		'type'    => 'select',
		'label'   => __( 'Select the checkout method', 'wc-kmp-gpl' ),
		'default' => 'redirect',
		'options' => array(
			'redirect' => __( 'Redirect to MercadoPago', 'wc-kmp-gpl' ),
			'modal'    => __( 'Modal', 'wc-kmp-gpl' ),
		),
	),
);
