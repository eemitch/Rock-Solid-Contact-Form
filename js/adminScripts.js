// alert('BANG!'); // Check the connection

jQuery(document).ready(function() {	
	
	
	jQuery( "#eeRSCF_emailMode" ).change(function() {
	
		// alert('BANG!'); // Check the connection
		
		if(jQuery('#eeRSCF_emailMode').val() == 'SMTP') {
			
			jQuery('#eeRSCF_SMTPEntry').slideDown();
			
		} else {
			
			jQuery('#eeRSCF_SMTPEntry').slideUp();
		}
	
	});


}); // END Ready Function


function eeRSCF_FindAndReplace(string, target, replacement) {
 
	var i = 0, length = string.length;
	
	for (i; i < length; i++) {
		string = string.replace(target, replacement);
	}
	
	return string;
}