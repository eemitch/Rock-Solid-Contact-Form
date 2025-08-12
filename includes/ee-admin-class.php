<?php
/**
 * Back-End class for Rock Solid Contact Form
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


// Our Admin Class
class eeRSCF_AdminClass {

	// Properties needed for admin processing
	public $log = array(
		'notices' => array(),
		'messages' => array(),
		'warnings' => array(),
		'errors' => array()
	);

	public $formID = 1;

    // Process Admin Settings
	public function eeRSCF_AdminSettingsProcess()	{

		$this->log['notices'][] = 'Processing Form Settings';

		if($_POST AND check_admin_referer( 'ee-rock-solid-settings', 'ee-rock-solid-settings-nonce')) {

			global $wpdb, $eeRSCF, $eeHelper;

			// Contact Form Fields and Destinations
			if( isset($_POST['eeRSCF_formSettings']) ) {

				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
				// $eeArray = array();

				// ID
				if(isset($_POST['eeRSCF_ID'])) {
					$this->formID = filter_var(wp_unslash($_POST['eeRSCF_ID']), FILTER_SANITIZE_NUMBER_INT);
				} else {
					$this->formID = 1;
				}

				// Name
				if(isset($_POST['eeRSCF_formName'])) {
					$eeRSCF->formSettings['formName'] = sanitize_text_field(wp_unslash($_POST['eeRSCF_formName']));
				} else {
					$eeArray['name'] = 'Contact Form';
				}

				// Email Addresses
				if( !empty($_POST['eeRSCF_form_to']) ) {

					$delivery = array('to', 'cc', 'bcc');

					foreach($delivery as $to) {

						$eeSet = ''; // String of comma delineated emails

						if( isset($_POST['eeRSCF_form_' . $to ]) ) {

							$eeString = sanitize_text_field(wp_unslash($_POST['eeRSCF_form_' . $to ]));

							if(strpos($eeString, ',')) { // More than one address

								$this->log['notices'][] = 'Multiple address for ' . $to . ' field.';

								$emails = explode(',', $eeString); // Make array

								foreach($emails as $email) { // Loop through them

									$email = trim($email); // Trim spaces

									if(filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validate address
										$eeSet .= $email . ','; // Assemble addresses for storage
									} else {
										$this->log['errors'][] = 'Bad ' . $to . ' Address: ' . $email;
									}
								}

								$eeSet = substr($eeSet, 0, -1); // Clip the last comma

							} elseif($eeString) { // Just one address

								if(filter_var($eeString, FILTER_VALIDATE_EMAIL)) {
									$this->log['notices'][] = 'Single address for ' . $to . ' field.';
									$eeSet .= $eeString;
								} else {
									$this->log['errors'][] = 'Bad ' . $to . ' Address: ' . (isset($_POST['eeAdmin' . $to]) ? $_POST['eeAdmin' . $to] : '');
								}
							}

						}

						$eeRSCF->formSettings[$to] = $eeSet;
					}

				} else {
					$this->log['errors'][] = 'Need at Least One Email Address';

				}

				$fieldsArray = isset($_POST['eeRSCF_fields']) && is_array($_POST['eeRSCF_fields'])
					? wp_unslash($_POST['eeRSCF_fields'])
					: array();

				if( !empty($fieldsArray) ) {

					foreach($fieldsArray as $thisName => $thisFieldArray) {

					if(isset($thisFieldArray['show'])) {
						$eeRSCF->formSettings['fields'][$thisName]['show'] = 'YES';
					} else {
						$eeRSCF->formSettings['fields'][$thisName]['show'] = 'NO';
					}

					if(isset($thisFieldArray['req'])) {
						$eeRSCF->formSettings['fields'][$thisName]['req'] = 'YES';
					} else {
						$eeRSCF->formSettings['fields'][$thisName]['req'] = 'NO';
					}

					if(isset($thisFieldArray['label'])) {
						$eeRSCF->formSettings['fields'][$thisName]['label'] = $thisFieldArray['label'];
					}

					}
				}

				// Results Page
				if(!empty($_POST['eeRSCF_Confirm'])) {
					$eeRSCF->confirm = filter_var(wp_unslash($_POST['eeRSCF_Confirm']), FILTER_VALIDATE_URL);
					if(empty($eeRSCF->confirm)) { $eeRSCF->confirm = home_url(); }
				} else { $eeRSCF->confirm = home_url(); }
				update_option('eeRSCF_Confirm', $eeRSCF->confirm);

			}



			// Attachements
			if($eeRSCF->formSettings['fields']['attachments']['show'] == 'YES'
				AND isset($_POST['eeRSCF_FileSettings']) ) {

				// This must be a number
				$uploadMaxSize = isset($_POST['eeMaxFileSize']) ? (int) wp_unslash($_POST['eeMaxFileSize']) : 0;

				// Can't be more than the system allows.
				if(!$uploadMaxSize OR $uploadMaxSize > $eeHelper->maxUploadLimit) {
					$uploadMaxSize = $eeHelper->maxUploadLimit;
				}
				$eeRSCF->formSettings['fileMaxSize'] = $uploadMaxSize; // Update the database

				// Strip all but what we need for the comma list of file extensions
				$formats = isset($_POST['eeFormats']) ? preg_replace("/[^a-z0-9,]/i", "", wp_unslash($_POST['eeFormats'])) : '';
				if(!$formats) { $formats = $this->fileFormats; } // Go with default if none.
				$eeRSCF->formSettings['fileFormats'] = $formats; // Update the database
			}



			// Spam Filtering
			if( isset($_POST['eeRSCF_SpamSettings']) ) {

				// Validate and sanitize the spamBlock field
				if (isset($_POST['spamBlock']) && (wp_unslash($_POST['spamBlock']) == 'YES' || wp_unslash($_POST['spamBlock']) == 'NO')) {
					$eeRSCF->formSettings['spamBlock'] = sanitize_text_field(wp_unslash($_POST['spamBlock']));
				}

				// Validate and sanitize the spamBlockBots field
				if (isset($_POST['spamBlockBots']) && (wp_unslash($_POST['spamBlockBots']) == 'YES' || wp_unslash($_POST['spamBlockBots']) == 'NO')) {
					$eeRSCF->formSettings['spamBlockBots'] = sanitize_text_field(wp_unslash($_POST['spamBlockBots']));
				}

				// Validate and sanitize the spamHoneypot field
				if (isset($_POST['spamHoneypot']) && !empty($_POST['spamHoneypot'])) {
					$eeRSCF->formSettings['spamHoneypot'] = sanitize_text_field(wp_unslash($_POST['spamHoneypot']));
				}

				// Validate and sanitize the spamEnglishOnly field
				if (isset($_POST['spamEnglishOnly']) && (wp_unslash($_POST['spamEnglishOnly']) == 'YES' || wp_unslash($_POST['spamEnglishOnly']) == 'NO')) {
					$eeRSCF->formSettings['spamEnglishOnly'] = sanitize_text_field(wp_unslash($_POST['spamEnglishOnly']));
				}

				// Validate and sanitize the spamBlockFishy field
				if (isset($_POST['spamBlockFishy']) && (wp_unslash($_POST['spamBlockFishy']) == 'YES' || wp_unslash($_POST['spamBlockFishy']) == 'NO')) {
					$eeRSCF->formSettings['spamBlockFishy'] = sanitize_text_field(wp_unslash($_POST['spamBlockFishy']));
				}

				// Validate and sanitize the spamBlockCommonWords field
				if (isset($_POST['spamBlockCommonWords']) && (wp_unslash($_POST['spamBlockCommonWords']) == 'YES' || wp_unslash($_POST['spamBlockCommonWords']) == 'NO')) {
					$eeRSCF->formSettings['spamBlockCommonWords'] = sanitize_text_field(wp_unslash($_POST['spamBlockCommonWords']));
				}

				// Validate and sanitize the spamBlockWords field
				if (isset($_POST['spamBlockWords']) && (wp_unslash($_POST['spamBlockWords']) == 'YES' || wp_unslash($_POST['spamBlockWords']) == 'NO')) {
					$eeRSCF->formSettings['spamBlockWords'] = sanitize_text_field(wp_unslash($_POST['spamBlockWords']));
				}

				// Validate and sanitize the spamBlockedWords field
				if (isset($_POST['spamBlockedWords']) && !empty($_POST['spamBlockedWords'])) {
				$eeRSCF->formSettings['spamBlockedWords'] = sanitize_textarea_field(wp_unslash($_POST['spamBlockedWords']));
				}

				// Validate and sanitize the spamSendAttackNotice field
				if (isset($_POST['spamSendAttackNotice']) && (wp_unslash($_POST['spamSendAttackNotice']) == 'YES' || wp_unslash($_POST['spamSendAttackNotice']) == 'NO')) {
				$eeRSCF->formSettings['spamSendAttackNotice'] = sanitize_text_field(wp_unslash($_POST['spamSendAttackNotice']));
				}

				// Validate and sanitize the spamNoticeEmail field
				if (isset($_POST['spamNoticeEmail'])) {
					$eeRSCF->formSettings['spamNoticeEmail'] = filter_var(wp_unslash($_POST['spamNoticeEmail']), FILTER_VALIDATE_EMAIL );
				}


				// Spam Prevention
				if($_POST['spamBlock'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Protection On: ' . $settings;
				update_option('eeRSCF_spamBlock', $settings); // Update the database

				// Block Spam Bots
				if($_POST['spamBlockBots'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Bots: ' . $settings;
				update_option('eeRSCF_spamBlockBots', $settings); // Update the database

				// Honeypot
				$settings = sanitize_text_field(wp_unslash($_POST['spamHoneypot']));
				$settings = $eeHelper->eeMakeSlug($settings);
				$this->log['notices'] = 'Spam Honeypot: ' . $settings;
				update_option('eeRSCF_spamHoneypot', $settings); // Update the database

				// English Only
				if($_POST['spamEnglishOnly'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam English Only: ' . $settings;
				update_option('eeRSCF_spamEnglishOnly', $settings); // Update the database

				// Block Fishy
				if($_POST['spamBlockFishy'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Fishy: ' . $settings;
				update_option('eeRSCF_spamBlockFishy', $settings); // Update the database

				// Block Words
				if($_POST['spamBlockWords'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block Words: ' . $settings;
				update_option('eeRSCF_spamBlockWords', $settings); // Update the database

				// Blocked Words
				$settings = sanitize_textarea_field(wp_unslash($_POST['spamBlockedWords']));
				$this->log['notices'] = 'Spam Blocked Words: ' . $settings;
				update_option('eeRSCF_spamBlockedWords', $settings); // Update the database

				// Block Common Words
				if($_POST['spamBlockCommonWords'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Block CommonWords: ' . $settings;
				update_option('eeRSCF_spamBlockCommonWords', $settings); // Update the database

				// Send Notice
				if($_POST['spamSendAttackNotice'] == 'YES') { $settings = 'YES'; } else { $settings = 'NO'; }
				$this->log['notices'] = 'Spam Attack Notice: ' . $settings;
				update_option('eeRSCF_spamSendAttackNotice', $settings); // Update the database

				// Notice Email
				$settings = filter_var(wp_unslash($_POST['spamNoticeEmail']), FILTER_VALIDATE_EMAIL );
				$this->log['notices'] = 'Spam Notice Email: ' . $settings;
				update_option('eeRSCF_spamNoticeEmail', $settings); // Update the database
			}


			// Email Settings
			if(isset($_POST['eeRSCF_EmailSettings'])) {

				// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;

				// Validate and sanitize eeRSCF_EmailSettings
				if ( isset( $_POST['eeRSCF_EmailSettings'] ) && wp_unslash($_POST['eeRSCF_EmailSettings']) == 'TRUE' ) {
					$eeRSCF->formSettings['email'] = isset($_POST['eeRSCF_email']) ? filter_var( wp_unslash($_POST['eeRSCF_email']), FILTER_SANITIZE_EMAIL ) : '';
					$eeRSCF->formSettings['emailMode'] = isset($_POST['eeRSCF_emailMode']) && ( wp_unslash($_POST['eeRSCF_emailMode']) == 'SMTP' ) ? 'SMTP' : 'PHP';
				}

				// Validate and sanitize eeRSCF_emailFormat
				if ( isset( $_POST['eeRSCF_emailFormat'] ) ) {
					$eeRSCF->formSettings['emailFormat'] = ( wp_unslash($_POST['eeRSCF_emailFormat']) == 'HTML' ) ? 'HTML' : 'TEXT';
				}

				// Validate and sanitize eeRSCF_emailName
				if ( isset( $_POST['eeRSCF_emailName'] ) ) {
					$eeRSCF->formSettings['emailName'] = sanitize_text_field( wp_unslash($_POST['eeRSCF_emailName']) );
				}

				// Validate and sanitize eeRSCF_emailServer
				if ( isset( $_POST['eeRSCF_emailServer'] ) ) {
					$eeRSCF->formSettings['emailServer'] = sanitize_text_field( wp_unslash($_POST['eeRSCF_emailServer']) );
				}

				// Validate and sanitize eeRSCF_emailUsername
				if ( isset( $_POST['eeRSCF_emailUsername'] ) ) {
					$eeRSCF->formSettings['emailUsername'] = sanitize_text_field( wp_unslash($_POST['eeRSCF_emailUsername']) );
				}

				// Validate and sanitize eeRSCF_emailPassword
				if ( isset( $_POST['eeRSCF_emailPassword'] ) ) {
					$eeRSCF->formSettings['emailPassword'] = sanitize_text_field( wp_unslash($_POST['eeRSCF_emailPassword']) );
				}

				// Validate and sanitize eeRSCF_emailSecure
				if ( isset( $_POST['eeRSCF_emailSecure'] ) ) {
					$eeRSCF->formSettings['emailSecure'] = sanitize_text_field( wp_unslash($_POST['eeRSCF_emailSecure']) );
				}

				// Validate and sanitize eeRSCF_emailAuth
				if ( isset( $_POST['eeRSCF_emailAuth'] ) ) {
					$eeRSCF->formSettings['emailAuth'] = ( wp_unslash($_POST['eeRSCF_emailAuth']) == 'YES' ) ? true : false;
				}

				// Validate and sanitize eeRSCF_emailPort
				if ( isset( $_POST['eeRSCF_emailPort'] ) ) {
					$eeRSCF->formSettings['emailPort'] = filter_var( wp_unslash($_POST['eeRSCF_emailPort']), FILTER_SANITIZE_NUMBER_INT );
				}

				// Validate and sanitize eeRSCF_emailDebug
				if ( isset( $_POST['eeRSCF_emailDebug'] ) ) {
					$eeRSCF->formSettings['emailDebug'] = ( $_POST['eeRSCF_emailDebug'] == 'YES' ) ? true : false;
				}
			}

			// Save to the Database
			if(empty($this->log['errors'])) {
				update_option('eeRSCF_Settings', $eeRSCF->formSettings); // Update the database
				$this->log['messages'][] = 'The Settings Have Been Saved';
			}

		}
	}


}