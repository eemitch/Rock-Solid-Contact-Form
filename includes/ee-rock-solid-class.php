<?php // EE Contact Form MAin Class
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTES -------

// SELECT * FROM `wp_options` WHERE option_name LIKE 'eeRSCF_%' ORDER BY option_name

	
class eeRSCF_Class {
	
	public $pluginName = "Rock Solid Contact Form";
	
	// private $pluginAuthorEmail = 'admin@elementengage.net'; // Set an email address to get bounce notices.
	// private $eeRemoteSpamWordsURL = ''; // Master words list. https://elementengage.com/eeCF/index.php?eePIN=ee0606
	
	public $dbFieldName = "eeRSCF"; // The name of the options field in the database
	
	public $eeRSCF_1 = array(
		
		'name' => 'Main',
		'to' => '',
		'cc' => '',
		'bcc' => '',
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
		'files' => array('show' => 'YES', 'req' => 'NO', 'label' => 'Attachments')
	));
	
	
/*
	public $default_fileAllowUploads = 'YES';
	public $default_fileMaxSize = 8;
	public $default_fileFormats = '.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip';
	public $default_spamBlock = 'Yes';
	public $default_spamWords = 'I am a web, websites, website design, web design, web designer, web developer, web development, more leads, more sales, leads and sales, first page of google, seo, search engine, more profitable';
	public $default_departments = 'Main^support@elementengage.com^mitch@elementengage.com';
	public $default_departmentName = 'Main';
*/
	
	
	
	// Our Form Settings
	// public $formFields;
	public $fileAllowUploads;
	public $fileMaxSize;
	public $fileFormats;
	public $spamBlock;
	public $spamWords;
	public $departments;
	
	
	
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
	public $department;		
	// private $deptArray = array();
	
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
		
		
		
		
		// Get First Form Array
		$this->eeRSCF_1 = unserialize( get_option('eeRSCF_1') );
		$this->log['settings']['eeRSCF_1'] = $this->eeRSCF_1;
		
		// Look for more forms...
		
		// echo '<pre>'; print_r($this->eeRSCF_1); echo '</pre>'; exit;
		
		
		
		
		
		
		
		// FILES
		$this->fileAllowUploads = get_option('eeRSCF_fileAllowUploads');
		$this->log['settings'][] = $this->fileAllowUploads;
		
		
		$this->fileMaxSize = get_option('eeRSCF_fileMaxSize');
		$this->log['settings'][] = $this->fileMaxSize;
		
		
		$this->fileFormats = get_option('eeRSCF_fileFormats');
		$this->log['settings'][] = $this->fileFormats;
		
		
		
		// SPAM
		$this->spamBlock = get_option('eeRSCF_spamBlock');
		$this->log['settings'][] = $this->spamBlock;
		
		$this->spamWords = get_option('eeRSCF_spamWords');
		$this->log['settings'][] = $this->spamWords;
		
		
		
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

	
	private function eeRSCF_PostProcess($post) {
	     
	     echo '<pre> eeRSCF_PostProcess '; print_r($post); echo '</pre>'; exit;
	     
	     $this->log[] = 'Processing the post...';
	     
	     $ignore = array('eeRSCF', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER');
	     
	     foreach ($post as $key => $value){
	        
	        if(!in_array($key, $ignore) AND $value) {
					
				$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);
				
				if($key != 'message') { $value = ucwords($value); } else { $value = striptags($value); }
				
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
	    
	    
	    
	}
	
	function eeRSCF_formTamperCheck() {
		
		$this->log[] = 'Form Tamper Check...';
		
		$tamper = FALSE; $entries = array();
		
		$array = array_filter($_POST); // Get rid of empty fields
		$count = count($array); // How many filled in fields?
		$newArray = array_unique($array); // Get rid of duplicates
		$newCount = count($newArray);
		
		// Get our field entries
		foreach($_POST as $key => $value) { 
			
			if($value) {
				$entries[] = $value;
				
				// If you can't read it, block it.
				// TO DO --------------------------- <<<------Add language options to admin panel
				
				if(preg_match('/[А-Яа-яЁё]/u', $value) OR preg_match('/\p{Han}+/u', $value)) {
					$this->errors[] = "Foreign language detected";
					break;
				}
			}
		}
		
		// Check for duplicated info in fields (spam)
		$counts = array_count_values($entries);
		foreach($counts as $value) {
			if($value > 2) { 
				$this->errors[] = "Multiple same field entries";
			}
		}
		
		// This line prevents values being entered in a URL
		if ($_SERVER['REQUEST_METHOD'] != "POST") {
			$this->errors[] = "Not a POST submission";
		} 
	
		// Check referrer is from same site.
		if(! wp_verify_nonce($_REQUEST['ee-rock-solid-nonce'], 'ee-rock-solid')) {
			$this->errors[] =  "Submission is not from this website";
		}
		
		if($this->spamBlock AND $_POST['eeRSCF_Link']) { // Spambot catcher. This field should never be completed.
			$this->errors[] = 'Spambot catch. Hidden field completed: ' . $_POST['eeRSCF_Link'];
		}
		
		if($this->spamBlock AND $this->spamWords) {
			
			// Local Words
			$spamWords = explode(',', $this->spamWords);
			
			// Check remote list of words
			$spamWordsRemote = $this->eeGetWebPage($this->eeRemoteSpamWordsURL);
			
			if($spamWordsRemote) {
				
				// Make array
				$spamWordsRemote = explode(PHP_EOL, $spamWordsRemote); // Split by End of Line
				
				// Combine with the local words
				$spamWords = array_merge($spamWords, $spamWordsRemote);
				
				// Remove Duplicates
				$spamWords = array_unique($spamWords);
			}
		
			// Loop through post to look for blocked words
			foreach($_POST as $key => $value){
				
				$value = strtolower($value);
				
				foreach($spamWords as $spamWord){
					
					$spamWord = trim($spamWord);
					
					if(strlen($value) AND strpos($value, ' ' . $spamWord . ' ')) {
						// Use of spaces around the word prevents parts of larger words tripping the catch.
						
						$this->errors[] = 'Spam word catch: ' . $spamWord;
					}
				}
			}
		}
		
		if(count($this->errors) >= 1) {
			
			$eeAttackLog = array(); // An array we will fill, serialize and encode, and then send to an EE server.
			
			// We do this to halp make the World a better place.
			
			// Website Info
			$eeAttackLog[] = 'CONTACT FORM SPAM: ' . $_SERVER['HTTP_HOST'];
			$eeAttackLog['Attack'] = $this->errors;
			$eeAttackLog[] = 'Victim Website: http://' . $_SERVER['HTTP_HOST'];
			
			// Attacker Info
			$eeAttackLog[] = 'Attacker User Agent: ' . @$_SERVER['HTTP_USER_AGENT'];
			$eeAttackLog[] = 'Attacker IP: ' . @$_SERVER['REMOTE_ADDR'];
			$eeAttackLog[] = 'Attacker Came From: ' . @$_POST['SCRIPT_REFERER'] . @$_SERVER['QUERY_STRING'];
			$eeAttackLog[] = "Attacker Message:";
			$eeAttackLog[] = $_POST;
			
			// Serialize and Encode for Transfer
			$eeAttackLog = serialize($eeAttackLog);
			$eeAttackLog = urlencode($eeAttackLog);
			
			// Send the attack log to the EE server 
			$eeResult = $this->eeGetWebPage('https://elementengage.com/eeCF/log_attack.php?eePIN=ee0606&eeAttackLog=' . $eeAttackLog);
			
			
			$this->log[] = 'Spam Check FAIL!';
			$this->log[] = $eeAttackLog;
			
			return TRUE; // Bad spammer !!!
		}
		
		$this->log[] = 'Spam Check Okay';
		
		return FALSE;
	}
	
	
	
	
	private function eeRSCF_GetWebPage($eeURL) {
        
        $user_agent = 'EE Contact Form 1.3';

        $options = array(

            CURLOPT_CUSTOMREQUEST  => "GET",        // set request type post or get
            CURLOPT_POST           => FALSE,        // set to GET
            CURLOPT_USERAGENT      => $user_agent,  // set user agent
            // CURLOPT_COOKIEFILE     =>"cookie.txt", //set cookie file
            // CURLOPT_COOKIEJAR      =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => TRUE,     // return web page
            CURLOPT_HEADER         => FALSE,    // don't return headers
            CURLOPT_FOLLOWLOCATION => FALSE,     // follow redirects
            // CURLOPT_ENCODING       => "",       // handle all encodings
            // CURLOPT_AUTOREFERER    => TRUE,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 30,      // timeout on connect
            CURLOPT_TIMEOUT        => 30,      // timeout on response
            // CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        );

        $ch = curl_init( $eeURL );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err = curl_errno( $ch );
        $errmsg = curl_error( $ch );
        $header = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        
        // $this->log[] = $header;
        
        return $header['content'];
    }
	
	
	// fields=first-name^SHOW^First Name^REQ|last-name^SHOW^Last Name^REQ|business^SHOW^Business^REQ|address^SHOW^Address^REQ|address-2^SHOW^Address 2^REQ|city^SHOW^City^REQ|state^SHOW^State^REQ|zip^SHOW^Zip^REQ|phone^SHOW^Phone^REQ|website^SHOW^Website^REQ|other^SHOW^Other^REQ|subject^SHOW^Subject^REQ][fileAllowUploads=Yes][uploadMaxFilesize=10][formats=.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip][spamBlock=Yes][spamWords=I am a web, websites, website design, web design, web designer, web developer, web development, more leads, more sales, leads and sales, first page of google, seo, search engine, more profitable][FROM:admin@elementengage.net][DEPT:N/A^TO:admin@elementengage.net^^)][version=1.2
	
		
	private function eeRSCF_UpgradeFromEE($eeString) {
		
		$this->deptArray = array();
		
		$eeArray = array();
		
		if($eeString) {
			
			$settings = explode('][', $eeString);
			
			// Process the Settings Array
			$fieldsArray = explode('=', $settings[0]);
			$eeArray[ 'eeRSCF_' . $fieldsArray[0] ] = $fieldsArray[1];
			
			$fileAllowUploads = explode('=', $settings[1]);
			$eeArray[ 'eeRSCF_' . $fileAllowUploads[0] ] = $fileAllowUploads[1];
			
			$fileMaxSize = explode('=', $settings[2]);
			$eeArray[ 'eeRSCF_' . $fileMaxSize[0] ] = $fileMaxSize[1];
			
			$formats = explode('=', $settings[3]);
			$eeArray[ 'eeRSCF_' . $formats[0] ] = $formats[1];
			
			$spamBlock = explode('=', $settings[4]);
			$eeArray[ 'eeRSCF_' . $spamBlock[0] ] = $spamBlock[1];
			
			$spamWords = explode('=', $settings[5]);
			$eeArray[ 'eeRSCF_' . $spamWords[0] ] = $spamWords[1];
			
			$eeArray[ 'eeRSCF_from'] = substr($settings[6], 5);
			
			$eeString = $settings[7];
			$eeString = str_replace('DEPT:', '', $eeString);
			$eeArray[ 'eeRSCF_departments'] = str_replace(')', '', $eeString);
			
			$this->log['Old Settings'] = $eeArray; // Our old settings
			
			// Insert the New Options
			foreach( $eeArray as $eeKey => $eeValue){
				
				if( str_replace('^TO:', '', $eeValue) ) {}
				
				update_option($eeKey, $eeValue);
			}
			
			// delete_option('eeContactForm');
			
			return;
					
		} else {
			
			$this->log[] = 'No Settings to Process?';
		}
	}
	
	
	public function eeRSCF_GetDepartments() {
		
			$departments = get_option('eeRSCF_departments');
			
			$departments = explode('|', $departments);
			
			if(count($departments)) {
				
				foreach($departments as $dept) {
					
					if(!empty($dept)) {
						$array = explode('^', $dept);
						
						$department = @substr($array[0], 5) . '^' . @substr($array[1], 3) . '^' . @substr($array[2], 3) . '^' . @substr($array[3], 4); // Chop of the labeling; i.e. TO:
						$this->deptArray[] = $department;
						
						// These will be set to last array's values (helpful if only one, otherwise overwritten)
						$this->department = @substr($array[0], 5);
						$this->to = @substr($array[1], 3);
						$this->cc = @substr($array[2], 3);
						$this->bcc = @substr($array[3], 4);
					}
				}
				
				$this->deptArray = array_unique($this->deptArray);
			}
		
		
	}
	
	
	public function eeRSCF_formDisplay() {
		
		$this->log[] = 'Displaying the Form...';
		
		global $eeRSCF_DevMode;
		
		self::eeRSCF_Setup(TRUE);
		
		// $eeMessageDisplay = new eeRSCF_MessageDisplay(); // Initialize the display messaging class
		
		if($this->confirmation) {
			
			$this->theForm .= '<div class="eeRSCF_Confirm">
				<h2>Thank You</h2>
				<p>' . $this->confirmation . '</p>
				</div>';
				
			// Log Display - Dev Mode Only
			if($eeRSCF_DevMode) { $this->eeRSCF_MessageDisplay($this->log); }
			
			return $this->theForm;
			
		} elseif($this->errors) {
		
			$this->theForm .= '<div class="eeRSCF_Confirm">
				<h2 class="eeError">Opps, we have a problem.</h2>';
			$this->eeRSCF_MessageDisplay($this->errors);
			$this->theForm .= '</div>';	
			
		}
		
		$this->theForm = '<div id="eeRSCF">
			<form action="';
			
		$this->theForm .= $this->permalink;
		
		$this->theForm .= '" method="post" enctype="multipart/form-data" id="eeRSCF_form">
				<input type="hidden" name="eeRSCF" value="TRUE" />
				<input type="hidden" name="SCRIPT_REFERER" value="';
				
		$this->theForm .= @$_SERVER['HTTP_REFERER'];
		
		$this->theForm .= '" />';
				
		$this->theForm .= wp_nonce_field( 'ee-rock-solid', 'ee-rock-solid-nonce' );
		
		$this->theForm .= '
		
		<fieldset>';
		
		// Is there more than one department?
		$num = count($this->departments);
		
		if($num > 1) {
			
			$this->theForm .= '<div class="eeRSCF_Row">
							<label for="department">Department</label><select name="department" id="department" required>
								<option value="">Please Choose</option>';
			
			foreach($this->departments as $department){
				$array = explode('^', $department);
				$this->theForm .= '<option value="' . $array[0] . '">' . $array[0] . '</option>';
			}
			
			$this->theForm .= '</select></div>';
		
		} else {
			
			// $this->theForm .= '<input type="hidden" name="department" value="' . $this->departments[0] . '" />';
			
		}
		
		if(is_array($this->formFields)) {
					
			foreach($this->formFields as $field) {
							
				$field = explode('^', $field);
							
				if(@$field[1] == 'SHOW') {
						
					$this->theForm .= '
					<div class="eeRSCF_Row">
					<label for="';
							
					$this->theForm .= $field[0];
					
					$this->theForm .=  '">';
					
					// Chech for custom label
					if($field[2]) { $this->theForm .= $field[2]; } else { $this->theForm .= self::eeRSCF_UnSlug($field[0]); }
					
					$this->theForm .= '</label>';
					
					$this->theForm .= '
					<input ';
					if(@$field[3] == 'REQ') { $this->theForm .= 'required '; }
					$this->theForm .= 'name="';
					// Chech for custom label
					if($field[2]) { $this->theForm .= self::eeRSCF_MakeSlug($field[2]); } else { $this->theForm .= self::eeRSCF_UnSlug($field[0]); }
					$this->theForm .= '"';
					
					$this->theForm .= ' id="';
					$this->theForm .= $field[0] . '"';
					
					$this->theForm .= ' type="';
					if(@$field[0] == 'email') { $this->theForm .=  'email'; } 
						elseif(@$field[0] == 'phone') { $this->theForm .=  'tel'; } 
							else { $this->theForm .= 'text'; }
					$this->theForm .= '" size="30" value="' . @$_POST[$field[0]] . '" />';
					
					if(@$field[3] == 'REQ') { $this->theForm .=  '
						<span class="eeRequired">*</span>'; }
					
					$this->theForm .=  '
					</div>';	
				}

			}
		} else {
			$this->theForm .= 'No Fields';
		}
		
		$this->theForm .= '<div class="eeRSCF_Row">
			<label for="message">Your Email</label>
			<input type="email" name="email" id="email" value="' . @$_POST['email'] . '" required /><span class="eeRequired">*</span>
			</div>';
					
					
		if($this->fileAllowUploads == 'YES') {
		
		$this->theForm .= '<div class="eeRSCF_Row">
			<label for="file">Attachment</label>
			<input type="file" name="file" id="file" accept="';
			
			$this->theForm .= $this->fileFormats . '" />';
			
			$this->theForm .= '
			
			<!-- <span class="eeNote">Files up to ' . $this->fileMaxSize . ' MB allowed.</span> -->
			
			</div>';
			
		}
		
		$this->theForm .= '<div class="eeRSCF_Row">
			<label for="message">Message</label>
			<textarea required name="message" id="message" cols="60" rows="6">';
			
		$this->theForm .= @$_POST['message'];
		
		$this->theForm .= '</textarea>
		
		<span class="eeRequired">*</span>
		
		</div>
		
		<br class="eeClearFix" />
		
		<div class="eeRSCF_Roww">
			<label for="eeRSCF_Link">Link:</label><input type="text" name="eeRSCF_Link" value="" id="eeRSCF_Link">
		</div>
		
		<span id="eeRSCF_SubmitMessage"><img src="' . $this->pluginURL . 'images/sending.gif" width="32" height="32" alt="Sending Icon" /> Sending Your Message</span>
		
		</fieldset>
		
		<input type="submit" id="eeRSCF_Submit" value="SEND">
		
		</form>
		
		
		<br class="eeClearFix" />Mode: ' . $this->emailMode . '
		
		
		</div>';
	
		// Log Display - Dev Mode Only
		if($eeRSCF_DevMode) { $this->eeMessageDisplay($this->log); }
		
		return $this->theForm;
			
	}
	
	
	public function eeRSCF_SendEmail($post) {
		
		global $eeRSCFU; $subject = FALSE;
				
		$this->log[] = 'Getting User Settings...';
		self::eeRSCF_Setup(TRUE);
		
		if($this->spamBlock == 'Yes') {
			if(self::eeRSCF_formTamperCheck()) {
				return FALSE;
			}
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
			
			if(count($this->departments) > 1) {
				
				$string = $this->thePost[0]; // Department is first one in post
				$field = explode(':', $string);
				$this->department = trim($field[1]);
				
				foreach($this->departments as $dept) {
						
					$array = explode('^', $dept);
					
					if($array[0] == $this->department) {
					
						$this->to = $array[1];
						$this->cc = @$array[2];
						$this->bcc = @$array[3];
						break;
					}
				}
				
			} else {
				
				$eeArray = explode('^', $this->departments[0]);
				
				$this->to = $eeArray[1];
				$this->cc = @$eeArray[2];
				$this->bcc = @$eeArray[3];
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
			$eeHeaders = "From: " . $this->email . "\n";
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
			
			
			if($this->emailMode == 'SMTP') {
				
				// Define SMTP Settings
				global $phpmailer;
				
				if ( !is_object( $phpmailer ) ) {
					$phpmailer = (object) $phpmailer;
				}
				
				$phpmailer->Mailer     = 'smtp';
				// $phpmailer->isHTML(FALSE);
				// $phpmailer->isSMTP();
				
				$phpmailer->From       = $this->email;
				$phpmailer->FromName   = $this->emailName;
				$phpmailer->Host       = $this->emailServer;
				$phpmailer->Username   = $this->emailUsername;
				$phpmailer->Password   = $this->emailPassword;
				$phpmailer->SMTPSecure = $this->emailSecure;
				$phpmailer->Port       = $this->emailPort;
				$phpmailer->SMTPAuth   = $this->emailAuth;
				
				if($this->emailFormat == 'HTML') {
					// $phpmailer->isHTML(TRUE);
					// $phpmailer->msgHTML = $body;
					// $phpmailer->Body = nl2br($body);
				}
				
				if( wp_mail($this->to, $subject, $eeBody, $eeHeaders) ) { // <<< ----------------Send the message via Authenticated SMTP.
					
					$this->confirmation = 'Your message was sent successfully.';
					
				} else {
					$this->errors[] = 'SMTP Message Failed to Send.';
				}
			
			} else {
				
				if(mail($this->to, $subject, $eeBody, $eeHeaders) ) { // <<< ---------------- OR send the message the basic way.
					
					$this->confirmation = 'Your message was sent successfully.';
					
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
			
			global $wpdb, $eeRSCFU;
			
			// Form Fields
			if(@$_POST['eeRSCF_formSettings'] == 'TRUE') {
			
				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				
				
				
				$eeArray = array();
			
				// ID
				$eeRSCF_ID = filter_var(@$_POST['eeRSCF_formID'], FILTER_SANITIZE_NUMBER_INT);
				
				// Name
				$formName = filter_var(@$_POST['eeRSCF_formName'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				if($formName) { $eeArray['name'] = $formName; }
	
				// Email Addresses
				if(@$_POST['eeRSCF_formTO']) {
				
					$delivery = array('to', 'cc', 'bcc');
					
					foreach($delivery as $to) {
					
						$eeSet = ''; // String of comma deliniated email addds
						
						$eeString = filter_var(@$_POST['eeRSCF_form' . strtoupper($to) ], FILTER_SANITIZE_STRING);
						
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
								$this->errors[] = 'Bad ' . $to . ' Address: ' . $_POST['eeAdmin' . $to . $i];
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
						
						$eeArray['fields'][$thisName] = array('show' => @$thisFieldArray['show'], 'req' => @$thisFieldArray['req'], 'label' => @$thisFieldArray['label']);
						
					}
				}
				
				// echo '<pre>'; print_r($eeArray); echo '</pre>'; exit;
				
				$eeArrayString = serialize($eeArray);
				update_option('eeRSCF_' . $eeRSCF_ID, $eeArrayString); // Update the database
				
			}
			
			
			
			
			
			
			if(@$_POST['eeRSCF_FileSettings'] == 'TRUE') {
							
				// Only accept Yes as the answer string. Gotta stay positive!
				if($_POST['eeAllowUploads'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				
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
				
				// Words Not Allowed
				$settings = filter_var($_POST['spamWords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				$eeRSCF->log[] = 'Spam Words: ' . $settings;
				update_option('eeRSCF_spamWords', $settings); // Update the database
			
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
			
			
			
			
			
			
			if(@$_POST['eeRSCF_DepartmentSettings'] == 'TRUE') {

				$departments = '';
				$departmentSet = '';
				
				$num = filter_var($_POST['eeRSCF_Departments'], FILTER_SANITIZE_NUMBER_INT); // How many departments?
				
				$this->log[] = $num . ' departments';
				
				for($i = 1; $i <= $num; $i++) {
					
					// The department
					if(@$_POST['eeRSCF_formName' . $i]) {
						$department = filter_var($_POST['eeRSCF_formName' . $i], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
					
					} else { // Account for a depertment in the middle removed (if more than one removed, others will be truncated)
						$i++;
						if(@$_POST['eeRSCF_formName' . $i]) {
							$department = filter_var($_POST['eeRSCF_formName' . $i], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
						}
					}
					
					if($department) {
						
						$department = ucwords($department); // Make all lowercase look gooder
					
						
					}
				
					// Add to the settings string
					$departments .= $departmentSet;
					
				} // End for loop
				
				$this->log[] = 'Departments: ' . $departments;
				
				update_option('eeRSCF_departments', $departments); // Update the database
				
			
			}
			
			// Re-Get our settings
			self::eeRSCF_Setup(TRUE);
			
		}
	}	
			
			
			

} // Ends Class eeRSCF	
	
?>