<?php
/**
 * File attachment settings for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
if (!wp_verify_nonce(eeRSCF_Nonce, 'eeRSCF_Nonce')) exit('Nonce verification failed!');

$eeOutput .= '

	<h2>' . esc_html__('File Attachments', 'rock-solid-contact-form') . '</h2>

	<input type="hidden" name="eeRSCF_FileSettings" value="TRUE" />
	<input type="hidden" name="tab" value="file_settings" />

<fieldset>
	<p>' . esc_html__('Files are uploaded to the web server rather than attached directly to messages. A link to the file is then included within the message.', 'rock-solid-contact-form') . '</p>

	<br class="eeClearFix" />

	<label for="eeMaxFileSize">' . esc_html__('How Big? (MB):', 'rock-solid-contact-form') . '</label>
	<input type="number" min="1" max="' . esc_attr($eeFileClass->eeDetectUploadLimit()) . '" step="1" name="eeMaxFileSize" value="' . esc_attr($eeRSCF->formSettings['fileMaxSize']) . '" class="adminInput" id="eeMaxFileSize" />

	<br class="eeClearFix" />
		<p class="eeNote">' . sprintf(esc_html__('Your hosting limits the maximum file upload size to %s MB.', 'rock-solid-contact-form'), '<strong>' . esc_html($eeFileClass->maxUploadLimit) . '</strong>') . '</p>


	<br class="eeClearFix" />

	<label for="eeFormats">' . esc_html__('Allowed Types:', 'rock-solid-contact-form') . '</label>
	<textarea name="eeFormats" class="adminInput" id="eeFormats" />' . esc_textarea($eeRSCF->formSettings['fileFormats']) . '</textarea>
		<br class="eeClearFix" />
		<p class="eeNote">' . esc_html__('Only use the file types you absolutely need, ie; .jpg, .jpeg, .png, .pdf, .mp4, etc', 'rock-solid-contact-form') . '</p>

</fieldset>';

?>