<?php

/**
 * @package Element Engage - eeRSCF
 */
/*
Plugin Name: Rock Solid Contact Form 
Plugin URI: https://elementengage.com
Description: A basic contact form that focuses on spam prevention and deliverability
Author: Mitchell Bennis - Element Engage, LLC
Version: 1.2.2
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
define('eeRSCF_Version', '1.2.2');

// Remote Spam Words List
define('eeRSCF_RemoteSpamWordsURL', 'http://eeserver1.net/ee-common-spam-words.txt'); // One phrase per line
// IMPORTANT - This URL is over-ridden by a Cloudflare Worker Rule
// https://ee-common-spam-words.element-engage.workers.dev/


$eeRSCF = ''; // Our Main class
$eeRSCFU = ''; // Our Upload class

// Plugin Setup
function eeRSCF_Setup() {
	
	global $eeRSCF, $eeRSCFU;
	$eeVersion = get_option('eeRSCF_Version');
	
	$eeRSCF_Nonce = wp_create_nonce('eeRSCF_Nonce'); // Security
	include_once(plugin_dir_path(__FILE__) . 'includes/eeFunctions.php'); // General Functions

	// Initiate the Class
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-rock-solid-class.php');
	$eeRSCF = new eeRSCF_Class();
	
	// Check for Install or Update
	// if( is_admin() ) { eeRSCF_UpdatePlugin(); } // Checking...
	
	// Get the Uploader class
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-rock-solid-upload-class.php');
	$eeRSCFU = new eeRSCFU_FileUpload();
	$eeRSCFU->eeRSCFU_Setup(); // Run the setup
	
	$eeRSCF->formSettings = get_option('eeRSCF_Settings_1');
	
	return TRUE;
}
add_action('init', 'eeRSCF_Setup');





// Shortcode
function eeRSCF_FrontEnd($atts, $content = null) {
	
	global $eeRSCF;
	$id = 1;
	
	if($atts) {
		
		// Use lowercase att names only
		$atts = shortcode_atts( array( 'id' => 1), $atts );
		extract($atts);
	}
	
	if( is_numeric($id) AND $id < 100 ) {
		return $eeRSCF->eeRSCF_formDisplay($id); // Usage: [rock-solid-contact id=1]
	}
}
add_shortcode( 'rock-solid-contact', 'eeRSCF_FrontEnd' );




// Admin Menu
function eeRSCF_BackEnd() {
	
	global $eeRSCF;
	
	$eeRSCF_Nonce = wp_create_nonce('eeRSCF_Nonce'); // Security
	include_once(plugin_dir_path(__FILE__) . 'includes/eeSettings.php'); // Admin's List Management Page
	
	// Top-Level Menu Addition
	add_menu_page(__('Rock Solid Contact Form', 'rock-solid-contact-form'), __('Contact Form', 'rock-solid-contact-form'), 'edit_posts', 'rock-solid-contact-form', 'eeRSCF_Settings', '
dashicons-email');

}
add_action( 'admin_menu', 'eeRSCF_BackEnd' );





// Deleting a Contact Form
function eeRSCF_DeleteForm() {
	
	$eeID = filter_var($_GET['eeRSCF_deleteForm'], FILTER_VALIDATE_INT);
	if($eeID) {
		delete_option('eeRSCF_' . $eeID);
	}
	
}
if(isset($_GET['eeRSCF_deleteForm'])) { add_action( 'wp_loaded', 'eeRSCF_DeleteForm' ); }


// Catch the POST and process it
function eeRSCF_ContactProcess() {
	
	global $eeRSCF, $eeRSCFU, $eeRSCF_Log;
	
	if(wp_verify_nonce($_POST['ee-rock-solid-nonce'], 'ee-rock-solid')) { // Front-end
		$eeRSCF->eeRSCF_SendEmail();
	}
}

// Add this Form Processor to the WP Flow
if(isset($_POST['eeRSCF'])) {
	
	// if( get_option('eeRSCF_emailMode') == 'SMTP' ) {
	// 	add_action( 'phpmailer_init', 'eeRSCF_SMTP' );
	// }
	
	add_action('wp_loaded', 'eeRSCF_ContactProcess'); // Process a form submission (front or back)
}



// Load stuff we need in the Admin head
function eeRSCF_AdminHead($eeHook) {
        
    // wp_die($eeHook); // Use this to discover the hook for each page
    
    // Only load scripts if on these Admin pages.
    $eeHooks = array(
    	'toplevel_page_rock-solid-contact-form'
    );
    
    if(in_array($eeHook, $eeHooks)) {
        wp_enqueue_style( 'rock-solid-contact-form-admin-style', plugins_url( 'css/adminStyle.css', __FILE__ ) );
		wp_enqueue_script('rock-solid-contact-form-admin-js', plugins_url('js/adminScripts.js', __FILE__) );
		wp_enqueue_script('rock-solid-contact-form-admin-js-footer', plugins_url('js/adminScriptsFooter.js', __FILE__), '', '1', TRUE );
    }
}
add_action( 'admin_enqueue_scripts', 'eeRSCF_AdminHead' );



// Load stuff we need in the front-side head
function eeRSCF_Enqueue() {
	
	// Register the style like this for a theme:
    wp_register_style( 'ee-rock-solid-contact-form-css', plugin_dir_url(__FILE__) . 'css/style.css');
	wp_enqueue_style('ee-rock-solid-contact-form-css');
    
    // Javascript
    $eeDeps = array('jquery');
    wp_enqueue_script('ee-rock-solid-contact-form-js-footer',plugin_dir_url(__FILE__) . 'js/scripts.js',$eeDeps,'30',TRUE);
}
add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );





// Log Failed Emails
function eeRSCF_Failed($wp_error) {
    return error_log(print_r($wp_error, true));
}
add_action('wp_mail_failed', 'eeRSCF_Failed', 10, 1);



// PLUGIN ACTIVATION
function eeRSCF_ActivateContactForm() {
	return TRUE;
}
register_activation_hook(__FILE__, 'eeRSCF_ActivateContactForm');
