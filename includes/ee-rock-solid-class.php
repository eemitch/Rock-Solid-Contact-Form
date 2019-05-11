<?php // EE Contact Form MAin Class
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
class eeRSCF_Class {
	
	public $pluginName = "Rock Solid Contact Form";
	public $eeRSCFDBVersion = '1.2'; // The version of the database data format, needed for updates.
	private $pluginAuthorEmail = 'admin@elementengage.net'; // Set an email address to get bounce notices.
	private $eeRemoteSpamWordsURL = ''; // Master words list. https://elementengage.com/eeCF/index.php?eePIN=ee0606
	
	public $dbFieldName = "eeRSCF"; // The name of the options field in the database
	
	
	// fields=first-name^SHOW^First Name^REQ|last-name^SHOW^Last Name^REQ|business^SHOW^Business^REQ|address^SHOW^Address^REQ|address-2^SHOW^Address 2^REQ|city^SHOW^City^REQ|state^SHOW^State^REQ|zip^SHOW^Zip^REQ|phone^SHOW^Phone^REQ|website^SHOW^Website^REQ|other^SHOW^Other^REQ|subject^SHOW^Subject^REQ
	// ][
	// allowUploads=Yes
	// ][
	// uploadMaxFilesize=10
	// ][
	// formats=.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip
	// ][
	// spamBlock=Yes
	// ][
	// spamWords=I am a web, websites, website design, web design, web designer, web developer, web development, more leads, more sales, leads and sales, first page of google, seo, search engine, more profitable
	// ][
	// FROM:admin@elementengage.net
	// ][
	// DEPT:N/A^TO:admin@elementengage.net^^)
	// ][
	// version=1.2

	
	// Our Default Settings
	public $default_formFields = 'first-name^SHOW^First Name^REQ|last-name^SHOW^Last Name^REQ|business^SHOW^Business^REQ|address^SHOW^Address^REQ|address-2^SHOW^Address 2^REQ|city^SHOW^City^REQ|state^SHOW^State^REQ|zip^SHOW^Zip^REQ|phone^SHOW^Phone^REQ|website^SHOW^Website^REQ|other^SHOW^Other^REQ|subject^SHOW^Subject^REQ';
	public $default_allowUploads = 'YES';
	public $default_maxFileSize = 8;
	public $default_fileFormats = '.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip';
	public $default_spamBlock = 'Yes';
	public $default_spamWords = 'I am a web, websites, website design, web design, web designer, web developer, web development, more leads, more sales, leads and sales, first page of google, seo, search engine, more profitable';
	
	// Our Current Settings
	public $fields;
	public $allowUploads;
	public $maxFileSize;
	public $fileFormats;
	public $spamBlock;
	public $spamWords;
	public $from;
	public $departments;
	
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
	private $deptArray = array();
	
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
		
		
		
		// Get DB Settings
		$this->fields = get_option('eeRSCF_fields');
		$this->log['settings'][] = $this->fields;
		
		
		$this->allowUploads = get_option('eeRSCF_allowUploads');
		$this->log['settings'][] = $this->allowUploads;
		
		
		$this->maxFileSize = get_option('eeRSCF_maxFileSize');
		$this->log['settings'][] = $this->maxFileSize;
		
		
		$this->fileFormats = get_option('eeRSCF_fileFormats');
		$this->log['settings'][] = $this->fileFormats;
		
		
		$this->spamBlock = get_option('eeRSCF_spamBlock');
		$this->log['settings'][] = $this->spamBlock;
		
		
		$this->spamWords = get_option('eeRSCF_spamWords');
		$this->log['settings'][] = $this->spamWords;
		
		
		$this->from = get_option('eeRSCF_from');
		$this->log['settings'][] = $this->from;
		
		
		$this->departments = get_option('eeRSCF_departments');
		$this->log['settings'][] = $this->departments;
		
		
		
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
	     
	     $this->log[] = 'Processing the post...';
	     
	     $ignore = array('eeRSCF', 'ee-rock-solid-nonce', '_wp_http_referer', 'SCRIPT_REFERER');
	     
	     foreach ($post as $key => $value){
	        
	        if(!in_array($key, $ignore) AND $value) {
					
				$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);
				
				if($key != 'message') { $value = ucwords($value); }
				
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
				
				$field = self::unSlug($key);
				$this->thePost[] = $field . ': ' . $value;
			}
	        
	        
	        // $this->log[] = "$key => $value";
	        
	        if(is_array($value)) { // Is $value is an array?
	            self::eeRSCF_PostProcess($value);
	        }
	    } 
	        
	    $this->log[] = $this->thePost;
	    
	    // echo '<pre>'; print_r($this->thePost); echo '</pre>'; exit;
	    
	}
	
	function eeRSCF_FormTamperCheck() {
		
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
		
		if($this->spamBlock AND $_POST['ee-link']) { // Spambot catcher. This field should never be completed.
			$this->errors[] = 'Spambot catch. Hidden field completed: ' . $_POST['ee-link'];
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
	
	
	// fields=first-name^SHOW^First Name^REQ|last-name^SHOW^Last Name^REQ|business^SHOW^Business^REQ|address^SHOW^Address^REQ|address-2^SHOW^Address 2^REQ|city^SHOW^City^REQ|state^SHOW^State^REQ|zip^SHOW^Zip^REQ|phone^SHOW^Phone^REQ|website^SHOW^Website^REQ|other^SHOW^Other^REQ|subject^SHOW^Subject^REQ][allowUploads=Yes][uploadMaxFilesize=10][formats=.gif, .jpg, .jpeg, .bmp, .png, .tif, .tiff, .txt, .eps, .psd, .ai, .pdf, .doc, .xls, .ppt, .docx, .xlsx, .pptx, .odt, .ods, .odp, .odg, .wav, .wmv, .wma, .flv, .3gp, .avi, .mov, .mp4, .m4v, .mp3, .webm, .zip][spamBlock=Yes][spamWords=I am a web, websites, website design, web design, web designer, web developer, web development, more leads, more sales, leads and sales, first page of google, seo, search engine, more profitable][FROM:admin@elementengage.net][DEPT:N/A^TO:admin@elementengage.net^^)][version=1.2
	
		
	private function eeRSCF_UpgradeFromEE($eeString) {
		
		$this->deptArray = array();
		
		$eeArray = array();
		
		if($eeString) {
			
			$settings = explode('][', $eeString);
			
			// Process the Settings Array
			$fieldsArray = explode('=', $settings[0]);
			$eeArray[ 'eeRSCF_' . $fieldsArray[0] ] = $fieldsArray[1];
			
			$allowUploads = explode('=', $settings[1]);
			$eeArray[ 'eeRSCF_' . $allowUploads[0] ] = $allowUploads[1];
			
			$maxFileSize = explode('=', $settings[2]);
			$eeArray[ 'eeRSCF_' . $maxFileSize[0] ] = $maxFileSize[1];
			
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
	
	
	public function eeRSCF_FormDisplay() {
		
		$this->log[] = 'Displaying the Form...';
		
		global $eeRSCFDevMode;
		
		self::setup(TRUE);
		
		$eeMessageDisplay = new eeMessageDisplay(); // Initialize the display messaging class
		
		if($this->confirmation) {
			
			$this->theForm .= '<div class="eeRSCFConfirm">
				<h2>Thank You</h2>
				<p>' . $this->confirmation . '</p>
				</div>';
				
			// Log Display - Dev Mode Only
			if($eeRSCFDevMode) { $eeMessageDisplay->display($this->log); }
			
			return $this->theForm;
			
		} elseif($this->errors) {
		
			$this->theForm .= '<div class="eeRSCFConfirm">
				<h2 class="eeError">Opps, we have a problem.</h2>';
			$eeMessageDisplay->display($this->errors);
			$this->theForm .= '</div>';	
			
		}
		
		$this->theForm = '<div id="eeRSCF">
			<form action="';
			
		$this->theForm .= $this->permalink;
		
		$this->theForm .= '" method="post" enctype="multipart/form-data" id="eeRSCF">
				<input type="hidden" name="eeRSCF" value="TRUE" />
				<input type="hidden" name="SCRIPT_REFERER" value="';
				
		$this->theForm .= @$_SERVER['HTTP_REFERER'];
		
		$this->theForm .= '" />';
				
		$this->theForm .= wp_nonce_field( 'ee-rock-solid', 'ee-rock-solid-nonce' );
		
		$this->theForm .= '<fieldset>';
		
		// Is there more than one department?
		$num = count($this->deptArray);
		
		if($num > 1) {
			
			$this->theForm .= '<div class="eeRSCFRow">
							<label for="department">Department</label><select name="department" id="department" required>
								<option value="">Please Choose</option>';
			
			foreach($this->deptArray as $department){
				$array = explode('^', $department);
				$this->theForm .= '<option value="' . $array[0] . '">' . $array[0] . '</option>';
			}
			
			$this->theForm .= '</select>';
		
		}
		
		if(is_array($this->fields)) {
					
			foreach($this->fields as $field) {
							
				$field = explode('^', $field);
							
				if(@$field[1] == 'SHOW') {
						
					$this->theForm .= '<div class="eeRSCFRow">
							<label for="';
							
					$this->theForm .= $field[0];
					
					$this->theForm .=  '">';
					
					// Chech for custom label
					if($field[2]) { $this->theForm .= $field[2]; } else { $this->theForm .= self::unSlug($field[0]); }
					
					$this->theForm .= '</label>';
					
					$this->theForm .= '<input ';
					if(@$field[3] == 'REQ') { $this->theForm .= 'required '; }
					$this->theForm .= 'name="';
					// Chech for custom label
					if($field[2]) { $this->theForm .= self::makeSlug($field[2]); } else { $this->theForm .= self::unSlug($field[0]); }
					$this->theForm .= '"';
					
					$this->theForm .= ' id="';
					$this->theForm .= $field[0] . '"';
					
					$this->theForm .= ' type="';
					if(@$field[0] == 'email') { $this->theForm .=  'email'; } 
						elseif(@$field[0] == 'phone') { $this->theForm .=  'tel'; } 
							else { $this->theForm .= 'text'; }
					$this->theForm .= '" size="30" value="' . @$_POST[$field[0]] . '" />';
					
					if(@$field[3] == 'REQ') { $this->theForm .=  '<span class="eeRequired">*</span>'; }
					
					$this->theForm .=  '</div>';	
				}

			}
		} else {
			$this->theForm .= 'No Fields';
		}
		
		$this->theForm .= '<div class="eeRSCFRow">
			<label for="message">Your Email:</label>
			<input type="email" name="email" id="email" value="' . @$_POST['email'] . '" required /><span class="eeRequired">*</span>';
					
					
		if($this->allowUploads == 'Yes') {
		
		$this->theForm .= '<div class="eeRSCFRow">
			<label for="file">Attachment:</label><input type="file" name="file" id="file" accept="';
			
			$this->theForm .= $this->formats . '" />';
			
			$this->theForm .= '<span class="eeNote">Files up to ' . $this->maxFileSize . ' MB allowed.</span></div>';
			
		}
		
		$this->theForm .= '<div class="eeRSCFRow">
			<label for="message">Message:</label>
			<textarea required name="message" id="message" cols="60" rows="6">';
			
		$this->theForm .= @$_POST['message'];
		
		$this->theForm .= '</textarea><span class="eeRequired">*</span></div><br class="eeClearFix" /><div class="eeRSCFRoww"><label for="ee-link">Link:</label><input type="text" name="ee-link" value="" id="ee-link"></div>
			<span id="eeRSCFSubmitMessage"><img src="' . $this->pluginURL . 'images/sending.gif" width="32" height="32" alt="Sending Icon" /> Sending Your Message</span>
				<input type="submit" id="eeRSCFSubmit" value="SEND"></fieldset></form><br class="eeClearFix" /></div>';
	
		// Log Display - Dev Mode Only
		if($eeRSCFDevMode) { $eeMessageDisplay->display($this->log); }
		
		return $this->theForm;
			
	}
	
	
	public function eeRSCF_SendEmail($post) {
		
		global $eeRSCF_FileUpload; $subject = FALSE;
				
		$this->log[] = 'Getting User Settings...';
		self::setup(TRUE);
		
		if($this->spamBlock == 'Yes') {
			if(self::formTamperCheck()) {
				return FALSE;
			}
		}
		
		$this->log[] = 'Sending the Email...';
		
		if(RSCF_SMTP_USER) {
			$this->log[] = 'Using SMTP as ' . RSCF_SMTP_USER;
		}
			
		self::postProcess($post);
		
		// There's a file and its size is less than our defined limit
		if(@$_FILES['file']['name']) {
			if($_FILES['file']['size'] <= $this->maxFileSize*1048576) {
				$eeRSCF_FileUpload->uploader();
			} else {
				$this->errors[] = 'File size is too large. Maximum allowed is ' . $this->maxFileSize . 'MB';
			}
		}

		if(!$this->errors AND count($this->thePost)) {
				
			$this->log[] = 'Preparing the Email...';
			
			if(count($this->deptArray) > 1) {
				
				$string = $this->thePost[0]; // Department is first one in post
				$field = explode(':', $string);
				$this->department = trim($field[1]);
				
				foreach($this->deptArray as $dept) {
						
					$array = explode('^', $dept);
					
					if($array[0] == $this->department) {
					
						$this->to = $array[1];
						$this->cc = $array[2];
						$this->bcc = $array[3];
						break;
					}
				}
				
			} else {
				
				// Add a custom department name
				if($this->department != 'N/A') {
					array_unshift($this->thePost, 'Department: ' . $this->department); 
				}
				
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
			$eeHeaders = "From: " . $this->from . "\n";
			if($this->cc) { $eeHeaders .= "CC: " . $this->cc . "\n"; }
			if($this->bcc) { $eeHeaders .= "BCC: " . $this->bcc . "\n"; }
			$eeHeaders .= "Return-Path: " . $this->from . "\n" . "Reply-To: " . $this->sender . "\n";
			
			$eeBody = '';
			
			foreach ($this->thePost as $value) {
				$eeBody .= $value . "\n\n";
			}
			
			if($eeRSCF_FileUpload->fileUploaded) { $eeBody .= 'File: ' . $eeRSCF_FileUpload->fileUploaded . "\n\n"; }
			
			$eeBody .= "---\n\n" . 'This message was sent via the contact form located at http://' . $this->baseURL . '/' . "\n\n";
			
			$eeBody = stripslashes($eeBody);
			$eeBody = strip_tags(htmlspecialchars_decode($eeBody, ENT_QUOTES));		
			
			if(RSCF_SMTP_USER) {
				
				if( wp_mail($this->to, $subject, $eeBody, $eeHeaders) ) { // <<< ----------------Send the message via Authenticated SMTP.
					
					$this->confirmation = 'Your message was sent successfully. (SMTP)';
					
				} else {
					$this->errors[] = 'Message Failed to Send.';
				}
			} else {
				$this->errors[] = 'NOT SMTP User. Defaulting to PHP';
				
				if( mail($this->to, $subject, $eeBody, $eeHeaders) ) { // <<< ---------------- OR send the message the basic PHP way.
					
					$this->confirmation = 'Your message was sent successfully.';
					
				} else {
					$this->errors[] = 'Message Failed to Send.';
				}
			}
		} else {
			$this->errors[] = 'Message not sent. Please try again.';
		}
				
	}
	
	
	
	
	
	// The Settings Form Display
	public function eeRSCF_AdminSettingsDisplay() {
		
		$this->log[] = 'Displaying the settings form...';
		
		global $eeRSCF_FileUpload, $eeBackLinkTitle, $eeBackLink, $eeDisclaimer;
		
		// self::eeRSCF_Setup(TRUE);
			
		?>
		
		<div class="eeAdminEntry">
			
			
				
				<?php wp_nonce_field( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce' ); ?>
				
				
				
				
				
				
				<fieldset>
					
					<h2>File Uploads</h2>
					<div class="eeNote">Files are uploaded to the web server, then a link is included within the email delivered.</div>
					
					<span>Allow File Uploads?</span><label for="eeUploadYes" class="eeRadioLabel">Yes</label><input type="radio" name="eeAllowUploads" value="Yes" id="eeUploadYes" <?php if($this->allowUploads == 'Yes') { echo 'checked'; } ?> />
						<label for="eeUploadNo" class="eeRadioLabel">No</label><input type="radio" name="eeAllowUploads" value="" id="eeUploadNo" <?php if($this->allowUploads != 'Yes') { echo 'checked'; } ?> />
							<br class="eeClearFix" />
							<div class="eeNote">Files will be uploaded to: <a href="<?php echo $eeRSCF_FileUpload->uploadUrl; ?>"><?php echo $eeRSCF_FileUpload->uploadUrl; ?></a>
							</div>
					<br class="eeClearFix" />
					
					
					
					<label for="eeMaxFileSize">How Big? (MB):</label><input type="number" min="1" max="<?php echo $eeRSCF_FileUpload->maxUploadLimit; ?>" step="1" name="eeMaxFileSize" value="<?php echo $this->maxFileSize; ?>" class="adminInput" id="eeMaxFileSize" />
						<br class="eeClearFix" />
						<div class="eeNote">Your hosting limits the maximum file upload size to <strong><?php echo $eeRSCF_FileUpload->maxUploadLimit; ?> MB</strong>.</div>
					
					
					<br class="eeClearFix" />
					
					
					
					
					<label for="eeFormats">Allowed Types:</label><textarea name="eeFormats" class="adminInput" id="eeFormats" /><?php echo $this->formats; ?></textarea>
						<br class="eeClearFix" />
						<div class="eeNote">Only use the file types you absolutely need, ie; .jpg, .jpeg, .png, .pdf, .mp4, etc</div>
					
				</fieldset>
				
				
				<fieldset>
					
					<h2>Spam Prevention</h2>
					
					<span>Block Spambots</span><label for="spamBlockYes" class="eeRadioLabel">Yes</label><input type="radio" name="spamBlock" value="Yes" id="spamBlockYes" <?php if($this->spamBlock == 'Yes') { echo 'checked'; } ?> />
						<label for="spamBlockNo" class="eeRadioLabel">No</label><input type="radio" name="spamBlock" value="" id="spamBlockNo" <?php if($this->spamBlock != 'Yes') { echo 'checked'; } ?> />
					<br class="eeClearFix" />
					
					<label for="spamWords">Blocked Words:</label><textarea name="spamWords" class="adminInput" id="spamWords" /><?php echo $this->spamWords; ?></textarea>
						<br class="eeClearFix" />
						<div class="eeNote">You can filter messages as spam if they contain certain words or phrases defined here. Separate with a comma.</div>
					
					
				</fieldset>
				
				
				
				
				<fieldset>
					
					<h2 class="eeFloatLeft">Message Delivery</h2>
					
					<input type="hidden" name="eeRSCFDepartments" id="eeRSCFDepartments" value="<?php echo count($this->deptArray); ?>" />
					
					<?php
						
						foreach($this->deptArray as $num => $string) { 
						
						$num = $num + 1; // Don't wanna start with a zero
						
						$dept = explode('^', $string);
					?>
						
					<fieldset id="eeDepartmentSet<?php echo $num; ?>">
						
						<?php if($num > 1) { echo '<button class="eeRemoveSet" type="button" onclick="eeRemoveSet(' . $num . ')">Remove</button>'; } ?>
					
						<label for="eeAdminDepartment<?php echo $num; ?>">Department:</label><input type="text" name="eeAdminDepartment<?php echo $num; ?>" value="<?php if(@$dept[0]) { echo $dept[0]; }  ?>" class="adminInput" id="eeAdminDepartment<?php echo $num; ?>" size="64" />
						
						<label for="eeAdminTO<?php echo $num; ?>">TO:</label><input type="text" name="eeAdminTO<?php echo $num; ?>" value="<?php if(@$dept[1]) { echo $dept[1]; } elseif($num == 1) { echo get_option('admin_email'); }  ?>" class="adminInput" id="eeAdminTO<?php echo $num; ?>" size="64" />
							
						<label for="eeAdminCC<?php echo $num; ?>">CC:</label><input type="text" name="eeAdminCC<?php echo $num; ?>" value="<?php if(@$dept[2]) { echo $dept[2]; } ?>" class="adminInput" id="eeAdminCC<?php echo $num; ?>" size="64" />
						
						<label for="eeAdminBCC<?php echo $num; ?>">BCC:</label><input type="text" name="eeAdminBCC<?php echo $num; ?>" value="<?php if(@$dept[3]) { echo $dept[3]; } ?>" class="adminInput" id="eeAdminBCC<?php echo $num; ?>" size="64" />	
						<br class="eeClearFix" />
					
					</fieldset>
					
					<?php } ?>
					
					<button type="button" id="eeAddDepartment">Add New Department</button>
					
					<div class="eeNote">You can add more than one address per field by separating them using a comma.</div>
					
				</fieldset>
				
				
				
				
				
				<fieldset>
				
					<h2>Server Address</h2>
					
					<label for="eeAdminFROM">FROM:</label><input type="email" name="eeAdminFROM" value="<?php if($this->from) { echo $this->from; } else { echo get_option('admin_email'); } ?>" class="adminInput" id="eeAdminFROM" size="64" />	
					<br class="eeClearFix" />
					<div class="eeNote">To improve deliverability, the FROM address should be a working email address on this server.<br />
						The senders email address will be the Reply-To address for all messages, not this address.
					</div>
					
				</fieldset>
				
				
				
				<fieldset>
				
					<input type="submit" name="eeSubmitContactForm" id="eeSubmitContactForm" value="SAVE" />
					
					<p>USAGE: Place this bit of shortcode in any Post or Page that you would like the plugin to appear: <strong><em>[eeRSCF]</em></strong></p>
			
					<p><?php echo $eeDisclaimer; ?><br />
					<a href="mailto:mitch@elementengage.com">Bug Reports / Feedback</a></p>
					
					<p><a class="eeBacklink" href="<?php echo $eeBackLink; ?>" target="_blank"><?php echo $eeBackLinkTitle; ?></a></p>
				
				</fieldset>
				
			</form>
		</div><?php 
			
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
		
		if($post AND check_admin_referer( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
			
			// Store our settings in the options table as an inverse bracket set "][" delimited string.
			// We'll turn this into an array on the other side.	
		
			global $wpdb, $eeRSCF_FileUpload;
			
			// $this->log[] = 'The POST...';
			// $this->log[] = implode('][', $post);
			
			// Email Form Fields
			// Storage format: default-name-slug^show^default-name-or-custom-label
			$settings = 'fields=';
			$fields = '';
			
			
			foreach($this->formFields as $defaultField) {
				
				$this->log[] = 'Looping through form fields...';
				
				$fieldSlug = self::makeSlug($defaultField);
				
				// Add the default
				$fields .= $fieldSlug;
				$this->log[] = 'Field: ' . $defaultField;
				
				
				if(@$_POST['show_' . $fieldSlug]) {
					
					// Do we show it and what is the label?
					$fields .= '^SHOW';
					
					if(@$_POST['label_' . $fieldSlug]) {
						$customLabel = filter_var($_POST['label_' . $fieldSlug], FILTER_SANITIZE_STRING);
					}
					
					$fields .= '^' . $customLabel;
					
					if(@$_POST['req_' . $fieldSlug]) {
						$fields .= '^REQ';
					}	
				}
				$fields .= '|'; // Seperator for fields
			}
			
			$fields = substr($fields, 0, -1); // Drop the last pipe.
			
			// $this->log[] = 'Form Fields: ' . $fields;
			
			$settings .= $fields . ']['; // END fields
						
						
			// Only accept Yes as the answer string. Gotta stay positive!
			$settings .= 'allowUploads=';
			if($_POST['eeAllowUploads'] == 'Yes') { $settings .= 'Yes'; } else { $settings .= 'No'; }
			$settings .= '][';
			
			// This must be a number
			$uploadMaxSize = (int) $_POST['eeMaxFileSize'];
			// Can't be more than the system allows.
			if(!$uploadMaxSize OR $uploadMaxSize > $eeRSCF_FileUpload->maxUploadLimit) { $uploadMaxSize = $eeRSCF_FileUpload->maxUploadLimit; }
			$settings .= 'maxFileSize=' . $uploadMaxSize . '][';
			
			// Strip all but what we need for the comma list of file extensions
			$formats = preg_replace("/[^a-z0-9.,]/i", "", $_POST['eeFormats']);
			if(!$formats) { $formats = $this->fileFormats; } // Go with default if none.
			$settings .= 'formats=' . $formats . '][';
			
			
			// Spam Prevention
			$settings .= 'spamBlock=';
			if($_POST['spamBlock'] == 'Yes') { $settings .= 'Yes'; } else { $settings .= 'No'; }
			$settings .= '][';
			
			$settings .= 'spamWords=';
			if($_POST['spamWords']) { $settings .= filter_var($_POST['spamWords'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH); }
			
			$settings .= '][';
				
			// The FROM address
			if(filter_var($_POST['eeAdminFROM'], FILTER_VALIDATE_EMAIL)) {
				$settings .= 'FROM:' . $_POST['eeAdminFROM']; // Assemble addresses for storage
			} else {
				$this->errors[] = 'Bad FROM Address: ' . $_POST['eeAdminFROM'];
			}
			
			$settings .= '][';
			
			// Departments -------
			
			$addresses = '';
			
			$num = filter_var($_POST['eeRSCFDepartments'], FILTER_SANITIZE_NUMBER_INT); // How many departments?
			
			for($i = 1; $i <= $num; $i++) {
				
				// The department
				if($_POST['eeAdminDepartment' . $i]) {
					$department = filter_var($_POST['eeAdminDepartment' . $i], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
				
				} else { // Account for a depertment in the middle removed (if more than one removed, others will be truncated)
					$i++;
					if($_POST['eeAdminDepartment' . $i]) {
						$department = filter_var($_POST['eeAdminDepartment' . $i], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
					}
				}
				
				if($department) {
					
					$department = ucwords($department); // Make all lowercase look gooder
				
					// Email addresses.	
					if($_POST['eeAdminTO' . $i]) {
					
						$addresses .= 'DEPT:' . $department . '^';
						
						$delivery = array('TO', 'CC', 'BCC');
						
						foreach($delivery as $to) { 
							
							// $this->log[] = 'Looping through ' . $to . ' addresses.' ;
							
							$addresses .= $to . ':';
							
							if(strpos($_POST['eeAdmin' . $to . $i], ',')) { // More than one address
							
								$this->log[] = 'Multiple address for ' . $to . ' field.';
								
								$emails = explode(',', $_POST['eeAdmin' . $to . $i]); // Make array
								
								foreach($emails as $email) { // Loop through them
									
									$email = trim($email); // Trim spaces
									
									if(filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate address
										$addresses .= $email . ','; // Assemble addresses for storage
									} else {
										$this->errors[] = 'Bad ' . $to . ' Address: ' . $email;
									}
								}
								$addresses = substr($addresses, 0, -1); // Clip the last comma
								
							} elseif(@$_POST['eeAdmin' . $to . $i]) { // Just one address
								
								$this->log[] = 'Single address for ' . $to . ' field.';
								
								if(filter_var($_POST['eeAdmin' . $to . $i], FILTER_VALIDATE_EMAIL)) {
									$addresses .= $_POST['eeAdmin' . $to . $i]; // Assemble addresses for storage
								} else {
									$this->errors[] = 'Bad ' . $to . ' Address: ' . $_POST['eeAdmin' . $to . $i];
								}
							}
							
							$addresses .= '^'; // Seperate address fields				
						}
						
						$addresses .= ')'; // Seperate departments			
					}
				}
				
			} // End for loop
			
			// Add to the settings string
			$settings .= $addresses;
			
			// Tack on the DB version
			$settings .= '][version=' . $this->eeRSCFDBVersion;
			
			
			// Update the wp_options table setting the options_value to our $settings 
			if(update_option($this->dbFieldName, $settings)) {
				$this->confirmation = 'Settings Have Been Updated!';
				$this->log[] = '>> Settings Updated: ' . $settings;
			} else {
				$this->log[] = @mysqli_error($wpdb);
			}
		}
	}	
			
			
			

} // Ends Class eeRSCF	
	
?>