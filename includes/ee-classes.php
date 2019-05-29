<?php // ee-classes.php version 1.0.0 - mitchellbennis@gmail.com
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
class eeRSCF_MessageDisplay {
	
	// Problem Display / Error reporting
	public function display($messages) {
		
		if(is_array($messages)) {
			echo '<div class="eeMessageDisplay"><ul>'; // Loop through array
			foreach($messages as $key => $value) { 
				if(is_array($value)) {
					foreach ($value as $value2) {
						if(is_array($value2)) {
							foreach ($value2 as $value3) {
								echo "<li>$value3</li>\n";
							}
						} else {
							echo "<li>$value2</li>\n";
						}
					}
				} else {
					echo "<li>$value</li>\n";
				}
			}
			echo "</ul></div>\n\n";
		} else {
			echo '<p>' . $messages . '</p>';
		}
	}	
}

class eeRSCF_NoticeEmail {

	function noticeEmail($messages, $to, $from, $name, $mode = 'standard') {
		
		if($to AND $from) {
			
			$body = '';
			$headers = "From: $from";
			
			if($mode == 'error') {
				$subject = $name . " Error";
			} else {
				$subject = $name . " Admin Notice";
			}
			
			if(is_array($messages)) {
				foreach ($messages as $value) {
					if(is_array($value)) {
						foreach ($value as $value2) {
							$body .= $value2 . "\n\n";
						}
					} else {
						$body .= $value . "\n\n";
					}
				}
			} else {
				$body = $messages . "\n\n";
			}
			
			$body .= 'Via: ' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		
			if(!wp_mail($to,$subject,$body,$headers)) { // Email the message or error report
				?><script>alert('EMAIL SEND FAILED');</script><?php
			}
		
		} else {
			?><script>alert('EMAIL SEND FAILED');</script><?php
		}
		
		return FALSE;		
	}

}
	
	
?>