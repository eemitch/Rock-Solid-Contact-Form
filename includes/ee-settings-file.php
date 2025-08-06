<?php
/**
 * File attachment settings for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$eeOutput .= '

	<h2>File Attachments</h2>

	<input type="hidden" name="eeRSCF_FileSettings" value="TRUE" />
	<input type="hidden" name="tab" value="file_settings" />

<fieldset>
	<p>Files are uploaded to the web server rather than attached directly to messages.
	A link to the file is then included within the message.</p>

	<br class="eeClearFix" />

	<label for="eeMaxFileSize">How Big? (MB):</label>
	<input type="number" min="1" max="' . $eeHelper->maxUploadLimit . '" step="1" name="eeMaxFileSize" value="' . $eeRSCF->formSettings['fileMaxSize'] . '" class="adminInput" id="eeMaxFileSize" />

	<br class="eeClearFix" />
		<p class="eeNote">Your hosting limits the maximum file upload size to <strong>' . $eeHelper->maxUploadLimit . ' MB</strong>.</p>


	<br class="eeClearFix" />

	<label for="eeFormats">Allowed Types:</label>
	<textarea name="eeFormats" class="adminInput" id="eeFormats" />' . $eeRSCF->formSettings['fileFormats'] . '</textarea>
		<br class="eeClearFix" />
		<p class="eeNote">Only use the file types you absolutely need, ie; .jpg, .jpeg, .png, .pdf, .mp4, etc</p>

</fieldset>';

?>