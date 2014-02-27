<?php
/*
Plugin Name: Easy Digital Downloads - Mangopay Gateway
Plugin URL: 
Description: A Mangopay gateway for Easy Digital Downloads
Version: 0.1
Author: Hackafou
Author URI: 
*/


require_once __DIR__ . "/includes/mwp_api.inc";
require_once __DIR__ . "/includes/mwp_user.inc";
require_once __DIR__ . "/includes/mwp_bank.inc";
require_once __DIR__ . "/includes/mwp_fields.inc";
require_once __DIR__ . "/includes/mwp_forms.inc";
require_once __DIR__ . "/includes/mwp_errors.inc";

wp_register_script ( "mwp_sc_contribute_js", plugins_url( '/templates/mwp_sc_contribute.js', __FILE__ ) , array( 'jquery' ) );

//Language
load_plugin_textdomain( 'mangopay_wp_plugin', false,  dirname(plugin_basename(__FILE__)) . '/languages/' );

//Configuration of Mangopay in profile. This is, minimum for users that are autors of posts. This user will owner the wallet.
include (__DIR__ . "/mwp_profile.php");

//Withdraw metabox in post edition sidebar
include (__DIR__ . "/mwp_post.php");

global $edd_options;

define('MWP_temp_path', $edd_options[ 'temp_folder']);
if ( edd_is_test_mode() ) {
	define('MWP_client_id', $edd_options[ 'test_api_user']);
	define('MWP_password', $edd_options[ 'test_api_key']);		
	define('MWP_base_path', 'https://api.sandbox.mangopay.com');
} else {
	define('MWP_client_id', $edd_options[ 'live_api_user']);
	define('MWP_password', $edd_options[ 'live_api_key']);		
	define('MWP_base_path', 'https://api.mangopay.com');
}	


//Activate globals
add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession() {
	if(!session_id()) {
		session_start();
	}
}
function myEndSession() {
	session_destroy ();
}


// registers the gateway
function pw_edd_register_gateway( $gateways ) {
	$gateways['mangopay_gateway'] = array( 'admin_label' => 'Mangopay', 'checkout_label' => __( 'Mangopay', 'mangopay_wp_plugin' ) );
	return $gateways;
}
add_filter( 'edd_payment_gateways', 'pw_edd_register_gateway' );


// credit card form
function pw_edd_mangopay_gateway_cc_form() {
	require_once ( __DIR__ . "/includes/mwp_pay.inc");
	$po = new mwp\mwp_pay ( wp_get_current_user () ); 	

	$output .= '<div>';
			
		//Ask for register (if user don't, we don't keep information, but do the process)
		$output .= mwp\mwp_forms::mwp_show_wordpress_login();

		//Display user data
		$output .= mwp\mwp_forms::mwp_show_user_section( $po->user, ! $po->user->mangopay_id );

		//Cards and paymentDirect
		$output .= mwp\mwp_forms::mwp_show_payment_section( $po, ! $po->user->card_id );
		
		$output .= mwp_print_link ("<img src='templates/mango.png'><br> Condicions generals", "http://www.mangopay.com/wp-content/blogs.dir/10/files/2013/11/CGU-API-MANGOPAY-MAI-2013_ESP.pdf");

		$output .=  "<input type='hidden' name ='mwp_post_id' value='{$_REQUEST['mwp_post_id']}'>";
	
	$output .= "</div>";

	echo $output;
	
}
add_action('edd_mangopay_gateway_cc_form', 'pw_edd_mangopay_gateway_cc_form');


// processes the payment
function pw_edd_process_payment( $purchase_data ) {	
	
	/**********************************
	* Purchase data comes in like this:

    $purchase_data = array(
        'downloads'     => array of download IDs,
        'tax' 		=> taxed amount on shopping cart
        'fees' 		=> array of arbitrary cart fees
        'discount' 	=> discounted amount, if any
        'subtotal'	=> total price before tax
        'price'         => total price of cart contents after taxes,
        'purchase_key'  =>  // Random key
        'user_email'    => $user_email,
        'date'          => date( 'Y-m-d H:i:s' ),
        'user_id'       => $user_id,
        'post_data'     => $_POST,
        'user_info'     => array of user's information and used discount code
        'cart_details'  => array of cart details,
     );
    */

	// check for any stored errors
	$errors = edd_get_errors();
	if ( ! $errors ) {

		$purchase_summary = edd_get_purchase_summary( $purchase_data );

		/****************************************
		* setup the payment details to be stored
		****************************************/

		$payment = array(
			'price'        => $purchase_data['price'],
			'date'         => $purchase_data['date'],
			'user_email'   => $purchase_data['user_email'],
			'purchase_key' => $purchase_data['purchase_key'],
			'currency'     => $edd_options['currency'],
			'downloads'    => $purchase_data['downloads'],
			'cart_details' => $purchase_data['cart_details'],
			'user_info'    => $purchase_data['user_info'],
			'status'       => 'pending'
		);

		// record the pending payment
		$payment = edd_insert_payment( $payment );

		$merchant_payment_confirmed = false;
		$error = false;
	
		//instantiate gateway object
		require_once ( __DIR__ . "/includes/mwp_pay.inc");
		$autosave = true;
		$po = new mwp\mwp_pay ( wp_get_current_user (), $autosave ) ;

		//Verify user
		if ( ! $po -> user -> mangopay_id ) {
			edd_set_error('register card', 'mangopay_id_missing', 'edd');
			$error = true;
		}

		//Card
		$needs_to_register_card = ! $po -> user -> card_id;

		if ( !$error && $needs_to_register_card ) {
			$data = $po -> mwp_preregister_card ();

			if ( ! $data ) {
				edd_set_error('register card', $_SESSION["MWP_API_ERROR"], 'edd');
				$error = true;
			} else {
				$output = $po -> mwp_send_to_token_server ( $data, $po->card_registration->CardRegistrationURL );
				if ( ! $po -> mwp_validate_card ( $output ) ) {			
					edd_set_error('register card', $_SESSION["MWP_API_ERROR"], 'edd');
					$error = true;
				} 		
			}		
		} 
	
		//Pay
		if ( !$error ) {		
			$po -> mwp_wallet_for_post ( $purchase_data['downloads'][0]['id'] );

			$output = $po -> mwp_payIn ( $purchase_data['price'], 0 );
		
			// if the merchant payment is complete, set a flag
			$merchant_payment_confirmed = $po -> result;
		}

		if ( $merchant_payment_confirmed == true) { // this is used when processing credit cards on site

			// once a transaction is successful, set the purchase to complete
			edd_update_payment_status( $payment, 'complete' );

			// record transaction ID, or any other notes you need
			edd_insert_payment_note( $payment, $output );

			// go to the success page
			edd_send_to_success_page();

		} else {
			$fail = true; // payment wasn't recorded
			edd_set_error('processing error', $output, 'edd');
		}

	} else {
		$fail = true; // errors were detected
	}

	if ( $fail !== false ) {
		// if errors are present, send the user back to the purchase page so they can be corrected
		edd_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['edd-gateway'] );
	}
}
add_action( 'edd_gateway_mangopay_gateway', 'pw_edd_process_payment' );


// adds the settings to the Payment Gateways section
function pw_edd_add_settings( $settings ) {

	$mangopay_gateway_settings = array(
		array(
			'id' => 'mangopay_gateway_settings',
			'name' => '<strong>' . __( 'Mangopay Gateway Settings', 'pw_edd' ) . '</strong>',
			'desc' => __( 'Configure the gateway settings', 'pw_edd' ),
			'type' => 'header'
		),
		array(
			'id' => 'live_api_user',
			'name' => __( 'Live API User', 'pw_edd' ),
			'desc' => __( 'Enter your live API user', 'pw_edd' ),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'live_api_key',
			'name' => __( 'Live API Key', 'pw_edd' ),
			'desc' => __( 'Enter your live API key', 'pw_edd' ),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'test_api_user',
			'name' => __( 'Test API User', 'pw_edd' ),
			'desc' => __( 'Enter your test API user', 'pw_edd' ),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'test_api_key',
			'name' => __( 'Test API Key', 'pw_edd' ),
			'desc' => __( 'Enter your test API key', 'pw_edd' ),
			'type' => 'text',
			'size' => 'regular'
		),
		array(
			'id' => 'temp_folder',
			'name' => __( 'Temp folder', 'pw_edd' ),
			'desc' => __( 'Enter a temp folder where Mangopay API can write', 'pw_edd' ),
			'type' => 'text',
			'size' => 'regular'
		)
	);

	return array_merge( $settings, $mangopay_gateway_settings );
}
add_filter( 'edd_settings_gateways', 'pw_edd_add_settings' );
