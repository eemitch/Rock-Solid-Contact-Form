<?php

$eeOutput .= '

	<h2>Form Configuration</h2>

	<input type="hidden" name="eeRSCF_formSettings" value="TRUE" />
	<input type="hidden" name="eeRSCF_ID" value="' . $eeRSCF->formID . '" />
		<input type="hidden" name="tab" value="form_settings" />

	<fieldset id="eeRSCF_formSettings">

	<p>Select the contact form fields to display. Also select if the field should be required. Change the text for each label as required. A text input box for the message will be provided automatically.</p>

	';

	$eeOutput .= '
	<fieldset id="eeRSCF_delivery">

		<h3>Delivery</h3>

				<label for="eeRSCF_formTO">TO</label>
				<input type="text" name="eeRSCF_form_to" value="';

			if(!empty($eeRSCF->formSettings['to'])) { $eeOutput .= $eeRSCF->formSettings['to']; }
					// else { $eeOutput .= get_option('admin_email'); }

			$eeOutput .= '" class="adminInput" id="eeRSCF_formTO" size="64" />

				<label for="eeRSCF_formCC">CC</label>
				<input type="text" name="eeRSCF_form_cc" value="';

			if(!empty($eeRSCF->formSettings['cc'])) { $eeOutput .= $eeRSCF->formSettings['cc']; }

			$eeOutput .= '" class="adminInput" id="eeRSCF_formCC" size="64" />

				<label for="eeRSCF_formBCC">BCC</label>
				<input type="text" name="eeRSCF_form_bcc" value="';

			if(!empty($eeRSCF->formSettings['bcc'])) { $eeOutput .= $eeRSCF->formSettings['bcc']; }

			$eeOutput .= '" class="adminInput" id="eeRSCF_formBCC" size="64" />

				<p class="eeNote">You can add more than one address per field by separating them using a comma.</p>

				<br class="eeClearFix" />';

			$eeOutput .= '</fieldset>

	<fieldset>

		<h3>Form Fields</h3>


	<table class="eeRSCF_formFields">
		<tr>
			<th>Show</th>
			<th>Require</th>
			<th>Label</th>
		</tr>';


	$eeFields = $eeRSCF->formSettings['fields'];

	// echo '<pre>'; print_r($eeFields); echo '</pre>'; exit;

	// Loop-de-doop
	foreach($eeFields as $eeFieldName => $fieldArray) {  // Field name and settings array

		$eeOutput .= '<tr>';

		foreach($fieldArray as $field => $value){ // Checkboxes

			if($field == 'label') { // Text Input

				$eeOutput .= '

				<td><input type="text" name="eeRSCF_fields[' . $eeFieldName . '][' . $field . ']" value="';

				if($value) { $eeOutput .= stripslashes($value); } else { $eeOutput .= $eeHelper->eeUnSlug($field); }

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

		<h3>Confirmation Page</h3>

		<p>This is the page that will load after the form has been submitted. If no page is defined, the contact form page will be loaded again.</p>

		<input class="eeFullWidth" type="url" name="eeRSCF_Confirm" value="' . $eeRSCF->confirm . '" size="128" />

	</fieldset>

	<br class="eeClearFix" />

</fieldset>

<!-- <a id="eeRSCF_deleteForm" href="admin.php?page=rock-solid-contact-form&subtab=form_settings&eeRSCF_deleteForm=' . $eeRSCF->formID . '">Delete This Form</a> -->

';



?>