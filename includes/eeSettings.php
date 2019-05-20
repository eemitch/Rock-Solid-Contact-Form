<?php
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeAwesomeness' )) exit('That is Noncense!'); // Exit if nonce fails

function eeRSCF_Settings() {
	
	global $eeRSCF, $eeRSCFU, $eeRSCF_Log; // Writes a log file in the plugin/logs folder.
	
	$eeRSCF_Log[] = 'eeRSCF_Settings() Loaded';
	
	// Process if POST
	if(@$_POST['eeRSCF_Settings'] AND check_admin_referer( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
		
		$eeRSCF_Log[] = 'Updating Settings...';
		
		$eeRSCF->eeRSCF_AdminSettingsProcess($_POST);
	}
	
	// Variables
	$eeRSCF_PluginSlug = 'rock-solid-contact-form';
	$eeResult = FALSE;
	$eeRSCF_Messages = array();
	$eeRSCF_Errors = array();
	$eeRSCF_PluginPath = plugin_dir_path( __FILE__ );
	
	// Security
	$eeRSCF_Nonce = wp_create_nonce('ee_include_page'); // Check on the included pages
	

	// HTML Buffer
	$eeOutput = '<div class="eeRSCF_Tabs wrap">
		
		<h1>' . __('Rock Solid Contact Form', 'rock-solid-contact-form') . '</h1>'; 
	
	$eeRSCF_Page = 'rock-solid-contact-form'; // This admin page slug
	
	// Reads the new tab's query string value
	if( isset( $_REQUEST[ 'tab' ] ) ) { $active_tab = $_REQUEST[ 'tab' ]; } else { $active_tab = 'settings'; }
	if( isset( $_REQUEST[ 'subtab' ] ) ) { $active_subtab = $_REQUEST[ 'subtab' ]; } else { $active_subtab = 'form_settings'; }
	
	$eeOutput .= '<h2 class="nav-tab-wrapper">';
	
	// Settings
	$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&tab=settings" class="nav-tab '; 
	if($active_tab == 'settings') {$eeOutput .= '  eeActiveTab '; }
	$active_tab == 'settings' ? 'nav-tab-active' : '';    
    $eeOutput .= $active_tab . '">' . __('Settings', 'rock-solid-contact-form') . '</a>';
    
    // Plugin Instructions
    $eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&tab=instructions" class="nav-tab ';  
	if($active_tab == 'instructions') {$eeOutput .= '  eeActiveTab '; }   
    $active_tab == 'support' ? 'nav-tab-active' : ''; 
    $eeOutput .= $active_tab . '">' . __('Instructions', 'rock-solid-contact-form') . '</a>';
    
    // The Help / Email Form Page
    $eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&tab=help" class="nav-tab ';   
	if($active_tab == 'help') {$eeOutput .= '  eeActiveTab '; }  
    $active_tab == 'help' ? 'nav-tab-active' : ''; 
    $eeOutput .= $active_tab . '">' . __('Help', 'rock-solid-contact-form') . '</a>';
    
    // Author
    $eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&tab=author" class="nav-tab ';   
	if($active_tab == 'author') {$eeOutput .= '  eeActiveTab '; }  
    $active_tab == 'author' ? 'nav-tab-active' : ''; 
    $eeOutput .= $active_tab . '">' . __('Author', 'rock-solid-contact-form') . '</a>';
    
    // Donate to Feel Great
    $eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&tab=donate" class="nav-tab ';    
	if($active_tab == 'donate') {$eeOutput .= '  eeActiveTab '; } 
    $active_tab == 'donate' ? 'nav-tab-active' : ''; 
    $eeOutput .= $active_tab . '">' . __('Donate', 'rock-solid-contact-form') . '</a>';
    
    $eeOutput .= '</h2>'; // END Tabs
    
    
    // Which Tab to Display? --------------------
    
	if($active_tab == 'settings') {	
	
		$eeOutput .= '<div class="eeRSCF_Admin">
		
		<h3 class="nav-tab-wrapper">' . __('Settings', 'rock-solid-contact-form') . '<br /><br />';
	
		// Settings
		$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&subtab=form_settings" class="nav-tab ';  
		if($active_subtab == 'form_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_subtab == 'form_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_subtab . '">' . __('Contact Form', 'rock-solid-contact-form') . '</a>';
	    
		$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&subtab=dept_settings" class="nav-tab ';  
		if($active_subtab == 'dept_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_subtab == 'dept_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_subtab . '">' . __('Departments', 'rock-solid-contact-form') . '</a>';
	    
		$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&subtab=file_settings" class="nav-tab ';  
		if($active_subtab == 'file_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_subtab == 'file_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_subtab . '">' . __('File Attachements', 'rock-solid-contact-form') . '</a>';
	    
		$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&subtab=spam_settings" class="nav-tab ';  
		if($active_subtab == 'spam_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_subtab == 'spam_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_subtab . '">' . __('Spam Prevention', 'rock-solid-contact-form') . '</a>';
	    
		$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&subtab=email_settings" class="nav-tab ';  
		if($active_subtab == 'email_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_subtab == 'email_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_subtab . '">' . __('Email Settings', 'rock-solid-contact-form') . '</a>';
	    
	    $eeOutput .= '</h3>'; // END Tabs
	    
	    
	    // Settings Form
	    
	    $eeOutput .= '
	    
	    <form action="' . $_SERVER['PHP_SELF'] . '?page=rock-solid-contact-form' . '" method="post" id="eeRSCF_Settings">
			<input type="hidden" name="eeRSCF_Settings" value="TRUE" />
																				<input type="submit" value="SAVE" />';
				
		$eeOutput .= wp_nonce_field( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce', TRUE, FALSE ) . "\n\n";
		
		
		// Sub Tab Display
		if($active_subtab == 'email_settings') {
	
			$eeOutput .= '
				
			<h2>Form Email Sender</h2>
			
			<input type="hidden" name="eeRSCF_EmailSettings" value="TRUE" />
			<input type="hidden" name="subtab" value="email_settings" />
			
			<fieldset>
			
			<p>The Contact Form sends an email message to you when someone submits the form. 
			Therefore, the Form needs to have an email address to send from. 
			It can be any working address, but to improve deliverability you should use an 
			address that is working on this domain, such as mail@' . $_SERVER['HTTP_HOST'] . '</p>
			
			<p>To improve email appearance, options 
			and to protect your domain from blacklisting, it is recommended to configure an 
			actual email account for the contact form and use SMTP to send messages rather than
			relying on the built-in Wordpress(PHP) mailer.</p>
			
			<p>When you get a message, simply reply and it will go to the email address of the person who sent the message.</p>
			
			
			
			<label for="eeRSCF_email">Form Address</label>
				<input type="email" name="eeRSCF_email" value="';
				
			if($eeRSCF->email) { $eeOutput .= $eeRSCF->email; } else { echo get_option('eeRSCF_email'); }
				
			$eeOutput .= '" class="adminInput" id="eeRSCF_email" size="64" />';
			
			if($eeRSCF->emailMode != 'SMTP') {
			
				$eeOutput .= '
				
				<p class="eeNote">To improve deliverability, the form\'s email address should be a working address on this web server.</p>';
			}
			
			
			
			$eeOutput .= '<label for="eeRSCF_emailMode">SMTP Mailer</label>
			
			<select name="eeRSCF_emailMode" id="eeRSCF_emailMode" class="">
					<option value="PHP"';
					
			if($eeRSCF->emailMode == 'PHP') { $eeOutput .= ' selected="selected"'; }
					
			$eeOutput .= '>OFF - Using Wordpress Mailer</option>
					<option value="SMTP"';
					
			if($eeRSCF->emailMode == 'SMTP') { $eeOutput .= ' selected="selected"'; }
					
			$eeOutput .= '>ON - Using SMTP (Recommended)</option>
				</select>
				
			</fieldset>	
			
			
			<div id="eeRSCF_SMTPEntry"';
				
			if($eeRSCF->emailMode != 'SMTP') { $eeOutput .= ' class="eeHide"'; }
			
			$eeOutput .= '>
			
			<fieldset id="eeRSCF_emailModeSMTP">
			
			<h3>Configure an SMTP Email Account</h3>
			
			
			
			
			<!-- <label for="eeRSCF_emailFormat">Message Format</label>
			
			<select name="eeRSCF_emailFormat" id="eeRSCF_emailFormat" class="">
					<option value="TEXT"';
					
			if($eeRSCF->emailFormat == 'TEXT') { $eeOutput .= ' selected="selected"'; }
					
			$eeOutput .= '>Get Contact Messages in Text Form (Recommended)</option>
					<option value="HTML"';
					
			if($eeRSCF->emailFormat == 'HTML') { $eeOutput .= ' selected="selected"'; }
					
					
			$eeOutput .= '>Get Contact Messages in HTML Format</option>
				</select>
			
				<p class="eeNote">Define the format of the message you receive from the contact form.</p> -->
			
			
			
			
			<label for="eeRSCF_emailName">The Form Name</label>
			<input type="text" name="eeRSCF_emailName" value="';		
			
			if($eeRSCF->emailName) { $eeOutput .= $eeRSCF->emailName; } else { $eeOutput .= 'Rock Solid Contact Form'; }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_emailName" size="64" />
			
				<p class="eeNote">This is the name that will appear for the Form\'s Address</p>
			
			
			
			
			<label for="eeRSCF_emailServer">Mail Server Hostname</label>
			<input type="text" name="eeRSCF_emailServer" value="';
			
			
			
			if($eeRSCF->emailServer) { $eeOutput .= $eeRSCF->emailServer; }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_emailServer" size="64" />
			
				<p class="eeNote">This is the hostname of your local mail server, such as mail.' . $_SERVER['HTTP_HOST'] . '</p>
			
			
			
			
			<label for="eeRSCF_emailUsername">Mail Account Username</label>
			<input type="text" name="eeRSCF_emailUsername" value="';
			
			if( strlen($eeRSCF->emailUsername) > 1 ) { $eeOutput .= $eeRSCF->emailUsername; } else {  $eeOutput .= 'mail@' . basename( get_site_url() ); }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_emailUsername" size="64" />
			
				<p class="eeNote">This is the username for your local mail server, often the complete email address.</p>
				
				
			
			
			<label for="eeRSCF_emailPassword">Mail Account Password</label>
			<input type="text" name="eeRSCF_emailPassword" value="';
			
			if($eeRSCF->emailPassword) { $eeOutput .= $eeRSCF->emailPassword; }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_emailPassword" size="64" />
			
				<p class="eeNote">This is the password for the email account.</p>
			
			
			
			
			<label for="eeRSCF_emailSecure">Mail Security</label>
			
			<select name="eeRSCF_emailSecure" id="eeRSCF_emailSecure" class="">
					<option value="SSL"';
					
			if($eeRSCF->emailSecure == 'SSL') { $eeOutput .= ' selected="selected"'; }
					
					
			$eeOutput .= '>Use SSL</option>
					<option value="TSL"';
					
			if($eeRSCF->emailSecure == 'TSL') { $eeOutput .= ' selected="selected"'; }
					
					
			$eeOutput .= '>Use TSL</option>
					<option value="NO"';
					
			if($eeRSCF->emailSecure == 'NO') { $eeOutput .= ' selected="selected"'; }
					
			$eeOutput .= '>Unencrypted</option>
				</select>
				
				<p class="eeNote">SSL (Secure Sockets Layers) establishes an encrypted link between this web server and your receiving email server when sending messages.</p>
			
			
			
			
			
			<label for="eeRSCF_emailAuth">Message Format</label>
			
			<select name="eeRSCF_emailAuth" id="eeRSCF_emailAuth" class="">
					<option value="YES"';
					
			if($eeRSCF->emailAuth == 'YES') { $eeOutput .= ' selected="selected"'; }
					
			$eeOutput .= '>Require authorization (Recommended)</option>
					<option value="NO"';
					
			if($eeRSCF->emailAuth == 'NO') { $eeOutput .= ' selected="selected"'; }
					
					
			$eeOutput .= '>No Authorization</option>
				</select>
			
				<p class="eeNote">Your account may or may not require authentication.</p>
			
			
			
			
			
			<label for="eeRSCF_emailPort">Port</label>
			<input type="text" name="eeRSCF_emailPort" value="';
			
			if($eeRSCF->emailPort) { $eeOutput .= $eeRSCF->emailPort; } else { $eeOutput .= '25'; }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_emailPort" size="64" />
			
				<p class="eeNote">This is the outgoing mail port. Common ports are 25, 465, 587, 2525 and 2526</p>
				
			
				
			
			<label for="eeRSCF_emailDebug">Debug Mode</label>
			
			<select name="eeRSCF_emailDebug" id="eeRSCF_emailDebug" class="">
					<option value="NO"';
					
			if($eeRSCF->emailDebug == 'NO') { $eeOutput .= ' selected="selected"'; }
					
			$eeOutput .= '>OFF</option>
					<option value="YES"';
					
			if($eeRSCF->emailDebug == 'YES') { $eeOutput .= ' selected="selected"'; }
					
					
			$eeOutput .= '>ON</option>
				</select>
				
				<p class="eeNote">This will write errors to your local Wordpress error log file. Turn this ON only when troubleshooting.</p>
				
			
			
			</fieldset>
			
			</div>';
			
		
		
		
		
			
			
		} elseif($active_subtab == 'dept_settings') {
		
			$eeOutput .= '
					
				<h2>Message Delivery</h2>
				
				<input type="hidden" name="eeRSCF_DepartmentSettings" value="TRUE" />
				<input type="hidden" name="subtab" value="dept_settings" />
			
			<fieldset>
				
				<p>Create departments and send form messages to them.</p>
					
				<input type="hidden" name="eeRSCF_Departments" id="eeRSCF_Departments" value="' . count($eeRSCF->departments) . '" />
				
				';
					
				foreach($eeRSCF->departments as $num => $string) { 
						
					$num = $num + 1; // Don't wanna start with a zero
					
					$dept = explode('^', $string);
						
					$eeOutput .= '<fieldset id="eeDepartmentSet' . $num . '">';
					
					$eeOutput .= '<label for="eeAdminDepartment' . $num . '">Department:</label>
						<input type="text" name="eeAdminDepartment' . $num . '" value="';
						
					if(@$dept[0]) { $eeOutput .= $dept[0]; }
					
					$eeOutput .= '" class="adminInput" id="eeAdminDepartment' . $num . '" size="64" />
						
						<label for="eeAdminTO' . $num . '">TO:</label>
						<input type="text" name="eeAdminTO' . $num . '" value="';
						
					if(@$dept[1]) { $eeOutput .= $dept[1]; } elseif($num == 1) { $eeOutput .= get_option('admin_email'); }
					
					$eeOutput .= '" class="adminInput" id="eeAdminTO' . $num . '" size="64" />
							
						<label for="eeAdminCC' . $num . '">CC:</label>
						<input type="text" name="eeAdminCC' . $num . '" value="';
						
					if(@$dept[2]) { $eeOutput .= $dept[2]; }
					
					$eeOutput .= '" class="adminInput" id="eeAdminCC' . $num . '" size="64" />
						
						<label for="eeAdminBCC' . $num . '">BCC:</label>
						<input type="text" name="eeAdminBCC' . $num . '" value="';
						
					if(@$dept[3]) { $eeOutput .= $dept[3]; }
					
					$eeOutput .= '" class="adminInput" id="eeAdminBCC' . $num . '" size="64" />	
						
						<br class="eeClearFix" />';
					
					if($num > 1) { $eeOutput .= '<button class="eeRemoveSet" type="button" onclick="eeRemoveSet(' . $num . ')">Remove</button>'; }
					
					$eeOutput .= '</fieldset>';
						
					}
					
					$eeOutput .= '<button type="button" id="eeAddDepartment">Add New Department</button>
					
					<p>You can add more than one address per field by separating them using a comma.</p>
					
				</fieldset>';
			
			
			
			
			
			
			
			
		} elseif($active_subtab == 'file_settings') {
		
			$eeOutput .= '
					
				<h2>File Uploads</h2>
				
				<input type="hidden" name="eeRSCF_FileSettings" value="TRUE" />
				<input type="hidden" name="subtab" value="file_settings" />
			
			<fieldset>
				<p>Files are uploaded to the web server, then a link to the file is included within the email delivered.</p>
				
				<span>Allow File Uploads?</span><label for="eeUploadYes" class="eeRadioLabel">Yes</label><input type="radio" name="eeAllowUploads" value="YES" id="eeUploadYes"';
				
				if($eeRSCF->fileAllowUploads == 'YES') { $eeOutput .= 'checked'; }
				
				$eeOutput .= ' />
					<label for="eeUploadNo" class="eeRadioLabel">No</label>
					<input type="radio" name="eeAllowUploads" value="NO" id="eeUploadNo"';
					
				if($eeRSCF->fileAllowUploads != 'YES') { $eeOutput .= 'checked'; }
				
				$eeOutput .= ' />
						<br class="eeClearFix" />
						<p class="eeNote">Files will be uploaded to: <a href="' . $eeRSCFU->uploadUrl . '">' . $eeRSCFU->uploadUrl . '</a></p>
				
				<br class="eeClearFix" />
				
				<label for="eeMaxFileSize">How Big? (MB):</label>
				<input type="number" min="1" max="' . $eeRSCFU->maxUploadLimit . '" step="1" name="eeMaxFileSize" value="' . $eeRSCF->fileMaxSize . '" class="adminInput" id="eeMaxFileSize" />
				
				<br class="eeClearFix" />
					<p class="eeNote">Your hosting limits the maximum file upload size to <strong>' . $eeRSCFU->maxUploadLimit . ' MB</strong>.</p>
				
				
				<br class="eeClearFix" />
				
				<label for="eeFormats">Allowed Types:</label>
				<textarea name="eeFormats" class="adminInput" id="eeFormats" />' . $eeRSCF->fileFormats . '</textarea>
					<br class="eeClearFix" />
					<p class="eeNote">Only use the file types you absolutely need, ie; .jpg, .jpeg, .png, .pdf, .mp4, etc</p>
				
			</fieldset>';
			
			
			
			
			
			
		} elseif($active_subtab == 'spam_settings') {
		
			$eeOutput .= '
					
				<h2>Spam Prevention</h2>
				
				<input type="hidden" name="eeRSCF_SpamSettings" value="TRUE" />
				<input type="hidden" name="subtab" value="spam_settings" />
			
			<fieldset>
				
				<p>Block those nasty spambots. You can also filter messages as spam if they contain certain words or phrases defined here. Separate phrases with a comma.</p>
				
				<span>Block Spambots</span><label for="spamBlockYes" class="eeRadioLabel">Yes</label>
				<input type="radio" name="spamBlock" value="YES" id="spamBlockYes"';
				
				if($eeRSCF->spamBlock == 'YES') { $eeOutput .= 'checked'; }
				
				$eeOutput .= ' />
					<label for="spamBlockNo" class="eeRadioLabel">No</label>
					<input type="radio" name="spamBlock" value="NO" id="spamBlockNo"';
					
				if($eeRSCF->spamBlock != 'YES') { $eeOutput .= 'checked'; }
				
				$eeOutput .= ' />
				
				<br class="eeClearFix" />
				
				<label for="spamWords">Blocked Words:</label>
				<textarea rows="4" cols="64" name="spamWords" id="spamWords" />' . $eeRSCF->spamWords . '</textarea>
					
				<br class="eeClearFix" />
				

			</fieldset>';
			
			
			
			
		} else { // Form Settings
			
			$eeOutput .= '
				
			<h2>Form Fields</h2>
			
			<input type="hidden" name="eeRSCF_formSettings" value="TRUE" />
				<input type="hidden" name="subtab" value="form_settings" />
			
			<fieldset>
			
			<p>Select the contact form fields to display. Also select if the field should be required. Change the text for each label as required. A text input box for the message will be provided automatically.</p>
			
			<table class="eeRSCF_formSettings">
				<tr>
					<th>Show</th>
					<th>Require</th>
					<th>Label</th>
				</tr>';
			
			// Get our fields
			
			// first-name^SHOW^First Name^REQ|last-name^SHOW^Last Name^REQ|business^SHOW^Business^REQ|address^SHOW^Address^REQ|address-2^SHOW^Address 2^REQ|city^SHOW^City^REQ|state^SHOW^State^REQ|zip^SHOW^Zip^REQ|phone^SHOW^Phone^REQ|website^SHOW^Website^REQ|other^SHOW^Other^REQ|subject^SHOW^Subject^REQ
			
			$eeFields = $eeRSCF->formFields;
			$eeFieldsDefaults = explode('|', $eeRSCF->default_formFields);
			
			if(!is_array($eeFields)) { $eeFields = $eeFieldsDefaults; } // Default to the defaults
			
			$i = 0; // Synch loop with defaults
			
			// Loop-de-doop
			foreach($eeFields as $field) {
				
				$field = explode('^', $field); // slug-name^SHOW^Slug Name^REQ
					
				$eeOutput .= '<tr>
						
						<td><input type="checkbox" name="show_' . $field[0] . '"'; // SHOW
						
				if(@$field[1] == 'SHOW') { $eeOutput .= ' checked="checked"'; }
					
				$eeOutput .= ' /></td>
						
						<td><input type="checkbox" name="req_' . $field[0] . '" '; // REQUIRED
						
				if(@$field[3] == 'REQ') { $eeOutput .= ' checked="checked"'; }
				
				$eeThisFieldDefault = explode('^', $eeFieldsDefaults[$i]);
				
				$eeOutput .= ' /></td>
						
						<!-- <td>' . $eeThisFieldDefault[2] . '</td> -->
						
						<td><input type="text" name="label_' . $field[0] . '" value="';
						
				if(@$field[1] AND @$field[2]) { $eeOutput .= $field[2]; } else { $eeOutput .= $eeRSCF->eeRSCF_UnSlug($field[0]); }
				
				$eeOutput .= '" size="24" maxsize="64" /></td>
					
					</tr>';
					
				$i++;
			
			}
				
			$eeOutput .= '</table>
			
			<br class="eeClearFix" />
			
		</fieldset>';
			
		}
		
		// Complete the form
		$eeOutput .= '
		
			<input type="submit" value="SAVE" />
		
		</form>
		
		</div>'; // END SubTabs
		
		
		
		
	} elseif($active_tab == 'instructions') { // Support Tab Display...
		
		$eeOutput .= '<h2>' . __('Instructions', 'rock-solid-contact-form') . '</h2>
		
		<p>USAGE: Place this bit of shortcode in any Post or Page that you would like the plugin to appear: <strong><em>[eeRSCF]</em></strong></p>';
			
		// Get the instructions page
		include($eeRSCF_PluginPath . '../support/ee-plugin-instructions.php');
	
	} elseif($active_tab == 'help') { // Support Tab Display...
		
		$eeSF_Plugin = eeRSCF_PluginName;
			
		// Get the support page
		include($eeRSCF_PluginPath . '../support/ee-plugin-support.php');
	
	} elseif($active_tab == 'author') { // About
					
		// Get the support page
		include($eeRSCF_PluginPath . '../support/ee-plugin-author.php');
		
	} else { // Please Donate - DEFAULT TAB
			
		// Get the support page
		include($eeRSCF_PluginPath . '../support/ee-donations.php');
	}
		
	$eeOutput .= '
	
	
	
	
	
	
	<div id="eeAdminFooter">
		
		<p><a href="' . eeRSCF_WebsiteLink . '">' . 
			eeRSCF_PluginName . ' &rarr; ' . __('Version', 'rock-solid-contact-form') . ' ' . eeRSCF_version . '</a></p>	
	</div>
	
	
	
	
	
	</div>'; // END .wrap
	
	
	
	
	
	// Closing function operations
	
	if(eeRSCF_DevMode) {
		
		$eeRSCF_Log[][] = $eeRSCF->log;
		
		if($eeRSCF_Messages) { $eeRSCF_Log[] = $eeRSCF_Messages; }
		if($eeRSCF_Errors) { $eeRSCF_Log[] = $eeRSCF_Errors; }
		
		$eeOutput .= '<pre>' . print_r($eeRSCF_Log, TRUE) . '</pre>';
		
		// eeRSCF_WriteLogFile($eeRSCF_Log);
		
	}
    
	
    // Dump the HTML buffer
    echo $eeOutput;
    
    
}

	
	
?>