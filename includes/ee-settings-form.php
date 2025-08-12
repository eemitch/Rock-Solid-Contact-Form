<?php
/**
 * Form configuration settings for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 */


// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
if (!wp_verify_nonce(eeRSCF_Nonce, 'eeRSCF_Nonce')) exit('Nonce verification failed!');

$eeOutput .= '

	<h2>' . esc_html__('Form Configuration', 'rock-solid-contact-form') . '</h2>

	<input type="hidden" name="eeRSCF_formSettings" value="TRUE" />
	<input type="hidden" name="eeRSCF_ID" value="' . esc_attr($eeRSCF->formID) . '" />
		<input type="hidden" name="tab" value="form_settings" />

	<fieldset id="eeRSCF_formSettings">

	<p>' . esc_html__('Select the contact form fields to display. Also select if the field should be required. Change the text for each label as required. A text input box for the message will be provided automatically.', 'rock-solid-contact-form') . '</p>

	';

	$eeOutput .= '
	<fieldset id="eeRSCF_delivery">

		<h3>' . esc_html__('Delivery', 'rock-solid-contact-form') . '</h3>

				<label for="eeRSCF_formTO">' . esc_html__('TO', 'rock-solid-contact-form') . '</label>
				<input type="text" name="eeRSCF_form_to" value="';

			if(!empty($eeRSCF->formSettings['to'])) { $eeOutput .= esc_attr($eeRSCF->formSettings['to']); }

			$eeOutput .= '" class="adminInput" id="eeRSCF_formTO" size="64" />

				<label for="eeRSCF_formCC">' . esc_html__('CC', 'rock-solid-contact-form') . '</label>
				<input type="text" name="eeRSCF_form_cc" value="';

			if(!empty($eeRSCF->formSettings['cc'])) { $eeOutput .= esc_attr($eeRSCF->formSettings['cc']); }

			$eeOutput .= '" class="adminInput" id="eeRSCF_formCC" size="64" />

				<label for="eeRSCF_formBCC">' . esc_html__('BCC', 'rock-solid-contact-form') . '</label>
				<input type="text" name="eeRSCF_form_bcc" value="';

			if(!empty($eeRSCF->formSettings['bcc'])) { $eeOutput .= esc_attr($eeRSCF->formSettings['bcc']); }

			$eeOutput .= '" class="adminInput" id="eeRSCF_formBCC" size="64" />

				<p class="eeNote">' . esc_html__('You can add more than one address per field by separating them using a comma.', 'rock-solid-contact-form') . '</p>

				<br class="eeClearFix" />';

			$eeOutput .= '</fieldset>

	<fieldset>

		<h3>' . esc_html__('Form Fields', 'rock-solid-contact-form') . '</h3>


	<table class="eeRSCF_formFields">
		<tr>
			<th>' . esc_html__('Show', 'rock-solid-contact-form') . '</th>
			<th>' . esc_html__('Require', 'rock-solid-contact-form') . '</th>
			<th>' . esc_html__('Label', 'rock-solid-contact-form') . '</th>
		</tr>';


	$eeFields = $eeRSCF->formSettings['fields'];

	// Loop-de-doop
	foreach($eeFields as $eeFieldName => $fieldArray) {  // Field name and settings array

		$eeOutput .= '<tr>';

		foreach($fieldArray as $field => $value){ // Checkboxes

			if($field == 'label') { // Text Input

				$eeOutput .= '

				<td><input type="text" name="eeRSCF_fields[' . $eeFieldName . '][' . $field . ']" value="';

				if($value) { $eeOutput .= esc_attr(stripslashes($value)); } else { $eeOutput .= esc_attr($eeRSCF->eeUnSlug($field)); }

				$eeOutput .= '" size="32" /></td>';

			} else {

				$eeOutput .= '

				<td><input type="checkbox" name="eeRSCF_fields[' . $eeFieldName . '][' . $field . ']"';

				if($value == 'YES') { $eeOutput .= ' checked="checked"'; }

				$eeOutput .= ' /></td>';
			}
		}

		$eeOutput .= '</tr>';

	}

	$eeOutput .= '</table>

	</fieldset>

	<fieldset>

		<h3>' . esc_html__('Confirmation Page', 'rock-solid-contact-form') . '</h3>

		<p>' . esc_html__('This is the page that will load after the form has been submitted. If no page is defined, the contact form page will be loaded again.', 'rock-solid-contact-form') . '</p>

		<input class="eeFullWidth" type="url" name="eeRSCF_Confirm" value="' . esc_attr($eeRSCF->confirm) . '" size="128" />

	</fieldset>

	<br class="eeClearFix" />

</fieldset>

';


?>