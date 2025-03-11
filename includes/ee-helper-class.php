<?php // ee-classes.php version 1.0.0 - mitchellbennis@gmail.com
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
class eeHelper_Class {
	
	// GENERAL -------------
	
	// Create a slug
	public function eeMakeSlug($string){
	   $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	   $slug = strtolower($slug);
	   return $slug;
	}
	
	// Undo a Slug
	public function eeUnSlug($slug){
	   $string = str_replace('-', ' ', $slug);
	   $string = ucwords($string);
	   return $string;
	}
	
	
	
	// NOTIFICATIONS
	
	
	// Results Notice Display
	public function eeRSCF_ResultsNotification() {
		
		$eeOutput = '';
		
		$eeLogParts = array('errors' => 'error', 'warnings' => 'warning', 'messages' => 'success');
		
		foreach($eeLogParts as $eePart => $eeType) {
			
			if(!empty($this->log[$eePart])) {
			
				$eeOutput .= '<div class="';
				
				if( is_admin() ) {
					$eeOutput .=  'notice notice-' . $eeType . ' is-dismissible';
				} else {
					$eeOutput .= 'eeResultsNotification eeResultsNotification_' . $eeType;
				}
				
				$eeOutput .= '">
				<ul>';
				
				foreach($this->log[$eePart] as $eeValue) { // We can go two-deep arrays
					
					if(is_array($eeValue)) {
						foreach ($eeValue as $eeValue2) {
							$eeOutput .= '
							<li>' . $eeValue2 . '</li>' . PHP_EOL;
						}
					} else {
						$eeOutput .= '
						<li>' . $eeValue . '</li>' . PHP_EOL;
					}
				}
				$eeOutput .= '
				</ul>
				</div>';
				
				$this->log[$eePart] = array(); // Clear this part fo the array
				
			}
		}
		
		return $eeOutput;
	
	}
	
	
	// Notice Email
	function eeRSCF_NoticeEmail($messages, $to, $from, $name = '') {
		
		if($messages AND $to AND $from) {
			
			$body = '';
			$headers = "From: $from";
			$subject = $name . " Admin Notice";
			
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
		
			if(!mail($to,$subject,$body,$headers)) { // Email the message or error report
				?><script>alert('EMAIL SEND FAILED');</script><?php
			}
		
		} else {
			?><script>alert('EMAIL SEND FAILED');</script><?php
		}
		
		return FALSE;		
	}
	
	
	
	
	// FILE UPLOADS ------------------------------------------------------------------------
	public $maxUploadLimit = 8;
	
	
	// Detect max upload size.
	public function eeDetectUploadLimit() {
		
		global $eeRSCF;
		
		$upload_max_filesize = substr(ini_get('upload_max_filesize'), 0, -1); // Strip off the "M".
		$post_max_size = substr(ini_get('post_max_size'), 0, -1); // Strip off the "M".
		if ($upload_max_filesize <= $post_max_size) { // Check which is smaller, upload size or post size.
			$this->maxUploadLimit = $upload_max_filesize;
		} else {
			$this->maxUploadLimit = $post_max_size;
		}
		
		$eeRSCF->log['notices'][] = 'Upload Limit: ' . $this->maxUploadLimit;
		
		return $this->maxUploadLimit;
	}
	
	
	private function eeBytesToSize($bytes, $precision = 2) {  
		
		$kilobyte = 1024;
		$megabyte = $kilobyte * 1024;
		$gigabyte = $megabyte * 1024;
		$terabyte = $gigabyte * 1024;
	   
		if (($bytes >= 0) && ($bytes < $kilobyte)) {
			return $bytes . ' B';
	 
		} elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
			return round($bytes / $kilobyte, $precision) . ' KB';
	 
		} elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
			return round($bytes / $megabyte, $precision) . ' MB';
	 
		} elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
			return round($bytes / $gigabyte, $precision) . ' GB';
	 
		} elseif ($bytes >= $terabyte) {
			return round($bytes / $terabyte, $precision) . ' TB';
		} else {
			return $bytes . ' B';
		}
	}
	
	
	
	// File Uploader
	function eeUploader($eeFile, $eePath = '') { // File Object, Path Relative to wp-content/uploads
		
		// Check if a file was uploaded
		if(empty($eeFile)) {
			trigger_error('No file uploaded or an error occurred.');
			return FALSE;
		}
		
		// Get upload directory info
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir']; // Absolute base directory
		$base_url = $upload_dir['baseurl']; // Base URL
		
		// Ensure the directory exists, create if necessary
		if (!is_dir($base_dir . '/' . $eePath)) {
			if (!wp_mkdir_p($base_dir . '/' . $eePath)) {
				trigger_error('Failed to create upload directory: ' . $base_dir . '/' . $eePath, E_USER_WARNING);
				return FALSE;
			}
		}
		
		// Prevent directory traversal
		$given_path = $base_dir . '/' . $eePath; // Remove slashes to avoid double slashes
		$resolved_path = realpath($base_dir . '/' . $eePath);
		if($resolved_path === false || strpos($resolved_path, $given_path) !== 0) {
			trigger_error('Invalid upload directory: ' . $resolved_path, E_USER_WARNING);
			return FALSE;
		}
	
		// Get file details
		$file_name = sanitize_file_name($eeFile['name']);
		$file_tmp = $eeFile['tmp_name'];
		$file_size = $eeFile['size'];
		$file_type = pathinfo($file_name, PATHINFO_EXTENSION);
	
		// Generate a unique file name
		$unique_file_name = wp_unique_filename($eePath, $file_name);
		$file_destination = $resolved_path . '/' . $unique_file_name;
	
		// Move file to upload directory
		if(move_uploaded_file($file_tmp, $file_destination)) {
			return str_replace($base_dir, $base_url, $file_destination); // Return the URL
		} else {
			trigger_error('Upload Process Failed');
			return FALSE;
		}
	}

}
	
?>