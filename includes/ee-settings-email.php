<?php

$eeOutput .= '

<h2>Form Email Sender</h2>

<input type="hidden" name="eeRSCF_EmailSettings" value="TRUE" />
<input type="hidden" name="tab" value="email_settings" />

<fieldset>

<p>The Contact Form sends an email message to you when someone submits the form.
Therefore, a rock solid contact form needs to have an email address to send from.</p>


<label for="eeRSCF_email">The Form\'s Email</label>
	<input type="email" name="eeRSCF_email" value="';

if($eeRSCF->formSettings['email']) { $eeOutput .= $eeRSCF->formSettings['email']; } else { echo get_option('eeRSCF_email'); }

$eeOutput .= '" class="adminInput" id="eeRSCF_email" size="64" />';

if($eeRSCF->formSettings['emailMode'] != 'SMTP') {

	$eeOutput .= '

	<p class="eeNote">To improve deliverability, the form\'s email address should be a working address on this web server, such as <strong><em>mail@' . $_SERVER['HTTP_HOST'] . '</em></strong>.</p>';
}



$eeOutput .= '

<h3>SMTP <small>(Optional)</small></h3>

<p>To improve email appearance, options
and to protect your domain from blacklisting, it is recommended to configure an
actual email account for the contact form and use SMTP to send messages rather than
relying on the built-in Wordpress(PHP) mailer.</p>

<label for="eeRSCF_emailMode">SMTP Mailer</label>

<select name="eeRSCF_emailMode" id="eeRSCF_emailMode" class="">
		<option value="PHP"';

if($eeRSCF->formSettings['emailMode'] == 'PHP') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>OFF - Using Wordpress Mailer</option>
		<option value="SMTP"';

if($eeRSCF->formSettings['emailMode'] == 'SMTP') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>ON - Using SMTP (Recommended)</option>
	</select>

</fieldset>


<div id="eeRSCF_SMTPEntry"';

if($eeRSCF->formSettings['emailMode'] != 'SMTP') { $eeOutput .= ' class="eeHide"'; }

$eeOutput .= '>

<fieldset id="eeRSCF_emailModeSMTP">

<h3>Configure an SMTP Email Account</h3>

<p>You may need to contact your host to get the settings required.</p>


<label for="eeRSCF_emailFormat">Message Format</label>

<select name="eeRSCF_emailFormat" id="eeRSCF_emailFormat" class="">
		<option value="TEXT"';

if($eeRSCF->formSettings['emailFormat'] == 'TEXT') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>Text</option>
		<option value="HTML"';

if($eeRSCF->formSettings['emailFormat'] == 'HTML') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>HTML</option>
	</select>

	<p class="eeNote">Define the format of the message you receive from the contact form.</p>




<label for="eeRSCF_emailName">The Form Name</label>
<input type="text" name="eeRSCF_emailName" value="';

if($eeRSCF->formSettings['emailName']) { $eeOutput .= $eeRSCF->formSettings['emailName']; } else { $eeOutput .= 'Rock Solid Contact Form'; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailName" size="64" />

	<p class="eeNote">This is the name for the form that will appear in your email. It is associated with your email address.</p>




<label for="eeRSCF_emailServer">Mail Server Hostname</label>
<input type="text" name="eeRSCF_emailServer" value="';



if($eeRSCF->formSettings['emailServer']) { $eeOutput .= $eeRSCF->formSettings['emailServer']; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailServer" size="64" />

	<p class="eeNote">This is the hostname of your local mail server, such as mail.' . $_SERVER['HTTP_HOST'] . '</p>




<label for="eeRSCF_emailUsername">Mail Account Username</label>
<input type="text" name="eeRSCF_emailUsername" value="';

if($eeRSCF->formSettings['emailUsername']) {
	if( strlen($eeRSCF->formSettings['emailUsername']) > 1 ) { $eeOutput .= $eeRSCF->formSettings['emailUsername']; } else {  $eeOutput .= 'mail@' . basename( get_site_url() ); }
}

$eeOutput .= '" class="adminInput" id="eeRSCF_emailUsername" size="64" />

	<p class="eeNote">This is the username for your local mail server, often the complete email address.</p>




<label for="eeRSCF_emailPassword">Mail Account Password</label>
<input type="text" name="eeRSCF_emailPassword" value="';

if($eeRSCF->formSettings['emailPassword']) { $eeOutput .= $eeRSCF->formSettings['emailPassword']; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailPassword" size="64" />

	<p class="eeNote">This is the password for the email account.</p>




<label for="eeRSCF_emailSecure">Mail Security</label>

<select name="eeRSCF_emailSecure" id="eeRSCF_emailSecure" class="">
		<option value="SSL"';

if($eeRSCF->formSettings['emailSecure'] == 'SSL') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>Use SSL</option>
		<option value="TSL"';

if($eeRSCF->formSettings['emailSecure'] == 'TSL') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>Use TSL</option>
		<option value="NO"';

if($eeRSCF->formSettings['emailSecure'] == 'NO') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>Unencrypted</option>
	</select>

	<p class="eeNote">SSL (Secure Sockets Layers) establishes an encrypted link between this web server and your receiving email server when sending messages.</p>





<label for="eeRSCF_emailAuth">Authentication</label>

<select name="eeRSCF_emailAuth" id="eeRSCF_emailAuth" class="">
		<option value="YES"';

if($eeRSCF->formSettings['emailAuth'] == 'YES') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>Require authorization (Recommended)</option>
		<option value="NO"';

if($eeRSCF->formSettings['emailAuth'] == 'NO') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>No Authorization</option>
	</select>

	<p class="eeNote">Your account may or may not require authentication.</p>





<label for="eeRSCF_emailPort">Port</label>
<input type="text" name="eeRSCF_emailPort" value="';

if($eeRSCF->formSettings['emailPort']) { $eeOutput .= $eeRSCF->formSettings['emailPort']; } else { $eeOutput .= '25'; }

$eeOutput .= '" class="adminInput" id="eeRSCF_emailPort" size="64" />

	<p class="eeNote">This is the outgoing mail port. Common ports are 25, 465, 587, 2525 and 2526</p>




<label for="eeRSCF_emailDebug">Debug Mode</label>

<select name="eeRSCF_emailDebug" id="eeRSCF_emailDebug" class="">
		<option value="NO"';

if($eeRSCF->formSettings['emailDebug'] == 'NO') { $eeOutput .= ' selected="selected"'; }

$eeOutput .= '>OFF</option>
		<option value="YES"';

if($eeRSCF->formSettings['emailDebug'] == 'YES') { $eeOutput .= ' selected="selected"'; }


$eeOutput .= '>ON</option>
	</select>

	<p class="eeNote">This will write errors to your local Wordpress error log file. Turn this ON only when troubleshooting.</p>



</fieldset>

</div>';

?>