<?php
/**
 * WordPress File API wrapper for Rock Solid Contact Form
 *
 * This class provides secure file operations using WordPress File API
 * instead of direct PHP file functions for better security and compatibility.
 *
 * @package Rock_Solid_Contact_Form
 * @since 2.1.2
 * @author Mitchell Bennis - Element Engage, LLC
 */

// Security First
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class eeRSCF_FileClass {

    /**
     * WordPress filesystem object
     */
    private $wp_filesystem;

    /**
     * Initialize the WordPress filesystem
     */
    public function __construct() {
        $this->init_filesystem();
    }

    /**
     * Initialize WordPress filesystem
     */
    private function init_filesystem() {
        global $wp_filesystem;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if ( ! WP_Filesystem() ) {
            // Fallback if filesystem cannot be initialized
            add_action( 'admin_notices', array( $this, 'filesystem_error_notice' ) );
            return false;
        }

        $this->wp_filesystem = $wp_filesystem;
        return true;
    }

    /**
     * Display filesystem error notice
     */
    public function filesystem_error_notice() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Rock Solid Contact Form: Unable to initialize WordPress filesystem.', 'rock-solid-contact-form' );
        echo '</p></div>';
    }

    /**
     * Secure file upload handling using WordPress functions
     *
     * @param array $file $_FILES array element
     * @param string $prefix File prefix for naming
     * @return string|false File URL on success, false on failure
     */
    public function handle_upload( $file, $prefix = 'contact' ) {

        // Validate filesystem is available
        if ( ! $this->wp_filesystem ) {
            return false;
        }

        // Validate file input
        if ( ! isset( $file['tmp_name'] ) || ! isset( $file['name'] ) || ! isset( $file['size'] ) ) {
            return false;
        }

        // Check for upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return false;
        }

        // Validate file size (WordPress handles max upload size automatically)
        if ( $file['size'] <= 0 ) {
            return false;
        }

        // Sanitize filename
        $filename = sanitize_file_name( $file['name'] );

        // Add timestamp prefix to avoid conflicts
        $timestamp = gmdate( 'Y-m-d_H-i-s' );
        $new_filename = $prefix . '_' . $timestamp . '_' . $filename;

        // Use WordPress upload directory
        $upload_dir = wp_upload_dir();

        if ( $upload_dir['error'] ) {
            return false;
        }

        // Create subdirectory for contact form uploads
        $contact_dir = $upload_dir['basedir'] . '/rock-solid-contact-form';
        $contact_url = $upload_dir['baseurl'] . '/rock-solid-contact-form';

        // Create directory if it doesn't exist
        if ( ! $this->wp_filesystem->is_dir( $contact_dir ) ) {
            if ( ! $this->wp_filesystem->mkdir( $contact_dir, FS_CHMOD_DIR ) ) {
                return false;
            }
        }

        $destination = $contact_dir . '/' . $new_filename;

        // Use WordPress function for secure file handling
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Override upload directory for this upload
        add_filter( 'upload_dir', array( $this, 'custom_upload_dir' ) );

        // Prepare file array for wp_handle_upload
        $file_array = array(
            'name' => $new_filename,
            'tmp_name' => $file['tmp_name'],
            'type' => $file['type'],
            'size' => $file['size'],
            'error' => $file['error']
        );

        // Handle upload securely
        $uploaded_file = wp_handle_upload( $file_array, array( 'test_form' => false ) );

        // Remove filter
        remove_filter( 'upload_dir', array( $this, 'custom_upload_dir' ) );

        if ( isset( $uploaded_file['error'] ) ) {
            return false;
        }

        // Return the URL of the uploaded file
        return isset( $uploaded_file['url'] ) ? $uploaded_file['url'] : false;
    }

    /**
     * Custom upload directory for contact form files
     */
    public function custom_upload_dir( $upload ) {
        $upload['subdir'] = '/rock-solid-contact-form';
        $upload['path'] = $upload['basedir'] . $upload['subdir'];
        $upload['url'] = $upload['baseurl'] . $upload['subdir'];
        return $upload;
    }

    /**
     * Write log file using WordPress filesystem
     *
     * @param string $content Content to write
     * @param string $filename Log filename
     * @return bool Success/failure
     */
    public function write_log( $content, $filename = 'contact-form.log' ) {

        if ( ! $this->wp_filesystem ) {
            return false;
        }

        // Use WordPress uploads directory for logs
        $upload_dir = wp_upload_dir();

        if ( $upload_dir['error'] ) {
            return false;
        }

        $log_dir = $upload_dir['basedir'] . '/rock-solid-contact-form/logs';
        $log_file = $log_dir . '/' . sanitize_file_name( $filename );

        // Create log directory if it doesn't exist
        if ( ! $this->wp_filesystem->is_dir( $log_dir ) ) {
            if ( ! $this->wp_filesystem->mkdir( $log_dir, FS_CHMOD_DIR, true ) ) {
                return false;
            }
        }

        // Prepare content with timestamp
        $timestamp = gmdate( "Y-m-d H:i:s" );
        $log_content = "\n" . $timestamp . " - " . $content . "\n";

        // Append to log file
        $existing_content = '';
        if ( $this->wp_filesystem->exists( $log_file ) ) {
            $existing_content = $this->wp_filesystem->get_contents( $log_file );
        }

        return $this->wp_filesystem->put_contents( $log_file, $existing_content . $log_content, FS_CHMOD_FILE );
    }

    /**
     * Read file contents using WordPress filesystem
     *
     * @param string $file_path Path to file
     * @return string|false File contents or false on failure
     */
    public function read_file( $file_path ) {

        if ( ! $this->wp_filesystem ) {
            return false;
        }

        if ( ! $this->wp_filesystem->exists( $file_path ) ) {
            return false;
        }

        return $this->wp_filesystem->get_contents( $file_path );
    }

    /**
     * Get remote content using WordPress HTTP API instead of file_get_contents
     *
     * @param string $url URL to fetch
     * @param int $timeout Timeout in seconds
     * @return string|false Content or false on failure
     */
    public function get_remote_content( $url, $timeout = 30 ) {

        // Validate URL
        if ( ! wp_http_validate_url( $url ) ) {
            return false;
        }

        // Use WordPress HTTP API
        $response = wp_remote_get( $url, array(
            'timeout' => $timeout,
            'user-agent' => 'Rock Solid Contact Form/' . eeRSCF_Version,
            'sslverify' => true
        ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            return false;
        }

        return wp_remote_retrieve_body( $response );
    }

    /**
     * Delete file using WordPress filesystem
     *
     * @param string $file_path Path to file
     * @return bool Success/failure
     */
    public function delete_file( $file_path ) {

        if ( ! $this->wp_filesystem ) {
            return false;
        }

        if ( ! $this->wp_filesystem->exists( $file_path ) ) {
            return false;
        }

        return $this->wp_filesystem->delete( $file_path );
    }

    /**
     * Check if file exists using WordPress filesystem
     *
     * @param string $file_path Path to file
     * @return bool File exists
     */
    public function file_exists( $file_path ) {

        if ( ! $this->wp_filesystem ) {
            return false;
        }

        return $this->wp_filesystem->exists( $file_path );
    }

    /**
     * Get file size using WordPress filesystem
     *
     * @param string $file_path Path to file
     * @return int|false File size in bytes or false on failure
     */
    public function get_file_size( $file_path ) {

        if ( ! $this->wp_filesystem ) {
            return false;
        }

        if ( ! $this->wp_filesystem->exists( $file_path ) ) {
            return false;
        }

        return $this->wp_filesystem->size( $file_path );
    }
}



