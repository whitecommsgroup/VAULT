<?php
		
		//	===========================================================================================================
		//	Form Validation Class
		//
		//	version 1.0 (12 January 2012)
		//
		//	DEPENDENCIES
		//	None
		//
		//	PURPOSE
		//	Helper class for Forms. Escapes user input to prevent XSS attacks and can validate entries.
		//
		//	HOW TO USE THIS CLASS
		//	$validator = new formvalidation();
		//	$validator->escapeUserInput();
		//	$validEmail = $validator->validate($_POST['email'],'email');
		//
		//	VERSION HISTORY
		//
		//	1.0		12 Jan 2012		First release
		//
		//	===========================================================================================================

        class formvalidation {
        	
			// Class Properties			

			function __construct() { 

			}
			
			public function escapeUserInput() {
				
				// =====================================================================================
				//	Escapes all POST data. This is an Anti-XSS attack measure.
				// =====================================================================================
				
				// get the keys from the input array
				$array_keys = array_keys($_POST);
				
				// if magic quotes is enabled then strip out the added slashes in all of the submitted field data				
				if (get_magic_quotes_gpc()) {	
					for( $i=0;$i<count($array_keys);$i++ ) {
					  $_POST[$array_keys[$i]] = stripslashes($_POST[$array_keys[$i]]);
					}
				}
				
				// escape the input data 
				for( $i=0;$i<count($array_keys);$i++ ) {
					$_POST[$array_keys[$i]] = trim(htmlspecialchars($_POST[$array_keys[$i]], ENT_COMPAT, 'UTF-8'));
				}
			}
			
			public function validate($val, $fieldtype = '', $low = 0, $high = 0) {
				
				// =====================================================================================
				//	Validates field data depending on the field type
				//
				//	INPUT
				//	$val = the input value
				//	$fieldtype = what sort of input field it is
				//	$low = a lower boundary
				//	$high = an upper boundary
				//
				//	RETURNS 
				//	True or False to indicate if the field validates or not
				// =====================================================================================
				
				// initialise
				$fieldtype = strtolower($fieldtype);
				$valid = false;
				
				// validate the input value based on its field type
				switch ($fieldtype) {
					case "name":
						// Peoples names are difficult to validate, so we will assume anything is allowed apart from
						// certain special characters. A length greater than 1 is also a requirement.
						if (strlen($val)>1) {
							$valid = true;
						}
						if (strpos($val,';') !== false) {
							// The data will have already been escaped, so a search for a semi-colon is a good
							// fast way of detecting a range of invalid entries
							$valid = false;
						}
						break;
					case "email":
						if(preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $val)) {
							// username: at least 1 character and it isn't an @
							// domain: at least 1 character and contains only valid characters
							// tld: at least 1 character, alpha only
							$valid = true;
						}
						break;
					case "number_range":
						// Must be a number and >= low value and <= high value
						if($val == strval(intval($val))) {
							if (($val >= $low) && ($val <= $high)) {
								$valid = true;
							}
						}
						break;
					case "telephone":
						// Validating a telephone number can only be done to a certain level.
						// The test used here is to strip out everything except numeric data and then checking that the length
						// of the numeric value is at least 7 characters.
						$numeric = preg_replace('/\D/', '', $val);
						if (strlen($numeric)>6) {
							$valid = true;
						}
						break;
					case "password":
						// Passwords must be at least 7 characters in length
						if (strlen($val)>6) {
							$valid = true;
						}
						break;
					case "embed":
						// embed codes should include an iframe tag
						if (strpos($val,'iframe') !== false) {
							$valid = true;
						}
						break;
					case "not_empty":
						if (strlen($val)>0) {
							$valid = true;
						}
						break;
				}
				
				return $valid;
			}
		
		}
?>