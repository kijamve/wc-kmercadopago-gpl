<?php
/**
 * MercadoPago Tools GPL - Functions
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

add_action(
	'woocommerce_after_add_to_cart_button',
	function() {
		$woocommerce = WC_KMercadoPagoGPL::woocommerce_instance();
		$woocommerce->payment_gateways();
		$instance = WC_KMercadoPagoGPL_Basic::get_instance();
		$instance->process_installment_product();
	}
);
add_filter(
	'woocommerce_checkout_fields',
	function( $fields ) {
		WC_KMercadoPagoGPL::get_instance();
		$manager = WC_KMercadoPagoGPL_Manager::get_instance();
		if ( ! $manager->get_mp_request_dni() ) {
			return $fields;
		}
		$types   = WC_KMercadoPagoGPL_Manager::get_identification_types();
		$options = array();
		foreach ( $types as $type ) {
			$options[ $type['id'] ] = $type['name'];
		}
		$fields['billing']['billing_kmercadopagogpl_vat_type'] = array(
			'label'       => __( 'Identification type', 'woocommerce-kmercadopagogpl' ),
			'type'        => 'select',
			'required'    => true,
			'options'     => $options,
			'class'       => apply_filters( 'kmercadopagogpl_form_row_first_field', array( 'form-row-wide', 'form-group', 'col-sm-12', 'col-md-12' ) ),
			'input_class' => apply_filters( 'kmercadopagogpl_form_row_first_input', array( 'form-control' ) ),
			'clear'       => true,
		);
		$fields['billing']['billing_kmercadopagogpl_vat']      = array(
			'label'       => __( 'Identification number', 'woocommerce-kmercadopagogpl' ),
			'type'        => 'text',
			'required'    => true,
			'class'       => apply_filters( 'kmercadopagogpl_form_row_last_field', array( 'form-row-wide', 'form-group', 'col-sm-12', 'col-md-12' ) ),
			'input_class' => apply_filters( 'kmercadopagogpl_form_row_last_input', array( 'form-control' ) ),
			'clear'       => true,
		);
		return $fields;
	}
);

/**
 * Show Metabox of MercadoPago in Admin Order Page.
 */
function kmercadopagogpl_metabox_cb() {
	$woocommerce = WC_KMercadoPagoGPL::woocommerce_instance();
	$woocommerce->payment_gateways();
	WC_KMercadoPagoGPL_Manager::get_instance();
	do_action( 'woocommerce_kmercadopagogpl_metabox' );
}
add_action(
	'add_meta_boxes',
	function() {
		add_meta_box( 'kmercadopagogpl-metabox', __( 'Data of MercadoPago', 'woocommerce-kmercadopagogpl' ), 'kmercadopagogpl_metabox_cb', 'shop_order', 'normal', 'high' );
	}
);
// phpcs:ignore
if ( isset( $_GET['topic'] ) || isset( $_GET['collection_id'] ) ) {
	// phpcs:ignore
	if ( isset( $_GET['collection_id'] ) ) {
		$_GET['topic'] = 'payment';
		// phpcs:ignore
		$_GET['id']    = $_GET['collection_id'];
	}
	remove_filter( 'template_redirect', 'redirect_canonical' );
}
add_action(
	'template_redirect',
	function() {
		WC_KMercadoPagoGPL::get_instance();
		WC_KMercadoPagoGPL_Manager::get_instance();
		kmercadopagogpl_set_device_id_cb();
		// phpcs:ignore
		if ( isset( $_GET['topic'] ) && ! isset( $_GET['wc-api'] ) ) {
			do_action( 'woocommerce_kmercadopagogpl_check_ipn_response' );
		}
	}
);

/**
 * Cronjob of MercadoPago.
 *
 * @param string $order_id Order ID.
 */
function do_kmercadopagogpl_hourly_check( $order_id ) {
	WC_KMercadoPagoGPL::get_instance();
	$time = WC_KMercadoPagoGPL_Manager::get_metadata(
		$order_id,
		'first_hourly_check'
	);
	$data = WC_KMercadoPagoGPL_Manager::get_instance()->validate_mercadopago( false, false, $order_id );
	if ( $data ) {
		WC_KMercadoPagoGPL_Manager::get_instance()->successful_request( $data, false );
	}
	$order  = wc_get_order( $order_id );
	$status = $order->get_status();
	if ( ! in_array( $status, array( 'pending', 'on-hold' ), true ) ) {
		wp_clear_scheduled_hook( 'do_kmercadopagogpl_hourly_check', array( (int) $order_id ) );
	} else {
		$last_mp_status = WC_KMercadoPagoGPL_Manager::get_metadata( $order_id, 'last_mp_status' );
		if ( 'pending' === $status || ! $last_mp_status || empty( $last_mp_status ) ) {
			wp_clear_scheduled_hook( 'do_kmercadopagogpl_hourly_check', array( (int) $order_id ) );
			$order->update_status( 'cancelled', __( 'MercadoPago: Payment canceled due to non-payment.', 'woocommerce-kmercadopagogpl' ) );
		} else {
			$time = WC_KMercadoPagoGPL_Manager::get_metadata(
				$order_id,
				'first_hourly_check'
			);
			if ( time() - $time > WC_KMercadoPagoGPL_Manager::get_instance()->get_cancel_hold_in() * 3600 ) {
				wp_clear_scheduled_hook( 'do_kmercadopagogpl_hourly_check', array( (int) $order_id ) );
				$order->update_status( 'cancelled', __( 'MercadoPago: Payment canceled due to non-payment.', 'woocommerce-kmercadopagogpl' ) );
			}
		}
	}
}
add_action( 'do_kmercadopagogpl_hourly_check', 'do_kmercadopagogpl_hourly_check' );


add_action(
	'woocommerce_cart_calculate_fees',
	function( $cart ) {
		WC_KMercadoPagoGPL::get_instance();
		$woocommerce = WC_KMercadoPagoGPL::woocommerce_instance();
		$woocommerce->payment_gateways();
		WC_KMercadoPagoGPL_Manager::get_instance();
		WC_KMercadoPagoGPL_Basic::get_instance()->add_fee_mercadopago( $cart );
	},
	( PHP_INT_MAX - 1 ),
	1
);

/**
 * Save MercadoPago Device ID.
 */
function kmercadopagogpl_set_device_id_cb() {
	if ( ! isset( $_POST['nonce'] ) && ! isset( $_POST['kmercadopagogpl_device_id'] ) ) {
		return;
	}
	$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'kmercadopagogpl_set_device_id' ) ) {
		die( 'Invalid nonce!' );
	}
	$wc = WC_KMercadoPagoGPL::woocommerce_instance();
	if ( ! isset( $wc->session ) || is_null( $wc->session ) ) {
		$wc->session = new WC_Session_Handler();
		$wc->session->init();
	}
	$wc->session->set( 'woocommere-kmercadopagogpl-device-id', sanitize_text_field( wp_unslash( $_POST['kmercadopagogpl_device_id'] ) ) );
	echo 'SET-DEVICE-ID';
	exit;
}
add_action( 'wp_ajax_kmercadopagogpl_set_device_id', 'kmercadopagogpl_set_device_id_cb' );
add_action( 'wp_ajax_nopriv_kmercadopagogpl_set_device_id', 'kmercadopagogpl_set_device_id_cb' );
