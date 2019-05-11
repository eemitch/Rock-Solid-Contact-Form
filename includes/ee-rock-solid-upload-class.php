<?php // eeRSCF File Uploading Classes - mitchellbennis@gmail.com
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
class eeRSCF_FileUpload {

	// Properties ------------------------------------
	public $uploadFolderName = 'eeRSCF_FileUploads';
	public $fileUploaded = FALSE;
	public $maxUploadLimit;
	private $uploadDir;
	public $uploadUrl;
	
	// METHODS ---------------------------------


	// Class setup
	public function eeRSCF_Setup() {
		
		global $eeContactForm;
		
		$eeContactForm->log[] = 'Running upload setup...';
		
		$uploadDirArray = wp_upload_dir();
		
		$this->uploadDir = $uploadDirArray['basedir'] . '/' . $this->uploadFolderName . '/';
		$eeContactForm->log[] = 'Upload Directory: ' . $this->uploadDir;
		
		$this->uploadUrl = $uploadDirArray['baseurl'] . '/' . $this->uploadFolderName . '/';
		$eeContactForm->log[] = 'Upload URL: ' . $this->uploadUrl;
		
		self::eeRSCF_DetectUploadLimit();
		
		if(!is_dir($this->uploadDir)) {
			self::eeRSCF_CreateUploadDir();
		}
	}

	// Detect max upload size.
	private function eeRSCF_DetectUploadLimit() {
		
		$upload_max_filesize = substr(ini_get('upload_max_filesize'), 0, -1); // Strip off the "M".
		$post_max_size = substr(ini_get('post_max_size'), 0, -1); // Strip off the "M".
		if ($upload_max_filesize <= $post_max_size) { // Check which is smaller, upload size or post size.
			$this->maxUploadLimit = $upload_max_filesize;
		} else {
			$this->maxUploadLimit = $post_max_size;
		}
	}
	
	// Create the upload folder if required.
	private function eeRSCF_CreateUploadDir() {
	
		$eeContactForm->log[] = 'Creating the upload directory.';
		
		if(!@is_writable($this->uploadDir)) {
			$eeContactForm->log[] = 'No Upload Directory Found.';
			$eeContactForm->log[] = 'Creating Upload Directory ...';
			
			if(!@mkdir($this->uploadDir, 0755)) {
				$eeContactForm->errors = 'Could not create the upload directory: ' . $this->uploadDir;
				return FALSE;
			
			} else {
				
				if(!@is_writable($this->uploadDir)) {
					$eeContactForm->errors = 'Upload directory not writable: ' . $this->uploadDir;
				} else {
					return TRUE;
				}
			}
		} else {
			$eeContactForm->log[] = 'Upload Folder: ' . $this->uploadDir;
			return TRUE;
		}
	}

	
	private function eeRSCF_BytesToSize($bytes, $precision = 2) {  
	    
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
	
	
	public function eeRSCF_Uploader() {
	
		global $eeContactForm;
		
		$eeContactForm->log[] = 'Preparing for the upload...';
		
		if($_FILES['file']['name']) {
			
			$eeContactForm->log[] = 'File Name: ' . $_FILES['file']['name'];
			
			$time = date('m-d-Y_G-i-s'); // We'll add a timestamp so files don't get overwritten.
			
			// Get the original file extension
			$fileName = $_FILES['file']['name'];
			
			$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
			$ext = '.' . $ext;
			
			$eeContactForm->log[] = 'File Ext: ' . $ext;
			
			// Make array and remove white space from array values
			$formatsArray = explode(',', $eeContactForm->formats);
			$formatsArray = array_filter(array_map('trim', $formatsArray));
			
			$eeContactForm->log[] = 'Allowed Formats: ' . implode(', ', $formatsArray);
			
			// Only allow allowed files, ah?
			if (in_array($ext,$formatsArray)) {
			
				$eeContactForm->log[] = 'Beginning the upload...';
				
				// File Naming
				$fileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME) . '_(' . $time . ')'; 
				$fileName = str_replace(' ', '_', $fileName); // Replace any spaces with underscores
			
				$newFile = $fileName . $ext;  // Assemble new file name and extension.
				$targetPath = $this->uploadDir . basename($newFile); // Define where the file will go.
			
				if (@move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) { // Move the file to the final destination
						
					$this->fileUploaded = $this->uploadUrl . $newFile;
					$eeContactForm->log[] = "File Uploaded: " . $newFile . " \n\n(" . self::eeRSCF_BytesToSize(filesize($this->uploadDir . $newFile)) . ')';
					return TRUE;
					
				} else { // Upload Problem
					
					$eeContactForm->errors[] = 'No file was uploaded';
					
					switch ($_FILES['file']['error']) {
						case 1:
							// The file exceeds the upload_max_filesize setting in php.ini
							$eeContactForm->errors[] = 'File Too Large - Please resize your file to meet the file size limit.';
							break;
						case 2:
							// The file exceeds the MAX_FILE_SIZE setting in the HTML form
							$eeContactForm->errors[] = 'File Too Large - Please resize your file to meet the file size limits.';
							break;
						case 3:
							// The file was only partially uploaded
							$eeContactForm->errors[] = 'Upload Interrupted - Please back up and try again.';
							break;
						case 4:
							// No file was uploaded
							$eeContactForm->errors[] = 'No File was Uploaded - Please back up and try again.';
							break;
					}
					
				}
			} else {
				$eeContactForm->errors[] = 'Sorry, the file type being uploaded is not accepted by this website.';
				$eeContactForm->errors[] = "Your Format: $ext";
				$eeContactForm->errors[] = 'Allowed Formats: ' . implode(', ', $formatsArray);
			}
			
			
		} else {
			$eeContactForm->errors[] = 'No file reference.';
		}
					
		$eeContactForm->log[] = $eeContactForm->errors;
			
	} // END uploader

}	
	
?>