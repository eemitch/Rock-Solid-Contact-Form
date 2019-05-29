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
   
   // Create a New Post/Page
   // Copy the Shortcode to the clipboard
   jQuery('.eeRSCF_createPost').click(function(evt) {  
	
		var eeType = jQuery(this).attr('title'); // Post or page?
		var eeName = jQuery('#eeRSCF_formName').val();
		var eeShortcode = jQuery('#eeRSCF_shortCode').val();
		var eeLink = 'admin.php?page=rock-solid-contact-form&subtab=form_settings&eeRSCF_createPost=' + eeType + '|' + encodeURI(eeName) + '|' + encodeURI(eeShortcode);
		
		window.location.href=eeLink;
    
   });
	

});	