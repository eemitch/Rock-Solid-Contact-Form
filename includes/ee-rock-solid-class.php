<?php
/**
 * Main plugin class for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


// Our Main Class
class eeRSCF_Class {

	// General Properties
	public $pluginName = "Rock Solid Contact Form";
	public $websiteLink = 'https://elementengage.com';
	public $formID = 1;
	public $formSettings = array(); // Holds Current Form Settings
	public $confirm = '';
	public $theFormOutput = '';
	public $fileFormats = 'jpg,jpeg,png,gif,pdf,doc,docx,txt'; // Default file formats

	public $basePath = '';
	public $baseURL = '';
	public $pluginPath = '';
	public $pluginURL = '';
	public $isAdmin = FALSE;
	public $thePost = array();
	public $confirmation = '';
	public $sender = '';
	public $to = '';
	public $cc = '';
	public $bcc = '';
	public $adminTo = '';

	// Messaging
	public $log = array(
		'notices' => array(),
		'messages' => array(),
		'warnings' => array(),
		'errors' => array()
	);


	// Default Contact Form
	public $contactFormDefault = array(
		'to' => '', // Will be set to admin email during initialization
		'cc' => '',
		'bcc' => '',
		'fields' => array(
			'first-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'First Name'),
			'last-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Last Name'),
			'biz-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Business Name'),
			'address1' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Address'),
			'address2' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Address 2'),
			'city' => array('show' => 'YES', 'req' => 'NO', 'label' => 'City'),
			'state' => array('show' => 'YES', 'req' => 'NO', 'label' => 'State'),
			'zip' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Postal Code'),
			'phone' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Phone'),
			'website' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Website'),
			'other' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Other'),
			'subject' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Subject'),
			'attachments' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Attachments')
		),
		'fileMaxSize' => 8,
		'fileFormats' => 'gif, jpg, jpeg, bmp, png, tif, tiff, txt, eps, psd, ai, pdf, doc, xls, ppt, docx, xlsx, pptx, odt, ods, odp, odg, wav, wmv, wma, flv, 3gp, avi, mov, mp4, m4v, mp3, webm, zip',
		'spamBlock' => 'NO',
		'spamBlockBots' => 'YES',
		'spamHoneypot' => 'link',
		'spamEnglishOnly' => 'YES',
		'spamBlockFishy' => 'YES',
		'spamBlockWords' => 'YES',
		'spamBlockCommonWords' => 'YES',
		'spamBlockedWords' => '',
		'spamSendAttackNotice' => 'NO',
		'spamNoticeEmail' => '',
		'email' => '', // Will be set to admin email during initialization
		'emailMode' => 'PHP',
		'emailName' => 'Contact Form',
		'emailServer' => FALSE,
		'emailUsername' => FALSE,
		'emailPassword' => FALSE,
		'emailPort' => FALSE,
		'emailSecure' => FALSE,
		'emailAuth' => FALSE,
		'emailFormat' => 'TEXT',
		'emailDebug' => FALSE
	);





	/**
	 * SECURITY: Comprehensive sanitization function
	 * Applies multiple layers of security to user input
	 */
	public function eeRSCF_SecureSanitize($value, $fieldName = '') {

		// First pass: WordPress sanitization
		$value = sanitize_text_field($value);

		// SECURITY: Remove potential script injections
		$value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $value);
		$value = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $value);
		$value = preg_replace('/javascript:/i', '', $value);
		$value = preg_replace('/on\w+\s*=/i', '', $value); // Remove event handlers like onclick, onload, etc.

		// SECURITY: Remove potential SQL injection patterns
		$sqlPatterns = [
			'/(\b(select|insert|update|delete|drop|create|alter|exec|union|script)\b)/i',
			'/(\-\-|\#|\/\*|\*\/)/i', // SQL comments
			'/(\bor\b|\band\b)\s*\d+\s*=\s*\d+/i', // OR 1=1, AND 1=1 patterns
			'/\b(union|select)\s+.*\bfrom\b/i'
		];

		foreach ($sqlPatterns as $pattern) {
			$value = preg_replace($pattern, '', $value);
		}

		// SECURITY: Remove command injection patterns
		$cmdPatterns = [
			'/\$\(.*?\)/', // Command substitution $(command)
			'/`.*?`/', // Backtick command execution
			'/\|\s*(cat|ls|dir|whoami|id|pwd|uname|ps|netstat|ifconfig|ping)/', // Piped commands
			'/;\s*(cat|ls|dir|whoami|id|pwd|uname|ps|netstat|ifconfig|ping)/', // Semicolon commands
			'/&&\s*(cat|ls|dir|whoami|id|pwd|uname|ps|netstat|ifconfig|ping)/', // AND commands
		];

		foreach ($cmdPatterns as $pattern) {
			$value = preg_replace($pattern, '', $value);
		}

		// SECURITY: Remove path traversal attempts
		$value = str_replace(['../', '..\\', '../', '..\\'], '', $value);
		$value = preg_replace('/\.{2,}/', '.', $value); // Multiple dots

		// SECURITY: Remove potential log injection patterns (Log4Shell, etc.)
		$value = preg_replace('/\$\{.*?\}/', '', $value); // ${expression} patterns
		$value = preg_replace('/%\{.*?\}/', '', $value); // %{expression} patterns

		// SECURITY: Remove null bytes and control characters
		$value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

		// SECURITY: Limit length to prevent buffer overflow attempts
		$maxLengths = [
			'first-name' => 100,
			'last-name' => 100,
			'subject' => 200,
			'phone' => 50,
			'message' => 5000,
			'default' => 500
		];

		$maxLength = isset($maxLengths[$fieldName]) ? $maxLengths[$fieldName] : $maxLengths['default'];
		if (strlen($value) > $maxLength) {
			$value = substr($value, 0, $maxLength);
		}

		return $value;
	}

	/**
	 * SECURITY: Check if content was significantly modified during sanitization
	 * This helps detect and reject malicious input attempts
	 */
	public function eeRSCF_SecurityCheck($original, $sanitized, $fieldName = '') {

		// If the sanitized version is significantly shorter, it likely contained malicious content
		$originalLength = strlen($original);
		$sanitizedLength = strlen($sanitized);

		// Allow for some normal sanitization (whitespace trimming, etc.)
		$tolerableReduction = 0.1; // 10% reduction is acceptable

		if ($originalLength > 10 && $sanitizedLength < ($originalLength * (1 - $tolerableReduction))) {
			// Content was significantly reduced - likely contained malicious code
			return true;
		}

		// SECURITY: Check for common attack patterns in original content
		$suspiciousPatterns = [
			'/<script/i',
			'/javascript:/i',
			'/\$\{.*?\}/', // Template injection
			'/\.\.\//i', // Path traversal
			'/union.*select/i', // SQL injection
			'/drop.*table/i', // SQL injection
			'/exec\(/i', // Code execution
			'/eval\(/i', // Code execution
			'/system\(/i', // System calls
			'/passthru\(/i', // System calls
			'/shell_exec\(/i', // System calls
			'/`.*`/', // Command execution
		];

		foreach ($suspiciousPatterns as $pattern) {
			if (preg_match($pattern, $original)) {
				return true; // Reject - contains suspicious patterns
			}
		}

		return false; // Content is acceptable
	}




	public function eeRSCF_formDisplay() {

		global $eeHelper;

		$this->log['notices'][] = 'Displaying the Form...';

		if($this->log['errors']) {
			$this->theFormOutput .= '
			<div class="eeRSCF_Confirm">
			<h2 class="eeError">Opps, we have a problem.</h2>';
			$this->eeRSCF_MessageDisplay($this->log['errors']);
			$this->theFormOutput .= '
			</div>';
		}

		$this->theFormOutput .= '
		<div id="eeRSCF">
		<form action="" method="post" enctype="multipart/form-data" id="eeRSCF_form">
		<input type="hidden" name="eeRSCF" value="TRUE" />
		<input type="hidden" name="eeRSCF_ID" value="' . $this->formID  . '" />' .
		wp_nonce_field( 'ee-rock-solid', 'ee-rock-solid-nonce', TRUE, FALSE ) .
		'
		<fieldset>';

		if( is_array($this->formSettings['fields']) ) {

			foreach($this->formSettings['fields'] as $eeField => $eeFieldArray) {

				if($eeFieldArray['show'] == 'YES') {

					if($eeField == 'attachments') { continue; }

					$this->theFormOutput .= '
					<div class="eeRSCF_Row">
					<label for="' . $eeFieldArray['show'] . '">';

					if($eeFieldArray['label']) {
						$this->theFormOutput .= esc_html(stripslashes($eeFieldArray['label'])); }
							else { $this->theFormOutput .= esc_html($eeHelper->eeUnSlug($eeField)); }

					$this->theFormOutput .= '</label>';

					$this->theFormOutput .= '
					<input ';

					if($eeFieldArray['req'] == 'YES') { $this->theFormOutput .= 'required '; }

					$this->theFormOutput .= 'name="';

					// Check for custom label
					if($eeFieldArray['label']) {
						$this->theFormOutput .= esc_attr($eeHelper->eeMakeSlug($eeFieldArray['label']));
					} else {

						$this->theFormOutput .= esc_attr($eeField);
					}

					$this->theFormOutput .= '"';

					$this->theFormOutput .= ' id="';
					$this->theFormOutput .= esc_attr($eeField) . '"';

					$this->theFormOutput .= ' type="';

					if($eeField == 'phone' OR strpos($eeField,'phone')) { $this->theFormOutput .=  'tel'; }
						else { $this->theFormOutput .= 'text'; }
					$this->theFormOutput .= '" size="30" value="" />';

					if($eeFieldArray['req'] == 'YES') { $this->theFormOutput .=  '
						<span class="eeRSCF_Required">*</span>'; }

					$this->theFormOutput .=  '
					</div>';
				}

			}
		} else {
			$this->theFormOutput .= 'ERROR - No Form Found';
		}

		$this->theFormOutput .= '<div class="eeRSCF_Row">
			<label for="eeRSCF_email">Your Email</label>
			<input type="email" name="email" id="eeRSCF_email" value="" required /><span class="eeRSCF_Required">*</span>
			</div>';

		if($this->formSettings['fields']['attachments']['show'] == 'YES') {

			$this->theFormOutput .= '<div class="eeRSCF_Row">
				<label for="eeRSCF_files">Attachment</label>
				<input type="file" name="file" id="eeRSCF_files" accept="';

				$this->theFormOutput .= esc_attr($this->formSettings['fileFormats']) . '" />';

				$this->theFormOutput .= '
				</div>';

		}

		$this->theFormOutput .= '
		<div class="eeRSCF_Row">
		<label for="eeRSCF_message">Message</label>
		<textarea required name="message" id="eeRSCF_message" cols="60" rows="6"></textarea>
		<span class="eeRSCF_Required">*</span>
		</div>

		<br class="eeClearFix" />

		<div class="eeRSCF_Roww">
			<label for="eeRSCF_' . $this->formSettings['spamHoneypot'] . '">Link:</label><input type="text" name="' . $this->formSettings['spamHoneypot'] . '" value="" id="eeRSCF_' . $this->formSettings['spamHoneypot'] . '">
		</div>

		<span id="eeRSCF_SubmitMessage"><img src="' . plugin_dir_url(__FILE__) . '/images/sending.gif" width="32" height="32" alt="Sending Icon" /> Sending Your Message</span>

		</fieldset>
		<input type="submit" id="eeRSCF_Submit" value="SEND">
		</form>
		<br class="eeClearFix" />
		</div>';

		// Log to the browser console
		if(eeRSCF_DevMode && defined('WP_DEBUG') && WP_DEBUG) {
			$this->theFormOutput .= eeDevOutput($this->log); // Output to console
			// Debug output disabled for production
			// $this->theFormOutput .= '<pre>LOG: ' . esc_html(print_r($this->log, TRUE)) . '</pre>';
			// $this->theFormOutput .= '<pre>SETTINGS: ' . esc_html(print_r($this->formSettings, TRUE)) . '</pre>';
		}

		return $this->theFormOutput;
	}


	// Problem Display / Error reporting
	public function eeRSCF_MessageDisplay($messages) {

		if(is_array($messages)) {
			echo '<div class="eeMessageDisplay"><ul>'; // Loop through array
			foreach($messages as $key => $value) {
				if(is_array($value)) {
					foreach ($value as $value2) {
						if(is_array($value2)) {
							foreach ($value2 as $value3) {
								echo "<li>" . esc_html($value3) . "</li>\n";
							}
						} else {
							echo "<li>" . esc_html($value2) . "</li>\n";
						}
					}
				} else {
					echo "<li>" . esc_html($value) . "</li>\n";
				}
			}
			echo "</ul></div>\n\n";
		} else {
			echo '<p>' . esc_html($messages) . '</p>';
		}
	}










} // Ends Class eeRSCF

?>