<?php // ee-classes.php version 1.0.0 - mitchellbennis@gmail.com
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
class eeHelper_Class {
	
	// GENERAL -------------
	
	// Create a slug
	public function eeRSCF_MakeSlug($string){
	   $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	   $slug = strtolower($slug);
	   return $slug;
	}
	
	// Undo a Slug
	public function eeRSCF_UnSlug($slug){
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
	
	public $uploadFolderName = eeRSCF_SLUG; // Folder will be created in the WP uploads folder
	public $fileUploaded = FALSE;
	public $maxUploadLimit = 8;
	public $uploadDir = '';
	public $uploadUrl = '';
	
	
	public function eeHelper() {
		
		global $eeRSCF;
		
		$eeRSCF->log['notices'][] = 'Running upload setup...';
		
		$uploadDirArray = wp_upload_dir();
		
		$this->uploadDir = $uploadDirArray['basedir'] . '/' . $this->uploadFolderName . '/';
		$eeRSCF->log['notices'][] = 'Upload Directory: ' . $this->uploadDir;
		
		$this->uploadUrl = $uploadDirArray['baseurl'] . '/' . $this->uploadFolderName . '/';
		$eeRSCF->log['notices'][] = 'Upload URL: ' . $this->uploadUrl;
		
		$this->eeRSCFU_DetectUploadLimit();
		
		if(!is_dir($this->uploadDir)) {
			$this->eeRSCFU_CreateUploadDir();
		}
	}
	
	
	
	
	
	
	// Detect max upload size.
	public function eeRSCFU_DetectUploadLimit() {
		
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
	
	
	
	
	
	
	// Create the upload folder if required.
	private function eeRSCFU_CreateUploadDir() {
	
		global $eeRSCF;
		
		$upload_dir = wp_upload_dir();
		$eeRSCF_UploadDir = $upload_dir['basedir'] . '/' . $this->uploadFolderName;
		
		$eeRSCF->log['notices'][] = 'Checking Folder...';
		$eeRSCF->log['notices'][] = $eeRSCF_UploadDir;
		
		if(strlen($eeRSCF_UploadDir)) {
			
			if(!is_writable($eeRSCF_UploadDir)) {
				
				$eeRSCF->log['notices'][] = 'No Directory Found.';
				$eeRSCF->log['notices'][] = 'Creating Upload Directory ...';
				
				// Environment Detection
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					$eeRSCF->log['notices'][] = 'Windows detected.';
					mkdir($eeRSCF_UploadDir); // Windows
				} else {
					$eeRSCF->log['notices'][] = 'Linux detected.';
					if(!mkdir($eeRSCF_UploadDir, 0755)) { // Linux - Need to set permissions
						$eeRSCF_Log['errors'][] = 'Cannot Create: ' . $eeRSCF_UploadDir;
					}
				}
				
				if(!is_writable($eeRSCF_UploadDir)) {
					$eeRSCF_Log['errors'][] = 'ERROR: I could not create the upload directory: ' . $eeRSCF_UploadDir;
					
					return FALSE;
				
				} else {
					
					$eeRSCF->log['notices'][] = 'Looks Good';
				}
			} else {
				$eeRSCF->log['notices'][] = 'Looks Good';
			}
			
			// Check index.html, create if needed.
					
			$eeFile = $eeRSCF_UploadDir . '/index.html'; // Disallow direct file indexing.
			
			if($handle = fopen($eeFile, "a+")) {
				
				if(!is_readable($eeFile)) {
					
					$eeRSCF_Log['errors'][] = 'ERROR: Could not write index.html';
					
					return FALSE;
					
				} else {
					
					fclose($handle);
				}
			}
			
		} else {
			$eeRSCF_Log['errors'] = 'No upload directory defined';
					
			return FALSE;
		}
		
		$this->uploadDir = $eeRSCF_UploadDir;
		
		return TRUE;
		
	}
	
	
	private function eeRSCFU_BytesToSize($bytes, $precision = 2) {  
		
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
	
	
	public function eeRSCFU_Uploader() {
	
		global $eeRSCF;
		
		$eeRSCF->log['notices'][] = 'Preparing for the upload...';
		
		if($_FILES['file']['name']) {
			
			$eeRSCF->log['notices'][] = 'File Name: ' . $_FILES['file']['name'];
			
			$time = date('m-d-Y_G-i-s'); // We'll add a timestamp so files don't get overwritten.
			
			// Get the original file extension
			$fileName = $_FILES['file']['name'];
			
			$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
			$ext = '.' . $ext;
			
			$eeRSCF->log['notices'][] = 'File Ext: ' . $ext;
			
			// Make array and remove white space from array values
			$formatsArray = explode(',', $eeRSCF->formSettings['fileFormats']);
			$formatsArray = array_filter(array_map('trim', $formatsArray));
			
			$eeRSCF->log['notices'][] = 'Allowed Formats: ' . implode(', ', $formatsArray);
			
			// Only allow allowed files, ah?
			if (in_array($ext,$formatsArray)) {
			
				$eeRSCF->log['notices'][] = 'Beginning the upload...';
				
				// File Naming
				$fileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) . '_(' . $time . ')'; 
				$fileName = str_replace(' ', '_', $fileName); // Replace any spaces with underscores
			
				$newFile = $fileName . $ext;  // Assemble new file name and extension.
				$targetPath = $this->uploadDir . basename($newFile); // Define where the file will go.
			
				if (@move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) { // Move the file to the final destination
						
					$this->fileUploaded = $this->uploadUrl . $newFile;
					$eeRSCF->log['notices'][] = "File Uploaded: " . $newFile . " \n\n(" . self::eeRSCFU_BytesToSize(filesize($this->uploadDir . $newFile)) . ')';
					return TRUE;
					
				} else { // Upload Problem
					
					$eeRSCF->log['errors'][] = 'No file was uploaded';
					
					switch ($_FILES['file']['error']) {
						case 1:
							// The file exceeds the upload_max_filesize setting in php.ini
							$eeRSCF->log['errors'][] = 'File Too Large - Please resize your file to meet the file size limit.';
							break;
						case 2:
							// The file exceeds the MAX_FILE_SIZE setting in the HTML form
							$eeRSCF->log['errors'][] = 'File Too Large - Please resize your file to meet the file size limits.';
							break;
						case 3:
							// The file was only partially uploaded
							$eeRSCF->log['errors'][] = 'Upload Interrupted - Please back up and try again.';
							break;
						case 4:
							// No file was uploaded
							$eeRSCF->log['errors'][] = 'No File was Uploaded - Please back up and try again.';
							break;
					}
					
				}
			} else {
				$eeRSCF->log['errors'][] = 'Sorry, the file type being uploaded is not accepted by this website.';
				$eeRSCF->log['errors'][] = "Your Format: $ext";
				$eeRSCF->log['errors'][] = 'Allowed Formats: ' . implode(', ', $formatsArray);
			}
			
			
		} else {
			$eeRSCF->log['errors'][] = 'No file reference.';
		}
					
		$eeRSCF->log['notices'][] = $eeRSCF->log['errors'];
			
	} // END uploader
	

}
	
	
?>