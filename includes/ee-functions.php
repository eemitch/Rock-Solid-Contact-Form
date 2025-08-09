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

	// Validate global object exists
	if ( ! isset( $eeRSCF ) || ! is_object( $eeRSCF ) ) {
		return '<p>Contact form is temporarily unavailable.</p>';
	}

	return $eeRSCF->eeRSCF_formDisplay(1);
}

// Contact form processing
function eeRSCF_ContactProcess() {
	global $eeRSCF, $eeHelper;

	// Validate nonce before processing
	if ( ! isset( $_POST['ee-rock-solid-nonce'] ) || ! wp_verify_nonce( $_POST['ee-rock-solid-nonce'], 'ee-rock-solid' ) ) {
		wp_die( 'Security check failed. Please try again.', 'Security Error', array( 'response' => 403 ) );
	}

	// Validate global objects exist
	if ( ! isset( $eeRSCF ) || ! is_object( $eeRSCF ) ) {
		wp_die( 'Contact form is temporarily unavailable.', 'Error', array( 'response' => 500 ) );
	}

	$eeRSCF->eeRSCF_SendEmail();
}


// Load frontend assets - Only load when shortcode is used
function eeRSCF_Enqueue() {
	// Only enqueue if we're on a page that uses the shortcode
	global $post;

	if ( ! is_admin() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'rock-solid-contact' ) ) {
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
}

// Admin assets - Load only on plugin pages
function eeRSCF_AdminEnqueue($hook_suffix) {
	// Only load scripts if on the Rock Solid Contact Form admin pages
	$allowed_hooks = array(
		'toplevel_page_rock-solid-contact-form'
	);

	if ( ! in_array( $hook_suffix, $allowed_hooks, true ) ) {
		return;
	}

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
// Admin Menu
function eeRSCF_BackEnd() {
	// Check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $eeRSCF;

	$eeRSCF_Nonce = wp_create_nonce('eeRSCF_Nonce'); // Security
	include_once(plugin_dir_path(__FILE__) . '../includes/ee-settings.php'); // Admin's List Management Page

	// Top-Level Menu Addition with proper capability check
	add_menu_page(
		__('Rock Solid Contact Form', 'rock-solid-contact-form'),
		__('Contact Form', 'rock-solid-contact-form'),
		'manage_options',
		'rock-solid-contact-form',
		'eeRSCF_Settings',
		'dashicons-email'
	);
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



// Update or Install New
function eeRSCF_UpdatePlugin() {

	global $eeRSCF;

	$eeRSCF->formSettings = get_option('eeRSCF_Settings_1');
	$eeVersion = get_option('eeRSCF_Version');
	if($eeVersion == eeRSCF_Version) { return TRUE; } // Return if we're good.

	if($eeRSCF->formSettings OR $eeVersion) { // Installed

		if(version_compare($eeVersion, eeRSCF_Version, '<') ) {

			$eeRSCF->formSettings = get_option('eeRSCF_Settings_1');

			if(!empty($eeRSCF->formSettings)) {

				// echo '<pre>'; print_r($eeRSCF->formSettings); echo '</pre>'; exit;

				// Move the confirmation URL to its own option
				if($eeRSCF->formSettings['confirm']) {
					$eeRSCF->confirm = $eeRSCF->formSettings['confirm'];
				}

				// Complete missing FROM address if needed
				if(empty($eeRSCF->formSettings['email'])) {
					$eeRSCF->formSettings['email'] = get_option('admin_emial');
				}

				// Get rid of dots in file types
				$formats = $eeRSCF->formSettings['fileFormats'];
				$formats = preg_replace('/\s*\.([a-z0-9]+)/i', ' $1', $formats);
				$formats = trim($formats);

				$eeRSCF->formSettings['fileFormats'] = $formats;

				// Out with the Old...
				global $wpdb;
				$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'eeRSCF_%'");
				unset($eeRSCF->formSettings['name']);
				unset($eeRSCF->formSettings['confirm']);

				// In with the New
				update_option('eeRSCF_Version' , eeRSCF_Version);
				update_option('eeRSCF_Settings', $eeRSCF->formSettings); // In with the New
				update_option('eeRSCF_Confirm', $eeRSCF->confirm);

				return TRUE;
			}
		}

	} else { // New Installation

		// Install Settings
		if(empty($eeRSCF->formSettings)) {
			$eeRSCF->contactFormDefault['to'] = get_option('admin_email');
			$eeRSCF->contactFormDefault['email'] = get_option('admin_email');
			update_option('eeRSCF_Settings', $eeRSCF->contactFormDefault);
			$eeRSCF->confirm = home_url();
			update_option('eeRSCF_Confirm', $eeRSCF->confirm);
			$eeRSCF->formSettings = $eeRSCF->contactFormDefault;
			update_option('eeRSCF_Version' , eeRSCF_Version);
			return TRUE;
		}
	}

	return FALSE;
}



?>