<?php

/**
 * @package Element Engage - eeRSCF
 */
/*
Plugin Name: Rock Solid Contact Form 
Plugin URI: http://elementengage.com
Description: A rock solid contact form that focuses on spam, security and deliverability
Author: Mitchell Bennis - Element Engage, LLC
Version: 1.1.7
Author URI: http://elementengage.com
License: GPLv2 or later
Text Domain: ee-rock-solid-contact-form
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('eeRSCF_PluginName', 'Rock Solid Contact Form');
define('eeRSCF_WebsiteLink', 'https://elementengage.com');
define('eeRSCF_version', '1.1.7');

$eeRSCF = ''; // Our Main class
$eeRSCFU = ''; // Our Upload class

// The Log
$eeRSCF_Log = array();
// Format: [] => 'log entry'
//	['messages'][] = 'Message to the user'
//	['errors'][] = 'Error condition'


define('eeRSCF_DevMode', FALSE); // Enables extended reporting
//  --> When TRUE, the log file is written onto the page.

// Wordpress User Level to See Menu
$eeRSCFUserAccess = 'edit_posts';



// Check for Update
// https://github.com/YahnisElsts/plugin-update-checker
// https://github.com/eemitch/ee-simple-file-list-extension
$eeRSCF_SLUG = 'ee-rock-solid-contact-form';
$eeRSCF_AUTH = '53d87b9e34521480df5b5050cbbf45a9ef629fd1'; // 53d87b9e34521480df5b5050cbbf45a9ef629fd1

include( plugin_dir_path(__FILE__) . '/updater/plugin-update-checker.php' );
$eeRSCF_updateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/eemitch/rock-solid-contact-form',
	__FILE__,
	$eeRSCF_SLUG
);
$eeRSCF_updateChecker->setAuthentication($eeRSCF_AUTH);
$eeRSCF_updateChecker->getVcsApi()->enableReleaseAssets();




// Catch the post and process it
function eeRSCF_ContactProcess() {
	
	global $eeRSCF, $eeRSCFU, $eeRSCF_Log;
	
	if(wp_verify_nonce(@$_POST['ee-rock-solid-nonce'], 'ee-rock-solid')) { // Front-end
		
		$eeRSCF->eeRSCF_SendEmail($_POST);
		
	}
	
} // Add this Form Processor to the WP Flow
if(@$_POST['eeRSCF'] == 'TRUE') {
	
	
	if( get_option('eeRSCF_emailMode') == 'SMTP' ) {
		// add_action( 'phpmailer_init', 'eeRSCF_SMTP' );
	}
	
	add_action('wp_loaded', 'eeRSCF_ContactProcess'); // Process a form submission (front or back)
}




function eeRSCF_SMTP() {
	
	$eeEmail = get_option('eeRSCF_email');
	
	if(filter_var($eeEmail, FILTER_VALIDATE_EMAIL)) {
		
		// Define SMTP Settings
		global $phpmailer;
		
		if ( !is_object( $phpmailer ) ) {
			$phpmailer = (object) $phpmailer;
		}
		
		// $phpmailer->Mailer     = 'smtp';
		// $phpmailer->isHTML(FALSE);
		
		$phpmailer->isSMTP();
		
		$phpmailer->From       = $eeEmail;
		$phpmailer->FromName   = get_option('eeRSCF_emailName');
		$phpmailer->Host       = get_option('eeRSCF_emailServer');
		$phpmailer->Username   = get_option('eeRSCF_emailUsername');
		$phpmailer->Password   = get_option('eeRSCF_emailPassword');
		$phpmailer->Sender     = get_option('eeRSCF_emailUsername');
		$phpmailer->ReturnPath = get_option('eeRSCF_emailUsername');
		$phpmailer->SMTPSecure = get_option('eeRSCF_emailSecure');
		$phpmailer->Port       = get_option('eeRSCF_emailPort');
		$phpmailer->SMTPAuth   = TRUE; // get_option('eeRSCF_emailAuth');
		$phpmailer->SMTPDebug  = 3;
		
		// Coming Soon
		if(get_option('emailFormat') == 'HTML') {
			// $phpmailer->isHTML(TRUE);
			// $phpmailer->msgHTML = $body;
			// $phpmailer->Body = nl2br($body);
		}
		
		
		
		// echo '<pre>'; print_r($phpmailer); echo '</pre>'; exit;
	
	}
}


// Plugin Setup
function eeRSCF_Setup() {
	
	global $eeRSCF, $eeRSCFU;
	
	$eeRSCF_Nonce = wp_create_nonce('eeAwesomeness'); // Security
	
	include_once(plugin_dir_path(__FILE__) . 'includes/eeFunctions.php'); // General Functions

	// Initiate the Class
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-rock-solid-class.php');
	$eeRSCF = new eeRSCF_Class();
	$eeRSCF->eeRSCF_Setup(TRUE); // Run the setup
	
	
	// Get the Uploader class
	include_once(plugin_dir_path(__FILE__) . 'includes/ee-rock-solid-upload-class.php');
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
    $deps = array('jquery');
    wp_enqueue_script('ee-rock-solid-contact-form-js-head',plugin_dir_url(__FILE__) . 'js/scripts.js',$deps,'30',TRUE);
}

add_action( 'wp_enqueue_scripts', 'eeRSCF_Enqueue' );





// Createing a New Post with Shortcode
function eeRSCF_CreatePostwithShortcode() { 
	
	global $eeRSCF_Log;
	
	$eeShortcode = FALSE;
	$eeCreatePostName = FALSE;
	$eeCreatePostType = FALSE;

	$eeCreatePost = filter_var(@$_GET['eeRSCF_createPost'], FILTER_SANITIZE_STRING);
	
	if(strpos($eeCreatePost, '|')) {
		$eeArray = explode('|', $eeCreatePost);  // Comes in type|name|shortcode
	}
	
	// echo '<pre>'; print_r($eeArray); echo '</pre>'; exit;
	
	$eeType= filter_var($eeArray[0], FILTER_SANITIZE_STRING);
	$eeName = filter_var(urldecode($eeArray[1]), FILTER_SANITIZE_STRING);
	$eeShortcode = filter_var(urldecode($eeArray[2]), FILTER_SANITIZE_STRING);
		
	if(($eeType == "post" OR $eeType == "page") AND $eeShortcode) {
		
		// Create Post Object
		$eeNewPost = array(
			'post_type'		=> $eeType,
			'post_title'    => $eeName . ' ' . ucwords($eeType),
			'post_content'  => '<p><em>Note that this ' . $eeType . ' is in draft status</em></p><div>' . $eeShortcode . '</div>',
			'post_status'   => 'draft'
		);
 
		// Create Post
		$eeNewPostID = wp_insert_post( $eeNewPost );
		
		if($eeNewPostID) {
			
			$eeRSCF_Log['p=' . $eeNewPostID][] = 'Creating new ' . $eeType . ' with shortcode...';
			$eeRSCF_Log['p=' . $eeNewPostID][] = $eeShortcode;
			
			header('Location: /?p=' . $eeNewPostID);
		}
		
		return TRUE;
	}
	
}
if(@$_GET['eeRSCF_createPost']) {
	add_action( 'wp_loaded', 'eeRSCF_CreatePostwithShortcode' );
}



function eeRSCF_deleteForm() {
	
	$eeID = filter_var($_GET['eeRSCF_deleteForm'], FILTER_VALIDATE_INT);
	if($eeID) {
		delete_option('eeRSCF_' . $eeID);
	}
	
}


if(@$_GET['eeRSCF_deleteForm']) {
	add_action( 'wp_loaded', 'eeRSCF_deleteForm' );
}





// Functions ---------------------------------



// Shortcode Setup
function eeRSCF_Shortcode($atts, $content = null) {
    
    global $eeRSCF;
    $id = 1;
    
    if($atts) {
		
		// Use lowercase att names only
		$atts = shortcode_atts( array( 'id' => 1), $atts );
		
		extract($atts);
    }
    
    if( is_numeric($id) AND $id < 100 ) {
	    
	    return $eeRSCF->eeRSCF_formDisplay($id); // Usage: [rock-solid-contact id=2]
    }
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







// Log Failed Emails
function eeRSCF_Failed($wp_error) {
    return error_log(print_r($wp_error, true));
}
add_action('wp_mail_failed', 'eeRSCF_Failed', 10, 1);



// Update or Install New
function eeRSCF_UpdatePlugin() {
	
	global $eeRSCF_Log;
	$eeInstalled = FALSE;
	$eeContactForm = FALSE;
	
	$eeRSCF_Nonce = wp_create_nonce('eeAwesomeness'); // Security
	
	include_once('includes/ee-rock-solid-class.php');
	$eeRSCF = new eeRSCF_Class();
	
	include_once('includes/ee-rock-solid-upload-class.php');
	$eeRSCFU = new eeRSCFU_FileUpload();
	
	if(!is_object($eeRSCF)) {
		$eeRSCF_Log['errors'] = 'No eeRSCF Object';
		return FALSE;
	}
	
	$eeInstalled = get_option('eeRSCF_version');
	
	if($eeInstalled AND eeRSCF_version > $eeInstalled) { // If this is a newer version
		
		return;
		
		// Get the contents of the text file
		$spamBlockedCommonWords = file_get_contents(plugin_dir_url( __FILE__ ) . 'common-spam-words.txt'); 
		if($spamBlockedCommonWords) {
			
			// Convert newline to comma seperated list
			$spamBlockedCommonWords = preg_replace("/(\n)/", ",", $spamBlockedCommonWords);
			$spamBlockedCommonWords = str_replace(',,', ',',  $spamBlockedCommonWords);
			$spamBlockedCommonWords = strtolower($spamBlockedCommonWords);
			
			// TO DO - Compare word lists so we don't overwrite user changes
			update_option('eeRSCF_spamBlockedCommonWords', $spamBlockedCommonWords);
		}
		
		$eeRSCF_Log[] = 'New Version: ' . eeRSCF_version;
		
		update_option('eeRSCF_version' , eeRSCF_version);
		
		
	} elseif(!$eeInstalled) { // A New Installation !!!
		
		$eeRSCF_Log[] = 'New Install: ' . eeRSCF_version;
		
		// Forms
		add_option('eeRSCF_1', $eeRSCF->eeRSCF_0);
		
		// Files
		add_option('eeRSCF_fileMaxSize', $eeRSCFU->eeRSCFU_DetectUploadLimit() );
		add_option('eeRSCF_fileFormats', $eeRSCF->default_fileFormats);
		
		// Spam
		add_option('eeRSCF_spamBlock', $eeRSCF->default_spamBlock);
		add_option('eeRSCF_spamBlockBots', $eeRSCF->default_spamBlockBots);
		add_option('eeRSCF_spamHoneypot', $eeRSCF->default_spamHoneypot);
		add_option('eeRSCF_spamEnglishOnly', $eeRSCF->default_spamEnglishOnly);
		add_option('eeRSCF_spamBlockFishy', $eeRSCF->default_spamBlockFishy);
		add_option('eeRSCF_spamBlockWords', $eeRSCF->default_spamBlockWords);
		add_option('eeRSCF_spamBlockedWords', $eeRSCF->default_spamBlockedWords);
		add_option('eeRSCF_spamBlockCommonWords', $eeRSCF->default_spamBlockCommonWords);
		
		
		// Get the contents of the text file
		$spamBlockedCommonWords = file_get_contents(plugin_dir_url( __FILE__ ) . 'common-spam-words.txt'); 
		if($spamBlockedCommonWords) {
			
			// Convert newline to comma seperated list
			$spamBlockedCommonWords = preg_replace("/(\n)/", ",", $spamBlockedCommonWords);
			$spamBlockedCommonWords = str_replace(',,', ',',  $spamBlockedCommonWords);
			$spamBlockedCommonWords = strtolower($spamBlockedCommonWords);
			add_option('eeRSCF_spamBlockedCommonWords', $spamBlockedCommonWords);
		}

		add_option('eeRSCF_spamSendAttackNotice', $eeRSCF->default_spamSendAttackNotice);
		add_option('eeRSCF_spamNoticeEmail', get_bloginfo('admin_email'));
		add_option('eeRSCF_spamSendAttackNoticeToDeveloper', $eeRSCF->default_spamSendAttackNoticeToDeveloper);
		
		// Email
		$current_user = wp_get_current_user();
		$userEmail = (string) $current_user->user_email;
		add_option('eeRSCF_email' , $userEmail);
		add_option('eeRSCF_emailMode' , 'PHP');
		add_option('eeRSCF_emailFormat' , 'TEXT');
		add_option('eeRSCF_emailName' , get_bloginfo('name') . ' Contact Form' );
		add_option('eeRSCF_emailUsername' , ' ');
		add_option('eeRSCF_emailPassword' , ' ');
		add_option('eeRSCF_emailServer' , 'mail.' . $_SERVER['HTTP_HOST']);
		add_option('eeRSCF_emailSecure' , 'YES');
		add_option('eeRSCF_emailPort' , '465');
		add_option('eeRSCF_emailAuth' , 'NO');
		add_option('eeRSCF_emailDebug' , 1); // 1 for No, 2 for Yes
		
		// Meta
		add_option('eeRSCF_version', eeRSCF_version);	
		
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
