// Footer Scripts

jQuery(document).ready(function() {	

	jQuery( "#eeRSCF_createForm" ).click(function() {
		window.location.href='admin.php?page=rock-solid-contact-form&subtab=form_settings&eeRSCF_createForm=1';
	});
	
	// Copy the Shortcode to the clipboard
   jQuery('.eeRSCF_copyToClipboard').click(function(evt) {  
	
		var eeShortCode = jQuery('#eeRSCF_shortCode').val();
		jQuery('#eeRSCF_shortCode').focus();
		jQuery('#eeRSCF_shortCode').select();
		document.execCommand('copy');
   });
	

});	