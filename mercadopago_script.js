/* global mercadopago */
;(function( $, window, document ) {
	'use strict';
	var id_variation = 0;
	var variation_price = typeof product_price_mpgpl !== "undefined" && product_price_mpgpl > 0 ? product_price_mpgpl : 0;
	if (wc_kmercadopagogpl_context.publickey.length > 0 && jQuery("#result_installments_mpgpl").length ) {
		jQuery.getScript("https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js").done(function(){
			Mercadopago.setPublishableKey(wc_kmercadopagogpl_context.publickey);
		});
	}
	$( '.variations_form').on( 'show_variation', function( event, variation ) {
		if (variation.display_regular_price > variation.display_price && variation.display_price > 0) {
			variation_price = variation.display_price;
		} else {
			variation_price = variation.display_regular_price > 0 ? variation.display_regular_price : variation.display_price;
		}
		id_variation = variation.variation_id;
		if ($('#inputSixTDCMPGPL').length > 0 && $('#inputSixTDCMPGPL').val().length >= 6) {
			calcInstallments();
		}
	});
	$(document).ready(function() {
		$('#inputSixTDCMPGPL').keypress(function(event) {
			if (event.keyCode == 13) {
				calcInstallments();
				event.preventDefault();
			}
		});
	});
	function calcInstallments() {
		$('#btn_mpgpl_calc_instalments.handleInstallment').trigger('click');
	}
	setInterval(function() {
		if (typeof $ == 'undefined')
			return;
		$('#btn_mpgpl_calc_instalments:not(.handleInstallment)').addClass('handleInstallment').click(
			function() {
				if ($('#inputSixTDCMPGPL').length < 1)
					return;
				var credit_card = $('#inputSixTDCMPGPL').val();
				if (credit_card.length < 1)
					return;
				var price = variation_price;
				if (credit_card.length != 6) {
					$("#result_installments_mpgpl").html("<b style='color:red'>"+wc_kmercadopagogpl_context.messages.cc_invalid+"</b>");
				} else {
					$("#result_installments_mpgpl").html(wc_kmercadopagogpl_context.messages.server_loading);
					Mercadopago.getInstallments({"bin": credit_card,"amount": price}, function(status, data) {
						var html = "";
						if (status != 200) {
							html = "<b style='color:red'>" + wc_kmercadopagogpl_context.messages.installment_error + data.cause[0].code + " -> " + data.cause[0].description + "</b>";
						} else {
							for (var i in data[0].payer_costs) {
								var cost = data[0].payer_costs[i];
								if(wc_kmercadopagogpl_context.max_installment > 0 && data[0].payer_costs[i].installments > wc_kmercadopagogpl_context.max_installment) {
									continue;
								}
								var line = " - "+cost.recommended_message;
								if (cost.labels && cost.labels.length > 0) {
									for(var j in cost.labels) {
										if (cost.labels[j].indexOf("CFT") >= 0 || cost.labels[j].indexOf("TEA") >= 0)
											line += ", "+cost.labels[j].replace(/_+/ig, " ").replace(/\|/ig, ", ");
									}
									line = line.replace('mensualidades', 'cuotas').replace('mensualidad', 'cuota').replace(/([^\$]+)(\$[^(]+)(.+)/ig, '$1 <b>$2</b> $3');
								} else {
									//line = line.replace(/([^\$]+)(\$[^(]+)(.+)/ig, '$1 <b>$2</b> $3');
								}
								html += line+"<br />";
							}
						}
						$("#result_installments_mpgpl").html(html);
						console.log(html);
					});
				}
			}
		);
	}, 500);
})( jQuery, window, document );
