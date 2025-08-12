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
Text Domain: rock-solid-contact-form
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// DEV MODE  --> When TRUE, the log file is written onto the page.
define('eeRSCF_DevMode', FALSE); // Enables extended reporting

// This Plugin
define('eeRSCF_SLUG', 'rock-solid-contact-form');
define('eeRSCF_Version', '2.1.2');

// Remote Spam Words List
define('eeRSCF_RemoteSpamWordsURL', 'http://eeserver1.net/ee-common-spam-words.txt'); // One phrase per line
// IMPORTANT - This URL is over-ridden by a Cloudflare Worker Rule
// https://ee-common-spam-words.element-engage.workers.dev/

$eeRSCF = ''; // Our Main class
$eeHelper = ''; // Our Helper class
$eeMailClass = ''; // Our Mail class
$eeAdminClass = ''; // Our Admin class
$eeFileClass = ''; // Our File class

// Plugin Setup
function eeRSCF_Setup() {

	// Nonce Security
	define('eeRSCF_Nonce', wp_create_nonce('eeRSCF_Nonce')); // Used on included pages

	global $eeRSCF, $eeHelper, $eeMailClass, $eeAdminClass, $eeFileClass;
	$eeVersion = get_option('eeRSCF_Version');

	// Includes
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-functions.php'); // General Functions
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-rock-solid-class.php'); //Display and general methods
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-helper-class.php'); // Might be rolled into the main class?
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-mail-class.php'); // Anything to do with contact form processing and sending mail
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-admin-class.php'); // Back-end UI and form processing
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-file-class.php'); // File uploads

	// Initialize Classes
	$eeRSCF = new eeRSCF_Class();
	$eeHelper = new eeHelper_Class();

	// Check for new install or needs update
	eeRSCF_UpdatePlugin();

	// Get our settings first
	if(empty($eeRSCF->formSettings)) {
		$eeRSCF->formSettings = get_option('eeRSCF_Settings');
		$eeRSCF->confirm = get_option('eeRSCF_Confirm');

		// If no settings found, use defaults
		if(empty($eeRSCF->formSettings)) {
			$eeRSCF->formSettings = $eeRSCF->contactFormDefault;
		}

		// Ensure critical email fields are populated (for both defaults and saved settings)
		if(empty($eeRSCF->formSettings['to'])) {
			$eeRSCF->formSettings['to'] = get_option('admin_email');
		}
		if(empty($eeRSCF->formSettings['email'])) {
			$eeRSCF->formSettings['email'] = get_option('admin_email');
		}
		if(empty($eeRSCF->confirm)) {
			$eeRSCF->confirm = home_url();
		}
	}

	// Now initialize the other classes that depend on settings
	$eeMailClass = new eeRSCF_MailClass();
	$eeAdminClass = new eeRSCF_AdminClass($eeRSCF);
	$eeFileClass = new eeRSCF_FileClass();

	// Sync settings to dependent classes
	$eeMailClass->syncSettings();

	// Add Actions
	add_action( 'admin_menu', 'eeRSCF_BackEnd' );
	add_action( 'admin_enqueue_scripts', 'eeRSCF_AdminEnqueue' );
	add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );
	add_shortcode( 'rock-solid-contact', 'eeRSCF_FrontEnd' );

	// Process the Contact Form Submission
	if(isset($_POST['ee-rock-solid-nonce'])) { add_action('wp_loaded', 'eeRSCF_ContactProcess'); }

	return TRUE;
}
add_action('init', 'eeRSCF_Setup');



function eeRSCF_Activate() {
	return TRUE;
}
register_activation_hook(__FILE__, 'eeRSCF_Activate');

?>