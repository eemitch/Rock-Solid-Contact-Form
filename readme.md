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

### File Structure
```
rock-solid-contact-form/
├── rock-solid-contact-form.php    # Main plugin file
├── css/
│   ├── style.css                  # Frontend styles
│   └── style-admin.css           # Admin interface styles
├── js/
│   ├── scripts.js                # Frontend JavaScript
│   ├── scripts-admin.js          # Admin JavaScript
│   └── scripts-admin-footer.js   # Admin footer scripts
├── includes/
│   ├── ee-functions.php          # Core utility functions
│   ├── ee-helper-class.php       # Helper utilities and notifications
│   ├── ee-rock-solid-class.php   # Main plugin class
│   ├── ee-settings-*.php         # Admin settings modules
│   └── images/                   # Admin interface assets
└── images/
    └── RSCF-Logo-Full.webp       # Plugin branding
```

## Core Components

### 1. Main Plugin Class (`eeRSCF_Class`)

**Primary Properties:**
- `$formSettings`: Configuration array for form behavior
- `$thePost`: Sanitized form submission data
- `$log`: Multi-level logging system (notices, warnings, errors)
- `$theFormOutput`: Generated HTML for form display

**Key Methods:**
- `eeRSCF_formDisplay()`: Generates secure HTML form
- `eeRSCF_SendEmail()`: Processes and sends contact emails
- `eeRSCF_formSpamCheck()`: Multi-stage spam detection
- `eeRSCF_PostProcess()`: Data sanitization and validation

### 2. Helper Class (`eeHelper_Class`)

Provides utility functions for:
- String manipulation (slug creation/conversion)
- Admin notifications and messaging
- Email delivery assistance

### 3. Security & Spam Prevention System

#### Multi-Layer Protection:
1. **Honeypot Fields**: Hidden form fields to catch bots
2. **Language Detection**: Optional English-only filtering
3. **Content Analysis**: Detects suspicious patterns and duplicated content
4. **Word Filtering**: Local and remote spam word lists
5. **Form Tampering Detection**: Validates form integrity

#### Spam Detection Features:
- **Remote Word List**: Fetches updated spam phrases from Cloudflare Worker
- **Local Custom Words**: Admin-defined blocked terms
- **Fishy Content Detection**: HTML tags, malicious encoding, duplicate entries
- **Attack Notifications**: Optional email alerts for spam attempts

### 4. Form Field Management

#### Configurable Fields:
- First Name, Last Name, Business Name
- Address fields (Address 1, Address 2, City, State, ZIP)
- Phone, Website, Other
- Subject (with smart email subject line detection)
- Message (required)
- File Attachments (optional, with format/size restrictions)

#### Field Configuration:
Each field supports:
- **Show/Hide**: Toggle field visibility
- **Required/Optional**: Validation requirements
- **Custom Labels**: Personalized field names

### 5. File Upload System

**Security Features:**
- Extension validation against whitelist
- File size limits (configurable MB)
- Directory traversal protection
- Secure file storage

**Supported Formats:**
Images, documents, audio, video, archives (fully configurable)

### 6. Email Delivery System

#### Delivery Methods:
- **WordPress Mail**: Built-in `wp_mail()` function
- **SMTP Support**: External SMTP server configuration (planned)

#### Email Features:
- **Smart Headers**: From, Reply-To, CC, BCC support
- **Subject Detection**: Automatic subject line parsing from form
- **Attachment Handling**: Secure file attachment delivery
- **HTML Encoding**: Proper character encoding and sanitization

### 7. Admin Interface

#### Tabbed Settings:
1. **Contact Form**: Field configuration, email destinations
2. **Attachments**: File upload settings and restrictions
3. **Spam Prevention**: Multi-layered protection configuration
4. **Email Settings**: SMTP and delivery optimization

#### User Experience:
- Contextual help and notes
- Real-time validation feedback
- One-click enable/disable options
- Visual status indicators

## Technical Implementation

### Initialization Flow
1. **Plugin Setup** (`eeRSCF_Setup()`):
   - Security nonce generation
   - Class instantiation
   - Database version checking
   - Settings migration/installation

2. **Form Display** (`[rock-solid-contact]` shortcode):
   - Settings retrieval
   - Dynamic form generation
   - JavaScript enhancement injection

3. **Form Processing** (`eeRSCF_ContactProcess()`):
   - Nonce verification
   - Spam filtering
   - Data sanitization
   - Email delivery
   - Redirect handling

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
- **Efficient Database Queries**: Minimal option calls
- **Smart Caching**: Settings cached during request lifecycle
- **Lazy Loading**: Heavy operations only when needed

## Development Mode

When `eeRSCF_DevMode` is enabled:
- Detailed logging to browser console
- Debug information display
- Settings array visualization
- Extended error reporting

## Hooks & Filters

### Actions:
- `init`: Plugin initialization
- `wp_loaded`: Form processing
- `admin_menu`: Admin interface setup
- `wp_enqueue_scripts`: Frontend asset loading
- `admin_enqueue_scripts`: Admin asset loading

### Shortcodes:
- `[rock-solid-contact]`: Primary form display

## Migration & Updates

The plugin includes automated migration from previous versions:
- Settings array restructuring
- File format normalization
- Option namespace cleanup
- Backward compatibility maintenance

## Error Handling

Comprehensive logging system with four levels:
- **Notices**: Informational messages
- **Messages**: Success confirmations
- **Warnings**: Non-critical issues
- **Errors**: Critical problems requiring attention

## Best Practices

### For Developers:
- Use provided helper functions for data manipulation
- Follow WordPress coding standards
- Implement proper nonce verification
- Utilize the logging system for debugging

### For Site Administrators:
- Configure SMTP for improved deliverability
- Regularly review spam settings effectiveness
- Monitor file upload restrictions
- Test email delivery after configuration changes

---

*This plugin represents a practical approach to WordPress contact forms, prioritizing security and reliability over feature complexity.*