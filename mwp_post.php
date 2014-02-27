<?php

/**
* Withdraw metabox in post edit sidebar.
**/
// User can withdraw if his role is author or up...
function mwp_current_user_can_withdraw () {
	$user = wp_get_current_user();
	return $user-> roles[0] != "suscriber" && $user-> roles[0] != "contributor";

}

add_action( 'submitpost_box', 'mwp_show_post_fields' );

function print_meta_box ( $post ) {

	//print previous messages (mwp_payout.php)
	echo $_SESSION["payout_result"];
	$_SESSION["payout_result"] = null;
	echo $_SESSION["MWP_API_ERROR"];
	$_SESSION["MWP_API_ERROR"] = null;

	//Search for wallet
	$wallet_id = get_post_meta( $post-> ID, "wallet_id", 1);
	if ( $wallet_id ) {
		$wallet = mwp\mwp_api::get_instance()->Wallets->Get($wallet_id);
	}

	//Display info
	if ( ! $wallet || $wallet->Balance->Amount == 0) {
		_e( "no_contributions", 'mangopay_wp_plugin');
	} else {
		
		//Total
		echo __( "<h2>" . 'Total: ', 'mangopay_wp_plugin') . 
			 $wallet->Balance->Amount / 100 .
			 __( 'eur', 'mangopay_wp_plugin') . "</h2>\n";
		
		//Bankaccount
		require_once __DIR__ . "/includes/mwp_payout.inc";
		$payout = new mwp\mwp_payout;
		echo $payout -> mwp_bankaccount_get_info ( $post );
	}
}

function mwp_show_post_fields( $post) { 
	$user = get_userdata( $post -> post_author );
	if ( wp_get_current_user() == $user )
		add_meta_box( $post->ID, __( "post_fields_title", 'mangopay_wp_plugin'), "print_meta_box", 'download', 'side', 'high');

}
