<?php
/**
 * Helper utilities and notifications for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

// Include the WordPress File API wrapper
require_once plugin_dir_path( __FILE__ ) . 'ee-file-class.php';


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
							<li>' . esc_html($eeValue2) . '</li>' . PHP_EOL;
						}
					} else {
						$eeOutput .= '
						<li>' . esc_html($eeValue) . '</li>' . PHP_EOL;
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
							$body .= sanitize_text_field($value2) . "\n\n";
						}
					} else {
						$body .= sanitize_text_field($value) . "\n\n";
					}
				}
			} else {
				$body = sanitize_text_field($messages) . "\n\n";
			}

			$http_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
			$php_self = isset($_SERVER['PHP_SELF']) ? sanitize_text_field(wp_unslash($_SERVER['PHP_SELF'])) : '';
			$body .= 'Via: ' . $http_host . $php_self;

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



	// File Uploader using WordPress File API
	function eeUploader($eeFile, $eePath = '') { // File Object, Path Relative to wp-content/uploads

		// Check if a file was uploaded
		if(empty($eeFile)) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// error_log('RSCF: No file uploaded or an error occurred.');
			}
			return FALSE;
		}

		// Initialize the WordPress File API wrapper
		$file_handler = new eeFile_Class();

		// Use the secure upload method
		$uploaded_url = $file_handler->handle_upload($eeFile, 'contact');

		if ($uploaded_url) {
			return $uploaded_url;
		} else {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				// error_log('RSCF: Upload Process Failed using WordPress File API');
			}
			return FALSE;
		}
	}

}

?>