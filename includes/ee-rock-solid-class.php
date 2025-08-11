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
		'to' => '',
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
		'email' => '',
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



	private function eeRSCF_PostProcess() {

		global $eeHelper;

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(wp_unslash($_POST['ee-rock-solid-nonce']), 'ee-rock-solid')) {
			$this->log['catch'][] = 'Nonce verification failed';
			return false;
		}

		$this->log['notices'][] = 'Processing the post...';

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
						$this->log['errors'][] = 'Your email address is not correct.';
						continue 2; // Skip to the next $_POST item
					}
					$this->sender = strtolower($eeValue);
					break;
				case strpos($eeKey, 'ebsite') !== false:
					$eeValue = esc_url_raw($eeValue, ['http', 'https']);
					if (empty($eeValue)) {
						$this->log['errors'][] = 'Your website address is not correct.';
						continue 2; // Skip to the next $_POST item
					}
					break;
				default:
					// SECURITY: Enhanced sanitization for all other fields
					$eeValue = $this->eeRSCF_SecureSanitize($eeValue, $eeKey);

					// SECURITY: Reject if sanitization changed the content significantly
					if ($this->eeRSCF_SecurityCheck($originalValue, $eeValue, $eeKey)) {
						$this->log['errors'][] = 'Invalid content detected in ' . $eeHelper->eeUnSlug($eeKey) . ' field. Please remove any scripts, HTML tags, or suspicious characters.';
						continue 2; // Skip to the next $_POST item
					}
					break;
			}

			$eeField = $eeHelper->eeUnSlug($eeKey);
			$this->thePost[] = $eeField . ': ' . $eeValue;
		}

		$this->log['notices'][] = $this->thePost;

		return $this->thePost;
	}

	/**
	 * SECURITY: Comprehensive sanitization function
	 * Applies multiple layers of security to user input
	 */
	private function eeRSCF_SecureSanitize($value, $fieldName = '') {

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
	private function eeRSCF_SecurityCheck($original, $sanitized, $fieldName = '') {

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





	private function eeRSCF_formSpamCheck() {

		// Verify nonce for form processing security
		if (!isset($_POST['ee-rock-solid-nonce']) || !wp_verify_nonce(wp_unslash($_POST['ee-rock-solid-nonce']), 'ee-rock-solid')) {
			$this->log['catch'][] = 'Nonce verification failed';
			return false;
		}

		$this->log['notices'][] = 'Form Spam Check...';
		$this->log['catch'] = array();

		$tamper = FALSE;
		$entries = array();

		$eeArray = array_filter($_POST); // Get rid of empty fields
		$eeCount = count($eeArray); // How many filled in fields?

		// Spam Bots
		if($this->formSettings['spamBlockBots'] == 'YES') {

			if($this->formSettings['spamBlock'] AND isset($_POST[$this->formSettings['spamHoneypot']]) AND wp_unslash($_POST[$this->formSettings['spamHoneypot']])) { // Honeypot. This field should never be completed.
				$this->log['catch'][] = 'Spambot Catch: Honeypot Field Completed.';
			}
		}

		// English Only
		if($this->formSettings['spamEnglishOnly'] == 'YES') {

			foreach($eeArray as $eeKey => $eeValue) {

				if($eeValue) {
					$entries[] = $eeValue;

					// If you can't read it, block it.
					if(preg_match('/[А-Яа-яЁё]/u', $eeValue) OR preg_match('/\p{Han}+/u', $eeValue)) {
						$this->log['catch'][] = "Non-English Language Detected";
						break;
					}
				}
			}
		}

		// Block Fishiness
		if($this->formSettings['spamBlockFishy'] == 'YES') {

			// Check for duplicated info in fields (spam)
			$eeValues = array_count_values($eeArray);
			foreach($eeValues as $eeValue) {
				if($eeValue > 2) {
					$this->log['catch'][] = "3x Duplicated Same Field Entries";
				}
			}

			foreach( $eeArray as $eeKey => $eeValue) {

				if(strpos($eeValue, '&#') OR strpos($eeValue, '&#') === 0) {
					$this->log['catch'][] = "Malicious Submission";
				}

				if(strpos($eeValue, '[url]') OR strpos($eeValue, '[url]') === 0) {
					$this->log['catch'][] = "Form Tampering";
				}

				if(strlen(wp_strip_all_tags($eeValue)) != strlen($eeValue) ) {
					$this->log['catch'][] = "HTML Tags Found";
				}
			}
		}


		// Block Words
		if($this->formSettings['spamBlockWords'] == 'YES') {

			// Update the Common SPAM Words
			// This is a new line delineated list of common phrases used in email spam
			$spamBlockedCommonWords_LOCAL = explode(',', $this->formSettings['spamBlockedWords']);
			$spamBlockedCommonWords_REMOTE = explode(PHP_EOL, eeGetRemoteSpamWords(eeRSCF_RemoteSpamWordsURL));
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
			$eeBody .= implode("\n\n", $this->eeRSCF_PostProcess($_POST)) . PHP_EOL . PHP_EOL .
				  "-----------------------------------" . PHP_EOL;
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
					$this->log['errors'][] = 'Notice Email Failed to Send';
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
			$this->log['notices'][] = $this->log['errors'];
			$this->log['errors'] = array();
			return TRUE; // THIS IS SPAM !!!

		} else {

			$this->log['notices'][] = 'Spam Check OKAY!';
			return FALSE; // Seems okay...
		}
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

			// echo '<pre>'; print_r($this->formSettings['fields']); echo '</pre>'; exit;

			foreach($this->formSettings['fields'] as $eeField => $eeFieldArray) {

				// echo '<pre>' . $eeField; print_r($eeArray); echo '</pre>'; exit;

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
			$this->theFormOutput .= '<pre>LOG: ' . esc_html(print_r($this->log, TRUE)) . '</pre>';
			$this->theFormOutput .= '<pre>SETTINGS: ' . esc_html(print_r($this->formSettings, TRUE)) . '</pre>';
		}

		return $this->theFormOutput;
	}





	public function eeRSCF_SendEmail() {

		global $eeHelper; // Get Upload Class

		// $this->formID = filter_var($_POST['eeRSCF_ID'], FILTER_VALIDATE_INT);

		// echo '<pre>'; print_r($this->formSettings); echo '</pre>'; exit;

		// Are we Blocking SPAM?
		if($this->formSettings['spamBlock'] == 'YES') {
			if( $this->eeRSCF_formSpamCheck() === TRUE ) { // This is SPAM
				wp_die('Sorry, there was a problem with your message content. Please go back and try again.');
			}
		}

		// Check referrer is from same site.
		if(!isset($_REQUEST['ee-rock-solid-nonce']) || !wp_verify_nonce(wp_unslash($_REQUEST['ee-rock-solid-nonce']), 'ee-rock-solid')) {
			$this->log['errors'][] =  "Submission is not from this website";
			return FALSE;
		}

		$this->log['notices'][] = 'Sending the Email...';

		$this->eeRSCF_PostProcess();

		// echo '<pre>'; print_r($this->thePost); echo '</pre>'; exit;


		// File Attachment
		$eeFileURL = FALSE;
		if(!empty($_FILES['file']) AND $this->formSettings['fields']['attachments']['show'] == 'YES') {

			$formatsArray = explode(',', $this->formSettings['fileFormats']);
			$formatsArray = array_filter(array_map('trim', $formatsArray));

			// Validate file data exists before accessing
			if (isset($_FILES['file']['name']) && isset($_FILES['file']['size'])) {
				$fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
				$max_size = $this->formSettings['fileMaxSize'] * 1048576; // Convert MB to Bytes

				if( $_FILES['file']['size'] <= $max_size ) {
					if( in_array($fileExt,$formatsArray) ) {
						$eeFileURL = $eeHelper->eeUploader($_FILES['file'],  'ee-contact'  );
					} else {
						$this->log['errors'][] = 'FileType ' . $fileExt . ' Not Allowed';
					}
				} else {
					$this->log['errors'][] = 'File Too Large';
				}
			} else {
				$this->log['errors'][] = 'Invalid file upload data';
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
			if(empty($eeSubject)) { $eeSubject = 'Contact Form Message (' . basename(home_url()) . ')'; }

			// Email assembly
			if(empty($this->formSettings['email'])) {
				$host = sanitize_text_field($_SERVER['HTTP_HOST'] ?? 'localhost');
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

			if($eeFileURL) { $eeBody .= 'File: ' . $eeFileURL . PHP_EOL . PHP_EOL; }

			$eeBody .=  PHP_EOL . PHP_EOL . 'This message was sent via the contact form located at ' . home_url() . '/' . PHP_EOL . PHP_EOL;

			$eeBody = stripslashes($eeBody);
			$eeBody = wp_strip_all_tags(htmlspecialchars_decode($eeBody, ENT_QUOTES));

			// wp_die(print_r($this->formSettings));

			if( wp_mail($this->formSettings['to'], $eeSubject, $eeBody, $eeHeaders) ) {

				$this->log['notices'][] = 'WP Mail Sent';

				// Remove SMTP hook to prevent affecting other emails
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}

				wp_redirect($this->confirm);

				exit;

			} else {

				$this->log['errors'][] = 'PHP Message Failed to Send.';

				// Remove SMTP hook even on failure
				if ($this->formSettings['emailMode'] == 'SMTP') {
					remove_action('phpmailer_init', array($this, 'eeRSCF_configure_smtp'));
				}
			}

		} else {
			$this->log['errors'][] = 'Message not sent. Please try again.';
		}
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






	// Process Admin Settings
	public function eeRSCF_AdminSettingsProcess()	{

		$this->log['notices'][] = 'Processing Form Settings';

		if($_POST AND check_admin_referer( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {

			global $wpdb, $eeRSCF, $eeHelper;

			// Contact Form Fields and Destinations
			if( isset($_POST['eeRSCF_formSettings']) ) {

				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				// $eeArray = array();

				// ID
				if(isset($_POST['eeRSCF_ID'])) {
					$this->formID = filter_var($_POST['eeRSCF_ID'], FILTER_SANITIZE_NUMBER_INT);
				} else {
					$this->formID = 1;
				}

				// Name
				if(isset($_POST['eeRSCF_formName'])) {
					$eeRSCF->formSettings['formName'] = sanitize_text_field($_POST['eeRSCF_formName']);
				} else {
					$eeArray['name'] = 'Contact Form';
				}

				// Email Addresses
				if( !empty($_POST['eeRSCF_form_to']) ) {

					$delivery = array('to', 'cc', 'bcc');

					foreach($delivery as $to) {

						$eeSet = ''; // String of comma delineated emails

						if( isset($_POST['eeRSCF_form_' . $to ]) ) {

							$eeString = sanitize_text_field($_POST['eeRSCF_form_' . $to ]);

							if(strpos($eeString, ',')) { // More than one address

								$this->log['notices'][] = 'Multiple address for ' . $to . ' field.';

								$emails = explode(',', $eeString); // Make array

								foreach($emails as $email) { // Loop through them

									$email = trim($email); // Trim spaces

									if(filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate address
										$eeSet .= $email . ','; // Assemble addresses for storage
									} else {
										$this->log['errors'][] = 'Bad ' . $to . ' Address: ' . $email;
									}
								}

								$eeSet = substr($eeSet, 0, -1); // Clip the last comma

							} elseif($eeString) { // Just one address

								if(filter_var($eeString, FILTER_VALIDATE_EMAIL)) {
									$this->log['notices'][] = 'Single address for ' . $to . ' field.';
									$eeSet .= $eeString;
								} else {
									$this->log['errors'][] = 'Bad ' . $to . ' Address: ' . $_POST['eeAdmin' . $to];
								}
							}

						}

						$eeRSCF->formSettings[$to] = $eeSet;
					}

				} else {
					$this->log['errors'][] = 'Need at Least One Email Address';

				}

				$fieldsArray = isset($_POST['eeRSCF_fields']) && is_array($_POST['eeRSCF_fields'])
					? $_POST['eeRSCF_fields']
					: array();

				if( !empty($fieldsArray) ) {

					foreach($fieldsArray as $thisName => $thisFieldArray) {

					if(isset($thisFieldArray['show'])) {
						$eeRSCF->formSettings['fields'][$thisName]['show'] = 'YES';
					} else {
						$eeRSCF->formSettings['fields'][$thisName]['show'] = 'NO';
					}

					if(isset($thisFieldArray['req'])) {
						$eeRSCF->formSettings['fields'][$thisName]['req'] = 'YES';
					} else {
						$eeRSCF->formSettings['fields'][$thisName]['req'] = 'NO';
					}

					if(isset($thisFieldArray['label'])) {
						$eeRSCF->formSettings['fields'][$thisName]['label'] = $thisFieldArray['label'];
					}

					}
				}

				// Results Page
				if(!empty($_POST['eeRSCF_Confirm'])) {
					$eeRSCF->confirm = filter_var($_POST['eeRSCF_Confirm'], FILTER_VALIDATE_URL);
					if(empty($eeRSCF->confirm)) { $eeRSCF->confirm = home_url(); }
				} else { $eeRSCF->confirm = home_url(); }
				update_option('eeRSCF_Confirm', $eeRSCF->confirm);

			}



			// Attachements
			if($eeRSCF->formSettings['fields']['attachments']['show'] == 'YES'
				AND isset($_POST['eeRSCF_FileSettings']) ) {

				// This must be a number
				$uploadMaxSize = (int) $_POST['eeMaxFileSize'];

				// Can't be more than the system allows.
				if(!$uploadMaxSize OR $uploadMaxSize > $eeHelper->maxUploadLimit) {
					$uploadMaxSize = $eeHelper->maxUploadLimit;
				}
				$eeRSCF->formSettings['fileMaxSize'] = $uploadMaxSize; // Update the database

				// Strip all but what we need for the comma list of file extensions
				$formats = preg_replace("/[^a-z0-9,]/i", "", $_POST['eeFormats']);
				if(!$formats) { $formats = $this->fileFormats; } // Go with default if none.
				$eeRSCF->formSettings['fileFormats'] = $formats; // Update the database
			}



			// Spam Filtering
			if( isset($_POST['eeRSCF_SpamSettings']) ) {

				// Validate and sanitize the spamBlock field
				if (isset($_POST['spamBlock']) && ($_POST['spamBlock'] == 'YES' || $_POST['spamBlock'] == 'NO')) {
					$eeRSCF->formSettings['spamBlock'] = $_POST['spamBlock'];
				}

				// Validate and sanitize the spamBlockBots field
				if (isset($_POST['spamBlockBots']) && ($_POST['spamBlockBots'] == 'YES' || $_POST['spamBlockBots'] == 'NO')) {
					$eeRSCF->formSettings['spamBlockBots'] = $_POST['spamBlockBots'];
				}

				// Validate and sanitize the spamHoneypot field
				if (isset($_POST['spamHoneypot']) && !empty($_POST['spamHoneypot'])) {
					$eeRSCF->formSettings['spamHoneypot'] = sanitize_text_field($_POST['spamHoneypot']);
				}

				// Validate and sanitize the spamEnglishOnly field
				if (isset($_POST['spamEnglishOnly']) && ($_POST['spamEnglishOnly'] == 'YES' || $_POST['spamEnglishOnly'] == 'NO')) {
					$eeRSCF->formSettings['spamEnglishOnly'] = $_POST['spamEnglishOnly'];
				}

				// Validate and sanitize the spamBlockFishy field
				if (isset($_POST['spamBlockFishy']) && ($_POST['spamBlockFishy'] == 'YES' || $_POST['spamBlockFishy'] == 'NO')) {
					$eeRSCF->formSettings['spamBlockFishy'] = $_POST['spamBlockFishy'];
				}

				// Validate and sanitize the spamBlockCommonWords field
				if (isset($_POST['spamBlockCommonWords']) && ($_POST['spamBlockCommonWords'] == 'YES' || $_POST['spamBlockCommonWords'] == 'NO')) {
					$eeRSCF->formSettings['spamBlockCommonWords'] = $_POST['spamBlockCommonWords'];
				}

				// Validate and sanitize the spamBlockWords field
				if (isset($_POST['spamBlockWords']) && ($_POST['spamBlockWords'] == 'YES' || $_POST['spamBlockWords'] == 'NO')) {
					$eeRSCF->formSettings['spamBlockWords'] = $_POST['spamBlockWords'];
				}

				// Validate and sanitize the spamBlockedWords field
				if (isset($_POST['spamBlockedWords']) && !empty($_POST['spamBlockedWords'])) {
				$eeRSCF->formSettings['spamBlockedWords'] = sanitize_textarea_field($_POST['spamBlockedWords']);
				}

				// Validate and sanitize the spamSendAttackNotice field
				if (isset($_POST['spamSendAttackNotice']) && ($_POST['spamSendAttackNotice'] == 'YES' || $_POST['spamSendAttackNotice'] == 'NO')) {
				$eeRSCF->formSettings['spamSendAttackNotice'] = $_POST['spamSendAttackNotice'];
				}

				// Validate and sanitize the spamNoticeEmail field
				if (isset($_POST['spamNoticeEmail'])) {
					$eeRSCF->formSettings['spamNoticeEmail'] = filter_var($_POST['spamNoticeEmail'], FILTER_VALIDATE_EMAIL );
				}


				// Spam Prevention
				if($_POST['spamBlock'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Protection On: ' . $settings;
				update_option('eeRSCF_spamBlock', $settings); // Update the database

				// Block Spam Bots
				if($_POST['spamBlockBots'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Bots: ' . $settings;
				update_option('eeRSCF_spamBlockBots', $settings); // Update the database

				// Honeypot
				$settings = sanitize_text_field($_POST['spamHoneypot']);
				$settings = $eeHelper->eeMakeSlug($settings);
				$this->log['notices'] = 'Spam Honeypot: ' . $settings;
				update_option('eeRSCF_spamHoneypot', $settings); // Update the database

				// English Only
				if($_POST['spamEnglishOnly'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam English Only: ' . $settings;
				update_option('eeRSCF_spamEnglishOnly', $settings); // Update the database

				// Block Fishy
				if($_POST['spamBlockFishy'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Fishy: ' . $settings;
				update_option('eeRSCF_spamBlockFishy', $settings); // Update the database

				// Block Words
				if($_POST['spamBlockWords'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Words: ' . $settings;
				update_option('eeRSCF_spamBlockWords', $settings); // Update the database

				// Blocked Words
				$settings = sanitize_textarea_field($_POST['spamBlockedWords']);
				$this->log['notices'] = 'Spam Blocked Words: ' . $settings;
				update_option('eeRSCF_spamBlockedWords', $settings); // Update the database

				// Block Common Words
				if($_POST['spamBlockCommonWords'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block CommonWords: ' . $settings;
				update_option('eeRSCF_spamBlockCommonWords', $settings); // Update the database

				// Send Notice
				if($_POST['spamSendAttackNotice'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Attack Notice: ' . $settings;
				update_option('eeRSCF_spamSendAttackNotice', $settings); // Update the database

				// Notice Email
				$settings = filter_var($_POST['spamNoticeEmail'], FILTER_VALIDATE_EMAIL );
				$this->log['notices'] = 'Spam Notice Email: ' . $settings;
				update_option('eeRSCF_spamNoticeEmail', $settings); // Update the database
			}


			// Email Settings
			if(isset($_POST['eeRSCF_EmailSettings'])) {

				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;

				// Validate and sanitize eeRSCF_EmailSettings
				if ( isset( $_POST['eeRSCF_EmailSettings'] ) && $_POST['eeRSCF_EmailSettings'] == 'TRUE' ) {
					$eeRSCF->formSettings['email'] = filter_var( $_POST['eeRSCF_email'], FILTER_SANITIZE_EMAIL );
					$eeRSCF->formSettings['emailMode'] = ( $_POST['eeRSCF_emailMode'] == 'SMTP' ) ? 'SMTP' : 'PHP';
				}

				// Validate and sanitize eeRSCF_emailFormat
				if ( isset( $_POST['eeRSCF_emailFormat'] ) ) {
					$eeRSCF->formSettings['emailFormat'] = ( $_POST['eeRSCF_emailFormat'] == 'HTML' ) ? 'HTML' : 'TEXT';
				}

				// Validate and sanitize eeRSCF_emailName
				if ( isset( $_POST['eeRSCF_emailName'] ) ) {
					$eeRSCF->formSettings['emailName'] = sanitize_text_field( $_POST['eeRSCF_emailName'] );
				}

				// Validate and sanitize eeRSCF_emailServer
				if ( isset( $_POST['eeRSCF_emailServer'] ) ) {
					$eeRSCF->formSettings['emailServer'] = sanitize_text_field( $_POST['eeRSCF_emailServer'] );
				}

				// Validate and sanitize eeRSCF_emailUsername
				if ( isset( $_POST['eeRSCF_emailUsername'] ) ) {
					$eeRSCF->formSettings['emailUsername'] = sanitize_text_field( $_POST['eeRSCF_emailUsername'] );
				}

				// Validate and sanitize eeRSCF_emailPassword
				if ( isset( $_POST['eeRSCF_emailPassword'] ) ) {
					$eeRSCF->formSettings['emailPassword'] = sanitize_text_field( $_POST['eeRSCF_emailPassword'] );
				}

				// Validate and sanitize eeRSCF_emailSecure
				if ( isset( $_POST['eeRSCF_emailSecure'] ) ) {
					$eeRSCF->formSettings['emailSecure'] = sanitize_text_field( $_POST['eeRSCF_emailSecure'] );
				}

				// Validate and sanitize eeRSCF_emailAuth
				if ( isset( $_POST['eeRSCF_emailAuth'] ) ) {
					$eeRSCF->formSettings['emailAuth'] = ( $_POST['eeRSCF_emailAuth'] == 'YES' ) ? true : false;
				}

				// Validate and sanitize eeRSCF_emailPort
				if ( isset( $_POST['eeRSCF_emailPort'] ) ) {
					$eeRSCF->formSettings['emailPort'] = filter_var( $_POST['eeRSCF_emailPort'], FILTER_SANITIZE_NUMBER_INT );
				}

				// Validate and sanitize eeRSCF_emailDebug
				if ( isset( $_POST['eeRSCF_emailDebug'] ) ) {
					$eeRSCF->formSettings['emailDebug'] = ( $_POST['eeRSCF_emailDebug'] == 'YES' ) ? true : false;
				}
			}

			// Save to the Database
			if(empty($this->log['errors'])) {
				update_option('eeRSCF_Settings', $eeRSCF->formSettings); // Update the database
				$this->log['messages'][] = 'The Settings Have Been Saved';
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
					error_log("RSCF SMTP Debug: " . sanitize_text_field($str));
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




} // Ends Class eeRSCF

?>