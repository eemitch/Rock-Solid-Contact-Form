// alert('BANG!'); // Check the connection

var eeDepartmentCount = 1;
var eeDepartmentSet = '';
var eeNewDepartment = '';

jQuery(document).ready(function() {	
	
	
	jQuery( "#eeRSCF_emailMode" ).change(function() {
	
		// alert('BANG!'); // Check the connection
		
		if(jQuery('#eeRSCF_emailMode').val() == 'SMTP') {
			
			jQuery('#eeRSCF_SMTPEntry').slideDown();
			
		} else {
			
			jQuery('#eeRSCF_SMTPEntry').slideUp();
		}
	
	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	jQuery( "#eeAddDepartment" ).click(function() {
		
	  // alert('BANG!'); // Check the connection
	  
	  var eeDepartmentCount = jQuery('#eeRSCF_Departments').val(); // Get the current count
	  
	  console.log(eeDepartmentCount + ' Departments');
	  
	  // Get the code
	  eeDepartmentSet = jQuery('#eeDepartmentSet' + eeDepartmentCount).html();
	  
	  var eeOldNum = eeDepartmentCount; // Save the old count number	  
	  
	  // Increment the counter
	  eeDepartmentCount++;
	  
	  // Copy it to a new set
	  eeNewDepartment = '<fieldset id="eeDepartmentSet' + eeDepartmentCount + '">';
	  
	  // Add Remove button to first one
	  if(eeOldNum == 1) {
		  eeNewDepartment = eeNewDepartment + '<button class="eeRemoveSet" type="button" onclick="eeRemoveSet(' + eeDepartmentCount + ')">Remove</button>';
	  }
	  
	  // Continue building...
	  eeNewDepartment = eeNewDepartment + eeDepartmentSet + '</fieldset>';
	  
	  // Replace old count numbers
	  eeNewDepartment = eeFindAndReplace(eeNewDepartment, 'eeRemoveSet('+eeOldNum+')', 'eeRemoveSet('+eeDepartmentCount+')');
	  eeNewDepartment = eeFindAndReplace(eeNewDepartment, 'eeRSCF_formName'+eeOldNum, 'eeRSCF_formName'+eeDepartmentCount);
	  eeNewDepartment = eeFindAndReplace(eeNewDepartment, 'eeRSCF_formTo'+eeOldNum, 'eeRSCF_formTo'+eeDepartmentCount);
	  eeNewDepartment = eeFindAndReplace(eeNewDepartment, 'eeRSCF_formCC'+eeOldNum, 'eeRSCF_formCC'+eeDepartmentCount);
	  eeNewDepartment = eeFindAndReplace(eeNewDepartment, 'eeRSCF_formBCC'+eeOldNum, 'eeRSCF_formBCC'+eeDepartmentCount);
	  
	  // Add it to the form after the last one
	  jQuery('#eeDepartmentSet' + eeOldNum).after(eeNewDepartment);
	  jQuery('#eeDepartmentSet' + eeDepartmentCount).hide(); // Hide for a second 
	  jQuery('#eeDepartmentSet' + eeDepartmentCount + ' input').val(''); // Clear the inputs
	  jQuery('#eeDepartmentSet' + eeDepartmentCount).slideDown(); // show it nicely
	  
	  // Set the hidden input value
	  jQuery('#eeRSCF_Departments').val(eeDepartmentCount); 
	
	});	


}); // END Ready Function


// Remove a department set
function eeRemoveSet(num) {
	jQuery('#eeDepartmentSet' + num + ' input').val(''); // Clear the inputs
	
	// Adjust the hidden value
	var count = jQuery('#eeContactFormDepartments').val();
	var newCount = count - 1;
	jQuery('#eeContactFormDepartments').val(newCount);
	
	jQuery('#eeDepartmentSet' + num).slideUp( "slow", function() { // Hide it nicely
	    jQuery('#eeDepartmentSet' + num).html('<!-- Removed -->'); // Clear the contents
  });
}


function eeFindAndReplace(string, target, replacement) {
 
	var i = 0, length = string.length;
	
	for (i; i < length; i++) {
		string = string.replace(target, replacement);
	}
	
	return string;
}