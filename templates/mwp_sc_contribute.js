jQuery(document).ready(function() {
	//Manage user type when ever user form is invoked
	function change_user_display () {
		if ( jQuery('.user_type').is(":checked") ) {
			jQuery('.mangopay_legal').show();
			jQuery('.mangopay_natural').hide();
		} else {
			jQuery('.mangopay_natural').show();
			jQuery('.mangopay_legal').hide();
		}
	}
	jQuery('.user_type').click(function() {	
		change_user_display ();
	});

	//Payform buttons to change sections already filled.
	jQuery('.bt_change_user_data').click(function() {
		jQuery('.mangopay_userheader').show();
		change_user_display ();
		jQuery('.bt_change_user_data').hide();
	});
	jQuery('.bt_register_card').click(function() {
		jQuery('.mangopay_cards').show();
		jQuery('.bt_register_card').hide();
		jQuery('.Alias').html('--');
	});

	jQuery.fn.exists = function(){return this.length>0;}
	function hide_object ( object ) {
		if ( object.exists() ) {
			object.hide();
		}
	}
	//Fill Crowfunding plugin Personal Info fieldset with our plugin fields
	hide_object ( jQuery("#edd_checkout_user_info") );
	hide_object ( jQuery("#edd-login-account-wrap") );
	hide_object ( jQuery("#edd-user-first-name-wrap") );
	hide_object ( jQuery("#edd-user-last-name-wrap") );
	hide_object ( jQuery("#edd-user-email-wrap") );

	jQuery('#mwp_Email').change(function() {
		jQuery('#edd-email').val(jQuery("#mwp_Email").val());		
	});
	jQuery( "#mwp_FirstName" ).change(function() {
		jQuery('#edd-first').val(jQuery("#mwp_FirstName").val());
	});
	jQuery( "#mwp_LastName" ).change(function() {
		jQuery('#edd-last').val(jQuery("#mwp_LastName").val());
	});
	jQuery('#mwp_LegalEmail').change(function() {
		jQuery('#edd-email').val(jQuery("#mwp_LegalEmail").val());
	});
	jQuery( "#mwp_LegalRepresentativeFirstName" ).change(function() {
		jQuery('#edd-first').val(jQuery("#mwp_LegalRepresentativeFirstName").val());
	});
	jQuery( "#mwp_LegalRepresentativeLastName" ).change(function() {
		jQuery('#edd-last').val(jQuery("#mwp_LegalRepresentativeLastName").val());
	});


});

