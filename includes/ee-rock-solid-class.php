<?php // EE Contact Form Main Class
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeRSCF_Nonce' )) exit('That is Noncense!'); // Exit if nonce fails

// NOTES -------

// Select all relevant database options
// SELECT * FROM `wp_options` WHERE option_name LIKE 'eeRSCF_%' ORDER BY option_name

	
class eeRSCF_Class {
	
	public $pluginName = "Rock Solid Contact Form";
	
	public $spamAdminNoticeEmail = 'rscf.spam@elementengage.net'; // If allowed by the user, send a copy of the spam report to Mitch
	
	public $dbFieldName = "eeRSCF"; // The name of the options field in the database
	
	public $eeRSCF_0 = array(
		
		'name' => 'New Form',
		'to' => '',
		'cc' => '',
		'bcc' => '',
		'confirm' => '',
		'fields' => array('first-name' => array('show' => 'YES', 'req' => 'NO', 'label' => 'First Name'), 
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
	));
	
	public $eeFormsArray = array();
	public $eeFormArray = array();
	
	
	// Files Allowed
	public $default_fileFormats = '.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip';
	
	
	// Spam
	public $default_spamBlock = 'YES';
	public $default_spamBlockBots = 'YES';
	public $default_spamHoneypot = 'link';
	public $default_spamEnglishOnly = 'YES';
	public $default_spamBlockFishy = 'YES';
	
	public $default_spamBlockWords = 'YES';
	public $default_spamBlockedWords = '';
	public $default_spamBlockCommonWords = 'YES';	
	public $default_spamSendAttackNotice = 'NO'; 
	public $default_spamSendAttackNoticeToDeveloper = 'YES';
	
	
	
	// Our Form Settings
	
	// public $formFields;
	public $fileAllowUploads;
	public $fileMaxSize;
	public $fileFormats;
	
	
	// Spam
	public $spamBlock; // Filter spam or not.
	public $spamBlockBots; // Block bots with a honeypot.
	public $spamHoneypot = 'link'; // The honeypot.
	public $spamEnglishOnly; // Block non-english chars.
	public $spamBlockFishy; // Block messages with duplicated fields and other fishy business.
	public $spamBlockWords; // To block words or not
	public $spamBlockCommonWords; // To use the built-in list of common words
	public $spamBlockedCommonWords; // These words and phrases are commonly found in contct form spam.
	public $spamBlockedWords; // In addition, these words and phrases are not allowed by the website owner.
	public $spamSendAttackNotice; // Send an email with a summary of the spam that was blocked. // Sends to ...
	public $spamNoticeEmail; // The users email address the notice gets sent to
	public $spamSendAttackNoticeToDeveloper; // Send an email with a summary of the spam that was blocked to Mitch.
	
	
	
	// Email
	public $email;
	public $emailMode;
	public $emailName;
	public $emailServer;
	public $emailUsername;
	public $emailPassword;
	public $emailPort;
	public $emailSecure;
	public $emailAuth;
	public $emailFormat;
	public $emailDebug;
	
	
	
	// Various Properties
	public $permalink;
	protected $basePath;
	protected $baseURL;
	protected $pluginPath;
	protected $pluginURL;
	protected $isAdmin = FALSE;
	protected $thePost = array();
	protected $theForm;
	public $confirmation;
	
	// Email
	protected $sender;
	private $to;
	private $cc;
	private $bcc;
	public $adminTo;
	
	// Messaging
	public $errors = array();
	public $log = array('Rock Solid Contact Form is Running!');
	
	// Methods -----------------------------------------------------------
	
	public function eeRSCF_Setup($getSettings) {
		
		$this->log[] = 'Running contact form setup...';
		
		$this->basePath = $_SERVER['DOCUMENT_ROOT'];
		$this->log['settings'][] = $this->basePath;
		
		$this->baseURL = $_SERVER['HTTP_HOST'];
		$this->log['settings'][] = $this->baseURL;
		
		
		$this->pluginPath = plugin_dir_path( __FILE__ );
		$this->log['settings'][] = $this->pluginPath;
		
		
		$this->pluginURL = plugin_dir_url( __FILE__ );
		$this->log['settings'][] = $this->pluginURL;
		
		
		$this->adminTo = get_option('admin_email');
		$this->log['settings'][] = $this->adminTo;
		
		
		$this->permalink = get_permalink();
		$this->log['settings'][] = $this->permalink;
		
		
		// FILES
		
		$this->fileMaxSize = get_option('eeRSCF_fileMaxSize');
		$this->log['settings'][] = $this->fileMaxSize;
		
		
		$this->fileFormats = get_option('eeRSCF_fileFormats');
		$this->log['settings'][] = $this->fileFormats;
		
		
		
		// SPAM
		$this->spamBlock = get_option('eeRSCF_spamBlock');
		$this->log['settings'][] = $this->spamBlock;
		
		$this->spamBlockBots = get_option('eeRSCF_spamBlockBots');
		$this->log['settings'][] = $this->spamBlockBots;
		
		$this->spamHoneypot = get_option('eeRSCF_spamHoneypot');
		$this->log['settings'][] = $this->spamHoneypot;
		
		$this->spamEnglishOnly = get_option('eeRSCF_spamEnglishOnly');
		$this->log['settings'][] = $this->spamEnglishOnly;
		
		$this->spamBlockFishy = get_option('eeRSCF_spamBlockFishy');
		$this->log['settings'][] = $this->spamBlockFishy;
		
		$this->spamBlockWords = get_option('eeRSCF_spamBlockWords');
		$this->log['settings'][] = $this->spamBlockWords;
		
		$this->spamBlockedWords = get_option('eeRSCF_spamBlockedWords');
		$this->log['settings'][] = $this->spamBlockedWords;
		
		$this->spamBlockCommonWords = get_option('eeRSCF_spamBlockCommonWords');
		$this->log['settings'][] = $this->spamBlockCommonWords;
		
		$this->spamBlockedCommonWords = get_option('eeRSCF_spamBlockedCommonWords');
		$this->log['settings'][] = $this->spamBlockedCommonWords;
		
		$this->spamSendAttackNotice = get_option('eeRSCF_spamSendAttackNotice');
		$this->log['settings'][] = $this->spamSendAttackNotice;
		
		$this->spamNoticeEmail = get_option('eeRSCF_spamNoticeEmail');
		$this->log['settings'][] = $this->spamNoticeEmail;
		
		$this->spamSendAttackNoticeToDeveloper = get_option('eeRSCF_spamSendAttackNoticeToDeveloper ');
		$this->log['settings'][] = $this->spamSendAttackNoticeToDeveloper ;
		
		
		// EMAIL
		$this->email = get_option('eeRSCF_email');
		$this->log['settings'][] = $this->email;
		
		$this->emailMode = get_option('eeRSCF_emailMode');
		$this->log['settings'][] = $this->emailMode;
		
		// SMTP
		if($this->emailMode == 'SMTP') {
		
			$this->emailName = get_option('eeRSCF_emailName');
			$this->log['settings'][] = $this->emailName;
			
			$this->emailServer = get_option('eeRSCF_emailServer');
			$this->log['settings'][] = $this->emailServer;
		
			$this->emailFormat = get_option('eeRSCF_emailFormat');
			$this->log['settings'][] = $this->emailFormat;
			
			$this->emailUsername = get_option('eeRSCF_emailUsername');
			$this->log['settings'][] = $this->emailUsername;
			
			$this->emailPassword = get_option('eeRSCF_emailPassword');
			$this->log['settings'][] = $this->emailPassword;
			
			$this->emailSecure = get_option('eeRSCF_emailSecure');
			$this->log['settings'][] = $this->emailSecure;
			
			$this->emailAuth = get_option('eeRSCF_emailAuth');
			$this->log['settings'][] = $this->emailAuth;
			
			$this->emailPort = get_option('eeRSCF_emailPort');
			$this->log['settings'][] = $this->emailPort;
			
			$this->emailDebug = get_option('eeRSCF_emailDebug');
			$this->log['settings'][] = $this->emailDebug;
		
		}
		
	}
	
	// Are we in the admin area?
	private function eeRSCF_IsAdmin() {
		if(is_admin() AND strpos($_SERVER['PHP_SELF'], 'wp-admin')) { $this->isAdmin = TRUE; }
	}
	
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
				
				$this->log[] = 'Form Found: eeRSCF_' . $i;
			}
		}
	}
	
	
	

	
	private function eeRSCF_PostProcess($post) {
	     
	     // echo '<pre> eeRSCF_PostProcess '; print_r($post); echo '</pre>'; exit;
	     
	     $this->log[] = 'Processing the post...';
	     
	     $ignore = array('eeRSCF', 'eeRSCF_ID', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER');
	     
	     foreach ($post as $key => $value){
	        
	        if(!in_array($key, $ignore) AND $value) {
					
				$value = filter_var($value, FILTER_SANITIZE_STRING);
				
				$value = strip_tags($value);
				
				if(strpos($key, 'mail')) { 
					if(!preg_match('/^[[:alnum:]][a-z0-9_\.\-]*@[a-z0-9\.\-]+\.[a-z]{2,4}$/', $value)) {
						$this->errors[] = 'Your email address in not correct.';
					} else {
						$value = strtolower($value);
						$this->sender = $value;
					}
				}
				
				if(strpos($key, 'ebsite')) { 
					
					if(!strpos($value, 'ttp')) { // add the http if needed.
						$value = 'http://' . $value;
					}
					
					if(!preg_match('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', $value)) {
						$this->errors[] = 'Your website address in not correct.';
					} else {
						$value = strtolower($value);
					}
				}
				
				$field = self::eeRSCF_UnSlug($key);
				$this->thePost[] = $field . ': ' . $value;
			}
	        
	        
	        // $this->log[] = "$key => $value";
	        
	        if(is_array($value)) { // Is $value is an array?
	            self::eeRSCF_PostProcess($value);
	        }
	    } 
	        
	    $this->log[] = $this->thePost;
	    
	    return $this->thePost;
	    
	    
	    
	}
	
	function eeRSCF_formSpamCheck() {
		
		$this->log[] = 'Form Spam Check...';
		
		$tamper = FALSE;
		$entries = array();
		
		$eeArray = array_filter($_POST); // Get rid of empty fields
		$eeCount = count($eeArray); // How many filled in fields?
		
		// Spam Bots
		if($this->spamBlockBots == 'YES') {
			
			if($this->spamBlock AND $_POST[$this->spamHoneypot]) { // Honeypot. This field should never be completed.
				$this->errors[] = 'Spambot Catch: Honeypot Field Completed.';
			}
		}
		
		// English Only
		if($this->spamEnglishOnly == 'YES') {

			foreach($eeArray as $eeKey => $eeValue) { 
				
				if($eeValue) {
					$entries[] = $eeValue;
					
					// If you can't read it, block it.
					// TO DO --------------------------- <<<------Add language options to admin panel
					
					if(preg_match('/[А-Яа-яЁё]/u', $eeValue) OR preg_match('/\p{Han}+/u', $eeValue)) {
						$this->errors[] = "Foreign Language Detected";
						break;
					}
				}
			}
		}
		
		// Block Fishyness
		if($this->spamBlockFishy == 'YES') {
			
			// Check for duplicated info in fields (spam)
			$eeValues = array_count_values($eeArray);
			foreach($eeValues as $eeValue) {
				if($eeValue > 2) { 
					$this->errors[] = "3x Duplicated Same Field Entries";
				}
			}
			
			foreach( $eeArray as $eeKey => $eeValue){
				
				if(strpos($eeValue, '&#') OR strpos($eeValue, '&#') === 0) {
					$this->errors[] = "Malicious Submission";
				}
				
				if(strpos($eeValue, '[url]') OR strpos($eeValue, '[url]') === 0) {
					$this->errors[] = "Form Tampering";
				}
				
				if(strlen(strip_tags($eeValue)) != strlen($eeValue) ) {
					$this->errors[] = "HTML Tags Found";
				}
			}
		}
		
		
		// Block Words
		if($this->spamBlockWords == 'YES') {
			
			// User Words
			$spamWords = explode(',', $this->spamBlockedWords);
			
			if($this->spamBlockCommonWords == 'YES') {
				
				// Common list of words
				$spamCommonWords = $this->spamBlockedCommonWords;
				$spamCommonWords = explode(',', $spamCommonWords); // Split by comma
				$spamCommonWords = array_map('trim', $spamCommonWords); // Trim values
				$spamCommonWords = array_map('strtolower', $spamCommonWords); // Ensure lower case
				$spamWords = array_merge($spamWords, $spamCommonWords); // Combine with the local words
				$spamWords = array_unique($spamWords); // Remove Duplicates
			}
		
			// Loop through post to look for blocked words
			foreach($eeArray as $eeKey => $eeValue){
				
				$eeValue = strtolower($eeValue);
				
				foreach($spamWords as $spamWord) {
					
					$spamWord = trim($spamWord);
					
					if(strlen($eeValue)) {
						
						// Use of spaces around the word prevents parts of larger words tripping the catch.
						if(strpos($eeValue, ' ' . $spamWord . ' ') OR strpos($eeValue, $spamWord . ' ') === 0) {
							
							$this->errors[] = 'Spam Word Catch: ' . $spamWord;	
						}
					}
				}
			}
		}
		
		if(count($this->errors) >= 1 AND ($this->spamSendAttackNotice == 'YES' OR $this->spamSendAttackNoticeToDeveloper == 'YES') ) {
			
			$eeAttackLog = array(); // An array we will fill, serialize and encode, and then send to an EE server.
			
			// We do this to halp make the World a better place.
			
			// Website Info
			$eeAttackLog[] = 'Contact Form Spam Catch';
			$eeAttackLog[] = '-----------------------------------';
			$eeAttackLog[] = array_map('strtoupper', $this->errors);
			$eeAttackLog[] = ' ';
			$eeAttackLog[] = 'Attacker';
			$eeAttackLog[] = '-----------------------------------';
			$eeAttackLog[] = 'User Agent: ' . @$_SERVER['HTTP_USER_AGENT'];
			$eeAttackLog[] = 'User IP: ' . @$_SERVER['REMOTE_ADDR'];
			$eeAttackLog[] = 'Came From: ' . @$_POST['SCRIPT_REFERER'] . @$_SERVER['QUERY_STRING'];
			$eeAttackLog[] = 'Camera Image: (You Wish)';
			$eeAttackLog[] = ' ';
			$eeAttackLog[] = "Attacker Message";
			$eeAttackLog[] = '-----------------------------------';
			$eeAttackLog[] = $this->eeRSCF_PostProcess($_POST);
			
			$eeTo = $this->spamNoticeEmail;
			$eeBody = '';
			$eeHeaders = "From: " . $this->email;
			$eeSubject = "Spam Block Notice";
			
			if(is_array($eeAttackLog)) {
				foreach ($eeAttackLog as $eeValue) {
					if(is_array($eeValue)) {
						foreach ($eeValue as $eeValue2) {
							$eeBody .= $eeValue2 . "\n\n";
						}
					} else {
						$eeBody .= $eeValue . "\n\n";
					}
				}
			}
			
			$eeBody .= "\n\r-----------------------------------\n\rVia Rock Solid Contact Form at http://" . $_SERVER['HTTP_HOST'] . "\n\r\n\r";
			
			
			// Send Notice Email
			if($this->spamSendAttackNotice == 'YES') {
				
				if(!wp_mail($eeTo, $eeSubject, $eeBody, $eeHeaders)) { // Email the message or error report
					$this->errors[] = 'Notice Email Failed to Send';
				}
			}
			
			
			// Optional Admin Notice
			if($this->spamSendAttackNoticeToDeveloper == 'YES') {
				
				$eeTo = $this->spamAdminNoticeEmail;
				
				if(!wp_mail($eeTo, $eeSubject, $eeBody, $eeHeaders)) { // Email the message or error report
					$this->errors[] = 'Admin Notice Email Failed to Send';
				}
			}
		}
		
		
		if(count($this->errors) >= 1) {
			
			$this->log[] = 'Spam Check FAIL!';
			$this->log[] = $this->errors;
			$this->errors = array();
			return FALSE;
		
		} else {
		
			$this->log[] = 'Spam Check OKAY!';
			return 'OK';
			
		}
	}
	
	
	
	
	
	
	
	
	
	public function eeRSCF_formDisplay($eeRSCF_ID) {
		
		$this->log[] = 'Displaying the Form...';
		
		if($this->errors) {
		
			$this->theForm .= '<div class="eeRSCF_Confirm">
				<h2 class="eeError">Opps, we have a problem.</h2>';
			$this->eeRSCF_MessageDisplay($this->errors);
			$this->theForm .= '</div>';	
			
		}
		
		$this->theForm .= '<div id="eeRSCF">
			<form action="';
			
		$this->theForm .= '/';
		
		$this->theForm .= '" method="post" enctype="multipart/form-data" id="eeRSCF_form">
				<input type="hidden" name="eeRSCF" value="TRUE" />
				<input type="hidden" name="eeRSCF_ID" value="' . $eeRSCF_ID  . '" />
				<input type="hidden" name="SCRIPT_REFERER" value="';
				
		$this->theForm .= @$_SERVER['HTTP_REFERER'];
		
		$this->theForm .= '" />';
				
		$this->theForm .= wp_nonce_field( 'ee-rock-solid', 'ee-rock-solid-nonce', TRUE, FALSE );
		
		$this->theForm .= '
		
		<fieldset>';
		
		$eeArray = get_option('eeRSCF_' . $eeRSCF_ID);
		
		// echo '<pre>'; print_r($eeArray); echo '</pre>'; exit;
		
		if( is_array($eeArray['fields']) ) {
					
			foreach($eeArray['fields'] as $field => $value) {
							
				if(@$value['show'] == 'YES') {
						
					if($field == 'attachments') { continue; }
					
					$this->theForm .= '
					<div class="eeRSCF_Row">
					<label for="';
							
					$this->theForm .= $value['show'];
					
					$this->theForm .=  '">';
					
					// Chech for custom label
					if($value['label']) { $this->theForm .= $value['label']; } else { $this->theForm .= self::eeRSCF_UnSlug($field); }
					
					$this->theForm .= '</label>';
					
					$this->theForm .= '
					<input ';
					
					if($value['req'] == 'YES') { $this->theForm .= 'required '; }
					
					$this->theForm .= 'name="';
					
					// Chech for custom label
					if($value['label']) {
						$this->theForm .= self::eeRSCF_MakeSlug($value['label']);
					} else {
						
						$this->theForm .= $field;
					}
					
					$this->theForm .= '"';
					
					$this->theForm .= ' id="';
					$this->theForm .= $field . '"';
					
					$this->theForm .= ' type="';
					
					if($field == 'website') { $this->theForm .=  'url'; } 
						elseif($field == 'phone' OR strpos($field,'phone')) { $this->theForm .=  'tel'; } 
							else { $this->theForm .= 'text'; }
					$this->theForm .= '" size="30" value="" />';
					
					if(@$value['req'] == 'YES') { $this->theForm .=  '
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
			<input type="email" name="email" id="eeRSCF_email" value="' . @$_POST['email'] . '" required /><span class="eeRSCF_Required">*</span>
			</div>';
					
					
		if($eeArray['fields']['attachments']['show'] == 'YES') {
		
		$this->theForm .= '<div class="eeRSCF_Row">
			<label for="eeRSCF_files">Attachment</label>
			<input type="file" name="file" id="eeRSCF_files" accept="';
			
			$this->theForm .= $this->fileFormats . '" />';
			
			$this->theForm .= '
			
			</div>';
			
		}
		
		$this->theForm .= '<div class="eeRSCF_Row">
			<label for="eeRSCF_message">Message</label>
			<textarea required name="message" id="eeRSCF_message" cols="60" rows="6">';
			
		$this->theForm .= @$_POST['message'];
		
		$this->theForm .= '</textarea>
		
		<span class="eeRSCF_Required">*</span>
		
		</div>
		
		<br class="eeClearFix" />
		
		<div class="eeRSCF_Roww">
			<label for="eeRSCF_' . $this->spamHoneypot . '">Link:</label><input type="text" name="' . $this->spamHoneypot . '" value="" id="eeRSCF_' . $this->spamHoneypot . '">
		</div>
		
		<span id="eeRSCF_SubmitMessage"><img src="' . $this->pluginURL . 'images/sending.gif" width="32" height="32" alt="Sending Icon" /> Sending Your Message</span>
		
		</fieldset>
		
		<input type="submit" id="eeRSCF_Submit" value="SEND">
		
		</form>
		
		
		<br class="eeClearFix" />
		
		</div>';
	
		// Log Display - Dev Mode Only
		if(eeRSCF_DevMode) {
		
			$this->theForm .= '<pre>' . print_r($this->log, TRUE) . '</pre>';
			
			// eeRSCF_WriteLogFile($eeRSCF_Log);
			
		}
		
		return $this->theForm;
			
	}
	
	
	public function eeRSCF_SendEmail($post) {
		
		// echo '<pre>'; print_r($post); echo '</pre>'; exit;
		
		if(strlen($post['message']) < 2) { return; } // Move to spam check? Detect HTML required fields that are blank
		
		global $eeRSCFU; $subject = FALSE;
		
		$eeRSCF_ID = filter_var($_POST['eeRSCF_ID'], FILTER_VALIDATE_INT);
		
		if($this->spamBlock == 'YES') {
			if( self::eeRSCF_formSpamCheck() !== 'OK' ) {
				return FALSE;
			}
		}
		
		// This line prevents values being entered in a URL
		if ($_SERVER['REQUEST_METHOD'] != "POST") {
			$this->errors[] = "Not a POST submission";
			return;
		} 
	
		// Check referrer is from same site.
		if(!wp_verify_nonce($_REQUEST['ee-rock-solid-nonce'], 'ee-rock-solid')) {
			$this->errors[] =  "Submission is not from this website";
			return;
		}
		
		$this->log[] = 'Sending the Email...';
			
		self::eeRSCF_PostProcess($post);
		
		// There's a file and its size is less than our defined limit
		if(@$_FILES['file']['name']) {
			if($_FILES['file']['size'] <= $this->fileMaxSize*1048576) {
				$eeRSCFU->eeRSCFU_Uploader();
			} else {
				$this->errors[] = 'File size is too large. Maximum allowed is ' . $this->fileMaxSize . 'MB';
			}
		}

		if(!$this->errors AND count($this->thePost)) {
				
			$this->log[] = 'Preparing the Email...';
			
			$eeThisFormArray = get_option('eeRSCF_' . $eeRSCF_ID);
			
			// echo '<pre>'; print_r($eeThisFormArray); echo '</pre>'; exit;
			
			if($eeThisFormArray['TO']) {
				$this->to = $eeThisFormArray['TO'];
			} elseif($eeThisFormArray['to']) {
				$this->to = $eeThisFormArray['to']; // Legacy
			} else {
				$this->errors[] = 'No TO Address Configured';
				return FALSE;
			}
			
			if(@$eeThisFormArray['CC']) {
				$this->cc = $eeThisFormArray['CC'];
			} elseif($eeThisFormArray['cc']) {
				$this->cc = $eeThisFormArray['bcc']; // Legacy
			}
			
			if(@$eeThisFormArray['BCC']) {
				$this->bcc = $eeThisFormArray['BCC'];
			} elseif($eeThisFormArray['bcc']) {
				$this->bcc = $eeThisFormArray['bcc']; // Legacy
			}

			
			// Loop through and see if we have a Subject field
			foreach($this->thePost as $value){
				$field = explode(':', $value);
					if(strpos($field[0], 'ubject')) {
						$subject = html_entity_decode($field[1], ENT_QUOTES);
						$subject = stripslashes($subject);
					}
			}
			if(!$subject) { $subject = 'Contact Form Message (' . $_SERVER['HTTP_HOST'] . ')'; }
			
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
				
				
			if(@filter_var($eeThisFormArray['confirm'], FILTER_VALIDATE_URL)) {
				
				$url = $eeThisFormArray['confirm'];
			
			} else {
				
				// Reload this page
				$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			}
			
			$this->emailMode = 'PHP'; // Force PHP until we fix SMTP :-(
			
			if($this->emailMode == 'SMTP') {
				
				if( wp_mail($this->to, $subject, $eeBody, $eeHeaders) ) { // <<< ----------------Send the message via Authenticated SMTP.
					
					$this->log[] = 'SMTP Mail Sent';
					
					wp_redirect($url); exit;
					
				} else {
					$this->errors[] = 'SMTP Message Failed to Send.';
				}
			
			} else {
				
				if(wp_mail($this->to, $subject, $eeBody, $eeHeaders) ) { // <<< ---------------- OR send the message the basic way.
					
					$this->log[] = 'WP Mail Sent';
					
					// wp_die('Going to: ' . $url);
					
					wp_redirect($url); exit;
					
				} else {
					
					
					
					$this->errors[] = 'PHP Message Failed to Send.';
					$this->errors[] = 'To: ' . $this->to;
				}
			}
		} else {
			$this->errors[] = 'Message not sent. Please try again.';
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
	
	
	
	
			
	function eeRSCF_AdminSettingsProcess($post)	{
		
		$this->log[] = 'Processing Form Settings';
		
		$this->log['post'] = $post;
		
		if($post AND check_admin_referer( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
			
			global $wpdb, $eeRSCF, $eeRSCFU;
			
			
			
			
			
			
			// Form Fields
			if(@$_POST['eeRSCF_formSettings'] == 'TRUE') {
			
				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				$eeArray = array();
			
				// ID
				$eeRSCF_ID = filter_var(@$_POST['eeRSCF_ID'], FILTER_SANITIZE_NUMBER_INT);
				
				// Name
				$formName = filter_var(@$_POST['eeRSCF_formName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				if($formName) { $eeArray['name'] = $formName; }
	
				// Email Addresses
				if( isset($_POST['eeRSCF_formTO']) ) {
				
					$delivery = array('TO', 'CC', 'BCC');
					
					foreach($delivery as $to) {
					
						$eeSet = ''; // String of comma deliniated emails
						
						if( isset($_POST['eeRSCF_form' . $to ]) ) {
								
							$eeString = filter_var($_POST['eeRSCF_form' . $to ], FILTER_SANITIZE_STRING);
							
							if(strpos($eeString, ',')) { // More than one address
							
								$this->log[] = 'Multiple address for ' . $to . ' field.';
								
								$emails = explode(',', $eeString); // Make array
								
								foreach($emails as $email) { // Loop through them
									
									$email = trim($email); // Trim spaces
									
									if(filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate address
										$eeSet .= $email . ','; // Assemble addresses for storage
									} else {
										$this->errors[] = 'Bad ' . $to . ' Address: ' . $email;
									}
								}
								
								$eeSet = substr($eeSet, 0, -1); // Clip the last comma
															
							} elseif($eeString) { // Just one address
								
								if(filter_var($eeString, FILTER_VALIDATE_EMAIL)) {
									$this->log[] = 'Single address for ' . $to . ' field.';
									$eeSet .= $eeString;
								} else {
									$this->errors[] = 'Bad ' . $to . ' Address: ' . $_POST['eeAdmin' . $to];
								}
							}
						}
							
						$eeSet;
						
						if($eeSet) { $eeArray[$to] = $eeSet; }
					}

				} else {
					$this->errors[] = 'Need at Least One Email Address';
					
				}
			
			
			
			
				$fieldsArray = $_POST['eeRSCF_fields'];
				
				if( is_array($fieldsArray) ) {
				
					foreach($fieldsArray as $thisName => $thisFieldArray) {
						
						if(@$thisFieldArray['show']) {
							$eeArray['fields'][$thisName]['show'] = 'YES';
						} else {
							$eeArray['fields'][$thisName]['show'] = 'NO';
						}
						
						if(@$thisFieldArray['req']) {
							$eeArray['fields'][$thisName]['req'] = 'YES';
						} else {
							$eeArray['fields'][$thisName]['req'] = 'NO';
						}
						
						$eeArray['fields'][$thisName]['label'] = @$thisFieldArray['label'];
					}
				}
				
				
				if(@$_POST['eeRSCF_confirm']) {
					
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
				$eeRSCF->log[] = 'Spam Protection On: ' . $settings;
				update_option('eeRSCF_spamBlock', $settings); // Update the database
				
				// Block Spam Bots
				if($_POST['spamBlockBots'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam Block Bots: ' . $settings;
				update_option('eeRSCF_spamBlockBots', $settings); // Update the database
				
				// Honeypot
				$settings = filter_var($_POST['spamHoneypot'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				$settings = $this->eeRSCF_MakeSlug($settings);
				$eeRSCF->log[] = 'Spam Honeypot: ' . $settings;
				update_option('eeRSCF_spamHoneypot', $settings); // Update the database
				
				// English Only
				if($_POST['spamEnglishOnly'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam English Only: ' . $settings;
				update_option('eeRSCF_spamEnglishOnly', $settings); // Update the database
				
				// Block Fishy
				if($_POST['spamBlockFishy'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam Block Fishy: ' . $settings;
				update_option('eeRSCF_spamBlockFishy', $settings); // Update the database
				
				// Block Words
				if($_POST['spamBlockWords'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam Block Words: ' . $settings;
				update_option('eeRSCF_spamBlockWords', $settings); // Update the database

				// Blocked Words
				$settings = filter_var($_POST['spamBlockedWords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				$eeRSCF->log[] = 'Spam Blocked Words: ' . $settings;
				update_option('eeRSCF_spamBlockedWords', $settings); // Update the database
				
				// Block Common Words
				if($_POST['spamBlockCommonWords'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam Block CommonWords: ' . $settings;
				update_option('eeRSCF_spamBlockCommonWords', $settings); // Update the database

				// Blocked Common Words
				$settings = filter_var($_POST['spamBlockedCommonWords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				$eeRSCF->log[] = 'Spam Blocked CommonWords: ' . $settings;
				update_option('eeRSCF_spamBlockedCommonWords', $settings); // Update the database
				
				// Send Notice
				if($_POST['spamSendAttackNotice'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam Attack Notice: ' . $settings;
				update_option('eeRSCF_spamSendAttackNotice', $settings); // Update the database
				
				// Notice Email
				$settings = filter_var($_POST['spamNoticeEmail'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				$eeRSCF->log[] = 'Spam Notice Email: ' . $settings;
				update_option('eeRSCF_spamNoticeEmail', $settings); // Update the database
				
				// Send Admin Notice
				if($_POST['spamSendAttackNoticeToDeveloper'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$eeRSCF->log[] = 'Spam Attack Developer Notice: ' . $settings;
				update_option('eeRSCF_spamSendAttackNoticeToDeveloper', $settings); // Update the database
			}
			
			
			
			
			
			if(@$_POST['eeRSCF_EmailSettings'] == 'TRUE') {
					
				
				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				
				// Form Address
				$setting = 'eeRSCF_email';
				$value = filter_var(@$_POST[$setting], FILTER_VALIDATE_EMAIL);
				if($value) {
					$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
					update_option($setting, $value); 
				} else {
					$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
				}
				
				// Form Mode
				$setting = 'eeRSCF_emailMode';
				$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
				if($value) {
					$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
					update_option($setting, $value); 
				} else {
					$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
				}
				
				
				if($value == 'SMTP') {
				
					// The Nice Name
					$setting = 'eeRSCF_emailName';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// The Message Format
					$setting = 'eeRSCF_emailFormat';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Hostname
					$setting = 'eeRSCF_emailServer';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Username
					$setting = 'eeRSCF_emailUsername';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Password
					$setting = 'eeRSCF_emailPassword';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Security
					$setting = 'eeRSCF_emailSecure';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Security
					$setting = 'eeRSCF_emailPort';
					$value = filter_var(@$_POST[$setting], FILTER_VALIDATE_INT);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					
					// Port
					$setting = 'eeRSCF_emailAuth';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
					
					// Debug
					$setting = 'eeRSCF_emailDebug';
					$value = filter_var(@$_POST[$setting], FILTER_SANITIZE_STRING);
					if($value) {
						$eeRSCF->log[] = 'Email Config: ' . $setting . ' = ' . $value;
						update_option($setting, $value); 
					} else {
						$this->errors[] = 'Email Config Problem: ' . $setting . ' = ' . $value;
					}
				
				}
				
			}
			
			
			// Re-Get our settings
			self::eeRSCF_Setup(TRUE);
			
		}
	}	
			
			
			

} // Ends Class eeRSCF	
	
?>