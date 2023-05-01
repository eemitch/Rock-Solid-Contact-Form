<?php
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeRSCF_Nonce' )) exit('That is Noncense!'); // Exit if nonce fails


function eeDevOutput($eeArray) {
	return PHP_EOL . "<script>console.table(" . json_encode($eeArray) . ")</script>" . PHP_EOL;
}



// Update or Install New
function eeRSCF_UpdatePlugin() {
	
	global $eeRSCF;
	
	if(get_option('eeRSCF_version')) {
		
		echo '<pre>'; print_r($eeRSCF->formSettings); echo '</pre>'; exit;
		
		// update_option('eeRSCF_Version' , eeRSCF_Version);
		
	} else {
		
		$eeSettingsArray = $eeRSCF->contactFormDefault;
		
		$eeSettingsArray['to'] = get_option('admin_email');
		$eeSettingsArray['email'] = $eeSettingsArray['to'];
		$eeSettingsArray['emailName'] = get_bloginfo('name');
		
		add_option('eeRSCF_Settings_1', $eeSettingsArray);
		add_option('eeRSCF_Version' , eeRSCF_Version);
		add_option('eeRSCF_AUTH' , eeRSCF_AUTH);
		
		$eeRSCF->formSettings = $eeSettingsArray;
		
	}
		
	return TRUE;
}




// Get Common Words from EE Server
function eeGetRemoteSpamWords($eeUrl) {
  
  // Try to get the content using file_get_contents()
  $eeContent = @file_get_contents($eeUrl);

  // If file_get_contents() fails, try to get the content using curl
  if (!$eeContent) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $eeUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$eeContent = curl_exec($ch);
	curl_close($ch);
  }

  return $eeContent;
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





// TO DO
// function eeRSCF_SMTP() {
// 	
// 	$eeEmail = get_option('eeRSCF_email');
// 	
// 	if(filter_var($eeEmail, FILTER_VALIDATE_EMAIL)) {
// 		
// 		// Define SMTP Settings
// 		global $phpmailer;
// 		
// 		if ( !is_object( $phpmailer ) ) {
// 			$phpmailer = (object) $phpmailer;
// 		}
// 		
// 		$phpmailer->Mailer = 'smtp';
// 		$phpmailer->isHTML(FALSE);
// 		
// 		$phpmailer->isSMTP();
// 		$phpmailer->From       = $eeEmail;
// 		$phpmailer->FromName   = get_option('eeRSCF_emailName');
// 		$phpmailer->Host       = get_option('eeRSCF_emailServer');
// 		$phpmailer->Username   = get_option('eeRSCF_emailUsername');
// 		$phpmailer->Password   = get_option('eeRSCF_emailPassword');
// 		$phpmailer->Sender     = get_option('eeRSCF_emailUsername');
// 		$phpmailer->ReturnPath = get_option('eeRSCF_emailUsername');
// 		$phpmailer->SMTPSecure = get_option('eeRSCF_emailSecure');
// 		$phpmailer->Port       = get_option('eeRSCF_emailPort');
// 		$phpmailer->SMTPAuth   = TRUE; // get_option('eeRSCF_emailAuth');
// 		$phpmailer->SMTPDebug  = 3;
// 		
// 		if(get_option('emailFormat') == 'HTML') {
// 			$phpmailer->isHTML(TRUE);
// 			$phpmailer->msgHTML = $body;
// 			$phpmailer->Body = nl2br($body);
// 		}
// 		
// 		echo '<pre>'; print_r($phpmailer); echo '</pre>'; exit;
// 	
// 	}
// }

?>