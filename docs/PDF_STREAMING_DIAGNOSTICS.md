# PDF Streaming Diagnostics Guide

This guide explains how to use the diagnostic tools for troubleshooting PDF streaming issues, particularly 403 Forbidden errors.

**Requirements Addressed:** 5.1, 5.2, 5.3

## Overview

The PDF streaming diagnostic tools help identify and troubleshoot issues in the signed URL generation, validation, and streaming pipeline. These tools test various scenarios including:

- Signed URL generation and validation
- URL encoding preservation
- Range request support
- Session independence
- Error handling
- Performance metrics

## Available Diagnostic Tools

### 1. CLI Diagnostic Script

**File:** `diagnose_signed_url.php`

**Purpose:** Command-line diagnostic tool for testing signed URL generation and validation.

**Usage:**
```bash
# Test with first PDF content found
php diagnose_signed_url.php

# Test with specific content ID
php diagnose_signed_url.php 123
```

**What it tests:**
- ✓ Signed URL generation
- ✓ URL structure validation
- ✓ Signature validation
- ✓ PDF.js fetch simulation
- ✓ Route configuration
- ✓ File access

**Output:** Colored console output with detailed diagnostics and recommendations.

---

### 2. Web-Based Diagnostic Tool

**File:** `public/diagnose_pdf_signed_url.php`

**Purpose:** Browser-based diagnostic tool with HTML interface.

**Usage:**
1. Access via browser: `http://your-domain.com/diagnose_pdf_signed_url.php`
2. Optionally specify content ID: `?content_id=123`

**What it tests:**
- ✓ Signed URL generation
- ✓ URL structure validation
- ✓ Signature validation
- ✓ Route configuration
- ✓ File access
- ✓ Visual summary with color-coded results

**Output:** HTML page with tables, color-coded status indicators, and recommendations.

---

### 3. Artisan Command

**File:** `app/Console/Commands/DiagnosePdfSignedUrl.php`

**Purpose:** Laravel Artisan command for diagnostics.

**Usage:**
```bash
# Test with first PDF content found
php artisan pdf:diagnose-signed-url

# Test with specific content ID
php artisan pdf:diagnose-signed-url 123
```

**What it tests:**
- ✓ Signed URL generation
- ✓ URL structure validation
- ✓ Signature validation
- ✓ PDF.js fetch simulation
- ✓ Route configuration
- ✓ File access

**Output:** Formatted console output with tables and color-coded status.

---

### 4. Streaming Diagnostic Script

**File:** `diagnose_pdf_streaming.php`

**Purpose:** Tests actual PDF streaming functionality with various scenarios.

**Usage:**
```bash
# Test with first PDF content found
php diagnose_pdf_streaming.php

# Test with specific content ID
php diagnose_pdf_streaming.php 123
```

**What it tests:**
- ✓ Basic PDF streaming
- ✓ Range request support (multiple formats)
- ✓ Expired signature handling
- ✓ Invalid signature handling
- ✓ Multiple requests (idempotence)
- ✓ Session independence
- ✓ File size and performance metrics

**Output:** Detailed console output with test results for each scenario.

---

### 5. Automated Test Suite

**File:** `tests/Feature/PdfStreamingDiagnosticTest.php`

**Purpose:** PHPUnit test suite for automated validation.

**Usage:**
```bash
# Run all diagnostic tests
php artisan test --filter PdfStreamingDiagnosticTest

# Run specific test
php artisan test --filter test_signed_url_generation
```

**What it tests:**
- ✓ Signed URL generation
- ✓ Signature validation
- ✓ Basic PDF streaming
- ✓ Range request support
- ✓ Expired signature handling
- ✓ Invalid signature handling
- ✓ Multiple requests (idempotence)
- ✓ Session independence
- ✓ URL encoding preservation
- ✓ Content-Type headers
- ✓ Accept-Ranges headers
- ✓ Various range formats
- ✓ PDF content validity
- ✓ Error response format
- ✓ Route configuration
- ✓ File access
- ✓ Streaming performance
- ✓ Diagnostic logging

**Output:** PHPUnit test results with pass/fail status.

---

## Common Issues and Solutions

### Issue 1: 403 Forbidden Error

**Symptoms:**
- PDF.js displays "Failed to load PDF"
- Browser console shows 403 error
- Signed URL appears valid

**Diagnostic Steps:**
1. Run `php diagnose_signed_url.php [content_id]`
2. Check "Signature Validation" section
3. Look for "Signature validation FAILED"

**Common Causes:**
- URL is not absolute (missing host)
- Signature parameter is missing
- Expires parameter is missing or expired
- Route is missing 'signed' middleware
- APP_KEY has changed

**Solutions:**
- Ensure `APP_URL` is set correctly in `.env`
- Use `URL::temporarySignedRoute()` for URL generation
- Add 'signed' middleware to route
- Verify APP_KEY is consistent

---

### Issue 2: URL Encoding Problems

**Symptoms:**
- Signature validation fails after PDF.js processes URL
- URL appears to be double-encoded

**Diagnostic Steps:**
1. Run `php diagnose_signed_url.php [content_id]`
2. Check "Simulating PDF.js Fetch Request" section
3. Look for encoding-related warnings

**Common Causes:**
- URL is being encoded multiple times
- Special characters in URL parameters
- PDF.js modifying the URL

**Solutions:**
- Pass URL to PDF.js without modification
- Don't encode the URL before passing to PDF.js
- Ensure consistent encoding throughout pipeline

---

### Issue 3: Range Requests Not Working

**Symptoms:**
- PDF loads slowly or not at all
- PDF.js makes full file requests instead of ranges
- Large PDFs fail to load

**Diagnostic Steps:**
1. Run `php diagnose_pdf_streaming.php [content_id]`
2. Check "Testing Range Request Support" section
3. Look for range request failures

**Common Causes:**
- Accept-Ranges header not set
- Range header not being parsed
- Controller not supporting partial content

**Solutions:**
- Set `Accept-Ranges: bytes` header
- Implement range request parsing in controller
- Return 206 Partial Content for range requests

---

### Issue 4: Session Dependency

**Symptoms:**
- PDF loads when logged in but fails when logged out
- Signed URL requires active session

**Diagnostic Steps:**
1. Run `php diagnose_pdf_streaming.php [content_id]`
2. Check "Testing Session Independence" section
3. Look for session-related failures

**Common Causes:**
- Auth middleware applied to route
- Session middleware interfering
- Controller checking authentication

**Solutions:**
- Remove auth middleware from signed route
- Validate only signature, not session
- Use signed URL as sole authentication

---

## Interpreting Diagnostic Output

### Status Indicators

- **✓ (Green)**: Test passed, no issues
- **✗ (Red)**: Test failed, critical issue
- **⚠ (Yellow)**: Warning, potential issue
- **ℹ (Blue)**: Informational message

### Key Sections to Check

1. **URL Validation**
   - Ensure URL is absolute (has scheme and host)
   - Verify signature and expires parameters exist
   - Check expiration time is >= 5 minutes

2. **Signature Validation**
   - Should show "Signature validation PASSED"
   - If failed, check diagnostics for specific reason

3. **Route Configuration**
   - Route 'secure.pdf.stream' should exist
   - 'signed' middleware should be applied
   - No conflicting middleware (auth, csrf)

4. **File Access**
   - File should exist on specified disk
   - File should be readable
   - File size should be > 0

---

## Testing Workflow

### For Development

1. **Initial Setup:**
   ```bash
   php artisan pdf:diagnose-signed-url
   ```
   Verify basic configuration is correct.

2. **Test Streaming:**
   ```bash
   php diagnose_pdf_streaming.php
   ```
   Verify all streaming scenarios work.

3. **Run Automated Tests:**
   ```bash
   php artisan test --filter PdfStreamingDiagnosticTest
   ```
   Ensure all tests pass.

### For Troubleshooting

1. **Reproduce Issue:**
   - Note the specific error (403, timeout, etc.)
   - Note the content ID if known

2. **Run Diagnostics:**
   ```bash
   php diagnose_signed_url.php [content_id]
   ```
   Identify the root cause.

3. **Test Specific Scenario:**
   ```bash
   php diagnose_pdf_streaming.php [content_id]
   ```
   Test the specific failing scenario.

4. **Verify Fix:**
   - Re-run diagnostics
   - Test in browser
   - Run automated tests

### For Production Monitoring

1. **Regular Health Checks:**
   ```bash
   php artisan pdf:diagnose-signed-url
   ```
   Run periodically to verify system health.

2. **Log Analysis:**
   - Check `storage/logs/laravel.log`
   - Look for PDF streaming errors
   - Monitor 403 error frequency

3. **Performance Monitoring:**
   ```bash
   php diagnose_pdf_streaming.php
   ```
   Check streaming performance metrics.

---

## Requirements Validation

The diagnostic tools validate the following requirements:

### Requirement 1: Signed URL Validation
- **1.1**: Signature remains valid during PDF.js fetch
- **1.2**: Signature middleware validates correctly
- **1.3**: Specific failure reasons are logged
- **1.4**: Multiple requests succeed with same URL

### Requirement 2: URL Encoding Compatibility
- **2.1**: URL encoding is preserved
- **2.2**: Special characters don't break signature
- **2.3**: PDF.js encoding doesn't break validation

### Requirement 3: HTTP Headers and CORS
- **3.1**: Content-Type is application/pdf
- **3.2**: CORS headers are included if needed
- **3.3**: Requests are not blocked
- **3.4**: Range requests are supported

### Requirement 4: Authentication and Session Handling
- **4.1**: Signed URLs work without session

### Requirement 5: Error Logging and Diagnostics
- **5.1**: 403 errors are logged with details
- **5.2**: Signature failures are logged
- **5.3**: Actionable error messages provided
- **5.4**: Successful requests are logged

### Requirement 6: PDF.js Integration
- **6.1**: Response is parseable by PDF.js
- **6.2**: Multiple range requests work
- **6.3**: Error responses are interpretable
- **6.4**: Meaningful error messages displayed

### Requirement 7: Signed URL Generation
- **7.1**: Expiration is >= 5 minutes
- **7.2**: All necessary parameters included
- **7.3**: Correct route name used
- **7.4**: URLs are PDF.js compatible

---

## Additional Resources

- **Requirements:** `.kiro/specs/pdf-stream-403-fix/requirements.md`
- **Design:** `.kiro/specs/pdf-stream-403-fix/design.md`
- **Tasks:** `.kiro/specs/pdf-stream-403-fix/tasks.md`
- **Laravel Logs:** `storage/logs/laravel.log`

---

## Support

If diagnostic tools reveal issues that you cannot resolve:

1. Check the recommendations in the diagnostic output
2. Review the requirements and design documents
3. Check Laravel logs for detailed error messages
4. Verify environment configuration (.env file)
5. Ensure APP_KEY and APP_URL are set correctly

---

## Maintenance

### Updating Diagnostic Tools

When adding new features or requirements:

1. Update diagnostic scripts to test new functionality
2. Add new test cases to `PdfStreamingDiagnosticTest.php`
3. Update this documentation
4. Verify all diagnostics still pass

### Regular Testing

Run diagnostics regularly to ensure system health:

- **Daily:** Automated test suite
- **Weekly:** CLI diagnostic script
- **Monthly:** Full streaming diagnostic script
- **After Changes:** All diagnostic tools

---

*Last Updated: 2024*
*Requirements: 5.1, 5.2, 5.3*
