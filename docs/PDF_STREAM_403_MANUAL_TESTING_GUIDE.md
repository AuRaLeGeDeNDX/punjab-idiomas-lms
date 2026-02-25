# PDF Stream 403 Fix - Manual Testing Guide

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Browser Testing Matrix](#browser-testing-matrix)
4. [Testing Procedures](#testing-procedures)
5. [PDF File Test Cases](#pdf-file-test-cases)
6. [Expected Behavior Reference](#expected-behavior-reference)
7. [Error Scenarios](#error-scenarios)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Test Results Template](#test-results-template)

---

## Overview

This guide provides comprehensive manual testing procedures for the PDF Stream 403 Fix implementation. The fix addresses 403 Forbidden errors that occur when PDF.js attempts to stream PDF files through Laravel's signed URL system.

### Key Features to Test

- **Signed URL Generation**: Correct generation of temporary signed URLs
- **Signature Validation**: Proper validation without false 403 errors
- **URL Encoding**: Preservation of URL encoding through PDF.js
- **Range Requests**: Support for HTTP range requests
- **Session Independence**: Access without requiring active sessions
- **Error Logging**: Comprehensive logging of failures
- **PDF.js Integration**: Seamless integration with PDF.js viewer

### Testing Scope

This guide covers:
- ✅ Chrome (latest version)
- ✅ Firefox (latest version)
- ✅ Edge (latest version)
- ✅ Various PDF file types and sizes
- ✅ All signed URL scenarios
- ✅ Error handling and edge cases

---

## Prerequisites

### Required Setup

1. **Test Environment**
   - Laravel application running locally or on test server
   - Database with proper migrations
   - Sample PDF files in protected storage
   - Diagnostic tools available (see `PDF_STREAMING_DIAGNOSTICS.md`)

2. **Test User Account**
   - Active user account with authentication
   - Enrolled in a course with PDF content
   - Known credentials for login

3. **Browser Requirements**
   - **Chrome**: Version 90 or later
   - **Firefox**: Version 88 or later
   - **Edge**: Version 90 or later
   - JavaScript enabled
   - Cookies enabled
   - No ad blockers or security extensions that might interfere

4. **Test PDF Files**
   - Small PDF (< 1 MB): `test-small.pdf`
   - Medium PDF (1-10 MB): `test-medium.pdf`
   - Large PDF (> 10 MB): `test-large.pdf`
   - Multi-page PDF (10+ pages): `test-multipage.pdf`
   - PDF with special characters in filename: `test-special-chars-éàü.pdf`

### Recommended Tools

- Browser Developer Tools (for console and network inspection)
- Network tab (for monitoring requests and headers)
- Diagnostic scripts (see `PDF_STREAMING_DIAGNOSTICS.md`)
- Screenshot tool (for documenting issues)
- Text editor (for viewing logs)

### Environment Configuration

Verify these settings in `.env`:
```env
APP_URL=http://localhost:8000  # Must be absolute URL
APP_KEY=base64:...              # Must be set and consistent
SECURE_PDF_TOKEN_EXPIRATION=60  # Token expiration in minutes
```

---

## Browser Testing Matrix

### Chrome Testing

| Feature | Test Case | Expected Result | Status |
|---------|-----------|-----------------|--------|
| Basic Load | Access PDF viewer | PDF loads without 403 error | ⬜ |
| Signed URL | Check URL format | Contains signature and expires params | ⬜ |
| Range Requests | Monitor network tab | Multiple range requests succeed | ⬜ |
| URL Encoding | PDF with special chars | Signature remains valid | ⬜ |
| Session Independence | Clear cookies, reload | PDF still loads with valid URL | ⬜ |
| Expired URL | Wait for expiration | 403 error with proper message | ⬜ |
| Invalid Signature | Tamper with URL | 403 error with proper message | ⬜ |
| Multiple Requests | Reload page multiple times | All requests succeed | ⬜ |

### Firefox Testing

| Feature | Test Case | Expected Result | Status |
|---------|-----------|-----------------|--------|
| Basic Load | Access PDF viewer | PDF loads without 403 error | ⬜ |
| Signed URL | Check URL format | Contains signature and expires params | ⬜ |
| Range Requests | Monitor network tab | Multiple range requests succeed | ⬜ |
| URL Encoding | PDF with special chars | Signature remains valid | ⬜ |
| Session Independence | Clear cookies, reload | PDF still loads with valid URL | ⬜ |
| Expired URL | Wait for expiration | 403 error with proper message | ⬜ |
| Invalid Signature | Tamper with URL | 403 error with proper message | ⬜ |
| Multiple Requests | Reload page multiple times | All requests succeed | ⬜ |

### Edge Testing

| Feature | Test Case | Expected Result | Status |
|---------|-----------|-----------------|--------|
| Basic Load | Access PDF viewer | PDF loads without 403 error | ⬜ |
| Signed URL | Check URL format | Contains signature and expires params | ⬜ |
| Range Requests | Monitor network tab | Multiple range requests succeed | ⬜ |
| URL Encoding | PDF with special chars | Signature remains valid | ⬜ |
| Session Independence | Clear cookies, reload | PDF still loads with valid URL | ⬜ |
| Expired URL | Wait for expiration | 403 error with proper message | ⬜ |
| Invalid Signature | Tamper with URL | 403 error with proper message | ⬜ |
| Multiple Requests | Reload page multiple times | All requests succeed | ⬜ |

---

## Testing Procedures

### Test 1: Basic PDF Streaming

**Objective**: Verify PDF loads successfully without 403 errors.

**Requirements Tested**: 1.1, 1.2, 6.1

**Steps**:
1. Log in to the application with test user credentials
2. Navigate to a course with PDF content
3. Click on a PDF content item to open the secure viewer
4. Observe the PDF loading process
5. Check browser console for errors
6. Check network tab for request status

**Expected Results**:
- ✅ PDF viewer page loads successfully
- ✅ PDF content renders within 3 seconds
- ✅ No 403 Forbidden errors in network tab
- ✅ No JavaScript errors in console
- ✅ PDF displays correctly with all pages accessible

**Pass Criteria**: PDF loads without any 403 errors

**Failure Actions**: 
- Run diagnostic script: `php diagnose_signed_url.php [content_id]`
- Check Laravel logs: `storage/logs/laravel.log`
- Verify APP_URL is set correctly in `.env`
- Check signed URL format in network tab

---

### Test 2: Signed URL Validation

**Objective**: Verify signed URLs are generated correctly and validate properly.

**Requirements Tested**: 1.1, 1.2, 7.1, 7.2, 7.3

**Steps**:
1. Access a PDF in the secure viewer
2. Open browser DevTools → Network tab
3. Find the PDF stream request (to `/secure-pdf/stream/{content}`)
4. Copy the full URL
5. Examine the URL parameters
6. Verify signature and expires parameters exist
7. Check expiration time is at least 5 minutes in the future

**Expected Results**:
- ✅ URL is absolute (includes scheme and host)
- ✅ URL contains `signature` parameter
- ✅ URL contains `expires` parameter
- ✅ Expiration time is >= 5 minutes (300 seconds) from generation
- ✅ Signature is a valid hash (64 characters)
- ✅ Request succeeds with 200 or 206 status
- ✅ Response Content-Type is `application/pdf`

**URL Format Example**:
```
https://your-domain.com/secure-pdf/stream/123?expires=1234567890&signature=abc123...
```

**Pass Criteria**: All URL components present and valid

**Failure Actions**:
- Run: `php artisan pdf:diagnose-signed-url [content_id]`
- Check SecurePdfController URL generation code
- Verify `URL::temporarySignedRoute()` is used
- Check APP_URL in `.env` is absolute

---

### Test 3: URL Encoding Preservation

**Objective**: Verify URL encoding is preserved through PDF.js processing.

**Requirements Tested**: 2.1, 2.2, 2.3

**Steps**:
1. Create or access a PDF with special characters in the filename
2. Examples: `test-éàü.pdf`, `test (copy).pdf`, `test&file.pdf`
3. Access the PDF in the secure viewer
4. Monitor network tab for the stream request
5. Verify the request succeeds
6. Check console for encoding-related errors

**Expected Results**:
- ✅ PDF with special characters loads successfully
- ✅ No 403 errors due to encoding issues
- ✅ Signature remains valid after PDF.js processes URL
- ✅ No double-encoding issues
- ✅ Special characters in URL are properly encoded

**Pass Criteria**: PDFs with special characters load without signature validation failures

**Failure Actions**:
- Check if URL is being encoded multiple times
- Verify PDF.js receives URL without modification
- Run: `php diagnose_pdf_streaming.php [content_id]`
- Check Laravel logs for encoding-related errors

---

### Test 4: Range Request Support

**Objective**: Verify HTTP range requests work correctly for efficient PDF loading.

**Requirements Tested**: 3.4, 6.2

**Steps**:
1. Access a large PDF (> 10 MB) in the secure viewer
2. Open browser DevTools → Network tab
3. Observe the PDF stream requests
4. Look for multiple requests with Range headers
5. Check response status codes
6. Verify Content-Range headers in responses

**Expected Results**:
- ✅ Multiple range requests are made (not just one full request)
- ✅ Requests include `Range: bytes=X-Y` header
- ✅ Responses have 206 Partial Content status
- ✅ Responses include `Content-Range: bytes X-Y/Total` header
- ✅ Responses include `Accept-Ranges: bytes` header
- ✅ All range requests succeed with same signed URL
- ✅ PDF loads progressively (pages render as data arrives)

**Range Request Example**:
```
Request:
  Range: bytes=0-65535

Response:
  Status: 206 Partial Content
  Content-Range: bytes 0-65535/1048576
  Accept-Ranges: bytes
  Content-Type: application/pdf
```

**Pass Criteria**: Multiple range requests succeed with 206 status

**Failure Actions**:
- Check if Accept-Ranges header is set
- Verify SecurePdfController handles Range header
- Run: `php diagnose_pdf_streaming.php [content_id]`
- Check if controller returns 206 for range requests

---

### Test 5: Session Independence

**Objective**: Verify signed URLs work without requiring active sessions.

**Requirements Tested**: 4.1, 4.2

**Steps**:
1. Access a PDF in the secure viewer
2. Copy the full viewer URL from address bar
3. Open browser DevTools → Application tab → Cookies
4. Delete all cookies for the domain
5. Paste the copied URL and press Enter
6. Observe if PDF loads

**Expected Results**:
- ✅ PDF loads successfully without cookies
- ✅ No redirect to login page
- ✅ Signature validation succeeds
- ✅ No session-related errors in console
- ✅ Access is granted based on signature alone

**Pass Criteria**: PDF loads without requiring authentication cookies

**Failure Actions**:
- Check if auth middleware is applied to signed route
- Verify route only uses 'signed' middleware
- Check SecurePdfController doesn't check session
- Run: `php diagnose_pdf_streaming.php [content_id]`

---

### Test 6: Expired Signature Handling

**Objective**: Verify expired signatures are properly rejected.

**Requirements Tested**: 1.1, 1.3, 5.1, 5.2

**Steps**:
1. Temporarily change token expiration to 1 minute in config
2. Access a PDF in the secure viewer
3. Copy the viewer URL
4. Wait for 2 minutes (past expiration)
5. Paste the URL and press Enter
6. Observe the error response
7. Check Laravel logs

**Expected Results**:
- ✅ 403 Forbidden error is returned
- ✅ Error message indicates expired signature
- ✅ Error is logged to Laravel logs with details
- ✅ Log includes URL, signature, and expiration time
- ✅ Log includes failure reason: "expired"
- ✅ User sees meaningful error message

**Error Message Example**:
```
403 Forbidden
Invalid or expired viewing session. Please request a new link.
```

**Pass Criteria**: Expired signatures are rejected with proper error

**Failure Actions**:
- Check signed middleware is applied
- Verify expiration time is checked
- Check Laravel logs for error details
- Verify error logging is comprehensive

---

### Test 7: Invalid Signature Handling

**Objective**: Verify tampered signatures are properly rejected.

**Requirements Tested**: 1.2, 1.3, 5.1, 5.2

**Steps**:
1. Access a PDF in the secure viewer
2. Copy the full URL from network tab
3. Modify the signature parameter (change a few characters)
4. Paste the modified URL in address bar
5. Press Enter
6. Observe the error response
7. Check Laravel logs

**Expected Results**:
- ✅ 403 Forbidden error is returned
- ✅ Error message indicates invalid signature
- ✅ Error is logged to Laravel logs with details
- ✅ Log includes URL, expected vs actual signature
- ✅ Log includes failure reason: "invalid_signature"
- ✅ User sees meaningful error message

**Pass Criteria**: Invalid signatures are rejected with proper error

**Failure Actions**:
- Check signed middleware validation logic
- Verify signature comparison is working
- Check Laravel logs for error details
- Verify error logging includes signature details

---

### Test 8: Multiple Requests (Idempotence)

**Objective**: Verify same signed URL can be used multiple times.

**Requirements Tested**: 1.4, 6.2

**Steps**:
1. Access a PDF in the secure viewer
2. Note the URL in the address bar
3. Reload the page (F5) 5 times
4. Observe each load
5. Check network tab for all requests
6. Verify all requests succeed

**Expected Results**:
- ✅ All 5 reloads succeed
- ✅ No 403 errors on any reload
- ✅ Same signed URL works for all requests
- ✅ PDF loads correctly each time
- ✅ No "signature already used" errors

**Pass Criteria**: Same signed URL works for multiple requests

**Failure Actions**:
- Check if signature is being invalidated after use
- Verify no "one-time use" logic exists
- Run: `php diagnose_pdf_streaming.php [content_id]`
- Check Laravel logs for repeated request handling

---

### Test 9: HTTP Headers Verification

**Objective**: Verify correct HTTP headers are sent with PDF responses.

**Requirements Tested**: 3.1, 3.2, 3.4

**Steps**:
1. Access a PDF in the secure viewer
2. Open browser DevTools → Network tab
3. Find the PDF stream request
4. Click on the request to view details
5. Check the Response Headers section
6. Verify all required headers are present

**Expected Response Headers**:
- ✅ `Content-Type: application/pdf`
- ✅ `Accept-Ranges: bytes`
- ✅ `Cache-Control: no-cache, no-store, must-revalidate`
- ✅ `Pragma: no-cache`
- ✅ `X-Content-Type-Options: nosniff`
- ✅ For range requests: `Content-Range: bytes X-Y/Total`
- ✅ For range requests: Status `206 Partial Content`

**Pass Criteria**: All required headers present with correct values

**Failure Actions**:
- Check SecurePdfController stream method
- Verify response headers are set correctly
- Test with curl: `curl -I [PDF_STREAM_URL]`
- Check for middleware interfering with headers

---

### Test 10: Error Logging Verification

**Objective**: Verify comprehensive error logging for debugging.

**Requirements Tested**: 5.1, 5.2, 5.3, 5.4

**Steps**:
1. Clear Laravel logs: `> storage/logs/laravel.log`
2. Perform Test 6 (expired signature)
3. Perform Test 7 (invalid signature)
4. Access a valid PDF successfully
5. Review Laravel logs
6. Verify all events are logged with details

**Expected Log Entries**:

**For Expired Signature**:
```
[ERROR] PDF stream signature validation failed
- url: https://...
- reason: expired
- signature: abc123...
- expires: 1234567890
- ip: 127.0.0.1
- user_agent: Mozilla/5.0...
```

**For Invalid Signature**:
```
[ERROR] PDF stream signature validation failed
- url: https://...
- reason: invalid_signature
- signature: abc123...
- expected_signature: def456...
- ip: 127.0.0.1
```

**For Successful Stream**:
```
[INFO] PDF streamed successfully
- content_id: 123
- url: https://...
- ip: 127.0.0.1
```

**Pass Criteria**: All events logged with comprehensive details

**Failure Actions**:
- Check PdfStreamLogger service exists
- Verify logging is integrated in controller
- Check log file permissions
- Verify LOG_CHANNEL is configured correctly

---

## PDF File Test Cases

### Test Case 1: Small PDF (< 1 MB)

**File**: `test-small.pdf` (e.g., 500 KB, 5 pages)

**Test Steps**:
1. Upload small PDF to content
2. Access in secure viewer
3. Navigate through all pages
4. Check network requests

**Expected Behavior**:
- ✅ Loads quickly (< 1 second)
- ✅ May load entire file in one request
- ✅ All pages render correctly
- ✅ No 403 errors

---

### Test Case 2: Medium PDF (1-10 MB)

**File**: `test-medium.pdf` (e.g., 5 MB, 50 pages)

**Test Steps**:
1. Upload medium PDF to content
2. Access in secure viewer
3. Navigate through pages
4. Check for range requests

**Expected Behavior**:
- ✅ Loads within 2-3 seconds
- ✅ Uses range requests for efficient loading
- ✅ Pages render progressively
- ✅ No 403 errors on range requests

---

### Test Case 3: Large PDF (> 10 MB)

**File**: `test-large.pdf` (e.g., 50 MB, 200 pages)

**Test Steps**:
1. Upload large PDF to content
2. Access in secure viewer
3. Navigate through pages
4. Monitor network tab closely

**Expected Behavior**:
- ✅ Loads progressively (first pages render quickly)
- ✅ Multiple range requests made
- ✅ All range requests succeed with same signed URL
- ✅ No timeout errors
- ✅ No 403 errors on any range request
- ✅ Memory usage remains reasonable

---

### Test Case 4: Multi-Page PDF

**File**: `test-multipage.pdf` (e.g., 100+ pages)

**Test Steps**:
1. Upload multi-page PDF to content
2. Access in secure viewer
3. Navigate to first page
4. Jump to middle page (e.g., page 50)
5. Jump to last page
6. Navigate backwards

**Expected Behavior**:
- ✅ All pages load correctly
- ✅ Page navigation works smoothly
- ✅ Range requests fetch only needed pages
- ✅ No 403 errors when jumping between pages
- ✅ Signed URL remains valid throughout navigation

---

### Test Case 5: PDF with Special Characters

**File**: `test-special-éàü (copy).pdf`

**Test Steps**:
1. Upload PDF with special characters in filename
2. Access in secure viewer
3. Check URL encoding in network tab
4. Verify signature validation

**Expected Behavior**:
- ✅ PDF loads successfully
- ✅ Special characters properly encoded in URL
- ✅ Signature remains valid
- ✅ No encoding-related 403 errors
- ✅ Filename displays correctly

---

### Test Case 6: PDF with Complex Content

**File**: PDF with images, fonts, forms, annotations

**Test Steps**:
1. Upload complex PDF to content
2. Access in secure viewer
3. Verify all content renders
4. Check for rendering errors

**Expected Behavior**:
- ✅ All images render correctly
- ✅ Fonts display properly
- ✅ Forms are visible (if applicable)
- ✅ Annotations display (if applicable)
- ✅ No 403 errors during rendering
- ✅ No missing content

---

## Expected Behavior Reference

### Normal Operation Flow

1. **User Requests PDF Access**
   - User clicks link to PDF content
   - System generates signed URL (valid for 5+ minutes)
   - User redirected to secure PDF viewer

2. **PDF.js Initialization**
   - PDF.js library loads
   - Signed URL passed to PDF.js without modification
   - PDF.js makes fetch request to signed URL

3. **Signed URL Validation**
   - Request hits Laravel route with 'signed' middleware
   - Middleware validates signature and expiration
   - If valid, request proceeds to controller
   - If invalid, 403 error returned with reason

4. **PDF Streaming**
   - Controller checks file exists
   - Sets proper headers (Content-Type, Accept-Ranges, etc.)
   - Handles range requests if present
   - Streams file content
   - Logs successful access

5. **PDF.js Rendering**
   - PDF.js receives PDF data
   - Renders pages on canvas
   - Makes additional range requests as needed
   - All requests use same signed URL
   - All requests succeed

### Signed URL Format

**Correct Format**:
```
https://your-domain.com/secure-pdf/stream/123?expires=1234567890&signature=abc123def456...
```

**Components**:
- **Scheme**: `https://` (absolute URL)
- **Host**: `your-domain.com` (from APP_URL)
- **Path**: `/secure-pdf/stream/123` (route with content ID)
- **Expires**: Unix timestamp (at least 5 minutes in future)
- **Signature**: 64-character hash

**Invalid Formats** (will cause 403):
```
# Missing scheme/host (relative URL)
/secure-pdf/stream/123?expires=...&signature=...

# Missing signature parameter
https://your-domain.com/secure-pdf/stream/123?expires=1234567890

# Missing expires parameter
https://your-domain.com/secure-pdf/stream/123?signature=abc123...

# Expired timestamp
https://your-domain.com/secure-pdf/stream/123?expires=1234567890&signature=...
(where expires is in the past)

# Invalid signature
https://your-domain.com/secure-pdf/stream/123?expires=1234567890&signature=invalid
```

### HTTP Request/Response Examples

**Initial Request**:
```http
GET /secure-pdf/stream/123?expires=1234567890&signature=abc123... HTTP/1.1
Host: your-domain.com
User-Agent: Mozilla/5.0...
Accept: */*
```

**Successful Response (Full File)**:
```http
HTTP/1.1 200 OK
Content-Type: application/pdf
Content-Length: 1048576
Accept-Ranges: bytes
Cache-Control: no-cache, no-store, must-revalidate
Pragma: no-cache
X-Content-Type-Options: nosniff

[PDF binary data]
```

**Successful Response (Range Request)**:
```http
HTTP/1.1 206 Partial Content
Content-Type: application/pdf
Content-Range: bytes 0-65535/1048576
Content-Length: 65536
Accept-Ranges: bytes
Cache-Control: no-cache, no-store, must-revalidate

[PDF binary data chunk]
```

**Error Response (403 Forbidden)**:
```http
HTTP/1.1 403 Forbidden
Content-Type: application/json

{
  "message": "Invalid or expired viewing session",
  "error": "signature_validation_failed",
  "reason": "expired"
}
```

---

## Error Scenarios

### Scenario 1: 403 Forbidden - Expired Signature

**Trigger**: Access URL after expiration time

**Expected Behavior**:
- ✅ 403 Forbidden status
- ✅ Error message: "Invalid or expired viewing session"
- ✅ Logged with reason: "expired"
- ✅ Log includes expiration timestamp
- ✅ User sees meaningful error message

**User Message**:
```
This viewing session has expired. Please request a new link to view this PDF.
```

---

### Scenario 2: 403 Forbidden - Invalid Signature

**Trigger**: Tamper with signature parameter

**Expected Behavior**:
- ✅ 403 Forbidden status
- ✅ Error message: "Invalid or expired viewing session"
- ✅ Logged with reason: "invalid_signature"
- ✅ Log includes expected vs actual signature
- ✅ User sees meaningful error message

**User Message**:
```
This link is invalid or has been tampered with. Please request a new link.
```

---

### Scenario 3: 403 Forbidden - Missing Parameters

**Trigger**: Remove signature or expires parameter from URL

**Expected Behavior**:
- ✅ 403 Forbidden status
- ✅ Error message: "Invalid or expired viewing session"
- ✅ Logged with reason: "missing_parameters"
- ✅ Log includes which parameters are missing
- ✅ User sees meaningful error message

**User Message**:
```
This link is incomplete or invalid. Please request a new link.
```

---

### Scenario 4: 404 Not Found - PDF File Missing

**Trigger**: Content record exists but file is missing from storage

**Expected Behavior**:
- ✅ 404 Not Found status
- ✅ Error message: "PDF file not found"
- ✅ Logged with file path and storage disk
- ✅ User sees meaningful error message

**User Message**:
```
The requested PDF file could not be found. Please contact support.
```

---

### Scenario 5: 403 Forbidden - Insufficient Permissions

**Trigger**: User doesn't have access to the content

**Expected Behavior**:
- ✅ 403 Forbidden status
- ✅ Error message: "You do not have permission to view this PDF"
- ✅ Logged with user ID and content ID
- ✅ User sees meaningful error message

**User Message**:
```
You do not have permission to view this PDF. Please contact your instructor.
```

---

### Scenario 6: Network Error - Connection Timeout

**Trigger**: Slow network or large file

**Expected Behavior**:
- ✅ PDF.js displays loading indicator
- ✅ Request eventually completes or times out
- ✅ User sees error message if timeout occurs
- ✅ Retry option available

**User Message**:
```
The PDF is taking longer than expected to load. Please check your connection and try again.
```

---

## Troubleshooting Guide

### Issue: 403 Forbidden on All PDF Requests

**Symptoms**:
- All PDFs fail with 403 error
- Signed URLs appear valid
- No obvious expiration or tampering

**Diagnostic Steps**:
1. Run: `php artisan pdf:diagnose-signed-url`
2. Check APP_URL in `.env` - must be absolute
3. Check APP_KEY in `.env` - must be set and consistent
4. Verify 'signed' middleware is applied to route
5. Check Laravel logs for specific failure reason

**Common Causes**:
- APP_URL is relative (e.g., `/` instead of `http://localhost:8000`)
- APP_KEY has changed since URL generation
- Signed middleware not applied to route
- Middleware order issue

**Solutions**:
- Set APP_URL to absolute URL: `APP_URL=http://localhost:8000`
- Ensure APP_KEY is consistent
- Add 'signed' middleware to route
- Check middleware order in route definition

---

### Issue: 403 Forbidden After Page Reload

**Symptoms**:
- PDF loads initially
- Fails with 403 on reload
- Signed URL appears unchanged

**Diagnostic Steps**:
1. Check if signature is being invalidated after use
2. Verify no "one-time use" logic exists
3. Run: `php diagnose_pdf_streaming.php [content_id]`
4. Check Laravel logs for repeated request handling

**Common Causes**:
- Signature being marked as "used"
- Cache issue with signature validation
- Session dependency interfering

**Solutions**:
- Remove any "one-time use" logic
- Ensure signed URLs can be used multiple times
- Verify session independence

---

### Issue: 403 Forbidden on Range Requests

**Symptoms**:
- Initial request succeeds
- Subsequent range requests fail with 403
- Large PDFs don't load completely

**Diagnostic Steps**:
1. Check network tab for range request failures
2. Verify same signed URL is used for all requests
3. Check if signature validation handles range requests
4. Run: `php diagnose_pdf_streaming.php [content_id]`

**Common Causes**:
- Signature validation interfering with Range header
- Different URL used for range requests
- Middleware stripping Range header

**Solutions**:
- Ensure signature validation doesn't modify request
- Verify PDF.js uses same URL for all requests
- Check middleware doesn't interfere with Range header

---

### Issue: URL Encoding Breaks Signature

**Symptoms**:
- PDFs with special characters fail
- Signature validation fails
- URL appears double-encoded

**Diagnostic Steps**:
1. Check URL in network tab
2. Look for double-encoding (e.g., `%2520` instead of `%20`)
3. Verify URL is passed to PDF.js without modification
4. Run: `php diagnose_signed_url.php [content_id]`

**Common Causes**:
- URL being encoded multiple times
- PDF.js modifying the URL
- Inconsistent encoding between generation and validation

**Solutions**:
- Pass URL to PDF.js without encoding
- Use consistent encoding throughout pipeline
- Verify `URL::temporarySignedRoute()` generates properly encoded URLs

---

### Issue: Session Dependency

**Symptoms**:
- PDF loads when logged in
- Fails with 403 when cookies cleared
- Requires active session

**Diagnostic Steps**:
1. Clear cookies and try to access PDF
2. Check if auth middleware is applied to route
3. Verify route definition only uses 'signed' middleware
4. Run: `php diagnose_pdf_streaming.php [content_id]`

**Common Causes**:
- Auth middleware applied to signed route
- Controller checking session/authentication
- Session middleware interfering

**Solutions**:
- Remove auth middleware from signed route
- Validate only signature, not session
- Ensure route definition: `->middleware(['signed'])`

---

### Issue: Slow PDF Loading

**Symptoms**:
- PDF takes long time to load
- No 403 errors
- Large files especially slow

**Diagnostic Steps**:
1. Check if range requests are being made
2. Verify Accept-Ranges header is set
3. Monitor network tab for request patterns
4. Check file size and network speed

**Common Causes**:
- Range requests not supported
- Entire file being loaded at once
- Network bandwidth limitations
- Server performance issues

**Solutions**:
- Implement range request support in controller
- Set Accept-Ranges header
- Optimize file streaming
- Consider CDN for large files

---

## Test Results Template

Use this template to document your testing results:

```markdown
# PDF Stream 403 Fix - Test Results

**Test Date**: [Date]
**Tester**: [Name]
**Environment**: [Local/Staging/Production]
**Laravel Version**: [Version]
**PHP Version**: [Version]

## Environment Configuration

- APP_URL: [URL]
- APP_KEY: [Set/Not Set]
- Token Expiration: [Minutes]

## Browser Testing

### Chrome [Version]

| Test | Status | Notes |
|------|--------|-------|
| Test 1: Basic PDF Streaming | ⬜ PASS / ❌ FAIL | |
| Test 2: Signed URL Validation | ⬜ PASS / ❌ FAIL | |
| Test 3: URL Encoding Preservation | ⬜ PASS / ❌ FAIL | |
| Test 4: Range Request Support | ⬜ PASS / ❌ FAIL | |
| Test 5: Session Independence | ⬜ PASS / ❌ FAIL | |
| Test 6: Expired Signature Handling | ⬜ PASS / ❌ FAIL | |
| Test 7: Invalid Signature Handling | ⬜ PASS / ❌ FAIL | |
| Test 8: Multiple Requests | ⬜ PASS / ❌ FAIL | |
| Test 9: HTTP Headers Verification | ⬜ PASS / ❌ FAIL | |
| Test 10: Error Logging Verification | ⬜ PASS / ❌ FAIL | |

### Firefox [Version]

| Test | Status | Notes |
|------|--------|-------|
| Test 1: Basic PDF Streaming | ⬜ PASS / ❌ FAIL | |
| Test 2: Signed URL Validation | ⬜ PASS / ❌ FAIL | |
| Test 3: URL Encoding Preservation | ⬜ PASS / ❌ FAIL | |
| Test 4: Range Request Support | ⬜ PASS / ❌ FAIL | |
| Test 5: Session Independence | ⬜ PASS / ❌ FAIL | |
| Test 6: Expired Signature Handling | ⬜ PASS / ❌ FAIL | |
| Test 7: Invalid Signature Handling | ⬜ PASS / ❌ FAIL | |
| Test 8: Multiple Requests | ⬜ PASS / ❌ FAIL | |
| Test 9: HTTP Headers Verification | ⬜ PASS / ❌ FAIL | |
| Test 10: Error Logging Verification | ⬜ PASS / ❌ FAIL | |

### Edge [Version]

| Test | Status | Notes |
|------|--------|-------|
| Test 1: Basic PDF Streaming | ⬜ PASS / ❌ FAIL | |
| Test 2: Signed URL Validation | ⬜ PASS / ❌ FAIL | |
| Test 3: URL Encoding Preservation | ⬜ PASS / ❌ FAIL | |
| Test 4: Range Request Support | ⬜ PASS / ❌ FAIL | |
| Test 5: Session Independence | ⬜ PASS / ❌ FAIL | |
| Test 6: Expired Signature Handling | ⬜ PASS / ❌ FAIL | |
| Test 7: Invalid Signature Handling | ⬜ PASS / ❌ FAIL | |
| Test 8: Multiple Requests | ⬜ PASS / ❌ FAIL | |
| Test 9: HTTP Headers Verification | ⬜ PASS / ❌ FAIL | |
| Test 10: Error Logging Verification | ⬜ PASS / ❌ FAIL | |
```

## PDF File Testing

### Small PDF (< 1 MB)

| File | Size | Pages | Status | Notes |
|------|------|-------|--------|-------|
| test-small.pdf | [Size] | [Pages] | ⬜ PASS / ❌ FAIL | |

### Medium PDF (1-10 MB)

| File | Size | Pages | Status | Notes |
|------|------|-------|--------|-------|
| test-medium.pdf | [Size] | [Pages] | ⬜ PASS / ❌ FAIL | |

### Large PDF (> 10 MB)

| File | Size | Pages | Status | Notes |
|------|------|-------|--------|-------|
| test-large.pdf | [Size] | [Pages] | ⬜ PASS / ❌ FAIL | |

### Multi-Page PDF

| File | Size | Pages | Status | Notes |
|------|------|-------|--------|-------|
| test-multipage.pdf | [Size] | [Pages] | ⬜ PASS / ❌ FAIL | |

### PDF with Special Characters

| File | Size | Pages | Status | Notes |
|------|------|-------|--------|-------|
| test-special-éàü.pdf | [Size] | [Pages] | ⬜ PASS / ❌ FAIL | |

## Issues Found

### Issue 1: [Title]
- **Severity**: Critical / High / Medium / Low
- **Browser**: [Browser and Version]
- **Description**: [Detailed description]
- **Steps to Reproduce**:
  1. [Step 1]
  2. [Step 2]
  3. [Step 3]
- **Expected**: [Expected behavior]
- **Actual**: [Actual behavior]
- **Screenshots**: [If applicable]
- **Logs**: [Relevant log entries]

### Issue 2: [Title]
[Same format as above]

## Diagnostic Results

### CLI Diagnostic Script
```bash
$ php diagnose_signed_url.php

[Paste output here]
```

### Streaming Diagnostic Script
```bash
$ php diagnose_pdf_streaming.php

[Paste output here]
```

### Automated Test Suite
```bash
$ php artisan test --filter PdfStreamingDiagnosticTest

[Paste output here]
```

## Summary

- **Total Tests**: [Number]
- **Passed**: [Number]
- **Failed**: [Number]
- **Pass Rate**: [Percentage]

### Critical Issues
- [List any critical issues that must be fixed]

### Non-Critical Issues
- [List any minor issues or improvements]

## Recommendations

[Any recommendations for improvements, optimizations, or fixes]

## Sign-off

- **Tester Signature**: _______________
- **Date**: _______________
- **Approved for Production**: ⬜ YES / ⬜ NO / ⬜ WITH CONDITIONS

**Conditions (if applicable)**:
_______________________________________
_______________________________________
```

---

## Quick Reference Commands

### Testing Commands

```bash
# Run CLI diagnostic
php diagnose_signed_url.php [content_id]

# Run streaming diagnostic
php diagnose_pdf_streaming.php [content_id]

# Run Artisan diagnostic
php artisan pdf:diagnose-signed-url [content_id]

# Run automated tests
php artisan test --filter PdfStreamingDiagnosticTest

# View Laravel logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Browser Console Commands

```javascript
// Check PDF.js version
pdfjsLib.version

// Check current PDF document
pdfDoc

// View signed URL
document.querySelector('iframe')?.src || 'No iframe found'

// Monitor fetch requests
performance.getEntriesByType('resource')
  .filter(r => r.name.includes('secure-pdf/stream'))
```

### cURL Testing

```bash
# Test signed URL directly
curl -I "https://your-domain.com/secure-pdf/stream/123?expires=...&signature=..."

# Test range request
curl -H "Range: bytes=0-1023" \
  "https://your-domain.com/secure-pdf/stream/123?expires=...&signature=..."

# Test with verbose output
curl -v "https://your-domain.com/secure-pdf/stream/123?expires=...&signature=..."
```

---

## Appendix: Configuration Reference

### Environment Variables

```env
# Application
APP_URL=http://localhost:8000  # Must be absolute URL
APP_KEY=base64:...              # Must be set and consistent

# PDF Streaming
SECURE_PDF_TOKEN_EXPIRATION=60  # Token expiration in minutes (default: 60)
```

### Route Configuration

```php
// routes/web.php
Route::get('/secure-pdf/stream/{content}', [SecurePdfController::class, 'stream'])
    ->name('secure.pdf.stream')
    ->middleware(['signed']);  // Only signed middleware, no auth
```

### Controller Method

```php
// app/Http/Controllers/SecurePdfController.php
public function stream(Request $request, Content $content): Response
{
    // Signature already validated by middleware
    // Set proper headers
    // Support range requests
    // Stream file content
    // Log successful access
}
```

---

## Related Documentation

- **Diagnostic Tools**: `docs/PDF_STREAMING_DIAGNOSTICS.md`
- **Requirements**: `.kiro/specs/pdf-stream-403-fix/requirements.md`
- **Design**: `.kiro/specs/pdf-stream-403-fix/design.md`
- **Tasks**: `.kiro/specs/pdf-stream-403-fix/tasks.md`
- **Implementation Summary**: `TASK_4.1_PDF_STREAM_IMPLEMENTATION_SUMMARY.md`

---

## Contact and Support

For issues or questions about this testing guide:

- **Laravel Logs**: `storage/logs/laravel.log`
- **Diagnostic Scripts**: See `PDF_STREAMING_DIAGNOSTICS.md`
- **Test Suite**: `tests/Feature/PdfStreamingDiagnosticTest.php`

---

**Document Version**: 1.0  
**Last Updated**: January 2024  
**Requirement**: 6.4 - Manual testing documentation  
**Spec**: pdf-stream-403-fix

