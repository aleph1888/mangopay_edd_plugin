<?php

function mwp_get_fields ( $type ) {

	switch ( $type ) {
		case "Natural":
			$yFields =  array ( "Email", "FirstName", "LastName", "Birthday", "Nationality", "CountryOfResidence" );
			break;
		case "Legal":
			//Notice LegalEmail field, that does not correspond with MangooayAPI expected. This is to make difference with Natural user due we are in the same form. Will need to manage manually across the flow. The fact is the need to install onchange event to fill Personal Field info in Crowfunding plugin so need to have two different ids.
			$yFields = array ("LegalEmail", "Name", 
				"LegalPersonType", "LegalRepresentativeFirstName", "LegalRepresentativeLastName", 
				"LegalRepresentativeBirthday", "LegalRepresentativeNationality", 
				"LegalRepresentativeCountryOfResidence");
			break;
		case "register":
			$yFields = array ("cardNumber", "cardDate", "cardCvx");
			break;
		case "pay":
			$yFields = array ( "Alias" );
			break;
		case "bank":
			$yFields = array ("OwnerName", "OwnerAddress", "IBAN", "BIC" );
	}
	return $yFields;
}

//Return true or false wether any of the specified fields has change between post and object
function mwp_has_changed_fields ( $yFields, $entity ) {
	
	foreach ( $yFields as $field ) {
		$has_changed = ( $_POST["mwp_{$field}"] && $_POST["mwp_{$field}"] != $entity -> $field );
		if ( $has_changed ) 			
			return true;
	}

	return false;
}


?>
