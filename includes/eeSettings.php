<?php
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'eeAwesomeness' )) exit('That is Noncense!'); // Exit if nonce fails

function eeRSCF_Settings() {
	
	global $eeRSCF, $eeRSCF_Log; // Writes a log file in the plugin/logs folder.
	
	$eeRSCF_Log[] = 'eeRSCF_Settings() Loaded';
	
	// Process if POST
	if(@$_POST['eeRSCF_Settings'] AND check_admin_referer( 'rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
		$eeRSCF_Log[] = 'Processing Post...';
		$eeRSCF->eeRSCF_AdminSettingsProcess($_POST);	// IF POST and Nonce
	}
	
	
	
	
	// Configuration
	// 
	
	// Variables
	$eeRSCF_PluginSlug = 'rock-solid-contact-form';
	$eeResult = FALSE;
	$eeRSCF_Messages = array();
	$eeRSCF_Errors = array();
	$eeRSCF_PluginPath = plugin_dir_path( __FILE__ );
	
	// Security
	$eeRSCF_Nonce = wp_create_nonce('ee_include_page'); // Check on the included page
	

	// HTML Buffer
	$eeOutput = '<div class="eeRSCF_Tabs wrap">
		
		<h1>' . __('Rock Solid Contact Form', 'rock-solid-contact-form') . '</h1>'; 
	
	$eeRSCF_Page = 'rock-solid-contact-form'; // This admin page slug
	
	// Reads the new tab's query string value
	if( isset( $_GET[ 'tab' ] ) ) { $active_tab = $_GET[ 'tab' ]; } else { $active_tab = 'settings'; }
	if( isset( $_GET[ 'subtab' ] ) ) { $active_subtab = $_GET[ 'subtab' ]; } else { $active_subtab = 'form_settings'; }
	
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
	
		$eeOutput .= '<div class="wrap">
		
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
				';
				
		$eeOutput .= wp_nonce_field( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce', TRUE, FALSE ) . '
		
		'; // Output spacing ;-)
		
		
		// Sub Tab Display
		if($active_subtab == 'email_settings') {
	
			
			
		} elseif($active_subtab == 'dept_settings') {
		
			
		} elseif($active_subtab == 'spam_settings') {
		
			
		} else { // Form Settings
			
			$eeOutput .= '<fieldset>
				
			<h2>Form Fields</h2>
			
			<table>
				<tr>
					<th>Show</th>
					<th>Require</th>
					<th>Default Label</th>
					<th>Custom Label</th>
				</tr>';
			
			// Get our fields
			
			// first-name^SHOW^First Name^REQ|last-name^SHOW^Last Name^REQ|business^SHOW^Business^REQ|address^SHOW^Address^REQ|address-2^SHOW^Address 2^REQ|city^SHOW^City^REQ|state^SHOW^State^REQ|zip^SHOW^Zip^REQ|phone^SHOW^Phone^REQ|website^SHOW^Website^REQ|other^SHOW^Other^REQ|subject^SHOW^Subject^REQ
			
			$eeFields = explode('|', $eeRSCF->fields);
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
						
						<td>' . $eeThisFieldDefault[2] . '</td>
						
						<td><input type="text" name="label_' . $field[0] . '" value="';
						
				if(@$field[1] AND @$field[2]) { $eeOutput .= $field[2]; } else { $eeOutput .= $eeRSCF->eeRSCF_UnSlug($field[0]); }
				
				$eeOutput .= '" size="24" maxsize="64" /></td>
					
					</tr>';
					
				$i++;
			
			}
				
			$eeOutput .= '</table>
			
			<p class="eeNote">A text box for the message will be provided by default.</p>
				<br class="eeClearFix" />
			
		</fieldset>';
			
		}
		
		// Complete the form
		$eeOutput .= '
		
			<input type="submit" value="SAVE" />
		
		</form>'; // END SubTabs
		
		
		
		
	} elseif($active_tab == 'instructions') { // Support Tab Display...
		
		$eeOutput .= '<h2>' . __('Instructions', 'rock-solid-contact-form') . '</h2>';
			
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
		
	$eeOutput .= '<div id="eeAdminFooter">
		
				<fieldset>
				<!-- <p>' . __('Notification or something', 'rock-solid-contact-form') . '</p> -->
				
				<p><a href="' . eeRSCF_WebsiteLink . '">' . eeRSCF_PluginName . ' &rarr; ' . __('Version', 'rock-solid-contact-form') . ' ' . eeRSCF_Version;
	
				$eeOutput .= '</a></p></fieldset>
		
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