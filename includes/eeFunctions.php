<?php
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeRSCF_Nonce' )) exit('That is Noncense!'); // Exit if nonce fails


function eeDevOutput($eeArray) {
	return PHP_EOL . "<script>console.table(" . json_encode($eeArray) . ")</script>" . PHP_EOL;
}



// Update or Install New
function eeRSCF_UpdatePlugin() {
	
	return TRUE;
	
	global $eeRSCF;
	
	$eeVersion = get_option('eeRSCF_version');
	
	if( $eeVersion AND version_compare($eeVersion, eeRSCF_Version, '<') ) {
		
		delete_option('eeRSCF_spamBlockedCommonWords'); // We get this remote now
		
		$eeRSCF->formSettings = $eeRSCF->contactFormDefault;
		
		foreach($eeRSCF->formSettings as $eeKey => $eeValue) {
			
			$eeSetting = get_option('eeRSCF_' . $eeKey);
			if($eeSetting !== FALSE) {
				$eeRSCF->formSettings[$eeKey] = $eeSetting;
				delete_option('eeRSCF_' . $eeKey);
			}
		}
		
		$eeArray = get_option('eeRSCF_1');
		if(is_array($eeArray)) {
			
			$eeRSCF->formSettings['name'] = $eeArray['name'];
			if(isset($eeArray['to'])) { $eeRSCF->formSettings['to'] = $eeArray['to']; } 
				elseif(isset($eeArray['TO'])) { $eeRSCF->formSettings['to'] = $eeArray['TO']; }
			if(isset($eeArray['cc'])) { $eeRSCF->formSettings['cc'] = $eeArray['cc']; } 
				elseif(isset($eeArray['CC'])) { $eeRSCF->formSettings['cc'] = $eeArray['CC']; }
			if(isset($eeArray['bcc'])) { $eeRSCF->formSettings['bcc'] = $eeArray['bcc']; } 
				elseif(isset($eeArray['BCC'])) { $eeRSCF->formSettings['bcc'] = $eeArray['BCC']; }
			$eeRSCF->formSettings['fields'] = $eeArray['fields'];
			if(isset($eeArray['confirm'])) { $eeRSCF->formSettings['confirm'] = $eeArray['confirm']; }
		}
		
		// echo '<pre>'; print_r($eeRSCF->formSettings); echo '</pre>'; exit;
		
		delete_option('eeRSCF_1');
		delete_option('eeRSCF_spamSendAttackNoticeToDeveloper');
		delete_option('eeRSCF_version');
		delete_option('eeRSCF_AUTH');
		delete_option('eeRSCF_eeContactFormOld');
		
		update_option('eeRSCF_Settings_1', $eeRSCF->formSettings);
		update_option('eeRSCF_Version' , eeRSCF_Version);
		
	} elseif(!$eeVersion) {
		
		$eeSettingsArray = $eeRSCF->contactFormDefault;
		
		$eeSettingsArray['to'] = get_option('admin_email');
		$eeSettingsArray['email'] = $eeSettingsArray['to'];
		$eeSettingsArray['emailName'] = get_bloginfo('name');
		
		add_option('eeRSCF_Settings_1', $eeSettingsArray);
		add_option('eeRSCF_Version' , eeRSCF_Version);
		
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