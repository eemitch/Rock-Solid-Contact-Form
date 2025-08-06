<?php

$eeOutput .= '

	<h2>Spam Blocking</h2>

	<input type="hidden" name="eeRSCF_SpamSettings" value="TRUE" />
	<input type="hidden" name="tab" value="spam_settings" />

	<fieldset id="eeRSCF_spamSettings">

	<span>Block Spam</span>
	<div class="radio-group">
	<label for="spamBlockYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamBlock" value="YES" id="spamBlockYes"';

	if($eeRSCF->formSettings['spamBlock'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamBlockNo" class="eeRadioLabel">No</label>
		<input type="radio" name="spamBlock" value="NO" id="spamBlockNo"';

	if($eeRSCF->formSettings['spamBlock'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

	<p class="eeNote">Leave this OFF unless your contact form spam is unacceptable.</p>

	<span>Block Spambots</span>
	<div class="radio-group">
	<label for="spamBlockBotsYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamBlockBots" value="YES" id="spamBlockBotsYes"';

	if($eeRSCF->formSettings['spamBlockBots'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamBlockBotsNo" class="eeRadioLabel">No</label>
		<input type="radio" name="spamBlockBots" value="NO" id="spamBlockBotsNo"';

	if($eeRSCF->formSettings['spamBlockBots'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

		<p class="eeNote">Spambots are not people.
		They are automated scripts that search the Internet for contact forms to exploit.
		Many websites use CAPTCHA to stop spambots, but Rock Solid Contact Form uses smarter
		methods to spot spambots instead.</p>



	<label for="eeRSCF_spamHoneypot">Honeypot</label>
	<input type="text" name="spamHoneypot" value="';

	if($eeRSCF->formSettings['spamHoneypot']) { $eeOutput .= $eeRSCF->formSettings['spamHoneypot']; } else { $eeOutput .= 'link'; }

	$eeOutput .= '" class="adminInput" id="eeRSCF_spamHoneypot" size="64" />

		<p class="eeNote">A honeypot is a used to trick spambots. The honeypot is hidden to people, but spambots see this field in the page code and will complete it.
			Spambots are smart, so they might guess your honeypot and not complete it. Change it if you are getting too many spambot messages.</p>




	<span>English Only</span>
	<div class="radio-group">
	<label for="spamEnglishOnlyYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamEnglishOnly" value="YES" id="spamEnglishOnlyYes"';

	if($eeRSCF->formSettings['spamEnglishOnly'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamEnglishOnlyNo" class="eeRadioLabel">No</label>
		<input type="radio" name="spamEnglishOnly" value="NO" id="spamEnglishOnlyNo"';

	if($eeRSCF->formSettings['spamEnglishOnly'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

		<p class="eeNote">Block messages with strange and indecipherable characters found within.</p>





	<span>Block Fishy</span>
	<div class="radio-group">
	<label for="spamBlockFishyYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamBlockFishy" value="YES" id="spamBlockFishyYes"';

	if($eeRSCF->formSettings['spamBlockFishy'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamBlockFishyNo" class="eeRadioLabel">No</label>
		<input type="radio" name="spamBlockFishy" value="NO" id="spamBlockFishyNo"';

	if($eeRSCF->formSettings['spamBlockFishy'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

	<p class="eeNote">Block messages with duplicated fields and other nonsense.</p>';

	// !!!!
	$eeOutput .= '<h3>Custom Word Blocking</h3>';

	$eeOutput .= '
	<span>Block Common Words</span>
	<div class="radio-group">
	<label for="spamBlockCommonWordsYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamBlockCommonWords" value="YES" id="spamBlockCommonWordsYes"';
	if($eeRSCF->formSettings['spamBlockCommonWords'] == 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	<label for="spamBlockCommonWordsNo" class="eeRadioLabel">No</label>
	<input type="radio" name="spamBlockCommonWords" value="NO" id="spamBlockCommonWordsNo"';
	if($eeRSCF->formSettings['spamBlockCommonWords'] != 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	</div>
	<p class="eeNote">Use the built-in list of words and phrases commonly found in contact form spam messages.</p>';

	$eeOutput .= '<span>Block Additional Words</span>
	<div class="radio-group">
	<label for="spamBlockWordsYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamBlockWords" value="YES" id="spamBlockWordsYes"';
	if($eeRSCF->formSettings['spamBlockWords'] == 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	<label for="spamBlockWordsNo" class="eeRadioLabel">No</label>
	<input type="radio" name="spamBlockWords" value="NO" id="spamBlockWordsNo"';
	if($eeRSCF->formSettings['spamBlockWords'] != 'YES') { $eeOutput .= 'checked'; }
	$eeOutput .= ' />
	</div>
	<p class="eeNote">Block messages containing any words or phrases you define below. Separate phrases with a comma.</p>
	<label for="eeRSCF_spamBlockedWords">Added Words</label>
	<textarea name="spamBlockedWords" id="eeRSCF_spamBlockedWords" >';
	if($eeRSCF->formSettings['spamBlockedWords']) { $eeOutput .= $eeRSCF->formSettings['spamBlockedWords']; }
	$eeOutput .= '</textarea>
	<p class="eeNote">Add your words and phrases here to improve spam filtering.</p>';


	$eeOutput .= '<h3>Notifications</h3>

	<span>Send Spam Notice</span>
	<div class="radio-group">
	<label for="spamSendAttackNoticeYes" class="eeRadioLabel">Yes</label>
	<input type="radio" name="spamSendAttackNotice" value="YES" id="spamSendAttackNoticeYes"';

	if($eeRSCF->formSettings['spamSendAttackNotice'] == 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
		<label for="spamSendAttackNoticeNo" class="eeRadioLabel">No</label>
		<input type="radio" name="spamSendAttackNotice" value="NO" id="spamSendAttackNoticeNo"';

	if($eeRSCF->formSettings['spamSendAttackNotice'] != 'YES') { $eeOutput .= 'checked'; }

	$eeOutput .= ' />
	</div>

	<p class="eeNote">Send an email notice showing details about the spam message. This is helpful for fine tuning your spam configuration.</p>


	<label for="eeRSCF_spamNoticeEmail">Notice Email</label>
	<input type="text" name="spamNoticeEmail" value="';

	if($eeRSCF->formSettings['spamNoticeEmail']) { $eeOutput .= $eeRSCF->formSettings['spamNoticeEmail']; }

	$eeOutput .= '" class="adminInput" id="eeRSCF_spamNoticeEmail" size="64" />

	<p class="eeNote">The email you wish the notices to be sent to.</p>

	<br class="eeClearFix" />

</fieldset>';

?>