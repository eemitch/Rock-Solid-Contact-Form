<?php
/**
 * Core utility functions for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Shortcode - Usage: [rock-solid-contact]
function eeRSCF_FrontEnd($atts, $content = null) {
	global $eeRSCF;
	return $eeRSCF->eeRSCF_formDisplay(1);
}


// Catch the POST and process it
function eeRSCF_ContactProcess() {
	global $eeRSCF, $eeHelper;
	if(wp_verify_nonce($_POST['ee-rock-solid-nonce'], 'ee-rock-solid')) { // Front-end
		$eeRSCF->eeRSCF_SendEmail();
	}
}


// Load frontend assets
function eeRSCF_Enqueue() {
	// Register and enqueue CSS with proper versioning
	wp_enqueue_style(
		'rock-solid-contact-form-css',
		plugin_dir_url(__FILE__) . '../css/style.css',
		array(),
		'2.1.2'
	);

	// Register and enqueue JavaScript with proper dependencies and versioning
	wp_enqueue_script(
		'rock-solid-contact-form-js',
		plugin_dir_url(__FILE__) . '../js/scripts.js',
		array('jquery'),
		'2.1.2',
		true
	);
}



// Load stuff we need in the Admin head
function eeRSCF_AdminEnqueue($eeHook) {
	// Only load scripts if on the Rock Solid Contact Form admin pages
	$eeHooks = array(
		'toplevel_page_rock-solid-contact-form'
	);

	if(in_array($eeHook, $eeHooks)) {
		// Admin CSS with proper versioning
		wp_enqueue_style(
			'rock-solid-contact-form-admin-style',
			plugins_url( '../css/style-admin.css', __FILE__ ),
			array(),
			'2.1.2'
		);

		// Admin JavaScript with proper versioning and dependencies
		wp_enqueue_script(
			'rock-solid-contact-form-admin-js',
			plugins_url('../js/scripts-admin.js', __FILE__),
			array('jquery'),
			'2.1.2',
			false
		);

		wp_enqueue_script(
			'rock-solid-contact-form-admin-js-footer',
			plugins_url('../js/scripts-admin-footer.js', __FILE__),
			array('jquery'),
			'2.1.2',
			true
		);
	}
}


// Admin Menu
function eeRSCF_BackEnd() {

	global $eeRSCF;

	$eeRSCF_Nonce = wp_create_nonce('eeRSCF_Nonce'); // Security
	include_once(plugin_dir_path(__FILE__) . '../includes/ee-settings.php'); // Admin's List Management Page

	// Top-Level Menu Addition
	add_menu_page(__('Rock Solid Contact Form', 'rock-solid-contact-form'), __('Contact Form', 'rock-solid-contact-form'), 'edit_posts', 'rock-solid-contact-form', 'eeRSCF_Settings', '
dashicons-email');

}


// Log Failed Emails
function eeRSCF_Failed($wp_error) {
	return error_log(print_r($wp_error, true));
}
add_action('wp_mail_failed', 'eeRSCF_Failed', 10, 1);




function eeDevOutput($eeArray) {
	return PHP_EOL . "<script>console.table(" . json_encode($eeArray) . ")</script>" . PHP_EOL;
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



?>