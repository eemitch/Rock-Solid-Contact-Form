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
	if ( ! isset( $_POST['ee-rock-solid-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ee-rock-solid-nonce'] ) ), 'ee-rock-solid' ) ) {
		wp_die( 'Security check failed. Please try again.', 'Security Error', array( 'response' => 403 ) );
	}

	// Validate global objects exist
	if ( ! isset( $eeRSCF ) || ! is_object( $eeRSCF ) ) {
		wp_die( 'Contact form is temporarily unavailable.', 'Error', array( 'response' => 500 ) );
	}

	global $eeMailClass;
	if ( ! isset( $eeMailClass ) || ! is_object( $eeMailClass ) ) {
		wp_die( 'Mail service is temporarily unavailable.', 'Error', array( 'response' => 500 ) );
	}

	$eeMailClass->eeRSCF_SendEmail();
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
	// Debug logging disabled for production
	// return error_log(print_r($wp_error, true));
}
add_action('wp_mail_failed', 'eeRSCF_Failed', 10, 1);




function eeDevOutput($eeArray) {
	return PHP_EOL . "<script>console.table(" . json_encode($eeArray) . ")</script>" . PHP_EOL;
}




// Get Common Words from EE Server using WordPress HTTP API
function eeGetRemoteSpamWords($eeUrl) {

  // Initialize the WordPress File API wrapper
  $file_handler = new eeRSCF_FileClass();

  // Use the secure remote content method
  $eeContent = $file_handler->get_remote_content($eeUrl);

  return $eeContent;
}




// Write log file using WordPress File API
function eeRSCF_WriteLogFile($eeLog) {

	if($eeLog) {

		// Initialize the WordPress File API wrapper
		$file_handler = new eeRSCF_FileClass();

		// Prepare log content
		$log_content = 'Date: ' . gmdate("Y-m-d H:i:s") . "\n";

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

	$eeVersion = get_option('eeRSCF_Version');

	// Check if we need to migrate from old version
	$oldSettings = get_option('eeRSCF_Settings_1');
	$newSettings = get_option('eeRSCF_Settings');

	if ($oldSettings) {
		// Migration needed from old version - use old settings even if new ones exist
		if (WP_DEBUG) {
			error_log('RSCF DEBUG [Migration]: Migrating from eeRSCF_Settings_1 to eeRSCF_Settings');
		}

		// Copy old settings to new format
		$migratedSettings = $oldSettings;

		// Force SMTP off for migration (as requested)
		$migratedSettings['emailMode'] = 'PHP';

		// Ensure the complete fields structure exists for proper form functionality
		if (!isset($migratedSettings['fields']) || !is_array($migratedSettings['fields'])) {
			$migratedSettings['fields'] = array();
		}

		// Only add missing fields with defaults - preserve existing user configurations
		$defaultFields = array(
			'first-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'First Name'),
			'last-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Last Name'),
			'biz-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Business Name'),
			'address1' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Address'),
			'address2' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Address 2'),
			'city' => array('show' => 'YES', 'req' => 'NO', 'label' => 'City'),
			'state' => array('show' => 'YES', 'req' => 'NO', 'label' => 'State'),
			'zip' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Postal Code'),
			'phone' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Phone'),
			'website' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Website'),
			'other' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Other'),
			'subject' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Subject'),
			'attachments' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Attachments')
		);

		foreach ($defaultFields as $fieldName => $fieldDefaults) {
			if (!isset($migratedSettings['fields'][$fieldName])) {
				$migratedSettings['fields'][$fieldName] = $fieldDefaults;
			}
		}

		// Ensure attachment settings are present
		if (!isset($migratedSettings['fileMaxSize'])) {
			$migratedSettings['fileMaxSize'] = '8';
		}
		if (!isset($migratedSettings['fileFormats'])) {
			$migratedSettings['fileFormats'] = 'gif, jpg, jpeg, bmp, png, tif, tiff, txt, eps, psd, ai, pdf, doc, xls, ppt, docx, xlsx, pptx, odt, ods, odp, odg, wav, wmv, wma, flv, 3gp, avi, mov, mp4, m4v, mp3, webm, zip';
		}

		// Clean up dots in file formats if present
		if (isset($migratedSettings['fileFormats'])) {
			$formats = $migratedSettings['fileFormats'];
			$formats = preg_replace('/\s*\.([a-z0-9]+)/i', ' $1', $formats);
			$formats = str_replace('.', '', $formats); // Remove any remaining dots
			$formats = trim($formats);
			$migratedSettings['fileFormats'] = $formats;
		}

		// Move confirmation URL to separate option
		$confirmUrl = home_url();
		if (isset($migratedSettings['confirm'])) {
			$confirmUrl = $migratedSettings['confirm'];
			unset($migratedSettings['confirm']);
		}

		// Complete missing FROM address if needed
		if (empty($migratedSettings['email'])) {
			$migratedSettings['email'] = get_option('admin_email');
		}

		// Remove deprecated fields
		unset($migratedSettings['name']);
		unset($migratedSettings['formName']);

		// Save migrated settings
		update_option('eeRSCF_Settings', $migratedSettings);
		update_option('eeRSCF_Confirm', $confirmUrl);
		update_option('eeRSCF_Version', eeRSCF_Version);

		// Clean up old options
		delete_option('eeRSCF_Settings_1');

		// Clean up duplicate individual spam options and old SMTP mode
		$legacy_options = array(
			'eeRSCF_spamBlock',
			'eeRSCF_spamBlockBots',
			'eeRSCF_spamHoneypot',
			'eeRSCF_spamEnglishOnly',
			'eeRSCF_spamBlockFishy',
			'eeRSCF_spamBlockWords',
			'eeRSCF_spamBlockedWords',
			'eeRSCF_spamBlockCommonWords',
			'eeRSCF_spamSendAttackNotice',
			'eeRSCF_spamNoticeEmail',
			'eeRSCF_Mode' // Remove the old SMTP mode option
		);

		foreach ($legacy_options as $option_name) {
			delete_option($option_name);
		}

		// Load the migrated settings
		$eeRSCF->formSettings = $migratedSettings;

		if (WP_DEBUG) {
			error_log('RSCF DEBUG [Migration]: Migration completed successfully');
		}

		return TRUE;
	}

	// Normal version check for existing installations
	if ($eeVersion == eeRSCF_Version) {
		$eeRSCF->formSettings = get_option('eeRSCF_Settings');
		return TRUE;
	}

	// Handle version upgrades for existing new-format installations
	if ($newSettings && $eeVersion) {
		if (version_compare($eeVersion, eeRSCF_Version, '<')) {

			// Ensure the complete fields structure exists for proper form functionality
			if (!isset($newSettings['fields']) || !is_array($newSettings['fields'])) {
				$newSettings['fields'] = array();
			}

			// Only add missing fields with defaults - preserve existing user configurations
			$defaultFields = array(
				'first-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'First Name'),
				'last-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Last Name'),
				'biz-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Business Name'),
				'address1' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Address'),
				'address2' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Address 2'),
				'city' => array('show' => 'YES', 'req' => 'NO', 'label' => 'City'),
				'state' => array('show' => 'YES', 'req' => 'NO', 'label' => 'State'),
				'zip' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Postal Code'),
				'phone' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Phone'),
				'website' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Website'),
				'other' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Other'),
				'subject' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Subject'),
				'attachments' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Attachments')
			);

			foreach ($defaultFields as $fieldName => $fieldDefaults) {
				if (!isset($newSettings['fields'][$fieldName])) {
					$newSettings['fields'][$fieldName] = $fieldDefaults;
				}
			}

			// Ensure attachment settings are present in existing installations
			if (!isset($newSettings['fileMaxSize'])) {
				$newSettings['fileMaxSize'] = '8';
			}
			if (!isset($newSettings['fileFormats'])) {
				$newSettings['fileFormats'] = 'gif, jpg, jpeg, bmp, png, tif, tiff, txt, eps, psd, ai, pdf, doc, xls, ppt, docx, xlsx, pptx, odt, ods, odp, odg, wav, wmv, wma, flv, 3gp, avi, mov, mp4, m4v, mp3, webm, zip';
			}

			// Clean up file formats
			if (isset($newSettings['fileFormats'])) {
				$formats = $newSettings['fileFormats'];
				$formats = preg_replace('/\s*\.([a-z0-9]+)/i', ' $1', $formats);
				$formats = str_replace('.', '', $formats);
				$formats = trim($formats);
				$newSettings['fileFormats'] = $formats;
			}

			// Clean up legacy options during version upgrade
			delete_option('eeRSCF_Settings_1'); // Remove old settings format

			// Clean up duplicate individual spam options and old SMTP mode
			$legacy_options = array(
				'eeRSCF_spamBlock',
				'eeRSCF_spamBlockBots',
				'eeRSCF_spamHoneypot',
				'eeRSCF_spamEnglishOnly',
				'eeRSCF_spamBlockFishy',
				'eeRSCF_spamBlockWords',
				'eeRSCF_spamBlockedWords',
				'eeRSCF_spamBlockCommonWords',
				'eeRSCF_spamSendAttackNotice',
				'eeRSCF_spamNoticeEmail',
				'eeRSCF_Mode' // Remove the old SMTP mode option
			);

			foreach ($legacy_options as $option_name) {
				delete_option($option_name);
			}

			// Update settings and version
			update_option('eeRSCF_Settings', $newSettings);
			update_option('eeRSCF_Version', eeRSCF_Version);

			$eeRSCF->formSettings = $newSettings;

			if (WP_DEBUG) {
				error_log('RSCF DEBUG [Update]: Plugin updated to version ' . eeRSCF_Version . ' with legacy cleanup');
			}

			return TRUE;
		}
	}

	// Fresh installation
	if (empty($newSettings)) {
		$eeRSCF->contactFormDefault['to'] = get_option('admin_email');
		$eeRSCF->contactFormDefault['email'] = get_option('admin_email');

		update_option('eeRSCF_Settings', $eeRSCF->contactFormDefault);
		update_option('eeRSCF_Confirm', home_url());
		update_option('eeRSCF_Version', eeRSCF_Version);

		$eeRSCF->formSettings = $eeRSCF->contactFormDefault;

		if (WP_DEBUG) {
			error_log('RSCF DEBUG [Install]: Fresh installation completed');
		}

		return TRUE;
	}

	return FALSE;
}


// Common Error Logging Function
function eeRSCF_Debug_Log($message, $context = 'General') {

	// Will only log errors if WordPress debugging and this plugin's debugging is set to TRUE
	if (WP_DEBUG && eeRSCF_Debug) {
        error_log(sprintf('RSCF DEBUG [%s]: %s', $context, $message));
    }
}



?>