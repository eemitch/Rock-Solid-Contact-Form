<?php
/**
 * Spam prevention settings for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
if (!wp_verify_nonce(eeRSCF_Nonce, 'eeRSCF_Nonce')) exit('Nonce verification failed!');

$eeOutput .= '

	<h2>' . esc_html__('Spam Blocking', 'rock-solid-contact-form') . '</h2>

	<input type="hidden" name="eeRSCF_SpamSettings" value="TRUE" />
	<input type="hidden" name="tab" value="spam_settings" />

	<fieldset id="eeRSCF_spamSettings">

	<span>' . esc_html__('Block Spam', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamBlockYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlock" value="YES" id="spamBlockYes"';

	if($eeRSCF->formSettings['spamBlock'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamBlockNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
		<input type="radio" name="spamBlock" value="NO" id="spamBlockNo"';

	if($eeRSCF->formSettings['spamBlock'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

	<p class="eeNote">' . esc_html__('Leave this OFF unless your contact form spam is unacceptable.', 'rock-solid-contact-form') . '</p>

	<span>' . esc_html__('Block Spambots', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamBlockBotsYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlockBots" value="YES" id="spamBlockBotsYes"';

	if($eeRSCF->formSettings['spamBlockBots'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamBlockBotsNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
		<input type="radio" name="spamBlockBots" value="NO" id="spamBlockBotsNo"';

	if($eeRSCF->formSettings['spamBlockBots'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

		<p class="eeNote">' . esc_html__('Spambots are not people. They are automated scripts that search the Internet for contact forms to exploit. Many websites use CAPTCHA to stop spambots, but Rock Solid Contact Form uses smarter methods to spot spambots instead.', 'rock-solid-contact-form') . '</p>



	<label for="eeRSCF_spamHoneypot">' . esc_html__('Honeypot', 'rock-solid-contact-form') . '</label>
	<input type="text" name="spamHoneypot" value="';

	if($eeRSCF->formSettings['spamHoneypot']) { $eeOutput .= esc_attr($eeRSCF->formSettings['spamHoneypot']); } else { $eeOutput .= 'link'; }

	$eeOutput .= '" class="adminInput" id="eeRSCF_spamHoneypot" size="64" />

		<p class="eeNote">' . esc_html__('A honeypot is a used to trick spambots. The honeypot is hidden to people, but spambots see this field in the page code and will complete it. Spambots are smart, so they might guess your honeypot and not complete it. Change it if you are getting too many spambot messages.', 'rock-solid-contact-form') . '</p>




	<span>' . esc_html__('English Only', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamEnglishOnlyYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamEnglishOnly" value="YES" id="spamEnglishOnlyYes"';

	if($eeRSCF->formSettings['spamEnglishOnly'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamEnglishOnlyNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
		<input type="radio" name="spamEnglishOnly" value="NO" id="spamEnglishOnlyNo"';

	if($eeRSCF->formSettings['spamEnglishOnly'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

		<p class="eeNote">' . esc_html__('Block messages with strange and indecipherable characters found within.', 'rock-solid-contact-form') . '</p>





	<span>' . esc_html__('Block Fishy', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamBlockFishyYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlockFishy" value="YES" id="spamBlockFishyYes"';

	if($eeRSCF->formSettings['spamBlockFishy'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamBlockFishyNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
		<input type="radio" name="spamBlockFishy" value="NO" id="spamBlockFishyNo"';

	if($eeRSCF->formSettings['spamBlockFishy'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

	<p class="eeNote">' . esc_html__('Block messages with duplicated fields and other nonsense.', 'rock-solid-contact-form') . '</p>';

	// !!!!
	$eeOutput .= '<h3>' . esc_html__('Custom Word Blocking', 'rock-solid-contact-form') . '</h3>';

	$eeOutput .= '
	<span>' . esc_html__('Block Common Words', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamBlockCommonWordsYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlockCommonWords" value="YES" id="spamBlockCommonWordsYes"';
	if($eeRSCF->formSettings['spamBlockCommonWords'] == 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	<label for="spamBlockCommonWordsNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlockCommonWords" value="NO" id="spamBlockCommonWordsNo"';
	if($eeRSCF->formSettings['spamBlockCommonWords'] != 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	</div>
	<p class="eeNote">' . esc_html__('Use the built-in list of words and phrases commonly found in contact form spam messages.', 'rock-solid-contact-form') . '</p>';

	$eeOutput .= '<span>' . esc_html__('Block Additional Words', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamBlockWordsYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlockWords" value="YES" id="spamBlockWordsYes"';
	if($eeRSCF->formSettings['spamBlockWords'] == 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	<label for="spamBlockWordsNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamBlockWords" value="NO" id="spamBlockWordsNo"';
	if($eeRSCF->formSettings['spamBlockWords'] != 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	</div>
	<p class="eeNote">' . esc_html__('Block messages containing any words or phrases you define below. Separate phrases with a comma.', 'rock-solid-contact-form') . '</p>
	<label for="eeRSCF_spamBlockedWords">' . esc_html__('Added Words', 'rock-solid-contact-form') . '</label>
	<textarea name="spamBlockedWords" id="eeRSCF_spamBlockedWords" >';
	if($eeRSCF->formSettings['spamBlockedWords']) { $eeOutput .= esc_textarea($eeRSCF->formSettings['spamBlockedWords']); }
	$eeOutput .= '</textarea>
	<p class="eeNote">' . esc_html__('Add your words and phrases here to improve spam filtering.', 'rock-solid-contact-form') . '</p>';


	$eeOutput .= '<h3>' . esc_html__('Notifications', 'rock-solid-contact-form') . '</h3>

	<span>' . esc_html__('Send Spam Notice', 'rock-solid-contact-form') . '</span>
	<div class="radio-group">
	<label for="spamSendAttackNoticeYes" class="eeRadioLabel">' . esc_html__('Yes', 'rock-solid-contact-form') . '</label>
	<input type="radio" name="spamSendAttackNotice" value="YES" id="spamSendAttackNoticeYes"';

	if($eeRSCF->formSettings['spamSendAttackNotice'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamSendAttackNoticeNo" class="eeRadioLabel">' . esc_html__('No', 'rock-solid-contact-form') . '</label>
		<input type="radio" name="spamSendAttackNotice" value="NO" id="spamSendAttackNoticeNo"';

	if($eeRSCF->formSettings['spamSendAttackNotice'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

	<p class="eeNote">' . esc_html__('Send an email notice showing details about the spam message. This is helpful for fine tuning your spam configuration.', 'rock-solid-contact-form') . '</p>


	<label for="eeRSCF_spamNoticeEmail">' . esc_html__('Notice Email', 'rock-solid-contact-form') . '</label>
	<input type="text" name="spamNoticeEmail" value="';

	if($eeRSCF->formSettings['spamNoticeEmail']) { $eeOutput .= esc_attr($eeRSCF->formSettings['spamNoticeEmail']); }

	$eeOutput .= '" class="adminInput" id="eeRSCF_spamNoticeEmail" size="64" />

	<p class="eeNote">' . esc_html__('The email you wish the notices to be sent to.', 'rock-solid-contact-form') . '</p>

	<br class="eeClearFix" />

</fieldset>';

?>