// Footer Scripts

jQuery(document).ready(function() {

	// Copy the Shortcode to the clipboard
   jQuery('.eeRSCF_copyToClipboard').click(function(evt) {

		var eeShortCode = jQuery('#eeRSCF_shortCode').val();
		jQuery('#eeRSCF_shortCode').focus();
		jQuery('#eeRSCF_shortCode').select();
		document.execCommand('copy');
   });


   jQuery( "#eeRSCF_emailMode" ).change(function() {

	   if(jQuery('#eeRSCF_emailMode').val() == 'SMTP') {
		   jQuery('#eeRSCF_SMTPEntry').slideDown();
	   } else {
		   jQuery('#eeRSCF_SMTPEntry').slideUp();
	   }
   });


});