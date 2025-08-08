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

	add_action( 'admin_menu', 'eeRSCF_BackEnd' );
	add_action( 'admin_enqueue_scripts', 'eeRSCF_AdminEnqueue' );
	add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );
	add_shortcode( 'rock-solid-contact', 'eeRSCF_FrontEnd' );

	// Only process form if nonce is present and valid
	if(isset($_POST['ee-rock-solid-nonce'])) {
		$nonce = sanitize_text_field(wp_unslash($_POST['ee-rock-solid-nonce']));
		if(wp_verify_nonce($nonce, 'ee-rock-solid')) {
			add_action('wp_loaded', 'eeRSCF_ContactProcess');
		}
	}

	return TRUE;
}
add_action('init', 'eeRSCF_Setup');



function eeRSCF_Activate() {
	return true;
}
register_activation_hook(__FILE__, 'eeRSCF_Activate');

?>