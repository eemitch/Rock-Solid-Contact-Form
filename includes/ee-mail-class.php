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

	// Properties needed for mail processing
	public $log = array(
		'notices' => array(),
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
		if (eeRSCF_Debug) {
			error_log('RSCF DEBUG [PostProcess]: Starting post processing...');
			error_log('RSCF DEBUG [PostProcess]: POST data count: ' . count($_POST));
		}

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			$this->log['catch'][] = 'Nonce verification failed';
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [PostProcess]: Nonce verification failed!');
			}
			return false;
		}

		if (eeRSCF_Debug) {
			error_log('RSCF DEBUG [PostProcess]: Nonce verification passed');
		}

		$this->log['notices'][] = 'Processing the post...';

		$eeIgnore = ['eeRSCF', 'eeRSCF_ID', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER'];

		if (eeRSCF_Debug) {
			error_log('RSCF DEBUG [PostProcess]: Ignored fields: ' . implode(', ', $eeIgnore));
		}

		foreach ($_POST as $eeKey => $eeValue) {

			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [PostProcess]: Processing field: ' . $eeKey . ' = "' . $eeValue . '"');
			}

			if (in_array($eeKey, $eeIgnore) || empty($eeValue)) {
				if (eeRSCF_Debug) {
					error_log('RSCF DEBUG [PostProcess]: Skipping field ' . $eeKey . ' (ignored or empty)');
				}
				continue;
			}

			// SECURITY: Comprehensive input validation and sanitization
			$originalValue = $eeValue;

			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [PostProcess]: Sanitizing field ' . $eeKey . ' with original value: "' . $originalValue . '"');
			}

			// Sanitize and validate specific fields
			switch (true) {
				case strpos($eeKey, 'mail') !== false:
					$eeValue = sanitize_email($eeValue);
					if (!is_email($eeValue)) {
						if (eeRSCF_Debug) {
							echo "<!-- RSCF DEBUG: Email validation error: " . esc_html(__('Your email address is not correct.', 'rock-solid-contact-form')) . " -->";
							error_log('RSCF DEBUG [PostProcess]: Invalid email detected: ' . $originalValue);
						}
						continue 2; // Skip to the next $_POST item
					}
					$this->sender = strtolower($eeValue);
					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [PostProcess]: Email field processed, sender set to: ' . $this->sender);
					}
					break;
				case strpos($eeKey, 'ebsite') !== false:
					$eeValue = esc_url_raw($eeValue, ['http', 'https']);
					if (empty($eeValue)) {
						if (eeRSCF_Debug) {
							echo "<!-- RSCF DEBUG: Website validation error: " . esc_html(__('Your website address is not correct.', 'rock-solid-contact-form')) . " -->";
							error_log('RSCF DEBUG [PostProcess]: Invalid website URL detected: ' . $originalValue);
						}
						continue 2; // Skip to the next $_POST item
					}
					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [PostProcess]: Website field processed: ' . $eeValue);
					}
					break;
				default:
					// SECURITY: Enhanced sanitization for all other fields
					if ($this->mainClass && method_exists($this->mainClass, 'eeRSCF_SecureSanitize')) {
						$eeValue = $this->mainClass->eeRSCF_SecureSanitize($eeValue, $eeKey);

						// SECURITY: Reject if sanitization changed the content significantly
						if (method_exists($this->mainClass, 'eeRSCF_SecurityCheck') && $this->mainClass->eeRSCF_SecurityCheck($originalValue, $eeValue, $eeKey)) {
							if (eeRSCF_Debug) {
								echo "<!-- RSCF DEBUG: Security check error: " . esc_html(sprintf(__('Invalid content detected in %s field. Please remove any scripts, HTML tags, or suspicious characters.', 'rock-solid-contact-form'), $this->mainClass->eeUnSlug($eeKey))) . " -->";
								error_log('RSCF DEBUG [PostProcess]: Security check failed for field ' . $eeKey . '. Original: "' . $originalValue . '" Sanitized: "' . $eeValue . '"');
							}
							continue 2; // Skip to the next $_POST item
						}
					} else {
						// Fallback sanitization if main class methods not available
						$eeValue = sanitize_text_field($eeValue);
						if (eeRSCF_Debug) {
							error_log('RSCF DEBUG [PostProcess]: Using fallback sanitization for field: ' . $eeKey);
						}
					}

					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [PostProcess]: Default field processed. Original: "' . $originalValue . '" Sanitized: "' . $eeValue . '"');
					}
					break;
			}

			$eeField = $this->mainClass && method_exists($this->mainClass, 'eeUnSlug') ? $this->mainClass->eeUnSlug($eeKey) : ucwords(str_replace('-', ' ', $eeKey));
			$processedEntry = $eeField . ': ' . $eeValue;
			$this->thePost[] = $processedEntry;

			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [PostProcess]: Added to thePost: "' . $processedEntry . '"');
			}
		}

		$this->log['notices'][] = $this->thePost;

		if (eeRSCF_Debug) {
			error_log('RSCF DEBUG [PostProcess]: Processing completed. Total entries: ' . count($this->thePost));
			error_log('RSCF DEBUG [PostProcess]: Final thePost array: ' . print_r($this->thePost, true));
		}

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
			if (eeRSCF_Debug) {
				echo "<!-- RSCF DEBUG: Nonce verification failed -->";
				error_log('RSCF DEBUG [SendEmail]: Submission is not from this website');
			}
			return FALSE;
		}

		$this->log['notices'][] = 'Sending the Email...';

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
						if (eeRSCF_Debug) {
							echo "<!-- RSCF DEBUG: File type error: " . esc_html(sprintf(__('File type %s is not allowed', 'rock-solid-contact-form'), $fileExt)) . " -->";
							error_log('RSCF DEBUG [SendEmail]: File type not allowed: ' . $fileExt);
						}
					}
				} else {
					if (eeRSCF_Debug) {
						echo "<!-- RSCF DEBUG: File size error: " . esc_html(__('File is too large', 'rock-solid-contact-form')) . " -->";
						error_log('RSCF DEBUG [SendEmail]: File too large: ' . $_FILES['file']['size'] . ' bytes');
					}
				}
			} else {
				if (eeRSCF_Debug) {
					echo "<!-- RSCF DEBUG: Invalid file upload data -->";
					error_log('RSCF DEBUG [SendEmail]: Invalid file upload data');
				}
			}
		}

		if(!$this->log['errors'] AND !empty($this->thePost)) {

			$this->log['notices'][] = 'Preparing the Email...';

			// Configure SMTP if enabled
			if ($this->formSettings['emailMode'] == 'SMTP') {
				add_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				$this->log['notices'][] = 'SMTP Mode Enabled';
			} else {
				$this->log['notices'][] = 'Using WordPress Default Mailer';
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

			$this->log['notices'][] = 'WP Mail Sent';				// Remove SMTP hook to prevent affecting other emails
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}

				wp_redirect($this->confirm);

				exit;

			} else {

				if (eeRSCF_Debug) {
					echo "<!-- RSCF DEBUG: PHP mail failed to send -->";
					error_log('RSCF DEBUG [SendEmail]: PHP Message Failed to Send.');
				}

				// Remove SMTP hook even on failure
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}
			}

		} else {
			if (eeRSCF_Debug) {
				echo "<!-- RSCF DEBUG: Message not sent, errors in form processing -->";
				error_log('RSCF DEBUG [SendEmail]: Message not sent. Please try again.');
			}
		}
	}



    // Configure SMTP for WordPress PHPMailer
	public function eeRSCF_configure_smtp($phpmailer) {

		// Only configure SMTP if the setting is enabled
		if ($this->formSettings['emailMode'] == 'SMTP') {

			$this->log['notices'][] = 'Configuring SMTP...';

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

			$this->log['notices'][] = 'SMTP Configuration Complete';
		}
	}







	private function eeRSCF_formSpamCheck() {

		// DEBUG: First thing - check if we're even reaching this function
		if (defined('eeRSCF_Debug') && eeRSCF_Debug) {
			echo "<!-- RSCF DEBUG: formSpamCheck function started -->";
			error_log('RSCF DEBUG: formSpamCheck function called');
		}

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			$this->log['catch'][] = 'Nonce verification failed';
			if (defined('eeRSCF_Debug') && eeRSCF_Debug) {
				echo "<!-- RSCF DEBUG: Nonce verification failed in spam check -->";
				error_log('RSCF DEBUG [SpamCheck]: Nonce verification failed!');
			}
			return false;
		}

		$this->log['notices'][] = 'Form Spam Check...';
		$this->log['catch'] = array();

		$tamper = FALSE;
		$entries = array();

		$eeArray = array_filter($_POST); // Get rid of empty fields
		$eeCount = count($eeArray); // How many filled in fields?

		// DEBUG: Log current spam settings
		if (eeRSCF_Debug) {
			error_log('RSCF DEBUG [SpamCheck]: spamBlock = ' . ($this->formSettings['spamBlock'] ?? 'NOT SET'));
			error_log('RSCF DEBUG [SpamCheck]: spamBlockBots = ' . ($this->formSettings['spamBlockBots'] ?? 'NOT SET'));
			error_log('RSCF DEBUG [SpamCheck]: spamEnglishOnly = ' . ($this->formSettings['spamEnglishOnly'] ?? 'NOT SET'));
			error_log('RSCF DEBUG [SpamCheck]: spamBlockFishy = ' . ($this->formSettings['spamBlockFishy'] ?? 'NOT SET'));
			error_log('RSCF DEBUG [SpamCheck]: spamBlockWords = ' . ($this->formSettings['spamBlockWords'] ?? 'NOT SET'));
			error_log('RSCF DEBUG [SpamCheck]: Filtered POST array count: ' . count($eeArray));
		}

		// Spam Bots
		if($this->formSettings['spamBlockBots'] == 'YES') {
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [SpamCheck]: Checking honeypot...');
			}

			// Make sure honeypot field name is set
			$honeypotField = isset($this->formSettings['spamHoneypot']) ? $this->formSettings['spamHoneypot'] : 'link';

			if($this->formSettings['spamBlock'] AND isset($_POST[$honeypotField]) AND !empty(sanitize_text_field(wp_unslash($_POST[$honeypotField])))) { // Honeypot. This field should never be completed.
				$this->log['catch'][] = 'Spambot Catch: Honeypot Field Completed.';
				if (eeRSCF_Debug) {
					error_log('RSCF DEBUG [SpamCheck]: Honeypot triggered! Field: ' . $honeypotField . ' Value: ' . sanitize_text_field(wp_unslash($_POST[$honeypotField])));
				}
			} else {
				if (eeRSCF_Debug) {
					error_log('RSCF DEBUG [SpamCheck]: Honeypot check passed. Field: ' . $honeypotField . ' Value: "' . (isset($_POST[$honeypotField]) ? sanitize_text_field(wp_unslash($_POST[$honeypotField])) : 'NOT SET') . '"');
				}
			}
		}		// English Only
		if($this->formSettings['spamEnglishOnly'] == 'YES') {
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [SpamCheck]: Checking English only...');
			}

			foreach($eeArray as $eeKey => $eeValue) {

				if($eeValue) {
					$entries[] = $eeValue;

					// If you can't read it, block it.
					if(preg_match('/[А-Яа-яЁё]/u', $eeValue) OR preg_match('/\p{Han}+/u', $eeValue)) {
						$this->log['catch'][] = "Non-English Language Detected";
						if (eeRSCF_Debug) {
							error_log('RSCF DEBUG [SpamCheck]: Non-English detected in field: ' . $eeKey . ' Value: ' . $eeValue);
						}
						break;
					}
				}
			}
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [SpamCheck]: English only check completed');
			}
		}

		// Block Fishiness
		if($this->formSettings['spamBlockFishy'] == 'YES') {
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [SpamCheck]: Checking fishy content...');
			}

			// Check for duplicated info in fields (spam)
			$eeValues = array_count_values($eeArray);
			foreach($eeValues as $eeValue) {
				if($eeValue > 2) {
					$this->log['catch'][] = "3x Duplicated Same Field Entries";
					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [SpamCheck]: Duplicate entries detected');
					}
				}
			}

			foreach( $eeArray as $eeKey => $eeValue) {

				if(strpos($eeValue, '&#') OR strpos($eeValue, '&#') === 0) {
					$this->log['catch'][] = "Malicious Submission";
					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [SpamCheck]: Malicious submission detected in field: ' . $eeKey);
					}
				}

				if(strpos($eeValue, '[url]') OR strpos($eeValue, '[url]') === 0) {
					$this->log['catch'][] = "Form Tampering";
					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [SpamCheck]: Form tampering detected in field: ' . $eeKey);
					}
				}

				if(strlen(wp_strip_all_tags($eeValue)) != strlen($eeValue) ) {
					$this->log['catch'][] = "HTML Tags Found";
					if (eeRSCF_Debug) {
						error_log('RSCF DEBUG [SpamCheck]: HTML tags found in field: ' . $eeKey . ' Original: ' . strlen($eeValue) . ' Stripped: ' . strlen(wp_strip_all_tags($eeValue)));
					}
				}
			}
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [SpamCheck]: Fishy content check completed');
			}
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
					if (eeRSCF_Debug) {
						echo "<!-- RSCF DEBUG: Notice email failed to send -->";
						error_log('RSCF DEBUG [SpamCheck]: Notice Email Failed to Send');
					}
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

			$this->log['notices'][] = 'Spam Check FAIL!';
			$this->log['notices'][] = 'Spam triggers: ' . implode(', ', $this->log['catch']);

			// Log detailed debugging information for development
			if (eeRSCF_Debug) {
				error_log('RSCF DEBUG [SpamCheck]: Spam Detection Triggered: ' . implode(', ', $this->log['catch']));
			}

			return TRUE; // THIS IS SPAM !!!

		} else {

			$this->log['notices'][] = 'Spam Check OKAY!';
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
				if (eeRSCF_Debug) {
					echo "<!-- RSCF DEBUG: Notice email failed to send -->";
					error_log('RSCF DEBUG [NoticeEmail]: Notice email failed to send');
				}
				return FALSE;
			}

			return TRUE;

		} else {
			if (eeRSCF_Debug) {
				echo "<!-- RSCF DEBUG: Notice email missing required parameters -->";
				error_log('RSCF DEBUG [NoticeEmail]: Notice email missing required parameters');
			}
			return FALSE;
		}
	}


}