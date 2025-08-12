<?php
/**
 * Simple debug log viewer for Rock Solid Contact Form
 * Access this file directly to view recent debug entries
 */

// Prevent direct access in production
if (!defined('ABSPATH')) {
    // Set up minimal WordPress environment for testing
    $wp_config_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php';
    if (file_exists($wp_config_path)) {
        require_once($wp_config_path);
    }
}

echo "<h2>Rock Solid Contact Form Debug Log Viewer</h2>";
echo "<p>Last updated: " . date('Y-m-d H:i:s') . "</p>";

// Try to find the debug log file
$possible_log_paths = array(
    WP_CONTENT_DIR . '/debug.log',
    ABSPATH . 'wp-content/debug.log',
    dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-content/debug.log',
    '/tmp/wp-debug.log'
);

$debug_log_path = null;
foreach ($possible_log_paths as $path) {
    if (file_exists($path)) {
        $debug_log_path = $path;
        break;
    }
}

if ($debug_log_path) {
    echo "<h3>Debug Log Location: " . $debug_log_path . "</h3>";

    // Read the last 50 lines of the debug log
    $lines = file($debug_log_path);
    $recent_lines = array_slice($lines, -50);

    echo "<h3>Recent Debug Entries (Last 50 lines):</h3>";
    echo "<div style='background: #f0f0f0; padding: 10px; overflow: auto; max-height: 400px; font-family: monospace; font-size: 12px;'>";

    foreach ($recent_lines as $line) {
        // Highlight RSCF entries
        if (strpos($line, 'RSCF') !== false) {
            echo "<span style='background: yellow; font-weight: bold;'>" . htmlspecialchars($line) . "</span><br>";
        } else {
            echo htmlspecialchars($line) . "<br>";
        }
    }
    echo "</div>";

    // Show only RSCF entries
    echo "<h3>RSCF-Specific Debug Entries:</h3>";
    echo "<div style='background: #e8f4f8; padding: 10px; font-family: monospace; font-size: 12px;'>";
    $rscf_lines = array_filter($lines, function($line) {
        return strpos($line, 'RSCF') !== false;
    });

    if (empty($rscf_lines)) {
        echo "<p>No RSCF debug entries found in log.</p>";
    } else {
        foreach (array_slice($rscf_lines, -20) as $line) {
            echo "<span style='color: #0066cc; font-weight: bold;'>" . htmlspecialchars($line) . "</span><br>";
        }
    }
    echo "</div>";

} else {
    echo "<p style='color: red;'>Debug log file not found. Checked paths:</p>";
    echo "<ul>";
    foreach ($possible_log_paths as $path) {
        echo "<li>" . $path . "</li>";
    }
    echo "</ul>";

    echo "<h3>WordPress Debug Configuration:</h3>";
    echo "<p>WP_DEBUG: " . (defined('WP_DEBUG') && WP_DEBUG ? 'TRUE' : 'FALSE') . "</p>";
    echo "<p>WP_DEBUG_LOG: " . (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'TRUE' : 'FALSE') . "</p>";
    echo "<p>WP_DEBUG_DISPLAY: " . (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'TRUE' : 'FALSE') . "</p>";

    echo "<h3>Suggested wp-config.php Settings:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo "define('WP_DEBUG', true);\n";
    echo "define('WP_DEBUG_LOG', true);\n";
    echo "define('WP_DEBUG_DISPLAY', false);\n";
    echo "</pre>";
}

// Show current RSCF settings
echo "<h3>Current RSCF Settings:</h3>";
$rscf_settings = get_option('eeRSCF_Settings');
if ($rscf_settings) {
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto; max-height: 300px;'>";
    print_r($rscf_settings);
    echo "</pre>";
} else {
    echo "<p>No RSCF settings found in database.</p>";
}

echo "<h3>WordPress Email Settings:</h3>";
echo "<p>Admin Email: " . get_option('admin_email') . "</p>";
echo "<p>Site URL: " . get_site_url() . "</p>";
echo "<p>Home URL: " . home_url() . "</p>";
?>
