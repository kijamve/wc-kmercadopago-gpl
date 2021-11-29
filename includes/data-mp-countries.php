<?php
/**
 * MercadoPago Tools GPL - Setting by Country
 *
 * @category   Components
 * @package    MercadoPago Tools GPL
 * @author     Kijam.com <info@kijam.com>
 * @license    GNU/GPLv2
 * @link       https://kijam.com
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Array of settings
 */

return array(
	'MLA' => array(
		'NAME'                    => __( 'Argentina', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'ARS',
		'CURRENCY_TYPE'           => 'FLOAT',
		'ISO'                     => 'AR',
		'REGISTER_URL'            => 'https://www.mercadopago.com/mla/registration',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mla/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mla/herramientas/aplicaciones',
		'IPN_URL'                 => 'https://www.mercadopago.com/mla/herramientas/notificaciones',
		'MPENVIOS_REGISTER_URL'   => 'http://envios.mercadolibre.com.ar/optin/doOptin',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mla/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mla/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => false,
		'INSTALLMENT_FEE'         => array(
			3,
			6,
			12,
			18,
		),
	),
	'MLM' => array(
		'NAME'                    => __( 'Mexico', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'MXN',
		'CURRENCY_TYPE'           => 'FLOAT',
		'ISO'                     => 'MX',
		'REGISTER_URL'            => 'https://www.mercadopago.com/mlm/registration',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mlm/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlm/herramientas/aplicaciones',
		'IPN_URL'                 => 'https://www.mercadopago.com/mlm/herramientas/notificaciones',
		'MPENVIOS_REGISTER_URL'   => 'http://shipping.mercadopago.com.mx/optin/doOptin',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mlm/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlm/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => true,
		'INSTALLMENT_FEE'         => array(
			3,
			6,
			9,
			12,
			18,
		),
	),
	'MLB' => array(
		'NAME'                    => __( 'Brasil', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'BRL',
		'CURRENCY_TYPE'           => 'FLOAT',
		'ISO'                     => 'BR',
		'REGISTER_URL'            => 'https://www.mercadopago.com/mlb/registration',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mlb/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlb/ferramentas/aplicacoes',
		'IPN_URL'                 => 'https://www.mercadopago.com/mlb/ferramentas/notificacoes',
		'MPENVIOS_REGISTER_URL'   => 'http://envios.mercadolivre.com.br/optin/doOptin',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mlb/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlb/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => false,
	),
	'MLC' => array(
		'NAME'                    => __( 'Chile', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'CLP',
		'CURRENCY_TYPE'           => 'INTEGER',
		'REGISTER_URL'            => 'https://www.mercadopago.com/mlc/registration',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mlc/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlc/herramientas/aplicaciones',
		'IPN_URL'                 => 'https://www.mercadopago.com/mlc/herramientas/notificaciones',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mlc/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlc/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => false,
	),
	'MCO' => array(
		'NAME'                    => __( 'Colombia', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'COP',
		'CURRENCY_TYPE'           => 'INTEGER',
		'REGISTER_URL'            => 'https://www.mercadopago.com/mco/registration',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mco/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mco/herramientas/aplicaciones',
		'IPN_URL'                 => 'https://www.mercadopago.com/mco/herramientas/notificaciones',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mco/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mco/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => false,
	),
	'MPE' => array(
		'NAME'                    => __( 'Peru', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'PEN',
		'CURRENCY_TYPE'           => 'FLOAT',
		'REGISTER_URL'            => 'https://registration.mercadopago.com.pe/registration-mp?mode=mp',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mpe/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mpe/account/credentials?type=basic',
		'IPN_URL'                 => 'https://www.mercadopago.com/mpe/herramientas/notificaciones',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mpe/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mpe/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => false,
	),
	'MLU' => array(
		'NAME'                    => __( 'Uruguay', 'woocommerce-kmercadopagogpl' ),
		'CURRENCY'                => 'UYU',
		'CURRENCY_TYPE'           => 'FLOAT',
		'REGISTER_URL'            => 'https://registration.mercadopago.com.uy/registration-mp?mode=mp',
		'SECRET_URL'              => 'https://www.mercadolibre.com/jms/mlu/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlu/account/credentials?type=basic',
		'IPN_URL'                 => 'https://www.mercadopago.com/mlu/herramientas/notificaciones',
		'PUBLICKEY_URL'           => 'https://www.mercadolibre.com/jms/mlu/lgz/login?platform_id=mp&go=https://www.mercadopago.com/mlu/account/credentials',
		'ACCEPT_DIGITAL_CURRENCY' => false,
	),
);
