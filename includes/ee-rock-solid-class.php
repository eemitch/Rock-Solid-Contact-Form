<?php // EE Contact Form Main Class
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeRSCF_Nonce' )) exit('That is Noncense!'); // Exit if nonce fails

// NOTES -------

// Select all relevant database options
// SELECT * FROM `wp_options` WHERE option_name LIKE 'eeRSCF_%' ORDER BY option_name

	
class eeRSCF_Class {
	
	// General Properties
	public $pluginName = "Rock Solid Contact Form";
	public $websiteLink = 'https://elementengage.com';
	public $formID = 1;
	public $contactForm = array();
	public $theForm = ''; // String
	public $formsArray = array(); // Holds multiple form - TO DO
	public $formArray = array(); // Holds Current Form Settings
	public $permalink = '';
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
		
	// $this->log['notices'][] = 'Processing Form Settings';	
		
		
	// Default Contact Form
	public $contactFormDefault = array(
		'name' => 'Main Contact Form',
		'to' => '',
		'cc' => '',
		'bcc' => '',
		'confirm' => '', // The page after
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
		'fileAllowUploads' => 'YES',
		'fileMaxSize' => 8,
		'fileFormats' => '.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip',
		'spamBlock' => 'YES',
		'spamBlockBots' => 'YES',
		'spamHoneypot' => 'link',
		'spamEnglishOnly' => 'YES',
		'spamBlockFishy' => 'YES',
		'spamBlockWords' => 'YES',
		'spamBlockCommonWords' => 'YES',
		'spamBlockedWords' => '',
		'spamSendAttackNotice' => 'YES',
		'spamNoticeEmail' => 'YES',
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

	
	
	
	
	
	
	
	
	
	// Methods -----------------------------------------------------------
	
	// Create a slug
	public function eeRSCF_MakeSlug($string){
	   $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
	   $slug = strtolower($slug);
	   return $slug;
	}
	
	// Undo a Slug
	public function eeRSCF_UnSlug($slug){
	   $string = str_replace('-', ' ', $slug);
	   $string = ucwords($string);
	   return $string;
	}	
	
	// Get Forms Info
	public function eeRSCF_GetForms() {
		
		for($i = 1; $i <= 99; $i++) {
		
			$eeThisFormArray = get_option('eeRSCF_' . $i);
			
			if( is_array($eeThisFormArray) ) {
				
				$this->eeFormsArray[$i] = $eeThisFormArray['name'];
				
				$this->log['notices'][] = 'Form Found: eeRSCF_' . $i;
			}
		}
	}
	
	
	

	
	private function eeRSCF_PostProcess() {
	     
	     $this->log['notices'][] = 'Processing the post...';
	     
	     $eeIgnore = array('eeRSCF', 'eeRSCF_ID', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER');
	     
	     foreach ($_POST as $eeKey => $eeValue) {
	        
	        if(!in_array($eeKey, $eeIgnore) AND $eeValue) {
					
				$eeValue = htmlspecialchars(strip_tags($eeValue));
				
				if(strpos($eeKey, 'mail')) { 
					if(!filter_var($eeValue, FILTER_VALIDATE_EMAIL )) {
						$this->log['errors'][] = 'Your email address in not correct.';
					} else {
						$eeValue = strtolower($eeValue);
						$this->sender = $eeValue;
					}
				}
				
				if(strpos($eeKey, 'ebsite')) { 
					
					if(!strpos($eeValue, 'ttp')) { // add the http if needed.
						$eeValue = 'http://' . $eeValue;
					}
					
					if( !filter_var( $eeValue, FILTER_VALIDATE_URL) ) {
						$this->log['errors'][] = 'Your website address in not correct.';
					} else {
						$eeValue = strtolower($eeValue);
					}
				}
				
				$eeField = $this->eeRSCF_UnSlug($eeKey);
				$this->thePost[] = $eeField . ': ' . $eeValue;
			}
	        
	        if(is_array($eeValue)) { // Is $value is an array?
	            $this->eeRSCF_PostProcess($eeValue);
	        }
	    } 
	        
	    $this->log['notices'][] = $this->thePost;
	    
	    return $this->thePost;
	}
	
	
	
	
	
	private function eeRSCF_formSpamCheck() {
		
		$this->log['notices'][] = 'Form Spam Check...';
		
		$tamper = FALSE;
		$entries = array();
		
		$eeArray = array_filter($_POST); // Get rid of empty fields
		$eeCount = count($eeArray); // How many filled in fields?
		
		// Spam Bots
		if($this->spamBlockBots == 'YES') {
			
			if($this->spamBlock AND $_POST[$this->spamHoneypot]) { // Honeypot. This field should never be completed.
				$this->log['errors'][] = 'Spambot Catch: Honeypot Field Completed.';
			}
		}
		
		// English Only
		if($this->spamEnglishOnly == 'YES') {

			foreach($eeArray as $eeKey => $eeValue) { 
				
				if($eeValue) {
					$entries[] = $eeValue;
					
					// If you can't read it, block it.
					if(preg_match('/[А-Яа-яЁё]/u', $eeValue) OR preg_match('/\p{Han}+/u', $eeValue)) {
						$this->log['errors'][] = "Foreign Language Detected";
						break;
					}
				}
			}
		}
		
		// Block Fishiness
		if($this->spamBlockFishy == 'YES') {
			
			// Check for duplicated info in fields (spam)
			$eeValues = array_count_values($eeArray);
			foreach($eeValues as $eeValue) {
				if($eeValue > 2) { 
					$this->log['errors'][] = "3x Duplicated Same Field Entries";
				}
			}
			
			foreach( $eeArray as $eeKey => $eeValue){
				
				if(strpos($eeValue, '&#') OR strpos($eeValue, '&#') === 0) {
					$this->log['errors'][] = "Malicious Submission";
				}
				
				if(strpos($eeValue, '[url]') OR strpos($eeValue, '[url]') === 0) {
					$this->log['errors'][] = "Form Tampering";
				}
				
				if(strlen(strip_tags($eeValue)) != strlen($eeValue) ) {
					$this->log['errors'][] = "HTML Tags Found";
				}
			}
		}
		
		
		// Block Words
		if($this->spamBlockWords == 'YES') {
			
			// Block messages containing these phrases
			$this->spamBlockedWords = explode(',', get_option('eeRSCF_spamBlockedWords'));
			
			if($this->spamBlockCommonWords == 'YES') {
				
				// Update the Common SPAM Words
				// This is a new line delineated list of common phrases used in email spam
				$spamBlockedCommonWords = eeGetRemoteSpamWords('http://eeserver1.net/ee-common-spam-words.txt'); // One phrase per line
				$spamBlockedCommonWordsArray = explode(PHP_EOL, $spamBlockedCommonWords); 
				if(is_array($this->spamBlockedWords) AND is_array($spamBlockedCommonWordsArray)) {
					$this->spamBlockedWords = array_merge($this->spamBlockedWords, $spamBlockedCommonWordsArray);
				}
			}
			
			$this->spamBlockedWords = array_map('trim', $this->spamBlockedWords);
			
			// echo '<pre>'; print_r($this->spamBlockedWords); echo '</pre>'; exit;
			
			// Check if any spam words are in the message
			foreach ($this->spamBlockedWords as $spamWord) {
			  
				if (stripos($_POST['message'], ' ' . $spamWord . ' ') !== FALSE) { // Use of spaces around the word prevents sub-string matches
					$this->log['errors'][] = 'Spam Word Catch: ' . $spamWord;
				}
			}
		}
		
		
		// If we detect spam, and the users want a report, create and send it here
		if (count($this->log['errors']) >= 1 && $this->spamSendAttackNotice == 'YES') {
  			
			  $eeTo = $this->spamNoticeEmail;
  			$eeSubject = "Spam Block Notice";
  			
  			$eeBody = "Contact Form Spam Catch" . PHP_EOL;
  			$eeBody .= "-----------------------------------" . PHP_EOL . PHP_EOL;
  			foreach ($this->log['errors'] as $eeError) {
				$eeBody .= $eeError . PHP_EOL;
  			}
  			$eeBody .= PHP_EOL . "Attacker" . PHP_EOL;
  			$eeBody .= "-----------------------------------" . PHP_EOL;
  			$eeBody .= "User Agent: " . @$_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
  			$eeBody .= "User IP: " . @$_SERVER['REMOTE_ADDR'] . PHP_EOL;
  			$eeBody .= "Came From: " . @$_POST['SCRIPT_REFERER'] . @$_SERVER['QUERY_STRING'] . PHP_EOL;
  			$eeBody .= "Attacker Message" . PHP_EOL . "-----------------------------------" . PHP_EOL;
  			$eeBody .= $this->eeRSCF_PostProcess($_POST) . PHP_EOL . PHP_EOL . 
			  	"-----------------------------------" . PHP_EOL;
  			$eeBody .= "Via Rock Solid Contact Form at http://" . [the-current-url] . PHP_EOL;
  			
  			$eeHeaders = array(
				'From: ' . $this->email,
				'Reply-To: ' . $this->email,
				'Content-Type: text/plain; charset=UTF-8',
  			);
  			
  			// Send Notice Email
  			if ($this->spamSendAttackNotice == 'YES') {
				if (!wp_mail($eeTo, $eeSubject, $eeBody, $eeHeaders)) {
	  			$this->log['errors'][] = 'Notice Email Failed to Send';
				}
  			}
		}

		
		$this->log['Errors'] = $this->log['errors'];
		
		
		if(count($this->log['errors']) >= 1) {
			
			$this->log['notices'][] = 'Spam Check FAIL!';
			$this->log['notices'][] = $this->log['errors'];
			$this->log['errors'] = array();
			return TRUE;
		
		} else {
		
			$this->log['notices'][] = 'Spam Check OKAY!';
			return FALSE;
			
		}
	}
	
	
	
	
	
	
	
	
	
	public function eeRSCF_formDisplay() {
		
		$this->log['notices'][] = 'Displaying the Form...';
		
		if($this->log['errors']) {
			$this->theForm .= '
			<div class="eeRSCF_Confirm">
			<h2 class="eeError">Opps, we have a problem.</h2>';
			$this->eeRSCF_MessageDisplay($this->log['errors']);
			$this->theForm .= '
			</div>';	
		}
		
		$this->theForm .= '
		<div id="eeRSCF">
		<form action="" method="post" enctype="multipart/form-data" id="eeRSCF_form">
		<input type="hidden" name="eeRSCF" value="TRUE" />
		<input type="hidden" name="eeRSCF_ID" value="' . $eeRSCF_ID  . '" />' . 
		wp_nonce_field( 'ee-rock-solid', 'ee-rock-solid-nonce', TRUE, FALSE ) . 
		'
		<fieldset>';
		
		if( is_array($this->contactForm['fields']) ) {
					
			// echo '<pre>'; print_r($this->contactForm['fields']); echo '</pre>'; exit;
			
			foreach($this->contactForm['fields'] as $eeField => $eeFieldArray) {
							
				// echo '<pre>' . $eeField; print_r($eeArray); echo '</pre>'; exit;
				
				if($eeFieldArray['show'] == 'YES') {
						
					if($eeField == 'attachments') { continue; }
					
					$this->theForm .= '
					<div class="eeRSCF_Row">
					<label for="' . $eeFieldArray['show'] . '">';
					
					if($eeFieldArray['label']) { 
						$this->theForm .= stripslashes($eeFieldArray['label']); } 
							else { $this->theForm .= $this->eeRSCF_UnSlug($eeField); }
					
					$this->theForm .= '</label>';
					
					$this->theForm .= '
					<input ';
					
					if($eeFieldArray['req'] == 'YES') { $this->theForm .= 'required '; }
					
					$this->theForm .= 'name="';
					
					// Check for custom label
					if($eeFieldArray['label']) {
						$this->theForm .= $this->eeRSCF_MakeSlug($eeFieldArray['label']);
					} else {
						
						$this->theForm .= $eeField;
					}
					
					$this->theForm .= '"';
					
					$this->theForm .= ' id="';
					$this->theForm .= $eeField . '"';
					
					$this->theForm .= ' type="';
					
					if($eeField == 'phone' OR strpos($eeField,'phone')) { $this->theForm .=  'tel'; } 
						else { $this->theForm .= 'text'; }
					$this->theForm .= '" size="30" value="" />';
					
					if($eeFieldArray['req'] == 'YES') { $this->theForm .=  '
						<span class="eeRSCF_Required">*</span>'; } 
					
					$this->theForm .=  ' 
					</div>';	
				}

			}
		} else {
			$this->theForm .= 'ERROR - No Form Found';
		}
		
		$this->theForm .= '<div class="eeRSCF_Row">
			<label for="eeRSCF_email">Your Email</label>
			<input type="email" name="email" id="eeRSCF_email" value="" required /><span class="eeRSCF_Required">*</span>
			</div>';
							
		if($this->contactForm['fields']['attachments']['show'] == 'YES') {
		
			$this->theForm .= '<div class="eeRSCF_Row">
				<label for="eeRSCF_files">Attachment</label>
				<input type="file" name="file" id="eeRSCF_files" accept="';
				
				$this->theForm .= $this->contactForm['fileFormats'] . '" />';
				
				$this->theForm .= '
				</div>';
			
		}
		
		$this->theForm .= '
		<div class="eeRSCF_Row">
		<label for="eeRSCF_message">Message</label>
		<textarea required name="message" id="eeRSCF_message" cols="60" rows="6"></textarea>
		<span class="eeRSCF_Required">*</span>
		</div>
		
		<br class="eeClearFix" />
		
		<div class="eeRSCF_Roww">
			<label for="eeRSCF_' . $this->contactForm['spamHoneypot'] . '">Link:</label><input type="text" name="' . $this->contactForm['spamHoneypot'] . '" value="" id="eeRSCF_' . $this->contactForm['spamHoneypot'] . '">
		</div>
		
		<span id="eeRSCF_SubmitMessage"><img src="' . plugin_dir_url(__FILE__) . '/' . eeRSCF_SLUG . '/images/sending.gif" width="32" height="32" alt="Sending Icon" /> Sending Your Message</span>
		
		</fieldset>
		<input type="submit" id="eeRSCF_Submit" value="SEND">
		</form>
		<br class="eeClearFix" />
		</div>';
	
		// Log to the browser console	
		if(eeRSCF_DevMode) { $this->theForm .= eeDevOutput($this->log); }
		
		return $this->theForm;
	}
	
	
	
	
	
	public function eeRSCF_SendEmail() {
		
		global $eeRSCFU; // Get Upload Class
		
		$eeRSCF_ID = filter_var($_POST['eeRSCF_ID'], FILTER_VALIDATE_INT);
		
		// Are we Blocking SPAM?
		if($this->spamBlock == 'YES') {
			if( $this->eeRSCF_formSpamCheck() ) { // This is SPAM
				wp_die('Sorry, there was a problem with your message. Please go back and try again.');
			}
		} 
	
		// Check referrer is from same site.
		if(!wp_verify_nonce($_REQUEST['ee-rock-solid-nonce'], 'ee-rock-solid')) {
			$this->log['errors'][] =  "Submission is not from this website";
			return FALSE;
		}
		
		$this->log['notices'][] = 'Sending the Email...';
			
		$this->eeRSCF_PostProcess();
		
		// echo '<pre>'; print_r($this->thePost); echo '</pre>'; exit;
		
		// There's a file and its size is less than our defined limit
		if(isset($_FILES['file']['name'])) {
			if($_FILES['file']['size'] <= $this->fileMaxSize*1048576) {
				$eeRSCFU->eeRSCFU_Uploader();
			} else {
				$this->log['errors'][] = 'File size is too large. Maximum allowed is ' . $this->fileMaxSize . 'MB';
			}
		}

		if(!$this->log['errors'] AND !empty($this->thePost)) {
				
			$this->log['notices'][] = 'Preparing the Email...';
			
			$eeThisFormArray = get_option('eeRSCF_' . $eeRSCF_ID);
			
			if(isset($eeThisFormArray['TO'])) {
				$this->to = $eeThisFormArray['TO'];
			} else {
				$this->log['errors'][] = 'No TO Address Configured';
				return FALSE;
			}
			
			if(isset($eeThisFormArray['CC'])) {
				$this->cc = $eeThisFormArray['CC'];
			}
			
			if(isset($eeThisFormArray['BCC'])) {
				$this->bcc = $eeThisFormArray['BCC'];
			}

			// Loop through and see if we have a Subject field
			foreach($this->thePost as $eeValue){
				$eeField = explode(':', $eeValue);
					if(strpos($eeField[0], 'ubject')) {
						$eeSubject = html_entity_decode($eeField[1], ENT_QUOTES);
						$eeSubject = stripslashes($eeSubject);
					}
			}
			if(empty($eeSubject)) { $eeSubject = 'Contact Form Message (' . $_SERVER['HTTP_HOST'] . ')'; }
			
			// Email assembly
			$eeHeaders = "From: " . get_bloginfo('name') . ' <' . $this->email . ">\n";
			if($this->cc) { $eeHeaders .= "CC: " . $this->cc . "\n"; }
			if($this->bcc) { $eeHeaders .= "BCC: " . $this->bcc . "\n"; }
			$eeHeaders .= "Return-Path: " . $this->email . "\n" . "Reply-To: " . $this->sender . "\n";
			
			$eeBody = '';
			
			foreach ($this->thePost as $value) {
				$eeBody .= $value . "\n\n";
			}
			
			if($eeRSCFU->fileUploaded) { $eeBody .= 'File: ' . $eeRSCFU->fileUploaded . "\n\n"; }
			
			$eeBody .= "---\n\n" . 'This message was sent via the contact form located at http://' . $this->baseURL . '/' . "\n\n";
			
			$eeBody = stripslashes($eeBody);
			$eeBody = strip_tags(htmlspecialchars_decode($eeBody, ENT_QUOTES));
				
			if(filter_var($eeThisFormArray['confirm'], FILTER_VALIDATE_URL)) {
				
				$eeUrl = $eeThisFormArray['confirm'];
			
			} else {
				
				$eeUrl = site_home();
			}
			
			if(wp_mail($this->to, $eeSubject, $eeBody, $eeHeaders) ) { // <<< ---------------- OR send the message the basic way.
				
				$this->log['notices'][] = 'WP Mail Sent';
				
				wp_redirect($eeUrl); exit;
				
			} else {
				
				$this->log['errors'][] = 'PHP Message Failed to Send.';
				$this->log['errors'][] = 'To: ' . $this->to;
			}
			
		} else {
			$this->log['errors'][] = 'Message not sent. Please try again.';
		}	
	}
	
	
	
	
	
	
	
	
	function eeRSCF_NoticeEmail($messages, $to, $from, $name, $mode = 'standard') {
		
		if($to AND $from) {
			
			$body = '';
			$headers = "From: $from";
			
			if($mode == 'error') {
				$subject = $name . " Error";
			} else {
				$subject = $name . " Admin Notice";
			}
			
			if(is_array($messages)) {
				foreach ($messages as $value) {
					if(is_array($value)) {
						foreach ($value as $value2) {
							$body .= $value2 . "\n\n";
						}
					} else {
						$body .= $value . "\n\n";
					}
				}
			} else {
				$body = $messages . "\n\n";
			}
			
			$body .= 'Via: ' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		
			if(!mail($to,$subject,$body,$headers)) { // Email the message or error report
				?><script>alert('EMAIL SEND FAILED');</script><?php
			}
		
		} else {
			?><script>alert('EMAIL SEND FAILED');</script><?php
		}
		
		return FALSE;		
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
								echo "<li>$value3</li>\n";
							}
						} else {
							echo "<li>$value2</li>\n";
						}
					}
				} else {
					echo "<li>$value</li>\n";
				}
			}
			echo "</ul></div>\n\n";
		} else {
			echo '<p>' . $messages . '</p>';
		}
	}
	
	
	
	
			
	function eeRSCF_AdminSettingsProcess()	{
		
		// echo '<pre>'; print_r($this->log); echo '</pre>'; exit;
		
		$this->log['notices'][] = 'Processing Form Settings';
		
		if($_POST AND check_admin_referer( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
			
			global $wpdb, $eeRSCF, $eeRSCFU;
			
			// Form Fields
			if(@$_POST['eeRSCF_formSettings'] == 'TRUE') {
			
				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				$eeArray = array();
			
				// ID
				if(isset($_POST['eeRSCF_ID'])) {
					$eeRSCF_ID = filter_var($_POST['eeRSCF_ID'], FILTER_SANITIZE_NUMBER_INT);
				} else {
					$eeRSCF_ID = 1;
				}
				
				// Name
				if(isset($_POST['eeRSCF_formName'])) {
					$formName = htmlspecialchars($_POST['eeRSCF_formName']);
				}
				if($formName) { $eeArray['name'] = $formName; } else { $eeArray['name'] = 'Contact Form'; }
	
				// Email Addresses
				if( isset($_POST['eeRSCF_formTO']) ) {
				
					$delivery = array('TO', 'CC', 'BCC');
					
					foreach($delivery as $to) {
					
						$eeSet = ''; // String of comma delineated emails
						
						if( isset($_POST['eeRSCF_form' . $to ]) ) {
								
							$eeString = htmlspecialchars($_POST['eeRSCF_form' . $to ]);
							
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
							
						$eeSet;
						
						if($eeSet) { $eeArray[$to] = $eeSet; }
					}

				} else {
					$this->log['errors'][] = 'Need at Least One Email Address';
					
				}
			
				$fieldsArray = $_POST['eeRSCF_fields'];
				
				if( is_array($fieldsArray) ) {
				
					foreach($fieldsArray as $thisName => $thisFieldArray) {
					
					if(isset($thisFieldArray['show'])) {
						$eeArray['fields'][$thisName]['show'] = 'YES';
					} else {
						$eeArray['fields'][$thisName]['show'] = 'NO';
					}
					
					if(isset($thisFieldArray['req'])) {
						$eeArray['fields'][$thisName]['req'] = 'YES';
					} else {
						$eeArray['fields'][$thisName]['req'] = 'NO';
					}
					
					if(isset($thisFieldArray['label'])) {
						$eeArray['fields'][$thisName]['label'] = $thisFieldArray['label'];
					}
					
					}
				}
				
				
				if(isset($_POST['eeRSCF_confirm'])) {
					
					$eeArray['confirm'] = filter_var($_POST['eeRSCF_confirm'], FILTER_VALIDATE_URL);
					
					if(strlen($eeArray['confirm']) === 0) {
						$eeArray['confirm'] = site_url();
					}
					
				} else {
					$eeArray['confirm'] = site_url();
				}
				
				
				// echo '<pre>'; print_r($eeArray); echo '</pre>'; exit;
				
				update_option('eeRSCF_' . $eeRSCF_ID, $eeArray); // Update the database
				
			}
			
			
			
			
			
			
			
			
			if(@$_POST['eeRSCF_FileSettings'] == 'TRUE') {
				
				// This must be a number
				$uploadMaxSize = (int) $_POST['eeMaxFileSize'];
				
				// Can't be more than the system allows.
				if(!$uploadMaxSize OR $uploadMaxSize > $eeRSCFU->maxUploadLimit) { 
					$uploadMaxSize = $eeRSCFU->maxUploadLimit;
				}
				update_option('eeRSCF_fileMaxSize', $uploadMaxSize); // Update the database
				
				// Strip all but what we need for the comma list of file extensions
				$formats = preg_replace("/[^a-z0-9.,]/i", "", $_POST['eeFormats']);
				if(!$formats) { $formats = $this->fileFormats; } // Go with default if none.
				update_option('eeRSCF_fileFormats', $formats); // Update the database
			}			
			
			
			
			
			
			
			
			if(@$_POST['eeRSCF_SpamSettings'] == 'TRUE') {
			
				// Spam Prevention
				if($_POST['spamBlock'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Protection On: ' . $settings;
				update_option('eeRSCF_spamBlock', $settings); // Update the database
				
				// Block Spam Bots
				if($_POST['spamBlockBots'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Bots: ' . $settings;
				update_option('eeRSCF_spamBlockBots', $settings); // Update the database
				
				// Honeypot
				$settings = htmlspecialchars($_POST['spamHoneypot']);
				$settings = $this->eeRSCF_MakeSlug($settings);
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
				$settings = htmlspecialchars($_POST['spamBlockedWords']);
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
			
			
			
			
			
			if(@$_POST['eeRSCF_EmailSettings'] == 'TRUE') {
					
				
				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				
				// Form Address
				$setting = 'eeRSCF_email';
				$value = htmlspecialchars($_POST[$setting]);
				if($value) {
					$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
					update_option($setting, $value); 
				} else {
					$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
				}
				
				// Form Mode
				$setting = 'eeRSCF_emailMode';
				$value = htmlspecialchars($_POST[$setting]);
				if($value) {
					$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
					update_option($setting, $value); 
				} else {
					$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
				}
				
				
				if($value == 'SMTP') {
				
					// The Nice Name
					$setting = 'eeRSCF_emailName';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// The Message Format
					$setting = 'eeRSCF_emailFormat';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Hostname
					$setting = 'eeRSCF_emailServer';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Username
					$setting = 'eeRSCF_emailUsername';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Password
					$setting = 'eeRSCF_emailPassword';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Security
					$setting = 'eeRSCF_emailSecure';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Security
					$setting = 'eeRSCF_emailPort';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					
					// Port
					$setting = 'eeRSCF_emailAuth';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Debug
					$setting = 'eeRSCF_emailDebug';
					$value = htmlspecialchars($_POST[$setting]);
					if($value) {
						$this->log['notices'] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->log['errors'][] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
				
				}
				
			}
			
		}
	}	
			
			
			

} // Ends Class eeRSCF
	
?>