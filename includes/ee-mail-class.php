<?php
/**
 * Mailing class for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


// Our Mail Class
class eeRSCF_MailClass {

	// Properties needed for mail processing (warnings and errors for user feedback only)
	public $log = array(
		'messages' => array(),
		'warnings' => array(),
		'errors' => array(),
		'catch' => array()
	);

	public $thePost = array();
	public $sender = '';
	public $formSettings = array();
	public $confirm = '';
	public $mainClass = null;

	public function __construct() {
		// Initialize with global settings if available
		global $eeRSCF;
		if (isset($eeRSCF) && is_object($eeRSCF)) {
			$this->mainClass = $eeRSCF;
			$this->syncSettings();
		}
	}

	/**
	 * Sync settings from main class
	 */
	public function syncSettings() {
		if ($this->mainClass && !empty($this->mainClass->formSettings)) {
			$this->formSettings = $this->mainClass->formSettings;
			$this->confirm = $this->mainClass->confirm;
		}
	}


    private function eeRSCF_PostProcess() {

		global $eeRSCF;

		// DEBUG: Start of post processing
		eeRSCF_Debug_Log('Starting post processing...', 'PostProcess');
		eeRSCF_Debug_Log('POST data count: ' . count($_POST), 'PostProcess');

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			$this->log['catch'][] = 'Nonce verification failed';
			eeRSCF_Debug_Log('Nonce verification failed!', 'PostProcess');
			return false;
		}

		eeRSCF_Debug_Log('Nonce verification passed', 'PostProcess');

		eeRSCF_Debug_Log('Processing the post...', 'PostProcess');

		$eeIgnore = ['eeRSCF', 'eeRSCF_ID', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER'];

		eeRSCF_Debug_Log('Ignored fields: ' . implode(', ', $eeIgnore), 'PostProcess');

		foreach ($_POST as $eeKey => $eeValue) {

			eeRSCF_Debug_Log('Processing field: ' . $eeKey . ' = "' . $eeValue . '"', 'PostProcess');

			if (in_array($eeKey, $eeIgnore) || empty($eeValue)) {
				eeRSCF_Debug_Log('Skipping field ' . $eeKey . ' (ignored or empty)', 'PostProcess');
				continue;
			}

			// SECURITY: Comprehensive input validation and sanitization
			$originalValue = $eeValue;

			eeRSCF_Debug_Log('Sanitizing field ' . $eeKey . ' with original value: "' . $originalValue . '"', 'PostProcess');

			// Sanitize and validate specific fields
			switch (true) {
				case strpos($eeKey, 'mail') !== false:
					$eeValue = sanitize_email($eeValue);
					if (!is_email($eeValue)) {
						eeRSCF_Debug_Log('Invalid email detected: ' . $originalValue, 'PostProcess');
						continue 2; // Skip to the next $_POST item
					}
					$this->sender = strtolower($eeValue);
					eeRSCF_Debug_Log('Email field processed, sender set to: ' . $this->sender, 'PostProcess');
					break;
				case strpos($eeKey, 'ebsite') !== false:
					$eeValue = esc_url_raw($eeValue, ['http', 'https']);
					if (empty($eeValue)) {
						eeRSCF_Debug_Log('Invalid website URL detected: ' . $originalValue, 'PostProcess');
						continue 2; // Skip to the next $_POST item
					}
					eeRSCF_Debug_Log('Website field processed: ' . $eeValue, 'PostProcess');
					break;
				default:
					// SECURITY: Enhanced sanitization for all other fields
					if ($this->mainClass && method_exists($this->mainClass, 'eeRSCF_SecureSanitize')) {
						$eeValue = $this->mainClass->eeRSCF_SecureSanitize($eeValue, $eeKey);

						// SECURITY: Reject if sanitization changed the content significantly
						if (method_exists($this->mainClass, 'eeRSCF_SecurityCheck') && $this->mainClass->eeRSCF_SecurityCheck($originalValue, $eeValue, $eeKey)) {
							eeRSCF_Debug_Log('Security check failed for field ' . $eeKey . '. Original: "' . $originalValue . '" Sanitized: "' . $eeValue . '"', 'PostProcess');
							continue 2; // Skip to the next $_POST item
						}
					} else {
						// Fallback sanitization if main class methods not available
						$eeValue = sanitize_text_field($eeValue);
						eeRSCF_Debug_Log('Using fallback sanitization for field: ' . $eeKey, 'PostProcess');
					}

					eeRSCF_Debug_Log('Default field processed. Original: "' . $originalValue . '" Sanitized: "' . $eeValue . '"', 'PostProcess');
					break;
			}

			$eeField = $this->mainClass && method_exists($this->mainClass, 'eeUnSlug') ? $this->mainClass->eeUnSlug($eeKey) : ucwords(str_replace('-', ' ', $eeKey));
			$processedEntry = $eeField . ': ' . $eeValue;
			$this->thePost[] = $processedEntry;

			eeRSCF_Debug_Log('Added to thePost: "' . $processedEntry . '"', 'PostProcess');
		}

		eeRSCF_Debug_Log('Processing completed. Total entries: ' . count($this->thePost), 'PostProcess');
		eeRSCF_Debug_Log('Final thePost array: ' . print_r($this->thePost, true), 'PostProcess');

		return $this->thePost;
	}




    public function eeRSCF_SendEmail() {

		global $eeFileClass; // Get File Upload Class

		// $this->formID = filter_var($_POST['eeRSCF_ID'], FILTER_VALIDATE_INT);

		// Are we Blocking SPAM?
		if($this->formSettings['spamBlock'] == 'YES') {
			if( $this->eeRSCF_formSpamCheck() === TRUE ) { // This is SPAM
				wp_die(esc_html__('Sorry, there was a problem with your message content. Please go back and try again.', 'rock-solid-contact-form'));
			}
		}		// Check referrer is from same site.
		if(!isset($_REQUEST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			eeRSCF_Debug_Log('Submission is not from this website', 'SendEmail');
			return FALSE;
		}

		eeRSCF_Debug_Log('Sending the Email...', 'SendEmail');

		$this->eeRSCF_PostProcess();

		// File Attachment
		$eeFileURL = FALSE;
		if(!empty($_FILES['file']) AND $this->formSettings['fields']['attachments']['show'] == 'YES') {

			$formatsArray = explode(',', $this->formSettings['fileFormats']);
			$formatsArray = array_filter(array_map('trim', $formatsArray));

			// Validate file data exists before accessing
			if (isset($_FILES['file']['name']) && isset($_FILES['file']['size'])) {
				$fileExt = strtolower(pathinfo(sanitize_file_name($_FILES['file']['name']), PATHINFO_EXTENSION));
				$max_size = $this->formSettings['fileMaxSize'] * 1048576; // Convert MB to Bytes

				if( $_FILES['file']['size'] <= $max_size ) {
					if( in_array($fileExt,$formatsArray) ) {
						// $_FILES is passed to WordPress secure upload handler
						$eeFileURL = $eeFileClass->eeUploader($_FILES['file'],  'ee-contact'  ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					} else {
						$this->log['errors'][] = 'File type not allowed: ' . $fileExt . '. Please use one of the following formats: ' . implode(', ', $formatsArray);
						eeRSCF_Debug_Log('File type not allowed: ' . $fileExt, 'SendEmail');
					}
				} else {
					$this->log['errors'][] = 'File too large: ' . round($_FILES['file']['size'] / 1048576, 2) . 'MB. Maximum allowed: ' . $this->formSettings['fileMaxSize'] . 'MB';
					eeRSCF_Debug_Log('File too large: ' . $_FILES['file']['size'] . ' bytes', 'SendEmail');
				}
			} else {
				$this->log['errors'][] = 'Invalid file upload data. Please try uploading your file again.';
				eeRSCF_Debug_Log('Invalid file upload data', 'SendEmail');
			}
		}

		if(!$this->log['errors'] AND !empty($this->thePost)) {

			eeRSCF_Debug_Log('Preparing the Email...', 'SendEmail');

			// Configure SMTP if enabled
			if ($this->formSettings['emailMode'] == 'SMTP') {
				add_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				eeRSCF_Debug_Log('SMTP Mode Enabled', 'SendEmail');
			} else {
				eeRSCF_Debug_Log('Using WordPress Default Mailer', 'SendEmail');
			}

			// Loop through and see if we have a Subject field
			foreach($this->thePost as $eeValue){
				$eeField = explode(':', $eeValue);
					if(strpos($eeField[0], 'ubject')) {
						$eeSubject = html_entity_decode($eeField[1], ENT_QUOTES);
						$eeSubject = stripslashes($eeSubject);
					}
			}
			if(empty($eeSubject)) { $eeSubject = sprintf(__('Contact Form Message (%s)', 'rock-solid-contact-form'), basename(home_url())); }

			// Email assembly
			if(empty($this->formSettings['email'])) {
				$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : 'localhost';
				$this->formSettings['email'] = 'mail@' . $host;
			} // Fallback
			$eeHeaders = "From: " . get_bloginfo('name') . ' <' . $this->formSettings['email'] . ">" . PHP_EOL;
			if($this->formSettings['cc']) { $eeHeaders .= "CC: " . $this->formSettings['cc'] . PHP_EOL; }
			if($this->formSettings['bcc']) { $eeHeaders .= "BCC: " . $this->formSettings['bcc'] . PHP_EOL; }
			$eeHeaders .= "Return-Path: " . $this->formSettings['email'] . PHP_EOL . "Reply-To: " . $this->sender . PHP_EOL;

			$eeBody = '';

			foreach ($this->thePost as $value) {
				$eeBody .= $value . PHP_EOL . PHP_EOL;
			}

			if($eeFileURL) { $eeBody .= __('File:', 'rock-solid-contact-form') . ' ' . $eeFileURL . PHP_EOL . PHP_EOL; }

		$eeBody .=  PHP_EOL . PHP_EOL . sprintf(__('This message was sent via the contact form located at %s', 'rock-solid-contact-form'), home_url() . '/') . PHP_EOL . PHP_EOL;

		$eeBody = stripslashes($eeBody);
		$eeBody = wp_strip_all_tags(htmlspecialchars_decode($eeBody, ENT_QUOTES));

		// Ensure we have a valid 'to' address
		if (empty($this->formSettings['to'])) {
			$this->formSettings['to'] = get_option('admin_email');
		}

		if( wp_mail($this->formSettings['to'], $eeSubject, $eeBody, $eeHeaders) ) {

			eeRSCF_Debug_Log('WP Mail Sent successfully', 'SendEmail');

			// Remove SMTP hook to prevent affecting other emails
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}

				wp_redirect($this->confirm);

				exit;

			} else {

				eeRSCF_Debug_Log('PHP Message Failed to Send.', 'SendEmail');

				// Remove SMTP hook even on failure
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}
			}

		} else {
			eeRSCF_Debug_Log('Message not sent. Please try again.', 'SendEmail');
		}
	}



    // Configure SMTP for WordPress PHPMailer
	public function eeRSCF_configure_smtp($phpmailer) {

		// Only configure SMTP if the setting is enabled
		if ($this->formSettings['emailMode'] == 'SMTP') {

			eeRSCF_Debug_Log('Configuring SMTP...', 'SMTP');

			// Enable SMTP
			$phpmailer->isSMTP();

			// SMTP Server Configuration
			if (!empty($this->formSettings['emailServer'])) {
				$phpmailer->Host = $this->formSettings['emailServer'];
			}

			// Authentication
			if ($this->formSettings['emailAuth']) {
				$phpmailer->SMTPAuth = true;
				if (!empty($this->formSettings['emailUsername'])) {
					$phpmailer->Username = $this->formSettings['emailUsername'];
				}
				if (!empty($this->formSettings['emailPassword'])) {
					$phpmailer->Password = $this->formSettings['emailPassword'];
				}
			} else {
				$phpmailer->SMTPAuth = false;
			}

			// Security/Encryption
			if (!empty($this->formSettings['emailSecure'])) {
				if ($this->formSettings['emailSecure'] == 'SSL') {
					$phpmailer->SMTPSecure = 'ssl';
				} elseif ($this->formSettings['emailSecure'] == 'TLS') {
					$phpmailer->SMTPSecure = 'tls';
				}
			}

			// Port
			if (!empty($this->formSettings['emailPort'])) {
				$phpmailer->Port = (int) $this->formSettings['emailPort'];
			}

			// Debug Mode
			if ($this->formSettings['emailDebug'] && defined('WP_DEBUG') && WP_DEBUG) {
				$phpmailer->SMTPDebug = 2; // Enable verbose debug output
				$phpmailer->Debugoutput = function($str, $level) {
					// error_log("RSCF SMTP Debug: " . sanitize_text_field($str));
				};
			}

			// From Name
			if (!empty($this->formSettings['emailName'])) {
				$phpmailer->FromName = $this->formSettings['emailName'];
			}

			// HTML Format
			if ($this->formSettings['emailFormat'] == 'HTML') {
				$phpmailer->isHTML(true);
			} else {
				$phpmailer->isHTML(false);
			}

			eeRSCF_Debug_Log('SMTP Configuration Complete', 'SMTP');
		}
	}







	private function eeRSCF_formSpamCheck() {

		// DEBUG: First thing - check if we're even reaching this function
		eeRSCF_Debug_Log('formSpamCheck function called', 'SpamCheck');

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			$this->log['catch'][] = 'Nonce verification failed';
			eeRSCF_Debug_Log('Nonce verification failed!', 'SpamCheck');
			return false;
		}

		eeRSCF_Debug_Log('Form Spam Check...', 'SpamCheck');

		$this->log['catch'] = array();

		$tamper = FALSE;
		$entries = array();

		$eeArray = array_filter($_POST); // Get rid of empty fields
		$eeCount = count($eeArray); // How many filled in fields?

		// DEBUG: Log current spam settings
		eeRSCF_Debug_Log('spamBlock = ' . ($this->formSettings['spamBlock'] ?? 'NOT SET'), 'SpamCheck');
		eeRSCF_Debug_Log('spamBlockBots = ' . ($this->formSettings['spamBlockBots'] ?? 'NOT SET'), 'SpamCheck');
		eeRSCF_Debug_Log('spamEnglishOnly = ' . ($this->formSettings['spamEnglishOnly'] ?? 'NOT SET'), 'SpamCheck');
		eeRSCF_Debug_Log('spamBlockFishy = ' . ($this->formSettings['spamBlockFishy'] ?? 'NOT SET'), 'SpamCheck');
		eeRSCF_Debug_Log('spamBlockWords = ' . ($this->formSettings['spamBlockWords'] ?? 'NOT SET'), 'SpamCheck');
		eeRSCF_Debug_Log('Filtered POST array count: ' . count($eeArray), 'SpamCheck');

		// Spam Bots
		if($this->formSettings['spamBlockBots'] == 'YES') {
			eeRSCF_Debug_Log('Checking honeypot...', 'SpamCheck');

			// Make sure honeypot field name is set
			$honeypotField = isset($this->formSettings['spamHoneypot']) ? $this->formSettings['spamHoneypot'] : 'link';

			if($this->formSettings['spamBlock'] AND isset($_POST[$honeypotField]) AND !empty(sanitize_text_field(wp_unslash($_POST[$honeypotField])))) { // Honeypot. This field should never be completed.
				$this->log['catch'][] = 'Spambot Catch: Honeypot Field Completed.';
				eeRSCF_Debug_Log('Honeypot triggered! Field: ' . $honeypotField . ' Value: ' . sanitize_text_field(wp_unslash($_POST[$honeypotField])), 'SpamCheck');
			} else {
				eeRSCF_Debug_Log('Honeypot check passed. Field: ' . $honeypotField . ' Value: "' . (isset($_POST[$honeypotField]) ? sanitize_text_field(wp_unslash($_POST[$honeypotField])) : 'NOT SET') . '"', 'SpamCheck');
			}
		}		// English Only
		if($this->formSettings['spamEnglishOnly'] == 'YES') {
			eeRSCF_Debug_Log('Checking English only...', 'SpamCheck');

			foreach($eeArray as $eeKey => $eeValue) {

				if($eeValue) {
					$entries[] = $eeValue;

					// If you can't read it, block it.
					if(preg_match('/[А-Яа-яЁё]/u', $eeValue) OR preg_match('/\p{Han}+/u', $eeValue)) {
						$this->log['catch'][] = "Non-English Language Detected";
						eeRSCF_Debug_Log('Non-English detected in field: ' . $eeKey . ' Value: ' . $eeValue, 'SpamCheck');
						break;
					}
				}
			}
			eeRSCF_Debug_Log('English only check completed', 'SpamCheck');
		}

		// Block Fishiness
		if($this->formSettings['spamBlockFishy'] == 'YES') {
			eeRSCF_Debug_Log('Checking fishy content...', 'SpamCheck');

			// Check for duplicated info in fields (spam)
			$eeValues = array_count_values($eeArray);
			foreach($eeValues as $eeValue) {
				if($eeValue > 2) {
					$this->log['catch'][] = "3x Duplicated Same Field Entries";
					eeRSCF_Debug_Log('Duplicate entries detected', 'SpamCheck');
				}
			}

			foreach( $eeArray as $eeKey => $eeValue) {

				if(strpos($eeValue, '&#') OR strpos($eeValue, '&#') === 0) {
					$this->log['catch'][] = "Malicious Submission";
					eeRSCF_Debug_Log('Malicious submission detected in field: ' . $eeKey, 'SpamCheck');
				}

				if(strpos($eeValue, '[url]') OR strpos($eeValue, '[url]') === 0) {
					$this->log['catch'][] = "Form Tampering";
					eeRSCF_Debug_Log('Form tampering detected in field: ' . $eeKey, 'SpamCheck');
				}

				if(strlen(wp_strip_all_tags($eeValue)) != strlen($eeValue) ) {
					$this->log['catch'][] = "HTML Tags Found";
					eeRSCF_Debug_Log('HTML tags found in field: ' . $eeKey . ' Original: ' . strlen($eeValue) . ' Stripped: ' . strlen(wp_strip_all_tags($eeValue)), 'SpamCheck');
				}
			}
			eeRSCF_Debug_Log('Fishy content check completed', 'SpamCheck');
		}


		// Block Words
		if($this->formSettings['spamBlockWords'] == 'YES') {

			// Update the Common SPAM Words
			// This is a new line delineated list of common phrases used in email spam
			$spamBlockedCommonWords_LOCAL = explode(',', $this->formSettings['spamBlockedWords']);

			// TODO: Implement remote spam words functionality if needed
			// For now, only use local spam words to prevent undefined function errors
			$spamBlockedCommonWords_REMOTE = array();

			$this->formSettings['spamBlockedWords'] = array_merge($spamBlockedCommonWords_LOCAL, $spamBlockedCommonWords_REMOTE);
			$this->formSettings['spamBlockedWords'] = array_map('trim', $this->formSettings['spamBlockedWords']);

			if( !empty($this->formSettings['spamBlockedWords']) ) {

				foreach($eeArray as $eeValue) {

					// The text we check for blocked terms
					$eeValue = preg_replace('/[!?.]/', '', $eeValue); // Strip off common punctuations

					// Check if any spam words are in the message
					foreach ($this->formSettings['spamBlockedWords'] as $spamWord) {

						// Escape any special characters in the spam word
						$safeSpamWord = preg_quote($spamWord, '/');

						// Use regex to detect the word with word boundaries (\b) and case-insensitive matching ('i')
						if (preg_match('/\b' . $safeSpamWord . '\b/i', $eeValue)) {
							if($spamWord) {
								$this->log['catch'][] = 'Spam Word Catch: ' . $spamWord;
							}
						}
					}
				}
			}
		}

		// If we detect spam, and the users want a report, create and send it here
		if (count($this->log['catch']) >= 1 && $this->formSettings['spamSendAttackNotice'] == 'YES') {

			$eeTo = $this->formSettings['spamNoticeEmail'];
			$eeSubject = "Spam Block Notice";

			$eeBody = "Contact Form Spam Catch" . PHP_EOL;
			$eeBody .= "-----------------------------------" . PHP_EOL . PHP_EOL;
			foreach ($this->log['catch'] as $eeError) {
				$eeBody .= $eeError . PHP_EOL;
			}
			$eeBody .= PHP_EOL . "Attacker" . PHP_EOL;
			$eeBody .= "-----------------------------------" . PHP_EOL;
			$eeBody .= "User Agent: " . sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? 'Not Available')) . PHP_EOL;
			$eeBody .= "User IP: " . sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? 'Not Available')) . PHP_EOL;
			$eeBody .= "Came From: " . sanitize_text_field(wp_unslash($_POST['SCRIPT_REFERER'] ?? '')) . sanitize_text_field(wp_unslash($_SERVER['QUERY_STRING'] ?? '')) . PHP_EOL;
			$eeBody .= "Attacker Message" . PHP_EOL . "-----------------------------------" . PHP_EOL;

			// Get post data for the notice (process locally for spam report)
			$postData = $this->eeRSCF_PostProcess();
			if (is_array($postData)) {
				$eeBody .= implode("\n\n", $postData) . PHP_EOL . PHP_EOL;
			}

			$eeBody .= "-----------------------------------" . PHP_EOL;
			$eeBody .= "Via Rock Solid Contact Form at " . home_url() . PHP_EOL;

			$eeHeaders = array(
				'From: ' . $this->formSettings['email'],
				'Reply-To: ' . $this->formSettings['email'],
				'Content-Type: text/plain; charset=UTF-8',
			);

			// Send Notice Email
			if (function_exists('wp_mail')) {
				// Use SMTP for spam notices too if configured
				if ($this->formSettings['emailMode'] == 'SMTP') {
					add_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}

				if (!wp_mail($eeTo, $eeSubject, $eeBody, $eeHeaders)) {
					eeRSCF_Debug_Log('Notice Email Failed to Send', 'SpamCheck');
				}

				// Remove SMTP hook after sending
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}
			} else {
				mail($eeTo, $eeSubject, $eeBody, $eeHeaders);
			}
		}


		if(count($this->log['catch']) >= 1) {

			eeRSCF_Debug_Log('Spam Check FAIL!', 'SpamCheck');
			eeRSCF_Debug_Log('Spam triggers: ' . implode(', ', $this->log['catch']), 'SpamCheck');

			// Log detailed debugging information for development
			eeRSCF_Debug_Log('Spam Detection Triggered: ' . implode(', ', $this->log['catch']), 'SpamCheck');

			return TRUE; // THIS IS SPAM !!!

		} else {

			eeRSCF_Debug_Log('Spam Check OKAY!', 'SpamCheck');
			return FALSE; // Seems okay...
		}
	}








	// Notice Email
	public function eeRSCF_NoticeEmail($messages, $to, $from, $name = '') {

		if($messages AND $to AND $from) {

			$body = '';
			$headers = "From: $from";
			$subject = $name . " Admin Notice";

			if(is_array($messages)) {
				foreach ($messages as $value) {
					if(is_array($value)) {
						foreach ($value as $value2) {
							$body .= sanitize_text_field($value2) . "\n\n";
						}
					} else {
						$body .= sanitize_text_field($value) . "\n\n";
					}
				}
			} else {
				$body = sanitize_text_field($messages) . "\n\n";
			}

			$http_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
			$php_self = isset($_SERVER['PHP_SELF']) ? sanitize_text_field(wp_unslash($_SERVER['PHP_SELF'])) : '';
			$body .= 'Via: ' . $http_host . $php_self;

			// Use WordPress mail function with SMTP support if configured
			$headers_array = array('From: ' . $from);

			// Configure SMTP if enabled
			if ($this->formSettings['emailMode'] == 'SMTP') {
				add_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
			}

			$mail_sent = wp_mail($to, $subject, $body, $headers_array);

			// Remove SMTP hook after sending
			if ($this->formSettings['emailMode'] == 'SMTP') {
				remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
			}

			if (!$mail_sent) {
				eeRSCF_Debug_Log('Notice email failed to send', 'NoticeEmail');
				return FALSE;
			}

			return TRUE;

		} else {
			eeRSCF_Debug_Log('Notice email missing required parameters', 'NoticeEmail');
			return FALSE;
		}
	}


}