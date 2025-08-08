<?php
/**
 * Email configuration settings for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$eeOutput .= '

<h2>Form Email Sender</h2>

<input type="hidden" name="eeRSCF_EmailSettings" value="TRUE" />
<input type="hidden" name="tab" value="email_settings" />

<fieldset>

<p>The Contact Form sends an email message to you when someone submits the form.
Therefore, a rock solid contact form needs to have an email address to send from.</p>


<label for="eeRSCF_email">The Form\'s Email</label>
	<input type="email" name="eeRSCF_email" value="';

if($eeRSCF->formSettings['email']) { $eeOutput .= esc_attr($eeRSCF->formSettings['email']); } else { $eeOutput .= esc_attr(get_option('eeRSCF_email')); }

$eeOutput .= '" class="adminInput" id="eeRSCF_email" size="64" />';

if($eeRSCF->formSettings['emailMode'] != 'SMTP') {

	// Validate and sanitize HTTP_HOST
	$server_host = '';
	if (isset($_SERVER['HTTP_HOST'])) {
		$server_host = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
		// Additional validation to ensure it's a valid hostname
		if (!preg_match('/^[a-zA-Z0-9.-]+$/', $server_host)) {
			$server_host = wp_parse_url(home_url(), PHP_URL_HOST);
		}
	} else {
		$server_host = wp_parse_url(home_url(), PHP_URL_HOST);
	}

	$eeOutput .= '

	<p class="eeNote">To improve deliverability, the form\'s email address should be a working address on this web server, such as <strong><em>mail@' . esc_html($server_host) . '</em></strong>.</p>';
}



$eeOutput .= '

<h3>SMTP <small>(Optional)</small></h3>

<p>SMTP (Simple Mail Transfer Protocol) sends emails through your email provider\'s servers instead of your website\'s server. This dramatically improves email deliverability and reduces the chance of messages being marked as spam.</p>

<label for="eeRSCF_emailMode">SMTP Mailer</label>

<select name="eeRSCF_emailMode" id="eeRSCF_emailMode" class="">
		<option value="PHP"';

if($eeRSCF->formSettings['emailMode'] == 'PHP') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>OFF - Using Wordpress Mailer</option>
		<option value="SMTP"';

if($eeRSCF->formSettings['emailMode'] == 'SMTP') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>ON - Using SMTP (BETA)</option>
	</select>

</fieldset>


<div id="eeRSCF_SMTPEntry"';

if($eeRSCF->formSettings['emailMode'] != 'SMTP') { $eeOutput .= ' class="eeHide"'; }

$eeOutput .= '>

<fieldset id="eeRSCF_emailModeSMTP">

<h3>Configure an SMTP Email Account (BETA)</h3>

<p><strong>Need help finding these settings?</strong> Look for "SMTP Settings" or "Outgoing Mail" in your email provider\'s help documentation. Popular providers: <strong>Gmail</strong> (smtp.gmail.com:587), <strong>Outlook</strong> (smtp-mail.outlook.com:587), <strong>Yahoo</strong> (smtp.mail.yahoo.com:587).</p>


<label for="eeRSCF_emailFormat">Message Format</label>

<select name="eeRSCF_emailFormat" id="eeRSCF_emailFormat" class="">
		<option value="TEXT"';

if($eeRSCF->formSettings['emailFormat'] == 'TEXT') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>Text</option>
		<option value="HTML"';

if($eeRSCF->formSettings['emailFormat'] == 'HTML') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>HTML</option>
	</select>

	<p class="eeNote">Choose <strong>HTML</strong> for rich formatting with links and styling, or <strong>Text</strong> for simple plain text messages. Most users prefer HTML.</p>




<label for="eeRSCF_emailName">The Form Name</label>
<input type="text" name="eeRSCF_emailName" value="';

if($eeRSCF->formSettings['emailName']) { $eeOutput .= $eeRSCF->formSettings['emailName']; } else { $eeOutput .= 'Rock Solid Contact Form'; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailName" size="64" />

	<p class="eeNote">This display name appears as the sender in email notifications. Example: "Website Contact Form" or your business name.</p>




<label for="eeRSCF_emailServer">Mail Server Hostname</label>
<input type="text" name="eeRSCF_emailServer" value="';



if($eeRSCF->formSettings['emailServer']) { $eeOutput .= $eeRSCF->formSettings['emailServer']; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailServer" size="64" />

	<p class="eeNote">Your email provider\'s SMTP server address. Common examples: <strong>smtp.gmail.com</strong>, <strong>smtp.outlook.com</strong>, or <strong>mail.yourdomain.com</strong>. Check your email provider\'s help documentation for the correct hostname.</p>




<label for="eeRSCF_emailUsername">Mail Account Username</label>
<input type="text" name="eeRSCF_emailUsername" value="';

if($eeRSCF->formSettings['emailUsername']) {
	if( strlen($eeRSCF->formSettings['emailUsername']) > 1 ) { $eeOutput .= $eeRSCF->formSettings['emailUsername']; } else {  $eeOutput .= 'mail@' . basename( get_site_url() ); }
}

$eeOutput .= '" class="adminInput" id="eeRSCF_emailUsername" size="64" />

	<p class="eeNote">Your complete email address for SMTP authentication. For Gmail/Outlook, use your full email address. For hosting providers, this might be just the username portion.</p>




<label for="eeRSCF_emailPassword">Mail Account Password</label>
<input type="text" name="eeRSCF_emailPassword" value="';

if($eeRSCF->formSettings['emailPassword']) { $eeOutput .= $eeRSCF->formSettings['emailPassword']; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailPassword" size="64" />

	<p class="eeNote">The password for your email account. For Gmail/Outlook, you may need to generate an <strong>App Password</strong> instead of using your regular login password. Check your email provider\'s 2-factor authentication settings.</p>




<label for="eeRSCF_emailSecure">Mail Security</label>

<select name="eeRSCF_emailSecure" id="eeRSCF_emailSecure" class="">
		<option value="SSL"';

if($eeRSCF->formSettings['emailSecure'] == 'SSL') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>Use SSL</option>
		<option value="TLS"';

if($eeRSCF->formSettings['emailSecure'] == 'TLS') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>Use TLS</option>
		<option value="NO"';

if($eeRSCF->formSettings['emailSecure'] == 'NO') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>Unencrypted</option>
	</select>

	<p class="eeNote">Encryption method for secure email transmission. <strong>TLS is recommended</strong> for most modern email providers. Use SSL for older systems or if TLS doesn\'t work. Only use Unencrypted if your email provider specifically requires it.</p>





<label for="eeRSCF_emailAuth">Authentication</label>

<select name="eeRSCF_emailAuth" id="eeRSCF_emailAuth" class="">
		<option value="YES"';

if($eeRSCF->formSettings['emailAuth'] == 'YES') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>Require authorization (Recommended)</option>
		<option value="NO"';

if($eeRSCF->formSettings['emailAuth'] == 'NO') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>No Authorization</option>
	</select>

	<p class="eeNote">Nearly all email providers require authentication. Choose <strong>"Require authorization"</strong> unless your hosting company specifically tells you otherwise.</p>





<label for="eeRSCF_emailPort">Port</label>
<input type="text" name="eeRSCF_emailPort" value="';

if($eeRSCF->formSettings['emailPort']) { $eeOutput .= $eeRSCF->formSettings['emailPort']; } else { $eeOutput .= '25'; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailPort" size="64" />

	<p class="eeNote">SMTP port number for your email provider. <strong>Common combinations:</strong> TLS + Port 587, SSL + Port 465, Gmail uses 587, Outlook uses 587. If unsure, try 587 first.</p>




<label for="eeRSCF_emailDebug">Debug Mode</label>

<select name="eeRSCF_emailDebug" id="eeRSCF_emailDebug" class="">
		<option value="NO"';

if($eeRSCF->formSettings['emailDebug'] == 'NO') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>OFF</option>
		<option value="YES"';

if($eeRSCF->formSettings['emailDebug'] == 'YES') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>ON</option>
	</select>

	<p class="eeNote">Enable this only when emails are not sending properly. Debug information will be written to your WordPress error log (usually wp-content/debug.log). <strong>Turn OFF</strong> once working to avoid log file bloat.</p>



</fieldset>

</div>';

?>