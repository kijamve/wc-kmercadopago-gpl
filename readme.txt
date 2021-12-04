=== Tools for MercadoPago and WooCommerce ===
Contributors: kijam
Tags: ecommerce, mercadopago, woocommerce
Requires at least: 4.9.10
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.0.4
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plataforma de pago MercadoPago para Argentina, Mexico, Brazil, Colombia, Chile, Peru and Uruguay. Solo Checkout Basico (Pro).

== Description ==
El plugin Tools for MercadoPago and WooCommerce le permite procesar pagos para su tienda en línea, acepta tarjetas de crédito American Express, Mastercard, Visa, tarjetas de débito, pagos en cajeros automáticos, kioscos y más.

Con este plug-in podrás:
- Añadir una comisión por usar MercadoPago
- Asignar un tiempo máximo de espera para los pedidos sin completar, estos se cancelarán al expirar el tiempo establecido.
- Asignar un tiempo máximo de espera para los pedidos completados que quedan en pendiente, estos se cancelaran al expirar el tiempo establecido.
- Posibilidad de solicitar el DNI al cliente en el checkout. Te ayudara a disminuir los pagos por concepto de riesgo de fraude.
- Limitar la cantidad máximas de cuotas que aceptaras.
- Mostrar calculadora de cuotas en la ficha de producto y/o en el checkout.
- Podrás cambiar el Nombre de la pasarela de pago.
- Podrás cambiar la descripción de la pasarela de pago, con la posibilidad de incrustar HTML en este campo.
- Se añade javascript de rastreo de seguridad desde MercadoPago, este tracking te ayudara a disminuir los pagos por concepto de riesgo de fraude.
- Desactivar algunas opciones de pago, por ejemplo: Tarjetas de Débito, transferencias, etc.

Por el momento, solo es posible usar el checkout basico (Version Pro), no incluye checkout personalizado o custom checkout.

= Descrição em portugues =
O plugin Tools for MercadoPago e WooCommerce permite processar pagamentos para sua loja online, aceita American Express, Mastercard, cartões de crédito Visa, cartões de débito, pagamentos em caixas eletrônicos, quiosques e muito mais.

Com este plug-in, você pode:
- Adicione uma comissão pelo uso do MercadoPago
- Atribuir um tempo máximo de espera para pedidos não atendidos, estes serão cancelados no final do tempo estabelecido.
- Atribuir um tempo máximo de espera aos pedidos concluídos e pendentes, estes serão cancelados no fim do tempo estabelecido.
- Possibilidade de solicitar o DNI do cliente no checkout. Isso o ajudará a reduzir os pagamentos devido ao risco de fraude.
- Limite a quantidade máxima de cotas que você aceitará.
- Mostrar calculadora de cotas na ficha do produto e / ou na finalização da compra.
- Você pode alterar o nome do portal de pagamento.
- Você pode alterar a descrição da plataforma de pagamento, com a possibilidade de embutir HTML neste campo.
- O rastreamento de segurança javascript é adicionado do MercadoPago, este rastreamento irá ajudá-lo a reduzir pagamentos devido ao risco de fraude.
- Desative algumas opções de pagamento, por exemplo: Cartões de Débito, Transferências, etc.

No momento, só é possível usar o checkout básico (versão Pro), não inclui checkout personalizado ou checkout personalizado.

== Installation ==
= Minimum Technical Requirements =
* WordPress
* WooCommerce
* LAMP Environment (Linux, Apache, MySQL, PHP)
* SSL Certificate
* Additional configuration: safe_mode off, memory_limit higher than 256MB

Install the module in two different ways: automatically, from the "**Plugins**" section of WordPress, or manually, downloading and copying the plugin files into your directory.

Automatic Installation by WordPress admin
1. Access "**Plugins**" from the navigation side menu of your WordPress administrator.
2. Once inside Plugins, click on "**Add New**" and search for "**Tools for MercadoPago and WooCommerce**" in the WordPress Plugin list
3. Click on "**Install**"

Done! It will be in the "Installed Plugins" section and from there you can activate it.

Manual Installation
1. Download the https://github.com/kijamve/wc-kmercadopago-gpl/releases now or from WordPress Module https://es.wordpress.org/plugins/wc-kmercadopago-gpl/
2. Unzip the folder and **rename** it to "**wc-kmercadopago-gpl**"
3. Copy the "**wc-kmercadopago-gpl**" file into your WordPress directory "**/wp-content/plugins/**".

Done!

= Installing this plugin does not affect the speed of your store! =

If you installed it correctly, you will see it in your list of "**Installed Plugins**" on the WordPress work area. Please proceed to activate it and then configure the Access token of your MercadoPago account.

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
= v1.0.4 (01/12/2021) =
- Added review message in admin page
= v1.0.3 (30/11/2021) =
- Fix invalid error message setting access token
- Minor fix on translation
= v1.0.2 (30/11/2021) =
- Initial Version