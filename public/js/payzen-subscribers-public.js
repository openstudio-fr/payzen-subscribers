(function( $ ) {
	'use strict';

	$(function() {

		jQuery.ajax({
			method: 'post',
			url: paysubsSettings.ajaxurl,
			data: {
				action: 'ajaxResponse',
				plan: 1
			}
		}).done(function(jsonData) {
			let response = JSON.parse(jsonData);
		});


		subscribeFormTreatment($);

		alertConfirm($);


	});
})( jQuery );

function alertConfirm($){
	$('.close_account form, .change_payment form, .logout form, .remove_wp_account form').submit(function(event){
			if(!confirm(paysubsSettings.confirm_text)){
				event.preventDefault();
			}
		}
	);
}

function subscribeFormTreatment($){
	//Treatment JS Form Subscribe
	//If plan is selected
	$('.paysubs_plans').click(function(el){
		$('#form-section-payment').empty();
		//Display plan : payment method
		$('#form-section-payment').append(paysubsFunc.paymentMethodsForm[el.currentTarget.value]);

		//If SDD or CB is selected
		//Defined action form : paysubs_action
		$('#paysubs_payment_anchor_form input[name="paysubs_payment_method"]').click(function(el){
			let value = el.currentTarget.value.toString();
			let actionForm = {
				"CB": "paysubs_payment_cb_step_2",
				"AMEX": "paysubs_payment_cb_step_2",
				"SDD": "paysubs_payment_sdd_step_2"
			};

			$('#paysubs_payment_anchor_form input[name="paysubs_action"]').val(actionForm[value]);

		});

	});


}
