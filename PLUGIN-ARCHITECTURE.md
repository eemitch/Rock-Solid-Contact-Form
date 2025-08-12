# Rock Solid Contact Form - Plugin Architecture

## Overview

Rock Solid Contact Form has been reorganized from a single monolithic class into a focused 4-class architecture for improved maintainability, security, and code organization.

## Architecture Version: 2.1.2

### Core Classes

#### 1. eeRSCF_Class (Main Display Class)
**File:** `includes/ee-rock-solid-class.php`
**Purpose:** Frontend form display and rendering
**Responsibilities:**
- Form HTML generation
- Field visibility and validation rules
- Form styling and layout
- Input sanitization and security
- Frontend display logic

**Key Methods:**
- `eeRSCF_FrontEnd()` - Main form display
- `eeRSCF_SecureSanitize()` - Input sanitization (public)
- `eeRSCF_SecurityCheck()` - Security validation (public)

#### 2. eeRSCF_MailClass (Email Processing Class)
**File:** `includes/ee-mail-class.php`
**Purpose:** Email processing and delivery
**Responsibilities:**
- Email composition and sending
- SMTP configuration and management
- Form data processing for email
- File attachment handling
- Post-submission processing

**Key Methods:**
- `eeRSCF_SendEmail()` - Main email sending
- `eeRSCF_PostProcess()` - Form data processing
- `eeRSCF_configure_smtp()` - SMTP setup
- `syncSettings()` - Settings synchronization

#### 3. eeRSCF_AdminClass (Backend Administration Class)
**File:** `includes/ee-admin-class.php`
**Purpose:** WordPress admin interface and settings
**Responsibilities:**
- Admin settings processing
- Form configuration management
- Spam filtering settings
- Email configuration settings
- File upload settings

**Key Methods:**
- `eeRSCF_AdminSettingsProcess()` - Settings form processing

#### 4. eeRSCF_FileClass (File Operations Class)
**File:** `includes/ee-file-class.php`
**Purpose:** File upload and remote content handling
**Responsibilities:**
- Secure file uploads
- Remote content fetching
- File validation and security
- WordPress file API integration

**Key Methods:**
- `handle_upload()` - File upload processing
- `get_remote_content()` - Secure remote fetching

### Initialization Flow

1. **Settings Loading** (`rock-solid-contact-form.php`)
   - Load saved settings from WordPress options
   - Apply default values for missing settings
   - Ensure critical email fields are populated

2. **Class Instantiation**
   - Main class: `$eeRSCF = new eeRSCF_Class()`
   - Mail class: `$eeMailClass = new eeRSCF_MailClass()`
   - Admin class: `$eeAdminClass = new eeRSCF_AdminClass($eeRSCF)`
   - File class: `$eeFileClass = new eeRSCF_FileClass()`

3. **Settings Synchronization**
   - Mail class syncs settings via `syncSettings()` method
   - Admin class receives main class reference for settings access

4. **WordPress Integration**
   - Action hooks registered for admin interface
   - Shortcode registered for frontend display
   - Form processing hooks registered

### Security Features

#### Input Sanitization
- All user inputs sanitized using WordPress functions
- Custom sanitization methods in main class
- HTML encoding for output security

#### Spam Protection
- Honeypot field implementation
- Bot detection and blocking
- Common spam word filtering
- Custom blocked word lists
- English-only content filtering

#### File Upload Security
- File type validation
- Size limit enforcement
- Secure upload directory handling
- WordPress file API integration

### Database Schema

#### Settings Storage
- **Option:** `eeRSCF_Settings` - Main form settings array
- **Option:** `eeRSCF_Confirm` - Confirmation page URL

#### Settings Structure
```php
$formSettings = array(
    'formName' => 'Contact Form',
    'to' => 'admin@example.com',
    'cc' => '',
    'bcc' => '',
    'email' => 'admin@example.com',
    'emailMode' => 'PHP', // or 'SMTP'
    'emailFormat' => 'HTML', // or 'TEXT'
    'fields' => array(
        // Field configuration arrays
    ),
    'spamBlock' => 'YES',
    // Additional spam and email settings
);
```

### Testing Framework

#### Test Structure
- **Frontend Tests:** Form display and submission validation
- **Backend Tests:** Admin interface functionality
- **Security Tests:** Spam protection and input validation

#### Test Files
- `playwright/tests/1-front/01-front-page-loads.spec.js`
- `playwright/tests/1-front/02-front-submit-form.spec.js`
- `playwright/tests/1-front/03-front-security-tests.spec.js`
- `playwright/tests/2-back/01-back-edit-message-settings.spec.js`

### Class Communication

#### Global Variables
- `$eeRSCF` - Main class instance
- `$eeMailClass` - Mail processing instance
- `$eeAdminClass` - Admin interface instance
- `$eeFileClass` - File operations instance
- `$eeHelper` - Helper utilities instance

#### Settings Synchronization
The mail and admin classes maintain synchronized copies of form settings through:
- Constructor injection (admin class)
- `syncSettings()` method (mail class)
- Global variable access for shared data

### Development Guidelines

#### Adding New Features
1. Identify the appropriate class for the feature
2. Add public methods for cross-class communication
3. Update settings synchronization if needed
4. Write corresponding tests
5. Update documentation

#### Security Considerations
- Always sanitize user inputs
- Use WordPress nonces for form security
- Validate file uploads thoroughly
- Follow WordPress coding standards

### Version History

#### 2.1.2 - Class Reorganization
- Split monolithic class into 4 focused classes
- Improved security with dedicated sanitization methods
- Enhanced maintainability and code organization
- Comprehensive test coverage implementation
- Fixed class naming consistency issues

### Support and Maintenance

For development questions or contributions, refer to:
- Security assessments in `SECURITY-ASSESSMENT-*.md` files
- Test results in `playwright-results/` directory
- Error logs in WordPress debug.log

---
*This documentation reflects the current architecture as of version 2.1.2*
