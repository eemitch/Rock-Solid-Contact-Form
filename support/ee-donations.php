<?php  // PLUGIN DONATIONS PAGE - Mitchell Bennis | Element Engage, LLC | mitch@elementengage.com
	
	// Rev 05.25.18
	
defined( 'ABSPATH' ) or die( 'No direct access is allowed' );
if ( ! wp_verify_nonce( $eeRSCF_Nonce, 'ee_include_page' ) ) exit; // Exit if nonce fails
	
$eeOutput .= '<article id="eeDonations" class="eeAdmin">

	<fieldset>
	
		<p style="text-align:center;"><a href="https://elementengage.com/donate/" title="' . __('Show Your Support', 'ee-protect-login-page') . '" target="_blank">
			<img src="' . plugin_dir_url( __FILE__ ) . '/images/ee-show-support.png" id="eeSupportBanner" /></a></p>
		
		<h2>' . __('Please Donate', 'ee-protect-login-page') . '</h2>' . 
		
		__('I have spent a lot of my free time working to make my Wordpress plugins better, incorporating as many of the features fans have requested as I can.', 'ee-text-domain') . '</p>
					
		<p>' . __('There are plenty more features possible for this free plugin, so please consider a ') . 
		
		'<a href="https://elementengage.com/donate/" title="' . __('donation', 'ee-protect-login-page') . '" target="_blank">' . __('donation', 'ee-protect-login-page') . '</a>' . 
		
		__(' to show your appreciation and allow for future improvements.', 'ee-protect-login-page') . '</p>
					
		<p>' . __('Thank you,') . '</p>
					
		<p><em><a href="?page=ee-protect-login-page&tab=credits">Mitchell</a></em></p>
				
		<h3><a href="https://elementengage.com/donate/" title="' . __('Make a Donation', 'ee-protect-login-page') . '" target="_blank">' . __('Make a Donation', 'ee-protect-login-page') . '</a></h3>
	
	</fieldset>

</article>';	
	
?>