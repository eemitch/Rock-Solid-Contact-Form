<?php

/**
 * @package Element Engage - eeRSCF
 */
/*
Plugin Name: Rock Solid Contact Form 
Plugin URI: http://elementengage.com
Description: A rock solid contact form that focuses on security and deiverability
Author: Mitchell Bennis - Element Engage, LLC
Version: 1.1.1
Author URI: http://elementengage.com
License: Proprietary - Copyright Mitchell Bennis
Text Domain: rock-solid-contact-form
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('eeRSCF_PluginName', 'Rock Solid Contact Form');
define('eeRSCF_WebsiteLink', 'https://elementengage.com');
define('eeRSCF_Version', '1.1.1');

// SMTP Email with Authentication
define( 'RSCF_SMTP_USER',   'mail@' . basename( get_site_url() ) ); // Username to use for SMTP authentication
define( 'RSCF_SMTP_PASS',   'eLemenTenGage2019' ); // Password to use for SMTP authentication
define( 'RSCF_SMTP_HOST',   'secure.eeserver2.net' ); // The hostname of the mail server
define( 'RSCF_SMTP_FROM',   RSCF_SMTP_USER ); // SMTP From email address
define( 'RSCF_SMTP_NAME',   'Website Contact Form' ); // SMTP From name
define( 'RSCF_SMTP_PORT',   '465' ); // SMTP port number - likely to be 25, 465 or 587
define( 'RSCF_SMTP_SECURE', 'ssl' ); // Encryption system to use - ssl or tls
define( 'RSCF_SMTP_AUTH',    TRUE ); // Use SMTP authentication (true|false)
define( 'RSCF_SMTP_HTML',   FALSE ); // Send in HTML format (Not Ready)
define( 'RSCF_SMTP_DEBUG',   2 ); // for debugging purposes only set to 1 or 2


$eeRSCF = ''; // Our Main class

// The Log
$eeRSCF_Log = array();
// Format: [] => 'log entry'
//	['messages'][] = 'Message to the user'
//	['errors'][] = 'Error condition'

define('eeRSCF_DevMode', TRUE); // Enables extended reporting
/*  --> When TRUE, the log file is written in the plugin's logs folder.

** Activation and errors are always written to the log file.

Log Files...
   /wp-content/plugins/rock-solid-contact-form/logs/eeLog.txt
   /wp-content/plugins/rock-solid-contact-form/logs/ee-email-error.log
   /wp-content/plugins/rock-solid-contact-form/logs/ee-upload-error.log

*/

// $eeRSCF_Config = array(); // We store DB return values here

// Wordpress User Level
$eeRSCFUserAccess = 'edit_posts'; 

// Admin Titling
$eeRSCFSettingsTitle = 'Contact Form Settings';
$eeRSCFMenuLabel = 'Contact Form';
	
// Admin Message Display
$eeBackLink = 'http://elementengage.com';
$eeBackLinkTitle = 'Plugin by Element Engage';
$eeDisclaimer = 'IMPORTANT - Allowing the public to send you computer files comes with risk. 
	Please double-check that you only use the file types you absolutely need and open each file submitted to you with great caution and intestinal fortitude.';
	




// Catch the post and process it
function eeRSCF_FormProcess() {
	
	global $eeRSCF, $eeRSCF_Log;
	
	// Back end
	if( @check_admin_referer('ee-rock-solid-settings', @$_POST['ee-rock-solid-settings-nonce']) ) {
		
		$eeRSCF_Log['submission'][] = 'Form Submitted...';
		$eeRSCF_Log['submission']['post'] = $_POST;
		
		
		
		
		
		
		
		// $eeRSCF->eeRSCF_SendEmail($_POST);
	
	
	
	
	
	
	
	
	
	
	} elseif(@wp_verify_nonce($_POST['ee-rock-solid-nonce'], 'ee-rock-solid')) { // Front-end
		
		
		
		
	}
	
} // Add this Form Processor to the WP Flow
if(@$_POST['eeRSCF_Settings'] == 'TRUE') {
	add_action('init', 'eeRSCF_FormProcess'); // Process a form submission (front or back)
}





// Plugin Setup
function eeRSCF_Setup() {
	
	global $eeRSCF;
	
	$eeRSCF_Nonce = wp_create_nonce('eeAwesomeness'); // Security
	
	include_once(plugin_dir_path(__FILE__) . 'includes/eeFunctions.php'); // General Functions
	
	include_once('includes/ee-rock-solid-class.php');
	include_once('includes/ee-rock-solid-upload-class.php');
	
	// Initiate the Class
	$eeRSCF = new eeRSCF_Class();
	$eeRSCF->eeRSCF_Setup(TRUE); // Run the setup
	
	// Get the Uploader class
	// $eeRSCF_FileUpload = new eeRSCF_FileUpload();
	// $eeRSCF_FileUpload->eeRSCF_Setup(TRUE); // Run the setup

}

add_action('init', 'eeRSCF_Setup');





// Load stuff we need in the Admin head
function eeRSCF_AdminHead($eeHook) {
        
        // wp_die($eeHook); // Use this to discover the hook for each page
        
        // https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
        
        // Only load scripts if on these Admin pages.
        $eeHooks = array(
        	'toplevel_page_rock-solid-contact-form',
        	'',
        	''
        );
        
        if(in_array($eeHook, $eeHooks)) {
            wp_enqueue_style( 'rock-solid-contact-form-style', plugins_url( 'css/adminStyle.css', __FILE__ ) );
			wp_enqueue_script('ee-plugin-js', plugins_url('js/adminScripts.js', __FILE__) );
        }
        

}
add_action( 'admin_enqueue_scripts', 'eeRSCF_AdminHead' );





// Load stuff we need in the front-side head
function eeRSCF_Enqueue() {
	
	// Register the style like this for a theme:
    wp_register_style( 'ee-plugin-css', plugin_dir_url(__FILE__) . 'css/style.css');
 
    // Enqueue the style:
    wp_enqueue_style('ee-plugin-css');
	
	wp_enqueue_script('ee-plugin-js',plugin_dir_url(__FILE__) . 'js/scripts.js');
}
add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );






// Functions ---------------------------------



// Shortcode Setup
function eeRSCF_Shortcode() {
    
    global $eeRSCF;
    
    return $eeRSCF->eeRSCF_FormDisplay(); // Usage: [rock-solid-contact]
}
add_shortcode( 'rock-solid-contact', 'eeRSCF_Shortcode' );




// Plugin Menu
function eeRSCF_plugin_menu() {
	
	global $eeRSCF, $eeRSCFUserAccess;
	
	// Learn more at https://codex.wordpress.org/Adding_Administration_Menus
	
	$eeOutput = ''; // We use this to collect ALL of our browser output.
	
	// Create a Nonce, include the page with the needed function, then check for it there.
	$eeRSCF_Nonce = wp_create_nonce('eeAwesomeness'); // Security
	include_once(plugin_dir_path(__FILE__) . 'includes/eeSettings.php'); // Admin's List Management Page
	
	// Top-Level Menu Addition
	add_menu_page(__('Rock Solid Contact Form', 'rock-solid-contact-form'), __('Contact Form', 'rock-solid-contact-form'), $eeRSCFUserAccess, 'rock-solid-contact-form', 'eeRSCF_Settings', '
dashicons-email');

}
add_action( 'admin_menu', 'eeRSCF_plugin_menu' );




/**
 * This function will connect wp_mail to your authenticated
 * SMTP server. This improves reliability of wp_mail, and 
 * avoids many potential problems.
 *
 * Values are constants set in wp-config.php. Be sure to
 * define the using the wp_config.php example in this gist.
 *
 * Author:     Chad Butler
 * Author URI: http://butlerblog.com
 *
 * For more information and instructions, see:
 * http://b.utler.co/Y3
 */

function eeRSCF_SMTP( $phpmailer ) {
	
	if ( !is_object( $phpmailer ) ) {
		$phpmailer = (object) $phpmailer;
	}
	
	$phpmailer->Mailer     = 'smtp';
	// $phpmailer->isSMTP();
	
	$phpmailer->Host       = RSCF_SMTP_HOST;
	$phpmailer->SMTPAuth   = RSCF_SMTP_AUTH;
	$phpmailer->Port       = RSCF_SMTP_PORT;
	$phpmailer->Username   = RSCF_SMTP_USER;
	$phpmailer->Password   = RSCF_SMTP_PASS;
	$phpmailer->SMTPSecure = RSCF_SMTP_SECURE;
	$phpmailer->From       = RSCF_SMTP_FROM;
	$phpmailer->FromName   = RSCF_SMTP_NAME;
	
	if(RSCF_SMTP_HTML) {
		$phpmailer->isHTML(TRUE);
		// $phpmailer->msgHTML = $body;
		// $phpmailer->Body = nl2br($body);
	}
	
	
	
}
add_action( 'phpmailer_init', 'eeRSCF_SMTP' );




// Log Failed Emails
function eeRSCF_Failed($wp_error) {
    return error_log(print_r($wp_error, true));
}
add_action('wp_mail_failed', 'eeRSCF_Failed', 10, 1);



// Update or Install New
function eeRSCF_UpdatePlugin() {
	
	global $eeRSCF_Log;
	
	include_once('includes/ee-rock-solid-class.php');
	$eeRSCF = new eeRSCF_Class();
	
	if(!is_object($eeRSCF)) {
		$eeRSCF_Log['errors'] = 'No eeRSCF Object';
		return FALSE;
	}
	
	$eeInstalled = get_option('eeRSCF_Version');
	
	$eeContactForm = get_option('eeContactForm'); // Check for old version
	
	if($eeInstalled AND eeRSCF_Version < $eeInstalled) { // If this is a newer version
	
		$eeRSCF_Log[] = 'New Version: ' . eeRSCF_Version;
		
		update_option('eeRSCF_Version' , eeRSCF_Version);
		
		
	} elseif($eeContactForm) { // Upgrade to New
		
		$eeRSCF->eeRSCF_UpgradeFromEE( get_option('eeContactForm') ); // Update, then delete the old option
		
	
	} elseif(!$eeInstalled) { // A New Installation !!!
		
		$eeRSCF_Log[] = 'New Install: ' . eeRSCF_Version;
		
		update_option('eeRSCF_fields', $eeRSCF->default_formFields);
		update_option('eeRSCF_allowUploads', $eeRSCF->default_allowUploads);
		update_option('eeRSCF_maxFileSize', $eeRSCF->default_maxFileSize);
		update_option('eeRSCF_fileFormats', $eeRSCF->default_fileFormats);
		update_option('eeRSCF_spamBlock', $eeRSCF->default_spamBlock);
		update_option('eeRSCF_spamWords', $eeRSCF->default_spamWords);
		
		// Get current user info
		global $current_user;
		get_currentuserinfo();
		$userEmail = (string) $current_user->user_email;
		update_option('eeRSCF_from' , $userEmail);
		
		update_option('eeRSCF_departments' , 'N/A^TO:' . $userEmail);
		
		update_option('eeRSCF_Version' , eeRSCF_Version);	
		
	} else {
		
		$eeRSCF_Log[] = 'No Update Needed';
	}
	
	return TRUE;
}

// Check Version and Update if Needed
if( is_admin() ) {
	$eeRSCF_Version = get_option('eeRSCF_Version');
	if(!$eeRSCF_Version OR eeRSCF_Version > $eeRSCF_Version) {
		add_action( 'plugins_loaded', 'eeRSCF_UpdatePlugin' );
	}
}




// PLUGIN ACTIVATION
function eeRSCF_ActivateContactForm() {
	
	return TRUE;
}
register_activation_hook(__FILE__, 'eeRSCF_ActivateContactForm');
