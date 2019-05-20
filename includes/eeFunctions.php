<?php
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeAwesomeness' )) exit('That is Noncense!'); // Exit if nonce fails


function eeRSCF_ValidateEmailAddresses($eeString) { // NOT USED YET
	
	if(strpos(@$_POST['eeAdmin' . $to . $i], ',')) { // More than one address
						
		$this->log[] = 'Multiple address for ' . $to . ' field.';
		
		$emails = explode(',', $_POST['eeAdmin' . $to . $i]); // Make array
		
		foreach($emails as $email) { // Loop through them
			
			$email = trim($email); // Trim spaces
			
			if(filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate address
				$departmentSet .= $email . ','; // Assemble addresses for storage
			} else {
				$this->errors[] = 'Bad ' . $to . ' Address: ' . $email;
			}
		}
		$departmentSet = substr($departmentSet, 0, -1); // Clip the last comma
		
	} elseif(@$_POST['eeAdmin' . $to . $i]) { // Just one address
		
		$this->log[] = 'Single address for ' . $to . ' field.';
		
		if(filter_var($_POST['eeAdmin' . $to . $i], FILTER_VALIDATE_EMAIL)) {
			$departmentSet .= $_POST['eeAdmin' . $to . $i]; // Assemble addresses for storage
		} else {
			$this->errors[] = 'Bad ' . $to . ' Address: ' . $_POST['eeAdmin' . $to . $i];
		}
	}
}





// Write log file
function eeRSCF_WriteLogFile($eeLog) {
	
	if($eeLog) {
		
		$eeLogFile = plugin_dir_path( __FILE__ ) . 'logs/eeLog.txt';
		
		// File Size Management
		$eeLimit = 262144; // 262144 = 256kb  1048576 = 1 MB
		$eeSize = @filesize($eeLogFile);
		
		if(@filesize($eeLogFile) AND $eeSize > $eeLimit) {
			unlink($eeLogFile); // Delete the file. Start Anew.
		}
		
		// Write the Log Entry
		if($handle = @fopen($eeLogFile, "a+")) {
			
			if(@is_writable($eeLogFile)) {
			    
				fwrite($handle, 'Date: ' . date("Y-m-d H:i:s") . "\n");
			    
			    foreach($eeLog as $key => $logEntry){
			    
			    	if(is_array($logEntry)) { 
				    	
				    	foreach($logEntry as $key2 => $logEntry2){
					    	fwrite($handle, '(' . $key2 . ') ' . $logEntry2 . "\n");
					    }
					    
				    } else {
					    fwrite($handle, '(' . $key . ') ' . $logEntry . "\n");
				    }
			    }
			    	
			    fwrite($handle, "\n\n\n---------------------------------------\n\n\n"); // Separator
			
			    fclose($handle);
			    
			    return TRUE;
			 
			} else {
			    return FALSE;
			}
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

?>