<?php
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeRSCF_Nonce' )) exit('That is Noncense!'); // Exit if nonce fails

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
	
	$eeOutput .= '<h2 class="nav-tab-wrapper">
	</h2>'; // END Tabs
    
    // Which Tab to Display? --------------------
    
	if($active_tab == 'settings') {	
	
		$eeOutput .= '<div class="eeRSCF_Admin">
		<h2 class="nav-tab-wrapper">' . __('Settings', 'rock-solid-contact-form') . '<br /><br />';
	
		// Form Settings
		$eeOutput .= '
		<a href="?page=' . $eeRSCF_Page . '&tab=form_settings" class="nav-tab ';  
		if($active_tab == 'form_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_tab == 'form_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_tab . '">' . __('Contact Forms', 'rock-solid-contact-form') . '</a>';
	    
		// File Uploads
		$eeOutput .= '
		<a href="?page=' . $eeRSCF_Page . '&tab=file_settings" class="nav-tab ';  
		if($active_tab == 'file_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_tab == 'file_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_tab . '">' . __('Attachments', 'rock-solid-contact-form') . '</a>';
	    
		// Spam Blocking
		$eeOutput .= '
		<a href="?page=' . $eeRSCF_Page . '&tab=spam_settings" class="nav-tab ';  
		if($active_tab == 'spam_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_tab == 'spam_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_tab . '">' . __('Spam Prevention', 'rock-solid-contact-form') . '</a>';
	    
		// Mail Setup
		$eeOutput .= '
		<a href="?page=' . $eeRSCF_Page . '&tab=email_settings" class="nav-tab ';  
		if($active_tab == 'email_settings') {$eeOutput .= '  eeActiveTab ';}    
	    $active_tab == 'email_settings' ? 'nav-tab-active' : '';    
	    $eeOutput .= $active_tab . '">' . __('Email Settings', 'rock-solid-contact-form') . '</a>';
		
		// Author
		$eeOutput .= '
		<a href="?page=' . $eeRSCF_Page . '&tab=author" class="nav-tab ';   
		if($active_tab == 'author') {$eeOutput .= '  eeActiveTab '; }  
		$active_tab == 'author' ? 'nav-tab-active' : ''; 
		$eeOutput .= $active_tab . '">' . __('Author', 'rock-solid-contact-form') . '</a>';
	    
	    $eeOutput .= '</h2>'; // END Tabs
		
		
	    
	    // Forms Navigation 
		// $eeRSCF->eeRSCF_GetForms(); // Fill $eeRSCF->eeFormsArray
		
		
		// Set Current Form
		// if(isset($_POST['eeRSCF_ID'])) {
		// 	$eeRSCF_ID = filter_var($_POST['eeRSCF_ID'], FILTER_VALIDATE_INT);
		// } else {
		// 	$eeRSCF_ID = 1;
		// }
		
		$eeRSCF_ID = 1;
		
		
		// Get Chosen Form
		if(isset($_POST['eeRSCF_forms'])) {
			if($_POST['eeRSCF_forms']) {
				$eeRSCF_ID = filter_var($_POST['eeRSCF_forms'], FILTER_VALIDATE_INT);
				$eeRSCF->eeFormArray = get_option('eeRSCF_' . $eeRSCF_ID);
			}	
		}
		
		
		// Add a new form
		// if(isset($_GET['eeRSCF_createForm'])) {
		// 	if($_GET['eeRSCF_createForm'] == 1) {
		// 		$count = count($eeRSCF->eeFormsArray);
		// 		$eeRSCF_ID = $count+1;
		// 		add_option('eeRSCF_' . $eeRSCF_ID, $eeRSCF->eeRSCF_0);
		// 	}
		// }
		
		// Get this form's settings
		$eeRSCF->eeFormArray = get_option('eeRSCF_' . $eeRSCF_ID);
		
		
		// $eeOutput .= '
		// <div id="eeRSCF_FormsNav" class="eeClearing">';
		
		// Choose your form - TO DO
		// if(count($eeRSCF->eeFormsArray) > 1) {
		//   $eeOutput .= '<form action="' . $_SERVER['PHP_SELF'] . '?page=rock-solid-contact-form' . '" method="POST">
		//   <select name="eeRSCF_forms" id="eeRSCF_forms">';
		//   
		//   foreach($eeRSCF->eeFormsArray as $eeID => $eeValue) {
		// 	$selected = ($eeRSCF_ID == $eeID) ? 'selected' : ''; // add selected attribute if $eeRSCF_ID == $eeID
		// 	$eeOutput .= '<option value="' . $eeID . '" ' . $selected . '>' . $eeValue . '</option>';
		//   }
		//   
		//   $eeOutput .= '</select>
		//   <input class="button" type="submit" name="eeRSCF_chooseForm" value="Go" id="eeRSCF_chooseForm" />
		//   </form>';
		// }
		// 
		// $eeOutput .= '
		// <button class="button" type="button" id="eeRSCF_createForm">New Form</button>
		// </div>';
    }
    
    
    // Settings Form
	$eeRSCF->eeFormArray = get_option('eeRSCF_' . $eeRSCF_ID);
    
    $eeOutput .= '
	
	<h2> ' . $eeRSCF->eeFormArray['name'] . ' Settings</h2>
    
    <form action="' . $_SERVER['PHP_SELF'] . '?page=rock-solid-contact-form' . '" method="POST" id="eeRSCF_Settings">
		<input type="hidden" name="eeRSCF_Settings" value="TRUE" />
	';
			
	$eeOutput .= wp_nonce_field( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce', TRUE, FALSE ) . "\n\n";
	
	
	// Tab Display
	if($active_tab == 'email_settings') {

		$eeOutput .= '
			
		<h2>Form Email Sender</h2>
		
		<input type="hidden" name="eeRSCF_EmailSettings" value="TRUE" />
		<input type="hidden" name="subtab" value="email_settings" />
		
		<fieldset>
		
		<p>The Contact Form sends an email message to you when someone submits the form. 
		Therefore, a rock solid contact form needs to have an email address to send from.</p>
		
		
		<label for="eeRSCF_email">The Form\'s Email</label>
			<input type="email" name="eeRSCF_email" value="';
			
		if($eeRSCF->email) { $eeOutput .= $eeRSCF->email; } else { echo get_option('eeRSCF_email'); }
			
		$eeOutput .= '" class="adminInput" id="eeRSCF_email" size="64" />';
		
		if($eeRSCF->emailMode != 'SMTP') {
		
			$eeOutput .= '
			
			<p class="eeNote">To improve deliverability, the form\'s email address should be a working address on this web server, such as <strong><em>mail@' . $_SERVER['HTTP_HOST'] . '</em></strong>.</p>';
		}
		
		
		
		$eeOutput .= '
		<br class="eeClearFix" />
		
		<p><strong>NOTE: </strong> When you get a message, simply reply. It will go to the email address of the person who submitted the contact form.</p>
		
		
		<h3>SMTP <small>(Optional)</small></h3>
		
		<p>To improve email appearance, options 
		and to protect your domain from blacklisting, it is recommended to configure an 
		actual email account for the contact form and use SMTP to send messages rather than
		relying on the built-in Wordpress(PHP) mailer.</p>
		
		<label for="eeRSCF_emailMode">SMTP Mailer</label>
		
		<select name="eeRSCF_emailMode" id="eeRSCF_emailMode" class="">
				<option value="PHP"';
				
		if($eeRSCF->emailMode == 'PHP') { $eeOutput .= ' selected="selected"'; }
				
		$eeOutput .= '>OFF - Using Wordpress Mailer</option>
				<option value="SMTP"';
				
		if($eeRSCF->emailMode == 'SMTP') { $eeOutput .= ' selected="selected"'; }
				
		$eeOutput .= '>ON - Using SMTP (Recommended)</option>
			</select>
			
			<p><strong>NOTE: </strong> You may need to contact your host to get the settings required.</p>
			
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
		
			<p class="eeNote">This is the name for the form that will appear in your email. It is associated with your email address.</p>
		
		
		
		
		<label for="eeRSCF_emailServer">Mail Server Hostname</label>
		<input type="text" name="eeRSCF_emailServer" value="';
		
		
		
		if($eeRSCF->emailServer) { $eeOutput .= $eeRSCF->emailServer; }
		
		$eeOutput .= '" class="adminInput" id="eeRSCF_emailServer" size="64" />
		
			<p class="eeNote">This is the hostname of your local mail server, such as mail.' . $_SERVER['HTTP_HOST'] . '</p>
		
		
		
		
		<label for="eeRSCF_emailUsername">Mail Account Username</label>
		<input type="text" name="eeRSCF_emailUsername" value="';
		
		if($eeRSCF->emailUsername) {
			if( strlen($eeRSCF->emailUsername) > 1 ) { $eeOutput .= $eeRSCF->emailUsername; } else {  $eeOutput .= 'mail@' . basename( get_site_url() ); }
		}
		
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
		
		
		
		
		
		<label for="eeRSCF_emailAuth">Authentication</label>
		
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
		
	
	
	
	
		
		
	} elseif($active_tab == 'file_settings') {
	
		$eeOutput .= '
				
			<h2>File Attachments</h2>
			
			<input type="hidden" name="eeRSCF_FileSettings" value="TRUE" />
			<input type="hidden" name="subtab" value="file_settings" />
		
		<fieldset>
			<p>Files are uploaded to the web server rather than attached directly to messages. 
			A link to the file is then included within the message.</p>
			
			<p>Files will be uploaded to: <em>' . $eeRSCFU->uploadUrl . '</em></p>
			
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
				
			<h2>Spam Blocking</h2>
			
			<input type="hidden" name="eeRSCF_SpamSettings" value="TRUE" />
			<input type="hidden" name="subtab" value="spam_settings" />
		
		<fieldset id="eeRSCF_spamSettings">
			
			<span>Block Spam</span><label for="spamBlockYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamBlock" value="YES" id="spamBlockYes"';
			
			if($eeRSCF->spamBlock == 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
				<label for="spamBlockNo" class="eeRadioLabel">No</label>
				<input type="radio" name="spamBlock" value="NO" id="spamBlockNo"';
				
			if($eeRSCF->spamBlock != 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
			
				<p class="eeNote">Leave this OFF unless your contact form spam is unacceptable.</p>
			
			
			
			
			
			
			<span>Block Spambots</span><label for="spamBlockBotsYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamBlockBots" value="YES" id="spamBlockBotsYes"';
			
			if($eeRSCF->spamBlockBots == 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
				<label for="spamBlockBotsNo" class="eeRadioLabel">No</label>
				<input type="radio" name="spamBlockBots" value="NO" id="spamBlockBotsNo"';
				
			if($eeRSCF->spamBlockBots != 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
			
				<p class="eeNote">Spambots are not people. 
				They are automated scripts that search the Internet for contact forms to exploit. 
				Many websites use CAPTCHA to stop spambots, but Rock Solid Contact Form uses smarter 
				methods to spot spambots instead.</p>
			
			
			
			<label for="eeRSCF_spamHoneypot">Honeypot</label>
			<input type="text" name="spamHoneypot" value="';
			
			if($eeRSCF->spamHoneypot) { $eeOutput .= $eeRSCF->spamHoneypot; } else { $eeOutput .= $eeRSCF->default_spamHoneypot; }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_spamHoneypot" size="64" />
			
				<p class="eeNote">A honeypot is a used to trick spambots. The honeypot is hidden to people, but spambots see this field in the page code and will complete it. 
					Spambots are smart, so they might guess your honeypot and not complete it. Change it if you are getting too many spambot messages.</p>
				
			
			
			
			
			<span>English Only</span><label for="spamEnglishOnlyYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamEnglishOnly" value="YES" id="spamEnglishOnlyYes"';
			
			if($eeRSCF->spamEnglishOnly == 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
				<label for="spamEnglishOnlyNo" class="eeRadioLabel">No</label>
				<input type="radio" name="spamEnglishOnly" value="NO" id="spamEnglishOnlyNo"';
				
			if($eeRSCF->spamEnglishOnly != 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
			
				<p class="eeNote">Block messages with strange and indecipherable characters found within.</p>
			
			
			
			
			
			<span>Block Fishy</span><label for="spamBlockFishyYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamBlockFishy" value="YES" id="spamBlockFishyYes"';
			
			if($eeRSCF->spamBlockFishy == 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
				<label for="spamBlockFishyNo" class="eeRadioLabel">No</label>
				<input type="radio" name="spamBlockFishy" value="NO" id="spamBlockFishyNo"';
				
			if($eeRSCF->spamBlockFishy != 'YES') { $eeOutput .= 'checked'; }
			
			
			
			$eeOutput .= ' />
			
				<p class="eeNote">Block messages with duplicated fields and other nonsense.</p>';
			
			// !!!!
			$eeOutput .= '<h3>Custom Word Blocking</h3>';	
			
			$eeOutput .= '
			<span>Block Common Words</span><label for="spamBlockCommonWordsYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamBlockCommonWords" value="YES" id="spamBlockCommonWordsYes"';
			if($eeRSCF->spamBlockCommonWords == 'YES') { $eeOutput .= 'checked'; }
			$eeOutput .= ' />
			<label for="spamBlockCommonWordsNo" class="eeRadioLabel">No</label>
			<input type="radio" name="spamBlockCommonWords" value="NO" id="spamBlockCommonWordsNo"';
			if($eeRSCF->spamBlockCommonWords != 'YES') { $eeOutput .= 'checked'; }
			$eeOutput .= ' />
			<p class="eeNote">Use the built-in list of words and phrases commonly found in contact form spam messages.</p>';
			
			$eeOutput .= '<span>Block Additional Words</span>
			<label for="spamBlockWordsYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamBlockWords" value="YES" id="spamBlockWordsYes"';
			if($eeRSCF->spamBlockWords == 'YES') { $eeOutput .= 'checked'; }
			$eeOutput .= ' />
			<label for="spamBlockWordsNo" class="eeRadioLabel">No</label>
			<input type="radio" name="spamBlockWords" value="NO" id="spamBlockWordsNo"';
			if($eeRSCF->spamBlockWords != 'YES') { $eeOutput .= 'checked'; }
			$eeOutput .= ' />
			<p class="eeNote">Block messages containing any words or phrases you define below. Separate phrases with a comma.</p>
			<label for="eeRSCF_spamBlockedWords">Added Words</label>
			<textarea name="spamBlockedWords" id="eeRSCF_spamBlockedWords" >';
			if($eeRSCF->spamBlockedWords) { $eeOutput .= $eeRSCF->spamBlockedWords; }
			$eeOutput .= '</textarea>
			<p class="eeNote">Add your words and phrases here to improve spam filtering.</p>';
			

			$eeOutput .= '<h3>Notifications</h3>
			
			<span>Send Spam Notice</span><label for="spamSendAttackNoticeYes" class="eeRadioLabel">Yes</label>
			<input type="radio" name="spamSendAttackNotice" value="YES" id="spamSendAttackNoticeYes"';
			
			if($eeRSCF->spamSendAttackNotice == 'YES') { $eeOutput .= 'checked'; }
			
			$eeOutput .= ' />
				<label for="spamSendAttackNoticeNo" class="eeRadioLabel">No</label>
				<input type="radio" name="spamSendAttackNotice" value="NO" id="spamSendAttackNoticeNo"';
				
			if($eeRSCF->spamSendAttackNotice != 'YES') { $eeOutput .= 'checked'; }
			
			
			
			$eeOutput .= ' />
			
				<p class="eeNote">Send an email notice showing details about the spam message. This is helpful for fine tuning your spam configuration.</p>
			
			
			<label for="eeRSCF_spamNoticeEmail">Notice Email</label>
			<input type="text" name="spamNoticeEmail" value="';
			
			if($eeRSCF->spamNoticeEmail) { $eeOutput .= $eeRSCF->spamNoticeEmail; }
			
			$eeOutput .= '" class="adminInput" id="eeRSCF_spamNoticeEmail" size="64" />
			
			<p class="eeNote">The email you wish the notices to be sent to.</p>
				
			<br class="eeClearFix" />
			

		</fieldset>';
		
		
		
		
	} else { // Form Settings
		
		
		$eeOutput .= '
		
		<input type="hidden" name="eeRSCF_formSettings" value="TRUE" />
		<input type="hidden" name="eeRSCF_ID" value="' . $eeRSCF_ID . '" />
			<input type="hidden" name="subtab" value="form_settings" />
		
		<fieldset id="eeRSCF_formSettings">
		
		<!-- <p>Select the contact form fields to display. Also select if the field should be required. Change the text for each label as required. A text input box for the message will be provided automatically.</p> -->
		
		';
				
		$eeOutput .= '<label for="eeRSCF_formName">Name</label>
			<input type="text" name="eeRSCF_formName" value="';
			
		if($eeRSCF->eeFormArray['name']) { $eeOutput .= $eeRSCF->eeFormArray['name']; }
		
		$eeOutput .= '" class="adminInput" id="eeRSCF_formName" size="64" />
		
		<label>Shortcode</label><input id="eeRSCF_shortCode" type="text" name="eeRSCF_shortCode" value="[rock-solid-contact id=' . $eeRSCF_ID . ']" />
		
		<br class="eeClearFix" />
		
		<p class="button eeRSCF_copyToClipboard">Copy Shortcode</p>
		
		<fieldset id="eeRSCF_delivery">
		
			<h3>Delivery</h3>
					
					<label for="eeRSCF_formTO">TO</label>
					<input type="text" name="eeRSCF_formTO" value="';
					
				if(!empty($eeRSCF->eeFormArray['TO'])) { $eeOutput .= $eeRSCF->eeFormArray['TO']; } 
						else { $eeOutput .= get_option('admin_email'); }
				
				$eeOutput .= '" class="adminInput" id="eeRSCF_formTO" size="64" />
						
					<label for="eeRSCF_formCC">CC</label>
					<input type="text" name="eeRSCF_formCC" value="';
					
				if(!empty($eeRSCF->eeFormArray['CC'])) { $eeOutput .= $eeRSCF->eeFormArray['CC']; }
				
				$eeOutput .= '" class="adminInput" id="eeRSCF_formCC" size="64" />
					
					<label for="eeRSCF_formBCC">BCC</label>
					<input type="text" name="eeRSCF_formBCC" value="';
					
				if(!empty($eeRSCF->eeFormArray['BCC'])) { $eeOutput .= $eeRSCF->eeFormArray['BCC']; }
				
				$eeOutput .= '" class="adminInput" id="eeRSCF_formBCC" size="64" />	
				
					<p class="eeNote">You can add more than one address per field by separating them using a comma.</p>
					
					<br class="eeClearFix" />';
				
				$eeOutput .= '</fieldset>
				
		<fieldset>
		
			<h3>Form Fields</h3>
		
		
		<table class="eeRSCF_formFields">
			<tr>
				<th>Show</th>
				<th>Require</th>
				<th>Label</th>
			</tr>';
		
		
		$eeFields = $eeRSCF->eeFormArray['fields'];
		
		// echo '<pre>'; print_r($eeFields); echo '</pre>'; exit;
		
		// Loop-de-doop
		foreach($eeFields as $eeFieldName => $fieldArray) {  // Field name and settings array
			
			$eeOutput .= '<tr>';
			
			foreach($fieldArray as $field => $value){ // Checkboxes
				
				if($field == 'label') { // Text Input
					
					$eeOutput .= '
					
					<td><input type="text" name="eeRSCF_fields[' . $eeFieldName . '][' . $field . ']" value="';
					
					if($value) { $eeOutput .= stripslashes($value); } else { $eeOutput .= $eeRSCF->eeRSCF_UnSlug($field); }
			
					$eeOutput .= '" size="32" /></td>';
				
				} else {
					
					$eeOutput .= '
					
					<td><input type="checkbox" name="eeRSCF_fields[' . $eeFieldName . '][' . $field . ']"';	
			
					if($value == 'YES') { $eeOutput .= ' checked="checked"'; }
				
					$eeOutput .= ' /></td>';
				}
			}
			
			$eeOutput .= '</tr>';
			
		}
		
		$eeOutput .= '</table>
		
		</fieldset>
		
		<fieldset>
		
			<h3>Confirmation Page</h3>
		
			<p>This is the page that will load after the form has been submitted. If no page is defined, the contact form page will be loaded again.</p>
			
			<input class="eeFullWidth" type="url" name="eeRSCF_confirm" value="';
					
				if(@$eeRSCF->eeFormArray['confirm'] != '/') { $eeOutput .= $eeRSCF->eeFormArray['confirm']; }
			
					$eeOutput .= '" size="128" />
		
		
		</fieldset>
		
		<br class="eeClearFix" />
		
	</fieldset>
		
	<a id="eeRSCF_deleteForm" href="admin.php?page=rock-solid-contact-form&subtab=form_settings&eeRSCF_deleteForm=' . $eeRSCF_ID . '">Delete This Form</a>
	
	';
		
	}
	
	// Complete the form
	$eeOutput .= '
	
		<input type="submit" value="SAVE" />
	
	</form>
	
	</div>'; // END SubTabs
		
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