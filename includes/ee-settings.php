<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

function eeRSCF_Settings() {

	global $eeRSCF, $eeHelper;

	$eeRSCF->formID = 1;
	$eeRSCF->log['notices'][] = 'eeRSCF Settings Page Loaded';

	// Ensure form settings are loaded
	if(empty($eeRSCF->formSettings)) {
		$eeRSCF->formSettings = get_option('eeRSCF_Settings');
		$eeRSCF->confirm = get_option('eeRSCF_Confirm');

		// If still empty, force plugin update to create defaults
		if(empty($eeRSCF->formSettings)) {
			// Force update/install
			include_once(plugin_dir_path(__FILE__) . 'ee-functions.php');
			eeRSCF_UpdatePlugin();

			// Reload after update
			$eeRSCF->formSettings = get_option('eeRSCF_Settings');
			$eeRSCF->confirm = get_option('eeRSCF_Confirm');

			// Final fallback
			if(empty($eeRSCF->formSettings)) {
				$eeRSCF->formSettings = $eeRSCF->contactFormDefault;
				$eeRSCF->confirm = home_url();
			}
		}
	}	// Process if POST
	if (isset($_POST['eeRSCF_Settings']) && check_admin_referer('ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
		$eeRSCF_Log[] = 'Updating Settings...';
		$eeRSCF->eeRSCF_AdminSettingsProcess();
	}

	// Security nonce
	$eeRSCF_Nonce = wp_create_nonce('ee_include_page');

	// Determine the active tab with proper validation
	$active_tab = 'form_settings'; // Default tab
	if (isset($_REQUEST['tab'])) {
		$tab = sanitize_text_field(wp_unslash($_REQUEST['tab']));
		// Validate tab is one of the allowed values
		$allowed_tabs = array('form_settings', 'file_settings', 'spam_settings', 'email_settings');
		if (in_array($tab, $allowed_tabs, true)) {
			$active_tab = $tab;
		}
	}
	$eeRSCF_Page = 'rock-solid-contact-form';

	// Output page header and tabs
	$eeOutput = '
	<header id="eeRSCF_Header">
		<div class="eeRSCF_ShortcodeWrapper">
			<input id="eeRSCF_shortCode" type="text" name="eeRSCF_shortCode" value="[rock-solid-contact]" readonly />
			<button class="eeRSCF_copyToClipboard">Copy</button>
		</div>
		<h1>' . __('Rock Solid Contact Form', 'rock-solid-contact-form') . '</h1>
	</header>

	<div class="eeRSCF_Tabs wrap">
	<div class="eeRSCF_Admin">
		<h2 class="nav-tab-wrapper">';

	$tabs = array(
		'form_settings' => __('Contact Form', 'rock-solid-contact-form'),
		'file_settings' => __('Attachments', 'rock-solid-contact-form'),
		'spam_settings' => __('Spam Prevention', 'rock-solid-contact-form'),
		'email_settings' => __('Email Settings', 'rock-solid-contact-form')
	);

	foreach ($tabs as $tab => $label) {
		$eeOutput .= '<a href="?page=' . $eeRSCF_Page . '&tab=' . $tab . '" class="nav-tab ' . ($active_tab == $tab ? 'nav-tab-active' : '') . '">' . $label . '</a>';
	}

	$eeOutput .= '</h2></div>'; // End Tabs

	echo '<pre>DEBUG: formSettings = '; print_r($eeRSCF->formSettings); echo '</pre>';  // Temporary debug


	$eeOutput .= $eeHelper->eeRSCF_ResultsNotification();
	$eeOutput .= '<form action="' . admin_url() . '/admin.php?page=rock-solid-contact-form" method="POST" id="eeRSCF_Settings">
		<input type="hidden" name="eeRSCF_Settings" value="TRUE" />';
	$eeOutput .= wp_nonce_field('ee-rock-solid-settings', 'ee-rock-solid-settings-nonce', TRUE, FALSE);

	// Include the appropriate tab content function
	if ($active_tab == 'file_settings') {
		include_once(plugin_dir_path(__FILE__) . 'ee-settings-file.php');
	} elseif ($active_tab == 'spam_settings') {
		include_once(plugin_dir_path(__FILE__) . 'ee-settings-spam.php');
	} elseif ($active_tab == 'email_settings') {
		include_once(plugin_dir_path(__FILE__) . 'ee-settings-email.php');
	} else {
		include_once(plugin_dir_path(__FILE__) . 'ee-settings-form.php');
	}

	// Submit Button & Footer
	$eeOutput .= '<input id="eeRSCF_SAVE" type="submit" value="SAVE" /></form>';
	$eeOutput .= '<div id="eeAdminFooter"><p><a href="' . $eeRSCF->websiteLink . '">' .
		$eeRSCF->pluginName . ' &rarr; ' . __('Version', 'rock-solid-contact-form') . ' ' . eeRSCF_Version . '</a></p></div>';

	$eeOutput .= '</div>'; // End wrap

	// Debug Mode Output
	if (eeRSCF_DevMode) {
		$eeOutput .= eeDevOutput($eeRSCF->log);
	}

	// Dump the HTML buffer - Note: $eeOutput contains trusted HTML built by this plugin
	echo wp_kses_post($eeOutput);
}

?>