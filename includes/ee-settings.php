<?php

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
if (!wp_verify_nonce(eeRSCF_Nonce, 'eeRSCF_Nonce')) exit('That is Noncense!'); // Exit if nonce fails

function eeRSCF_Settings() {

	global $eeRSCF, $eeFileClass;

	$eeRSCF->formID = 1;

	if (eeRSCF_Debug) {
		echo "<!-- RSCF DEBUG: eeRSCF Settings Page Loaded -->";
		error_log('RSCF DEBUG [Settings]: eeRSCF Settings Page Loaded');
	}

	// Process if POST
	if (isset($_POST['eeRSCF_Settings']) && check_admin_referer('ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {
		$eeRSCF_Log[] = 'Updating Settings...';
		global $eeAdminClass;
		if (isset($eeAdminClass) && is_object($eeAdminClass)) {
			$eeAdminClass->eeRSCF_AdminSettingsProcess();
		}
	}

	// Determine the active tab
	$active_tab = isset($_REQUEST['tab']) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'settings';
	$eeRSCF_Page = 'rock-solid-contact-form';

	// Output page header and tabs
	$eeOutput = '
	<header id="eeRSCF_Header">
		<div class="eeRSCF_ShortcodeWrapper">
			<input id="eeRSCF_shortCode" type="text" name="eeRSCF_shortCode" value="[rock-solid-contact]" />
			<button class="eeRSCF_copyToClipboard">' . esc_html__('Copy', 'rock-solid-contact-form') . '</button>
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

	$eeOutput .= $eeRSCF->eeRSCF_ResultsNotification();
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
	$eeOutput .= '<input id="eeRSCF_SAVE" type="submit" value="' . esc_attr__('SAVE', 'rock-solid-contact-form') . '" /></form>';
	$eeOutput .= '<div id="eeAdminFooter"><p><a href="' . $eeRSCF->websiteLink . '">' .
		$eeRSCF->pluginName . ' &rarr; ' . __('Version', 'rock-solid-contact-form') . ' ' . eeRSCF_Version . '</a></p></div>';

	$eeOutput .= '</div>'; // End wrap

	// Debug Mode Output
	if (eeRSCF_Debug) {
		$eeOutput .= eeDevOutput($eeRSCF->log);
	}

	// Dump the HTML buffer
	echo $eeOutput; // The contents of this output have already been escaped
}

?>