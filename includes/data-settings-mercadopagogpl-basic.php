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
		'title'   => __( 'Enable / Disable', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable MercadoPago', 'wc-kmercadopago-gpl' ),
		'default' => 'yes',
	),
	'title'                                => array(
		'title'       => __( 'Title', 'wc-kmercadopago-gpl' ),
		'type'        => 'text',
		'description' => __( 'Add the name to MercadoPago that will be shown to the customer.', 'wc-kmercadopago-gpl' ),
		'default'     => __( 'MercadoPago', 'wc-kmercadopago-gpl' ),
	),
	'description'                          => array(
		'title'       => __( 'Description', 'wc-kmercadopago-gpl' ),
		'type'        => 'textarea',
		'description' => __( 'Add a description to this payment method.', 'wc-kmercadopago-gpl' ),
		'default'     => __( 'Pay with MercadoPago', 'wc-kmercadopago-gpl' ),
	),
	'mp_icon'                              => array(
		'title'       => __( 'Icon (Maximum recommended height: 21px)', 'wc-kmercadopago-gpl' ),
		'type'        => 'text',
		'description' => __( 'To remove the icon use this url', 'wc-kmercadopago-gpl' ) . ': ' . plugins_url( 'images/blank.gif', plugin_dir_path( __FILE__ ) ),
		'default'     => plugins_url( 'images/mercadopago.png', plugin_dir_path( __FILE__ ) ),
	),
	'installment_paymentbutton_calculator' => array(
		'title'   => __( 'Show Installment calculator in the payment button', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Show a Installment calculator on the payment button when you want to pay by credit card', 'wc-kmercadopago-gpl' ),
		'default' => 'yes',
	),
	'installment_product_calculator'       => array(
		'title'   => __( 'Show Installment calculator on products', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Displays an installment calculator on the product when you want to pay by credit card', 'wc-kmercadopago-gpl' ),
		'default' => 'yes',
	),
	'mp_fee'                               => array(
		'title'       => __( 'Commission percentage', 'wc-kmercadopago-gpl' ),
		'type'        => 'text',
		'description' => __( 'Enter the percentage of commission you want to charge your clients.', 'wc-kmercadopago-gpl' ),
		'default'     => '',
	),
	'mp_fee_amount'                        => array(
		// translators: %s: Fixed fee amount.
		'title'       => sprintf( __( 'Fixed amount of Commission in %s', 'wc-kmercadopago-gpl' ), $currency_dst ),
		'type'        => 'text',
		'description' => __( 'Enter a commission amount that you want to charge your clients (It is additional to the percentage).', 'wc-kmercadopago-gpl' ),
		'default'     => '',
	),
	'marketplace'                          => array(
		'title'             => __( 'Marketplace Mode', 'wc-kmercadopago-gpl' ),
		'type'              => 'checkbox',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'label'             => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => 'no',
	),
	'subcription'                          => array(
		'title'             => __( 'Subscription Mode (Only work with WooCommerce Subscription plugin)', 'wc-kmercadopago-gpl' ),
		'type'              => 'checkbox',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'label'             => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => 'no',
	),
	'mp_installments'                      => array(
		'title'       => __( 'Maximum number of installments', 'wc-kmercadopago-gpl' ),
		'type'        => 'text',
		'description' => __( 'Indicate the maximum number of quotas available to your clients', 'wc-kmercadopago-gpl' ),
		'default'     => '18',
	),
	'mp_installments_msi'                  => array(
		'title'             => __( 'Activate fixed commission to Months without Interest', 'wc-kmercadopago-gpl' ),
		'type'              => 'checkbox',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'label'             => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => 'no',
	),
	'mp_installments_msi_3'                => array(
		'title'             => __( 'Commission Percentage for 3 Months without Interest', 'wc-kmercadopago-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_6'                => array(
		'title'             => __( 'Commission Percentage for 6 Months without Interest', 'wc-kmercadopago-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_9'                => array(
		'title'             => __( 'Commission Percentage for 9 Months without Interest', 'wc-kmercadopago-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_12'               => array(
		'title'             => __( 'Commission Percentage for 12 Months without Interest', 'wc-kmercadopago-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'mp_installments_msi_18'               => array(
		'title'             => __( 'Commission Percentage for 18 Months without Interest', 'wc-kmercadopago-gpl' ),
		'type'              => 'text',
		'custom_attributes' => array( 'disabled' => 'disabled' ),
		'description'       => __( 'Only available in MercadoPago Tools Pro', 'wc-kmercadopago-gpl' ) . ': <a href="https://kijam.com/tienda/p/mercadopago-para-woocommerce/" target="_blank">https://kijam.com/</a>',
		'default'           => '',
	),
	'disable_bank'                         => array(
		'title'   => __( 'Deactivate Deposits / Bank Transfers', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your clients from paying with Deposits / Bank Transfers', 'wc-kmercadopago-gpl' ),
		'default' => 'no',
	),
	'disable_prepaid'                      => array(
		'title'   => __( 'Deactivate Prepaid Cards', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Prepaid Cards', 'wc-kmercadopago-gpl' ),
		'default' => 'no',
	),
	'disable_credit_card'                  => array(
		'title'   => __( 'Deactivate Credit Card', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Credit Cards', 'wc-kmercadopago-gpl' ),
		'default' => 'no',
	),
	'disable_debit_card'                   => array(
		'title'   => __( 'Deactivate Debit Card', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Debit Cards', 'wc-kmercadopago-gpl' ),
		'default' => 'no',
	),
	'disable_tickets'                      => array(
		'title'   => __( 'Deactivate Coupon', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with coupons', 'wc-kmercadopago-gpl' ),
		'default' => 'no',
	),
	'disable_bitcoin'                      => array(
		'title'   => __( 'Deactivate Cryptocurrencies (Bitcoin, etc ...)', 'wc-kmercadopago-gpl' ),
		'type'    => 'checkbox',
		'label'   => __( 'Prevent your customers from paying with Cryptocurrencies (Bitcoin, etc...)', 'wc-kmercadopago-gpl' ),
		'default' => 'yes',
	),
	'mp_redirect'                          => array(
		'title'       => __( 'Redirect automatically', 'wc-kmercadopago-gpl' ),
		'type'        => 'checkbox',
		'label'       => __( 'Activate', 'wc-kmercadopago-gpl' ),
		'default'     => 'yes',
		'description' => __( 'When the payment is approved, the client will be automatically redirected to your website.', 'wc-kmercadopago-gpl' ),
	),
	'method'                               => array(
		'title'   => __( 'Checkout Method', 'wc-kmercadopago-gpl' ),
		'type'    => 'select',
		'label'   => __( 'Select the checkout method', 'wc-kmercadopago-gpl' ),
		'default' => 'redirect',
		'options' => array(
			'redirect' => __( 'Redirect to MercadoPago', 'wc-kmercadopago-gpl' ),
			'modal'    => __( 'Modal', 'wc-kmercadopago-gpl' ),
		),
	),
);
