<?php
/**
 * Debug script for Rock Solid Contact Form email testing
 * Place this in your plugin directory and access via browser to test email functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // If we're not in WordPress, set up minimal environment for testing
    require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php');
}

// Get current settings
$rscf_settings = get_option('eeRSCF_Settings');
$admin_email = get_option('admin_email');

echo "<h2>Rock Solid Contact Form Email Debug</h2>";

echo "<h3>Current Settings:</h3>";
echo "<pre>";
print_r($rscf_settings);
echo "</pre>";

echo "<h3>Admin Email:</h3>";
echo "<p>" . $admin_email . "</p>";

// Test basic WordPress email
echo "<h3>Testing WordPress wp_mail():</h3>";
$test_subject = "RSCF Debug Test - " . date('Y-m-d H:i:s');
$test_message = "This is a test email from Rock Solid Contact Form debug script.";
$test_to = $admin_email;

if (wp_mail($test_to, $test_subject, $test_message)) {
    echo "<p style='color: green;'>✓ Basic wp_mail() test SUCCESSFUL</p>";
} else {
    echo "<p style='color: red;'>✗ Basic wp_mail() test FAILED</p>";
}

// Test with RSCF settings if they exist
if (!empty($rscf_settings['to'])) {
    echo "<h3>Testing with RSCF 'to' address:</h3>";
    if (wp_mail($rscf_settings['to'], $test_subject, $test_message)) {
        echo "<p style='color: green;'>✓ RSCF 'to' address test SUCCESSFUL</p>";
    } else {
        echo "<p style='color: red;'>✗ RSCF 'to' address test FAILED</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠ No 'to' address set in RSCF settings</p>";
}

// Check if any PHP mail errors
if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error && strpos($last_error['message'], 'mail') !== false) {
        echo "<h3>Recent PHP Mail Errors:</h3>";
        echo "<pre style='color: red;'>";
        print_r($last_error);
        echo "</pre>";
    }
}

echo "<h3>WordPress Mail Configuration:</h3>";
echo "<p>WordPress Site URL: " . get_site_url() . "</p>";
echo "<p>WordPress Admin Email: " . get_option('admin_email') . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_NAME'] . "</p>";

// Test mail headers
echo "<h3>Default Mail Headers:</h3>";
$headers = array(
    'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
    'Reply-To: ' . get_option('admin_email'),
    'Content-Type: text/plain; charset=UTF-8'
);
echo "<pre>";
foreach ($headers as $header) {
    echo $header . "\n";
}
echo "</pre>";
