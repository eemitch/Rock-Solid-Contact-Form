<?php
/**
 * Core utility functions for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


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




// Get Common Words from EE Server using WordPress HTTP API
function eeGetRemoteSpamWords($eeUrl) {

  // Initialize the WordPress File API wrapper
  $file_handler = new eeFile_Class();

  // Use the secure remote content method
  $eeContent = $file_handler->get_remote_content($eeUrl);

  return $eeContent;
}




// Write log file using WordPress File API
function eeRSCF_WriteLogFile($eeLog) {

	if($eeLog) {

		// Initialize the WordPress File API wrapper
		$file_handler = new eeFile_Class();

		// Prepare log content
		$log_content = 'Date: ' . date("Y-m-d H:i:s") . "\n";

		foreach($eeLog as $key => $logEntry){

			if(is_array($logEntry)) {

				foreach($logEntry as $key2 => $logEntry2){
					$log_content .= '(' . $key2 . ') ' . $logEntry2 . "\n";
				}

			} else {
				$log_content .= '(' . $key . ') ' . $logEntry . "\n";
			}
		}

		$log_content .= "\n\n\n---------------------------------------\n\n\n"; // Separator

		// Use the secure write log method
		if ($file_handler->write_log($log_content, 'eeLog.txt')) {
			return TRUE;
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
				$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'eeRSCF_%'));
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