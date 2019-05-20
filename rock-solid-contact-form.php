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
define('eeRSCF_version', '1.1.1');

$eeRSCF = ''; // Our Main class
$eeRSCFU = ''; // Our Upload class

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
function eeRSCF_ContactProcess() {
	
	global $eeRSCF, $eeRSCFU, $eeRSCF_Log;
	
	if(wp_verify_nonce(@$_POST['ee-rock-solid-nonce'], 'ee-rock-solid')) { // Front-end
		
		$eeRSCF->eeRSCF_SendEmail($_POST);
		
	}
	
} // Add this Form Processor to the WP Flow
if(@$_POST['eeRSCF'] == 'TRUE') {
	add_action('wp_loaded', 'eeRSCF_ContactProcess'); // Process a form submission (front or back)
}





// Plugin Setup
function eeRSCF_Setup() {
	
	global $eeRSCF, $eeRSCFU;
	
	$eeRSCF_Nonce = wp_create_nonce('eeAwesomeness'); // Security
	
	include_once(plugin_dir_path(__FILE__) . 'includes/eeFunctions.php'); // General Functions

	// Initiate the Class
	include_once('includes/ee-rock-solid-class.php');
	$eeRSCF = new eeRSCF_Class();
	$eeRSCF->eeRSCF_Setup(TRUE); // Run the setup
	
	// Get the Uploader class
	include_once('includes/ee-rock-solid-upload-class.php');
	$eeRSCFU = new eeRSCFU_FileUpload();
	$eeRSCFU->eeRSCFU_Setup(TRUE); // Run the setup

}

add_action('init', 'eeRSCF_Setup');





// Load stuff we need in the Admin head
function eeRSCF_AdminHead($eeHook) {
        
        // wp_die($eeHook); // Use this to discover the hook for each page
        
        // https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
        
        // Only load scripts if on these Admin pages.
        $eeHooks = array(
        	'toplevel_page_rock-solid-contact-form'
        );
        
        if(in_array($eeHook, $eeHooks)) {
            wp_enqueue_style( 'rock-solid-contact-form-admin-style', plugins_url( 'css/adminStyle.css', __FILE__ ) );
			wp_enqueue_script('rock-solid-contact-form-admin-js', plugins_url('js/adminScripts.js', __FILE__) );
        }
        

}
add_action( 'admin_enqueue_scripts', 'eeRSCF_AdminHead' );





// Load stuff we need in the front-side head
function eeRSCF_Enqueue() {
	
	// Register the style like this for a theme:
    wp_register_style( 'ee-plugin-css', plugin_dir_url(__FILE__) . 'css/style.css');
	wp_enqueue_style('ee-plugin-css');
    
    // Javascript
    $deps = array('jquery');
    wp_enqueue_script('ee-rock-solid-contact-form-js-head',plugin_dir_url(__FILE__) . 'js/scripts.js',$deps,'30',TRUE);
}

add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );






// Functions ---------------------------------



// Shortcode Setup
function eeRSCF_Shortcode($atts, $content = null) {
    
    global $eeRSCF;
    
     // Over-Riding Shortcode Attributes
	if($atts) {
		
		// Use lowercase att names only
		$atts = shortcode_atts( array( 'department' => ''), $atts );
		
		extract($atts);
    
		
    
    }
		
	return $eeRSCF->eeRSCF_formDisplay(); // Usage: [rock-solid-contact]
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






function eeRSCF_SMTP( $phpmailer ) {
	
	if ( !is_object( $phpmailer ) ) {
		$phpmailer = (object) $phpmailer;
	}
	
	$phpmailer->Mailer     = 'smtp';
	// $phpmailer->isSMTP();
	
	$phpmailer->From       = $eeRSCF->email;
	$phpmailer->FromName   = $eeRSCF->emailName;
	$phpmailer->Host       = $eeRSCF->emailServer;
	$phpmailer->Username   = $eeRSCF->emailUsername;
	$phpmailer->Password   = $eeRSCF->emailPassword;
	$phpmailer->SMTPSecure = $eeRSCF->emailSecure;
	$phpmailer->Port       = $eeRSCF->emailPort;
	$phpmailer->SMTPAuth   = $eeRSCF->emailAuth;
	
	if($eeRSCF->emailFormat == 'HTML') {
		$phpmailer->isHTML(TRUE);
		// $phpmailer->msgHTML = $body;
		// $phpmailer->Body = nl2br($body);
	}
	
}
// add_action( 'phpmailer_init', 'eeRSCF_SMTP' );




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
	
	$eeInstalled = get_option('eeRSCF_version');
	
	$eeContactForm = get_option('eeContactForm'); // Check for old version
	
	if($eeInstalled AND eeRSCF_version < $eeInstalled) { // If this is a newer version
	
		$eeRSCF_Log[] = 'New Version: ' . eeRSCF_version;
		
		update_option('eeRSCF_version' , eeRSCF_version);
		
		
	} elseif($eeContactForm) { // Upgrade to New
		
		
		
		
		
		$eeRSCF->eeRSCF_UpgradeFromEE( get_option('eeContactForm') ); // Update, then delete the old option
		
	
	} elseif(!$eeInstalled) { // A New Installation !!!
		
		$eeRSCF_Log[] = 'New Install: ' . eeRSCF_version;
		
		// Forms
		$eeString = serialize($eeRSCF->eeRSCF_1);
		update_option('eeRSCF_1', $eeString);
		
		// Files
		update_option('eeRSCF_fileAllowUploads', $eeRSCF->default_fileAllowUploads);
		update_option('eeRSCF_fileMaxSize', $eeRSCF->default_fileMaxSize);
		update_option('eeRSCF_fileFormats', $eeRSCF->default_fileFormats);
		
		// Spam
		update_option('eeRSCF_spamBlock', $eeRSCF->default_spamBlock);
		update_option('eeRSCF_spamWords', $eeRSCF->default_spamWords);
		
		// Get current user info
		$current_user = wp_get_current_user();
		$userEmail = (string) $current_user->user_email;
		update_option('eeRSCF_email' , $userEmail);
		update_option('eeRSCF_emailMode' , 'PHP');
		update_option('eeRSCF_emailFormat' , 'TEXT');
		update_option('eeRSCF_emailName' , get_bloginfo('name') . ' Contact Form' );
		update_option('eeRSCF_emailUsername' , ' ');
		update_option('eeRSCF_emailPassword' , ' ');
		update_option('eeRSCF_emailServer' , 'mail.' . $_SERVER['HTTP_HOST']);
		update_option('eeRSCF_emailSecure' , 'NO');
		update_option('eeRSCF_emailPort' , '25');
		update_option('eeRSCF_emailAuth' , 'NO');
		update_option('eeRSCF_emailDebug' , 1); // 1 for No, 2 for Yes
		
		
		// update_option('eeRSCF_departments' , 'MAIN^' . $userEmail);
		
		update_option('eeRSCF_version', eeRSCF_version);	
		
	} else {
		
		$eeRSCF_Log[] = 'No Update Needed';
	}
	
	return TRUE;
}

// Check Version and Update if Needed
// Check on Plugins pages only
if( is_admin() AND (strpos($_SERVER['PHP_SELF'], 'plugins.php') OR  strpos($_SERVER['PHP_SELF'], 'plugin-install.php')) ) {
	$eeRSCF_version = get_option('eeRSCF_version');
	if(!$eeRSCF_version OR eeRSCF_version > $eeRSCF_version) {
		add_action( 'plugins_loaded', 'eeRSCF_UpdatePlugin' );
	}
}




// PLUGIN ACTIVATION
function eeRSCF_ActivateContactForm() {
	
	return TRUE;
}
register_activation_hook(__FILE__, 'eeRSCF_ActivateContactForm');
