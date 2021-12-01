=== Tools for MercadoPago and WooCommerce ===
Contributors: kijam
Tags: ecommerce, mercadopago, woocommerce
Requires at least: 4.9.10
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.0.2
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plataforma de pago MercadoPago para Argentina, Mexico, Brazil, Colombia, Chile, Peru and Uruguay. Solo Checkout Basico (Pro).

== Description ==
El plugin Tools for MercadoPago and WooCommerce le permite procesar pagos para su tienda en línea, acepta tarjetas de crédito American Express, Mastercard, Visa, tarjetas de débito, pagos en cajeros automáticos, kioscos y más.

Por el momento, solo es posible usar el checkout basico (Version Pro), no incluye checkout personalizado o custom checkout.

== Installation ==
= Minimum Technical Requirements =
* WordPress
* WooCommerce
* LAMP Environment (Linux, Apache, MySQL, PHP)
* SSL Certificate
* Additional configuration: safe_mode off, memory_limit higher than 256MB

Install the module in two different ways: automatically, from the “Plugins” section of WordPress, or manually, downloading and copying the plugin files into your directory.

Automatic Installation by WordPress admin
1. Access "Plugins" from the navigation side menu of your WordPress administrator.
2. Once inside Plugins, click on \'Add New\' and search for \'Mercado Pago payments for WooCommerce\' in the WordPress Plugin list
3. Click on "Install."

Done! It will be in the "Installed Plugins" section and from there you can activate it.

Manual Installation
1. Download the https://github.com/kijamve/wc-kmercadopago-gpl/releases now or from the o WordPress Module https://es.wordpress.org/plugins/wc-kmercadopago-gpl/
2. Unzip the folder and rename it to "wc-kmercadopago-gpl"
3. Copy the "wc-kmercadopago-gpl" file into your WordPress directory, inside the "Plugins" folder.

Done!

= Installing this plugin does not affect the speed of your store! =

If you installed it correctly, you will see it in your list of "Installed Plugins" on the WordPress work area. Please enable it and proceed to your Mercado Pago account integration and setup.

=  Configuration =
Set up both the plugin and the checkouts you want to activate on your payment avenue. Follow these five steps instructions and get everything ready to receive payments:

1. Add your **credentials** to test the store and charge with your Mercado Pago account **according to the country** where you are registered.
2. Approve your account in order to charge.
3. Fill in the basic information of your business in the plugin configuration.
4. Set up **payment preferences** for your customers.
5. Access **advanced** plugin and checkout **settings** only when you want to change the default settings.

== Screenshots ==
1. Configuración del plugin 1
2. Configuración del plugin 2
3. Configuración del plugin 3
4. Configuración del plugin 4
5. Configuración del plugin 5
6. Ficha de producto
7. Página de pago
8. Página de pago en MercadoPago
9. Detalles del pedido

== Changelog ==
= v1.0.3 (30/11/2021) =
- Fix invalid error message setting access token
- Minor fix on translation
= v1.0.2 (30/11/2021) =
- Initial Version