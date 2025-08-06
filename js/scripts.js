// Frontside Javascript

jQuery(document).ready(function() {

	jQuery( "#eeRSCF_SubmitMessage" ).hide();

	// Remove the submit button when the form submits and replace with message
	// Prevents multiple submissions
	jQuery( "#eeRSCF_form" ).submit(function() {
		jQuery( "#eeRSCF_Submit" ).hide();
		jQuery( "#eeRSCF_SubmitMessage" ).fadeIn();
	});

}); // END Ready Function

