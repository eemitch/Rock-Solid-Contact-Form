<?php

/**
 * @package Element Engage - eeRSCF
 */
/*
Plugin Name: Rock Solid Contact Form
Plugin URI: https://elementengage.com
Description: A basic contact form that focuses on spam prevention and deliverability
Author: Mitchell Bennis - Element Engage, LLC
Version: 2.1.2
Author URI: https://elementengage.com
License: GPLv2 or later
Text Domain: ee-rock-solid-contact-form
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// DEV MODE  --> When TRUE, the log file is written onto the page.
define('eeRSCF_DevMode', TRUE); // Enables extended reporting

// This Plugin
define('eeRSCF_SLUG', 'rock-solid-contact-form');
define('eeRSCF_Version', '2.1.2');

// Remote Spam Words List
define('eeRSCF_RemoteSpamWordsURL', 'http://eeserver1.net/ee-common-spam-words.txt'); // One phrase per line
// IMPORTANT - This URL is over-ridden by a Cloudflare Worker Rule
// https://ee-common-spam-words.element-engage.workers.dev/


$eeRSCF = ''; // Our Main class
$eeHelper = ''; // Our Helper class

// Plugin Setup
function eeRSCF_Setup() {

	global $eeRSCF, $eeHelper;
	$eeVersion = get_option('eeRSCF_Version');

	$eeRSCF_Nonce = wp_create_nonce('eeRSCF_Nonce'); // Security
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-functions.php'); // General Functions

	// Initiate the Class
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-rock-solid-class.php');
	$eeRSCF = new eeRSCF_Class();

	// Get the helper class
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-helper-class.php');
	$eeHelper = new eeHelper_Class();

	// Check for Install or Update
	$eeReturn = eeRSCF_UpdatePlugin();

	if(empty($eeRSCF->formSettings)) {
		$eeRSCF->formSettings = get_option('eeRSCF_Settings');
		$eeRSCF->confirm = get_option('eeRSCF_Confirm');
	}

	// Load text domain for internationalization
	load_plugin_textdomain( 'ee-rock-solid-contact-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	add_action( 'admin_menu', 'eeRSCF_BackEnd' );
	add_action( 'admin_enqueue_scripts', 'eeRSCF_AdminEnqueue' );
	add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );
	add_shortcode( 'rock-solid-contact', 'eeRSCF_FrontEnd' );

	if(isset($_POST['ee-rock-solid-nonce'])) { add_action('wp_loaded', 'eeRSCF_ContactProcess'); }

	return TRUE;
}
add_action('init', 'eeRSCF_Setup');



function eeRSCF_Activate() {
	return true;
}
register_activation_hook(__FILE__, 'eeRSCF_Activate');



// Update or Install New
function eeRSCF_UpdatePlugin() {

	global $eeRSCF;

	$eeTestReset = FALSE; // Go back to older DB version
	if($eeTestReset == TRUE) {

		// Clear All Previous
		global $wpdb;
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'eeRSCF_%'");

		// Add v1.x Test Data
		$eeString = 'a:31:{s:4:"name";s:17:"Main Contact Form";s:2:"to";s:23:"mitch@elementengage.com";s:2:"cc";s:0:"";s:3:"bcc";s:17:"mail@ee-email.net";s:7:"confirm";s:39:"https://elementengage.com/message-sent/";s:6:"fields";a:13:{s:10:"first-name";a:3:{s:4:"show";s:3:"YES";s:3:"req";s:3:"YES";s:5:"label";s:4:"Name";}s:9:"last-name";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:9:"Last Name";}s:8:"biz-name";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:13:"Business Name";}s:8:"address1";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:7:"Address";}s:8:"address2";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:9:"Address 2";}s:4:"city";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:4:"City";}s:5:"state";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:5:"State";}s:3:"zip";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:11:"Postal Code";}s:5:"phone";a:3:{s:4:"show";s:3:"YES";s:3:"req";s:2:"NO";s:5:"label";s:5:"Phone";}s:7:"website";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:7:"Website";}s:5:"other";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:5:"Other";}s:7:"subject";a:3:{s:4:"show";s:3:"YES";s:3:"req";s:3:"YES";s:5:"label";s:7:"Subject";}s:11:"attachments";a:3:{s:4:"show";s:2:"NO";s:3:"req";s:2:"NO";s:5:"label";s:11:"Attachments";}}s:16:"fileAllowUploads";s:3:"YES";s:11:"fileMaxSize";i:64;s:11:"fileFormats";s:174:".gif,.jpg,.jpeg,.bmp,.png,.tif,.tiff,.txt,.eps,.psd,.ai,.pdf,.doc,.xls,.ppt,.docx,.xlsx,.pptx,.odt,.ods,.odp,.odg,.wav,.wmv,.wma,.flv,.3gp,.avi,.mov,.mp4,.m4v,.mp3,.webm,.zip";s:9:"spamBlock";s:3:"YES";s:13:"spamBlockBots";s:3:"YES";s:12:"spamHoneypot";s:4:"link";s:15:"spamEnglishOnly";s:3:"YES";s:14:"spamBlockFishy";s:3:"YES";s:14:"spamBlockWords";s:3:"YES";s:20:"spamBlockCommonWords";s:3:"YES";s:16:"spamBlockedWords";s:0:"";s:20:"spamSendAttackNotice";s:3:"YES";s:15:"spamNoticeEmail";s:17:"mail@ee-email.net";s:5:"email";s:35:"elementengage@vps.elementengage.com";s:9:"emailMode";s:3:"PHP";s:9:"emailName";s:12:"Contact Form";s:11:"emailServer";s:0:"";s:13:"emailUsername";s:0:"";s:13:"emailPassword";s:0:"";s:9:"emailPort";s:2:"25";s:11:"emailSecure";s:3:"SSL";s:9:"emailAuth";b:1;s:11:"emailFormat";s:4:"TEXT";s:10:"emailDebug";b:0;s:8:"formName";s:17:"Main Contact Form";}';
		$eeRSCF->formSettings = unserialize($eeString);
		update_option('eeRSCF_Settings_1', $eeRSCF->formSettings);
		update_option('eeRSCF_Version', '1.0', 'yes');

		// Older Stuff
		update_option('eeRSCF_spamBlock', 'NO', 'yes');
		update_option('eeRSCF_spamBlockBots', 'YES', 'yes');
		update_option('eeRSCF_spamBlockCommonWords', 'YES', 'yes');
		update_option('eeRSCF_spamBlockedWords', '', 'yes');
		update_option('eeRSCF_spamBlockFishy', 'YES', 'yes');
		update_option('eeRSCF_spamBlockWords', 'YES', 'yes');
		update_option('eeRSCF_spamEnglishOnly', 'YES', 'yes');
		update_option('eeRSCF_spamHoneypot', 'link', 'yes');
		update_option('eeRSCF_spamSendAttackNotice', 'NO', 'yes');

		echo '<pre>RESET COMPLETE '; print_r($eeRSCF->formSettings); echo '</pre>';

		exit;

	}

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