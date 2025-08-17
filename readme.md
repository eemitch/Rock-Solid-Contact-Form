# Rock Solid Contact Form - Technical Documentation

## Overview

Rock Solid Contact Form is a WordPress plugin designed with security, spam prevention, and email deliverability as core principles. Built from years of experience managing WordPress websites, this plugin eliminates unnecessary complexity while providing robust contact form functionality.

**Version:** 2.1.2
**Author:** Mitchell Bennis - Element Engage, LLC
**License:** GPLv2 or later
**Minimum WordPress:** 5.0
**Minimum PHP:** 7.4

## Architecture & Design

### Core Philosophy
- **Simplicity First**: No form builder required - single shortcode deployment
- **Security Focused**: Multi-layered spam protection without CAPTCHA dependency
- **Reliability**: Built for high-traffic, production WordPress environments
- **Deliverability**: Email optimization for improved message delivery

### Version 2.1.2 - New 4-Class Architecture

The plugin has been reorganized into four focused classes for improved maintainability and security:

#### 1. eeRSCF_Class (Main Display Class)
**File:** `includes/ee-rock-solid-class.php`
**Purpose:** Frontend form display, rendering, and utility methods
**Responsibilities:**
- Form HTML generation and display logic
- Field visibility and validation rules
- Input sanitization and security validation
- Utility methods (slug creation, notifications)
- Frontend form styling and layout

**Key Methods:**
- `eeRSCF_formDisplay()` - Main form display and generation
- `eeMakeSlug()` - Creates URL-friendly slugs from strings
- `eeUnSlug()` - Converts slugs back to readable text
- `eeRSCF_ResultsNotification()` - Admin notification display

#### 2. eeRSCF_MailClass (Email Processing Class)
**File:** `includes/ee-mail-class.php`
**Purpose:** Email processing, delivery, and form data handling
**Responsibilities:**
- Email composition and sending
- SMTP configuration and management
- Form data processing and validation
- File attachment handling
- Post-submission processing and spam detection

**Key Methods:**
- `eeRSCF_SendEmail()` - Main email sending and processing
- `eeRSCF_PostProcess()` - Form data processing and validation
- `eeRSCF_formSpamCheck()` - Multi-stage spam detection
- `eeRSCF_NoticeEmail()` - Spam attack notifications

#### 3. eeRSCF_AdminClass (Backend Administration Class)
**File:** `includes/ee-admin-class.php`
**Purpose:** WordPress admin interface and settings management
**Responsibilities:**
- Admin settings processing and validation
- Form configuration management
- Spam filtering configuration
- Email settings and SMTP configuration
- File upload settings and restrictions

**Key Methods:**
- `eeRSCF_AdminSettingsProcess()` - Main settings form processing

#### 4. eeRSCF_FileClass (File Operations Class)
**File:** `includes/ee-file-class.php`
**Purpose:** File upload handling and system utilities
**Responsibilities:**
- Secure file uploads using WordPress File API
- Upload limit detection and management
- File validation and security checks
- File size formatting utilities
- Remote content fetching (future use)

**Key Methods:**
- `handle_upload()` - Secure file upload processing
- `eeDetectUploadLimit()` - System upload limit detection
- `eeUploader()` - Legacy upload method wrapper
- `eeBytesToSize()` - Human-readable file size formatting

### File Structure
```
rock-solid-contact-form/
├── rock-solid-contact-form.php    # Main plugin file & initialization
├── css/
│   ├── style.css                  # Frontend styles
│   └── style-admin.css           # Admin interface styles
├── js/
│   ├── scripts.js                # Frontend JavaScript
│   ├── scripts-admin.js          # Admin JavaScript
│   └── scripts-admin-footer.js   # Admin footer scripts
├── includes/
│   ├── ee-functions.php          # Core utility functions
│   ├── ee-rock-solid-class.php   # Main display class
│   ├── ee-mail-class.php         # Email processing class
│   ├── ee-admin-class.php        # Admin interface class
│   ├── ee-file-class.php         # File operations class
│   ├── ee-settings-*.php         # Admin settings modules
│   └── images/                   # Admin interface assets
├── images/
│   └── RSCF-Logo-Full.webp       # Plugin branding
├── playwright/                   # Automated testing framework
└── readme.md                     # Technical documentation
```

### Initialization Flow

1. **Plugin Setup** (`eeRSCF_Setup()`):
   - Security nonce generation
   - Class instantiation in proper order
   - Database version checking
   - Settings migration/installation

2. **Settings Loading** (`rock-solid-contact-form.php`):
   - Load saved settings from WordPress options
   - Apply default values for missing settings
   - Ensure critical email fields are populated

3. **Class Instantiation**:
   - Main class: `$eeRSCF = new eeRSCF_Class()`
   - Mail class: `$eeMailClass = new eeRSCF_MailClass()`
   - Admin class: `$eeAdminClass = new eeRSCF_AdminClass($eeRSCF)`
   - File class: `$eeFileClass = new eeRSCF_FileClass()`

4. **Form Display** (`[rock-solid-contact]` shortcode):
   - Settings retrieval and validation
   - Dynamic form generation with security
   - JavaScript enhancement injection

5. **Form Processing** (`eeRSCF_ContactProcess()`):
   - Nonce verification and security checks
   - Multi-layer spam filtering
   - Data sanitization and validation
   - Email delivery and file processing
   - Redirect handling and confirmation

## Core Components & Technical Implementation

### Database Design

#### Options Storage:
- `eeRSCF_Settings`: Main configuration array
- `eeRSCF_Confirm`: Confirmation page URL
- `eeRSCF_Version`: Plugin version tracking

#### Settings Structure:
```php
$formSettings = [
    'formName' => 'Contact Form',       // Form identifier
    'to' => 'admin@site.com',           // Primary recipient
    'cc' => '',                         // Carbon copy recipients
    'bcc' => '',                        // Blind carbon copy
    'email' => 'mail@site.com',         // From address
    'emailMode' => 'PHP',               // Delivery method (PHP/SMTP)
    'emailFormat' => 'HTML',            // Email format (HTML/TEXT)
    'emailName' => 'Contact Form',      // From name
    'fields' => [...],                  // Field configuration array
    'fileMaxSize' => 8,                 // MB limit for uploads
    'fileFormats' => 'jpg,pdf,doc...',  // Allowed file extensions
    'spamBlock' => 'YES',               // Master spam protection toggle
    'spamBlockBots' => 'YES',           // Bot detection and blocking
    'spamHoneypot' => 'link',           // Honeypot field name
    'spamEnglishOnly' => 'YES',         // Language filtering
    'spamBlockFishy' => 'YES',          // Content analysis
    'spamBlockWords' => 'YES',          // Word filtering
    'spamBlockedWords' => '',           // Custom blocked terms
    'spamBlockCommonWords' => 'YES',    // Common spam words
    'spamSendAttackNotice' => 'NO',     // Attack notifications
    'spamNoticeEmail' => '',            // Notice recipient
];
```

### Class Communication & Global Variables

#### Global Instances:
- `$eeRSCF` - Main class instance
- `$eeMailClass` - Mail processing instance
- `$eeAdminClass` - Admin interface instance
- `$eeFileClass` - File operations instance

#### Settings Synchronization:
The classes maintain synchronized settings through:
- Constructor injection (admin class receives main class reference)
- Direct global variable access for shared data
- Settings validation during initialization

### Security & Spam Prevention System

#### Multi-Layer Protection:
1. **Nonce Verification**: WordPress security tokens for form integrity
2. **Honeypot Fields**: Hidden form fields to catch automated bots
3. **Language Detection**: Optional English-only content filtering
4. **Content Analysis**: Detects suspicious patterns and duplicated content
5. **Word Filtering**: Local and remote spam word lists
6. **Form Tampering Detection**: Validates form structure and fields
7. **Input Sanitization**: WordPress-standard data cleaning

#### Advanced Spam Detection Features:
- **Remote Word List**: Fetches updated spam phrases from Cloudflare Worker
- **Local Custom Words**: Admin-defined blocked terms and phrases
- **Fishy Content Detection**: HTML tags, malicious encoding, duplicate entries
- **Bot Pattern Recognition**: Behavior analysis and timing detection
- **Attack Notifications**: Optional email alerts for spam attempts and attacks

#### Security Implementation:
- All user inputs sanitized using WordPress functions
- HTML encoding for output security and XSS prevention
- File upload validation with type and size restrictions
- Direct file access prevention with WordPress security headers

### Form Field Management

#### Configurable Fields:
- **Name Fields**: First Name, Last Name, Business Name
- **Contact Fields**: Phone, Website, Email (auto-detected)
- **Address Fields**: Address 1, Address 2, City, State, ZIP
- **Content Fields**: Subject (with smart detection), Message (required), Other
- **File Attachments**: Optional uploads with security restrictions

#### Field Configuration Options:
Each field supports:
- **Show/Hide**: Toggle field visibility in form display
- **Required/Optional**: Validation requirements and error handling
- **Custom Labels**: Personalized field names and descriptions
- **Smart Detection**: Automatic field type recognition (email, subject)

### File Upload System

**Security Features:**
- **WordPress File API**: Secure upload handling using core WordPress functions
- **Extension Validation**: Whitelist-based file type verification
- **File Size Limits**: Configurable MB restrictions with system limit detection
- **Directory Security**: Protected upload directories with access controls
- **MIME Type Checking**: Validates actual file content vs. extension
- **Secure Storage**: Files stored in WordPress uploads directory structure

**Supported Formats:**
Images (jpg, jpeg, png, gif), documents (pdf, doc, docx, txt, rtf), and more (fully configurable)

### Email Delivery System

#### Delivery Methods:
- **WordPress Mail**: Built-in `wp_mail()` function with optimization
- **SMTP Support**: External SMTP server configuration for improved deliverability

#### Email Features:
- **Smart Headers**: Proper From, Reply-To, CC, BCC header management
- **Subject Detection**: Automatic subject line parsing from form submissions
- **Attachment Handling**: Secure file attachment delivery via email
- **HTML/Text Formats**: Support for both HTML and plain text emails
- **Character Encoding**: Proper UTF-8 encoding and sanitization
- **Error Handling**: Comprehensive email delivery error reporting

### Admin Interface

#### Tabbed Settings Interface:
1. **Contact Form**: Field configuration, email destinations, form naming
2. **File Attachments**: Upload settings, size limits, format restrictions
3. **Spam Prevention**: Multi-layered protection configuration and word filtering
4. **Email Settings**: SMTP configuration, delivery optimization, debugging

#### User Experience Features:
- **Contextual Help**: Detailed explanations and usage notes
- **Real-time Validation**: Immediate feedback on settings changes
- **One-click Toggles**: Easy enable/disable options for features
- **Visual Status Indicators**: Clear status display for all settings
- **Error Prevention**: Input validation and helpful error messages

## Testing Framework

### Automated Testing with Playwright
- **Frontend Tests**: Form display, submission validation, security testing
- **Backend Tests**: Admin interface functionality and settings management
- **Security Tests**: Spam protection validation and input sanitization
- **Integration Tests**: Email delivery and file upload testing

### Test Files Structure:
```
playwright/tests/
├── 1-front/
│   ├── 01-front-page-loads.spec.js      # Basic form display
│   ├── 02-front-submit-form.spec.js     # Form submission testing
│   └── 03-front-security-tests.spec.js  # Security validation
└── 2-back/
    └── 01-back-edit-message-settings.spec.js  # Admin interface testing
```

## Technical Implementation

### Initialization Flow
1. **Plugin Setup** (`eeRSCF_Setup()`):
   - Security nonce generation
   - Class instantiation in proper order
   - Database version checking
   - Settings migration/installation

2. **Settings Loading** (`rock-solid-contact-form.php`):
   - Load saved settings from WordPress options
   - Apply default values for missing settings
   - Ensure critical email fields are populated

3. **Class Instantiation**:
   - Main class: `$eeRSCF = new eeRSCF_Class()`
   - Mail class: `$eeMailClass = new eeRSCF_MailClass()`
   - Admin class: `$eeAdminClass = new eeRSCF_AdminClass($eeRSCF)`
   - File class: `$eeFileClass = new eeRSCF_FileClass()`

4. **Form Display** (`[rock-solid-contact]` shortcode):
   - Settings retrieval and validation
   - Dynamic form generation with security
   - JavaScript enhancement injection

5. **Form Processing** (`eeRSCF_ContactProcess()`):
   - Nonce verification and security checks
   - Multi-layer spam filtering
   - Data sanitization and validation
   - Email delivery and file processing
   - Redirect handling and confirmation

### Database Design

#### Options Storage:
- `eeRSCF_Settings`: Main configuration array
- `eeRSCF_Confirm`: Confirmation page URL
- `eeRSCF_Version`: Plugin version tracking

#### Data Structure:
```php
$formSettings = [
    'to' => 'admin@site.com',           // Primary recipient
    'cc' => '',                         // Carbon copy recipients
    'bcc' => '',                        // Blind carbon copy
    'fields' => [...],                  // Field configuration array
    'fileMaxSize' => 8,                 // MB limit
    'fileFormats' => 'jpg,pdf,doc...',  // Allowed extensions
    'spamBlock' => 'YES',               // Master spam toggle
    'spamBlockBots' => 'YES',           // Bot detection
    'spamHoneypot' => 'link',           // Honeypot field name
    'spamEnglishOnly' => 'YES',         // Language filtering
    'spamBlockFishy' => 'YES',          // Content analysis
    'spamBlockWords' => 'YES',          // Word filtering
    'spamBlockedWords' => '',           // Custom blocked terms
    'spamSendAttackNotice' => 'NO',     // Attack notifications
    'spamNoticeEmail' => '',            // Notice recipient
    'email' => 'mail@site.com',         // From address
    'emailMode' => 'PHP',               // Delivery method
    'emailName' => 'Contact Form'       // From name
];
```

### Security Measures

1. **Input Validation**:
   - WordPress nonce verification
   - Data type validation
   - Character encoding sanitization
   - SQL injection prevention

2. **Output Sanitization**:
   - HTML entity encoding
   - Strip tags on email content
   - XSS prevention

3. **File Security**:
   - MIME type validation
   - File extension whitelisting
   - Directory traversal protection
   - Secure file storage

4. **Access Control**:
   - Admin capability checks
   - Direct file access prevention
   - WordPress action hook integration

### Performance Optimizations

- **Conditional Loading**: Admin scripts only load on plugin pages
- **Efficient Database Queries**: Minimal option calls with smart caching
- **Smart Settings Caching**: Settings cached during request lifecycle
- **Lazy Loading**: Heavy operations only when needed
- **Optimized File Handling**: WordPress File API for secure, efficient uploads

## Development & Maintenance

### Development Mode
When `eeRSCF_Debug` is enabled:
- Detailed logging to browser console and WordPress debug.log
- Debug information display in admin interface
- Settings array visualization for troubleshooting
- Extended error reporting and stack traces

### Version History & Migration

#### 2.1.2 - Major Architecture Reorganization
- **Helper Class Elimination**: Moved all helper methods to appropriate classes
- **4-Class to 3-Class**: Streamlined to Main, Mail, Admin, and File classes
- **Method Redistribution**: Utility methods moved to main class, file methods to file class
- **Enhanced Security**: Comprehensive WordPress security compliance
- **Plugin Check Compliance**: Passes all WordPress.org plugin directory checks
- **Improved Testing**: Comprehensive Playwright test coverage

#### Migration Features:
- Automated migration from previous versions
- Settings array restructuring and validation
- File format normalization and security updates
- Option namespace cleanup and optimization
- Backward compatibility maintenance for existing installations

### Error Handling & Logging

Comprehensive logging system with four levels:
- **Notices**: Informational messages and status updates
- **Messages**: Success confirmations and positive feedback
- **Warnings**: Non-critical issues requiring attention
- **Errors**: Critical problems requiring immediate action

### Hooks & Filters

#### WordPress Actions:
- `init`: Plugin initialization and security setup
- `wp_loaded`: Form processing and email handling
- `admin_menu`: Admin interface setup and permissions
- `wp_enqueue_scripts`: Frontend asset loading and optimization
- `admin_enqueue_scripts`: Admin asset loading with conditional logic

#### WordPress Shortcodes:
- `[rock-solid-contact]`: Primary form display with security and styling

#### Custom Actions:
- Form submission processing with nonce verification
- Spam detection and filtering workflows
- Email delivery and attachment handling
- Admin settings validation and saving

### Development Guidelines

#### Adding New Features:
1. **Class Identification**: Determine the appropriate class for new functionality
2. **Method Design**: Create focused, single-purpose methods
3. **Security Integration**: Implement proper input validation and sanitization
4. **Testing Requirements**: Write corresponding Playwright tests
5. **Documentation Updates**: Update this readme with new features

#### Security Best Practices:
- Always sanitize user inputs using WordPress functions
- Use WordPress nonces for all form security
- Validate file uploads thoroughly with multiple checks
- Follow WordPress coding standards and security guidelines
- Implement proper capability checks for admin functions

#### Code Organization:
- **Main Class**: Frontend display, utility methods, core functionality
- **Mail Class**: Email processing, SMTP configuration, form handling
- **Admin Class**: Backend interface, settings management, validation
- **File Class**: Upload handling, file operations, system utilities

## Best Practices

### For Developers:
- **Security First**: Use WordPress sanitization functions for all user inputs
- **Follow Standards**: Adhere to WordPress coding standards and security guidelines
- **Proper Validation**: Implement comprehensive input validation and nonce verification
- **Testing**: Write Playwright tests for new features and functionality
- **Documentation**: Keep this readme updated with architectural changes
- **Class Organization**: Place methods in the appropriate class based on functionality

### For Site Administrators:
- **Email Configuration**: Configure SMTP for improved email deliverability
- **Security Monitoring**: Regularly review spam protection effectiveness
- **File Management**: Monitor file upload restrictions and storage usage
- **Testing Protocol**: Test email delivery after any configuration changes
- **Performance**: Monitor form submission performance and spam blocking metrics

### For WordPress Integration:
- **Plugin Compatibility**: Test with other plugins for conflicts
- **Theme Integration**: Ensure proper CSS styling integration
- **Performance**: Monitor plugin impact on page load times
- **Security**: Regular plugin updates and security monitoring

## Support and Maintenance

### Documentation Resources:
- Technical architecture details in this readme
- Security assessments in plugin check results
- Test results in `playwright-results/` directory
- Error logs in WordPress debug.log when WP_DEBUG is enabled

### Development Support:
For development questions, contributions, or security assessments:
- Review automated test coverage in `playwright/` directory
- Check security compliance with WordPress plugin check tool
- Monitor error logs for debugging information
- Use development mode for enhanced debugging capabilities

---
*This plugin represents a practical, security-focused approach to WordPress contact forms, prioritizing reliability and spam protection over feature complexity. The 4-class architecture ensures maintainable, scalable code that follows WordPress best practices.*