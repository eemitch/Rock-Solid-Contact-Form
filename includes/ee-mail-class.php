<?php
/**
 * Email processing class for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class eeRSCF_Mail_Class
 * Handles all email processing, form data processing, and SMTP configuration
 */
class eeRSCF_Mail_Class {

	// Reference to main class for accessing properties
	private $mainClass;

	/**
	 * Constructor
	 */
	public function __construct($mainClass) {
		$this->mainClass = $mainClass;
	}

	/**
	 * Process form POST data with comprehensive security validation
	 * 
	 * @return array|false Processed form data or false on error
	 */
	public function eeRSCF_PostProcess() {

		global $eeHelper;

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			$this->mainClass->log['catch'][] = 'Nonce verification failed';
			return false;
		}

		$this->mainClass->log['notices'][] = 'Processing the post...';

		$eeIgnore = ['eeRSCF', 'eeRSCF_ID', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER'];

		foreach ($_POST as $eeKey => $eeValue) {

			if (in_array($eeKey, $eeIgnore) || empty($eeValue)) {
				continue;
			}

			// SECURITY: Comprehensive input validation and sanitization
			$originalValue = $eeValue;

			// Sanitize and validate specific fields
			switch (true) {
				case strpos($eeKey, 'mail') !== false:
					$eeValue = sanitize_email($eeValue);
					if (!is_email($eeValue)) {
						$this->mainClass->log['errors'][] = 'Your email address is not correct.';
						continue 2; // Skip to the next $_POST item
					}
					$this->mainClass->sender = strtolower($eeValue);
					break;
				case strpos($eeKey, 'ebsite') !== false:
					$eeValue = esc_url_raw($eeValue, ['http', 'https']);
					if (empty($eeValue)) {
						$this->mainClass->log['errors'][] = 'Your website address is not correct.';
						continue 2; // Skip to the next $_POST item
					}
					break;
				default:
					// SECURITY: Enhanced sanitization for all other fields
					$eeValue = $this->mainClass->eeRSCF_SecureSanitize($eeValue, $eeKey);

					// SECURITY: Reject if sanitization changed the content significantly
					if ($this->mainClass->eeRSCF_SecurityCheck($originalValue, $eeValue, $eeKey)) {
						$this->mainClass->log['errors'][] = 'Invalid content detected in ' . $eeHelper->eeUnSlug($eeKey) . ' field. Please remove any scripts, HTML tags, or suspicious characters.';
						continue 2; // Skip to the next $_POST item
					}
					break;
			}

			$eeField = $eeHelper->eeUnSlug($eeKey);
			$this->mainClass->thePost[] = $eeField . ': ' . $eeValue;
		}

		$this->mainClass->log['notices'][] = $this->mainClass->thePost;

		return $this->mainClass->thePost;
	}

	/**
	 * Send email with comprehensive security and SMTP support
	 * 
	 * @return void
	 */
	public function eeRSCF_SendEmail() {

		global $eeHelper; // Get Upload Class

		// Are we Blocking SPAM?
		if($this->mainClass->formSettings['spamBlock'] == 'YES') {
			if( $this->mainClass->eeRSCF_formSpamCheck() === TRUE ) { // This is SPAM
				wp_die('Sorry, there was a problem with your message content. Please go back and try again.');
			}
		}

		// Check referrer is from same site.
		if(!isset($_REQUEST['ee-rock-solid-nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['ee-rock-solid-nonce'])), 'ee-rock-solid')) {
			$this->mainClass->log['errors'][] =  "Submission is not from this website";
			return FALSE;
		}

		$this->mainClass->log['notices'][] = 'Sending the Email...';

		$this->eeRSCF_PostProcess();

		// File Attachment
		$eeFileURL = FALSE;
		if(!empty($_FILES['file']) AND $this->mainClass->formSettings['fields']['attachments']['show'] == 'YES') {

			$formatsArray = explode(',', $this->mainClass->formSettings['fileFormats']);
			$formatsArray = array_filter(array_map('trim', $formatsArray));

			// Validate file data exists before accessing
			if (isset($_FILES['file']['name']) && isset($_FILES['file']['size'])) {
				$fileExt = strtolower(pathinfo(sanitize_file_name($_FILES['file']['name']), PATHINFO_EXTENSION));
				$max_size = $this->mainClass->formSettings['fileMaxSize'] * 1048576; // Convert MB to Bytes

				if( $_FILES['file']['size'] <= $max_size ) {
					if( in_array($fileExt,$formatsArray) ) {
						$eeFileURL = $eeHelper->eeUploader($_FILES['file'],  'ee-contact'  );
					} else {
						$this->mainClass->log['errors'][] = 'FileType ' . $fileExt . ' Not Allowed';
					}
				} else {
					$this->mainClass->log['errors'][] = 'File Too Large';
				}
			} else {
				$this->mainClass->log['errors'][] = 'Invalid file upload data';
			}
		}

		if(!$this->mainClass->log['errors'] AND !empty($this->mainClass->thePost)) {

			$this->mainClass->log['notices'][] = 'Preparing the Email...';

			// Configure SMTP if enabled
			if ($this->mainClass->formSettings['emailMode'] == 'SMTP') {
				add_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				$this->mainClass->log['notices'][] = 'SMTP Mode Enabled';
			} else {
				$this->mainClass->log['notices'][] = 'Using WordPress Default Mailer';
			}

			// Loop through and see if we have a Subject field
			foreach($this->mainClass->thePost as $eeValue){
				$eeField = explode(':', $eeValue);
					if(strpos($eeField[0], 'ubject')) {
						$eeSubject = html_entity_decode($eeField[1], ENT_QUOTES);
						$eeSubject = stripslashes($eeSubject);
					}
			}
			if(empty($eeSubject)) { $eeSubject = 'Contact Form Message (' . basename(home_url()) . ')'; }

			// Email assembly
			if(empty($this->mainClass->formSettings['email'])) {
				$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : 'localhost';
				$this->mainClass->formSettings['email'] = 'mail@' . $host;
			} // Fallback
			$eeHeaders = "From: " . get_bloginfo('name') . ' <' . $this->mainClass->formSettings['email'] . ">" . PHP_EOL;
			if($this->mainClass->formSettings['cc']) { $eeHeaders .= "CC: " . $this->mainClass->formSettings['cc'] . PHP_EOL; }
			if($this->mainClass->formSettings['bcc']) { $eeHeaders .= "BCC: " . $this->mainClass->formSettings['bcc'] . PHP_EOL; }
			$eeHeaders .= "Return-Path: " . $this->mainClass->formSettings['email'] . PHP_EOL . "Reply-To: " . $this->mainClass->sender . PHP_EOL;

			$eeBody = '';

			foreach ($this->mainClass->thePost as $value) {
				$eeBody .= $value . PHP_EOL . PHP_EOL;
			}

			if($eeFileURL) { $eeBody .= 'File: ' . $eeFileURL . PHP_EOL . PHP_EOL; }

			$eeBody .=  PHP_EOL . PHP_EOL . 'This message was sent via the contact form located at ' . home_url() . '/' . PHP_EOL . PHP_EOL;

			$eeBody = stripslashes($eeBody);
			$eeBody = wp_strip_all_tags(htmlspecialchars_decode($eeBody, ENT_QUOTES));

			if( wp_mail($this->mainClass->formSettings['to'], $eeSubject, $eeBody, $eeHeaders) ) {

				$this->mainClass->log['notices'][] = 'WP Mail Sent';

				// Remove SMTP hook to prevent affecting other emails
				if ($this->mainClass->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}

				wp_redirect($this->mainClass->confirm);

				exit;

			} else {

				$this->mainClass->log['errors'][] = 'PHP Message Failed to Send.';

				// Remove SMTP hook even on failure
				if ($this->mainClass->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}
			}

		} else {
			$this->mainClass->log['errors'][] = 'Message not sent. Please try again.';
		}
	}

	/**
	 * Configure SMTP for WordPress PHPMailer
	 * 
	 * @param object $phpmailer PHPMailer instance
	 * @return void
	 */
	public function eeRSCF_configure_smtp($phpmailer) {

		// Only configure SMTP if the setting is enabled
		if ($this->mainClass->formSettings['emailMode'] == 'SMTP') {

			$this->mainClass->log['notices'][] = 'Configuring SMTP...';

			// Enable SMTP
			$phpmailer->isSMTP();

			// SMTP Server Configuration
			if (!empty($this->mainClass->formSettings['emailServer'])) {
				$phpmailer->Host = $this->mainClass->formSettings['emailServer'];
			}

			// Authentication
			if ($this->mainClass->formSettings['emailAuth']) {
				$phpmailer->SMTPAuth = true;
				if (!empty($this->mainClass->formSettings['emailUsername'])) {
					$phpmailer->Username = $this->mainClass->formSettings['emailUsername'];
				}
				if (!empty($this->mainClass->formSettings['emailPassword'])) {
					$phpmailer->Password = $this->mainClass->formSettings['emailPassword'];
				}
			} else {
				$phpmailer->SMTPAuth = false;
			}

			// Security/Encryption
			if (!empty($this->mainClass->formSettings['emailSecure'])) {
				if ($this->mainClass->formSettings['emailSecure'] == 'SSL') {
					$phpmailer->SMTPSecure = 'ssl';
				} elseif ($this->mainClass->formSettings['emailSecure'] == 'TLS') {
					$phpmailer->SMTPSecure = 'tls';
				}
			}

			// Port
			if (!empty($this->mainClass->formSettings['emailPort'])) {
				$phpmailer->Port = (int) $this->mainClass->formSettings['emailPort'];
			}

			// Debug Mode
			if ($this->mainClass->formSettings['emailDebug'] && defined('WP_DEBUG') && WP_DEBUG) {
				$phpmailer->SMTPDebug = 2; // Enable verbose debug output
				$phpmailer->Debugoutput = function($str, $level) {
					// error_log("RSCF SMTP Debug: " . sanitize_text_field($str));
				};
			}

			// From Name
			if (!empty($this->mainClass->formSettings['emailName'])) {
				$phpmailer->FromName = $this->mainClass->formSettings['emailName'];
			}

			// HTML Format
			if ($this->mainClass->formSettings['emailFormat'] == 'HTML') {
				$phpmailer->isHTML(true);
			} else {
				$phpmailer->isHTML(false);
			}

			$this->mainClass->log['notices'][] = 'SMTP Configuration Complete';
		}
	}

} // Ends Class eeRSCF_Mail_Class

?>
