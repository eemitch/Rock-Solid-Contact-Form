<?php
/**
 * Email configuration settings for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
if (!wp_verify_nonce(eeRSCF_Nonce, 'eeRSCF_Nonce')) exit('Nonce verification failed!');

$eeOutput .= '

<h2>' . esc_html__('Form Email Sender', 'rock-solid-contact-form') . '</h2>

<input type="hidden" name="eeRSCF_EmailSettings" value="TRUE" />
<input type="hidden" name="tab" value="email_settings" />

<fieldset>

<p>' . esc_html__('The Contact Form sends an email message to you when someone submits the form. Therefore, a rock solid contact form needs to have an email address to send from.', 'rock-solid-contact-form') . '</p>


<label for="eeRSCF_email">' . esc_html__('The Form\'s Email', 'rock-solid-contact-form') . '</label>
	<input type="email" name="eeRSCF_email" value="';

if($eeRSCF->formSettings['email']) { $eeOutput .= esc_attr($eeRSCF->formSettings['email']); } else { $eeOutput .= esc_attr(get_option('eeRSCF_email')); }

$eeOutput .= '" class="adminInput" id="eeRSCF_email" size="64" />';

if($eeRSCF->formSettings['emailMode'] != 'SMTP') {

	$http_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
	$eeOutput .= '

	<p class="eeNote">' . sprintf(esc_html__('To improve deliverability, the form\'s email address should be a working address on this web server, such as %s.', 'rock-solid-contact-form'), '<strong><em>mail@' . $http_host . '</em></strong>') . '</p>';
}



$eeOutput .= '

<h3>' . esc_html__('SMTP', 'rock-solid-contact-form') . ' <small>(' . esc_html__('Optional', 'rock-solid-contact-form') . ')</small></h3>

<p>' . esc_html__('SMTP (Simple Mail Transfer Protocol) sends emails through your email provider\'s servers instead of your website\'s server. This dramatically improves email deliverability and reduces the chance of messages being marked as spam.', 'rock-solid-contact-form') . '</p>

<label for="eeRSCF_emailMode">' . esc_html__('SMTP Mailer', 'rock-solid-contact-form') . '</label>

<select name="eeRSCF_emailMode" id="eeRSCF_emailMode" class="">
		<option value="PHP"';

if($eeRSCF->formSettings['emailMode'] == 'PHP') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>' . esc_html__('OFF - Using Wordpress Mailer', 'rock-solid-contact-form') . '</option>
		<option value="SMTP"';

if($eeRSCF->formSettings['emailMode'] == 'SMTP') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>' . esc_html__('ON - Using SMTP (BETA)', 'rock-solid-contact-form') . '</option>
	</select>

</fieldset>


<div id="eeRSCF_SMTPEntry"';

if($eeRSCF->formSettings['emailMode'] != 'SMTP') { $eeOutput .= ' class="eeHide"'; }

$eeOutput .= '>

<fieldset id="eeRSCF_emailModeSMTP">

<h3>' . esc_html__('Configure an SMTP Email Account (BETA)', 'rock-solid-contact-form') . '</h3>

<p>' . esc_html__('Need help finding these settings? Look for "SMTP Settings" or "Outgoing Mail" in your email provider\'s help documentation. Popular providers: Gmail (smtp.gmail.com:587), Outlook (smtp-mail.outlook.com:587), Yahoo (smtp.mail.yahoo.com:587).', 'rock-solid-contact-form') . '</p>


<label for="eeRSCF_emailFormat">' . esc_html__('Message Format', 'rock-solid-contact-form') . '</label>

<select name="eeRSCF_emailFormat" id="eeRSCF_emailFormat" class="">
		<option value="TEXT"';

if($eeRSCF->formSettings['emailFormat'] == 'TEXT') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>' . esc_html__('Text', 'rock-solid-contact-form') . '</option>
		<option value="HTML"';

if($eeRSCF->formSettings['emailFormat'] == 'HTML') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>' . esc_html__('HTML', 'rock-solid-contact-form') . '</option>
	</select>

	<p class="eeNote">' . esc_html__('Choose HTML for rich formatting with links and styling, or Text for simple plain text messages. Most users prefer HTML.', 'rock-solid-contact-form') . '</p>




<label for="eeRSCF_emailName">' . esc_html__('The Form Name', 'rock-solid-contact-form') . '</label>
<input type="text" name="eeRSCF_emailName" value="';

if($eeRSCF->formSettings['emailName']) { $eeOutput .= esc_attr($eeRSCF->formSettings['emailName']); } else { $eeOutput .= 'Rock Solid Contact Form'; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailName" size="64" />

	<p class="eeNote">' . esc_html__('This display name appears as the sender in email notifications. Example: "Website Contact Form" or your business name.', 'rock-solid-contact-form') . '</p>




<label for="eeRSCF_emailServer">' . esc_html__('Mail Server Hostname', 'rock-solid-contact-form') . '</label>
<input type="text" name="eeRSCF_emailServer" value="';



if($eeRSCF->formSettings['emailServer']) { $eeOutput .= esc_attr($eeRSCF->formSettings['emailServer']); }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailServer" size="64" />

	<p class="eeNote">' . esc_html__('Your email provider\'s SMTP server address. Common examples: smtp.gmail.com, smtp.outlook.com, or mail.yourdomain.com. Check your email provider\'s help documentation for the correct hostname.', 'rock-solid-contact-form') . '</p>




<label for="eeRSCF_emailUsername">' . esc_html__('Mail Account Username', 'rock-solid-contact-form') . '</label>
<input type="text" name="eeRSCF_emailUsername" value="';

if($eeRSCF->formSettings['emailUsername']) {
	if( strlen($eeRSCF->formSettings['emailUsername']) > 1 ) { $eeOutput .= esc_attr($eeRSCF->formSettings['emailUsername']); } else {  $eeOutput .= esc_attr('mail@' . basename( get_site_url() )); }
}

$eeOutput .= '" class="adminInput" id="eeRSCF_emailUsername" size="64" />

	<p class="eeNote">' . esc_html__('Your complete email address for SMTP authentication. For Gmail/Outlook, use your full email address. For hosting providers, this might be just the username portion.', 'rock-solid-contact-form') . '</p>




<label for="eeRSCF_emailPassword">' . esc_html__('Mail Account Password', 'rock-solid-contact-form') . '</label>
<input type="text" name="eeRSCF_emailPassword" value="';

if($eeRSCF->formSettings['emailPassword']) { $eeOutput .= esc_attr($eeRSCF->formSettings['emailPassword']); }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailPassword" size="64" />

	<p class="eeNote">' . esc_html__('The password for your email account. For Gmail/Outlook, you may need to generate an App Password instead of using your regular login password. Check your email provider\'s 2-factor authentication settings.', 'rock-solid-contact-form') . '</p>




<label for="eeRSCF_emailSecure">' . esc_html__('Mail Security', 'rock-solid-contact-form') . '</label>

<select name="eeRSCF_emailSecure" id="eeRSCF_emailSecure" class="">
		<option value="SSL"';

if($eeRSCF->formSettings['emailSecure'] == 'SSL') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>' . esc_html__('Use SSL', 'rock-solid-contact-form') . '</option>
		<option value="TLS"';

if($eeRSCF->formSettings['emailSecure'] == 'TLS') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>' . esc_html__('Use TLS', 'rock-solid-contact-form') . '</option>
		<option value="NO"';

if($eeRSCF->formSettings['emailSecure'] == 'NO') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>' . esc_html__('Unencrypted', 'rock-solid-contact-form') . '</option>
	</select>

	<p class="eeNote">' . esc_html__('Encryption method for secure email transmission. TLS is recommended for most modern email providers. Use SSL for older systems or if TLS doesn\'t work. Only use Unencrypted if your email provider specifically requires it.', 'rock-solid-contact-form') . '</p>





<label for="eeRSCF_emailAuth">' . esc_html__('Authentication', 'rock-solid-contact-form') . '</label>

<select name="eeRSCF_emailAuth" id="eeRSCF_emailAuth" class="">
		<option value="YES"';

if($eeRSCF->formSettings['emailAuth'] == 'YES') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>' . esc_html__('Require authorization (Recommended)', 'rock-solid-contact-form') . '</option>
		<option value="NO"';

if($eeRSCF->formSettings['emailAuth'] == 'NO') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>' . esc_html__('No Authorization', 'rock-solid-contact-form') . '</option>
	</select>

	<p class="eeNote">' . esc_html__('Nearly all email providers require authentication. Choose "Require authorization" unless your hosting company specifically tells you otherwise.', 'rock-solid-contact-form') . '</p>





<label for="eeRSCF_emailPort">' . esc_html__('Port', 'rock-solid-contact-form') . '</label>
<input type="text" name="eeRSCF_emailPort" value="';

if($eeRSCF->formSettings['emailPort']) { $eeOutput .= esc_attr($eeRSCF->formSettings['emailPort']); } else { $eeOutput .= '25'; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailPort" size="64" />

	<p class="eeNote">' . esc_html__('SMTP port number for your email provider. Common combinations: TLS + Port 587, SSL + Port 465, Gmail uses 587, Outlook uses 587. If unsure, try 587 first.', 'rock-solid-contact-form') . '</p>




<label for="eeRSCF_emailDebug">' . esc_html__('Debug Mode', 'rock-solid-contact-form') . '</label>

<select name="eeRSCF_emailDebug" id="eeRSCF_emailDebug" class="">
		<option value="NO"';

if($eeRSCF->formSettings['emailDebug'] == 'NO') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>' . esc_html__('OFF', 'rock-solid-contact-form') . '</option>
		<option value="YES"';

if($eeRSCF->formSettings['emailDebug'] == 'YES') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>' . esc_html__('ON', 'rock-solid-contact-form') . '</option>
	</select>

	<p class="eeNote">' . esc_html__('Enable this only when emails are not sending properly. Debug information will be written to your WordPress error log (usually wp-content/debug.log). Turn OFF once working to avoid log file bloat.', 'rock-solid-contact-form') . '</p>



</fieldset>

</div>';

?>