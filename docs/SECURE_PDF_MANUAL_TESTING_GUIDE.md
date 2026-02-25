# Secure PDF Viewer - Manual Testing Guide

## Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Browser Testing Matrix](#browser-testing-matrix)
4. [Testing Procedures](#testing-procedures)
5. [Feature Verification Checklists](#feature-verification-checklists)
6. [Expected Behavior Reference](#expected-behavior-reference)
7. [Troubleshooting Guide](#troubleshooting-guide)
8. [Test Results Template](#test-results-template)

---

## Overview

This guide provides comprehensive manual testing procedures for the Secure PDF Viewer implementation. The secure viewer includes multiple security features designed to prevent unauthorized downloading, printing, and copying of PDF content while providing a professional viewing experience.

### Key Features to Test

- **Session Token Validation**: Time-limited access tokens
- **Custom PDF.js Viewer**: Minimal toolbar without download/print buttons
- **Watermark Overlay**: User identification displayed over PDF content
- **Anti-Download Protections**: Keyboard shortcuts and context menu blocking
- **Access Logging**: Database tracking of all viewing attempts
- **Responsive Design**: Adaptive layout for desktop and tablet devices
- **Security Headers**: Server-side protection against caching and direct access

### Testing Scope

This guide covers:
- ✅ Chrome (latest version)
- ✅ Firefox (latest version)
- ✅ Desktop and tablet viewports
- ✅ All security features
- ✅ Error handling and edge cases

---

## Prerequisites

### Required Setup

1. **Test Environment**
   - Laravel application running locally or on test server
   - Database with `pdf_access_logs` table migrated
   - Sample PDF file in protected storage (`storage/app/protected/sample-test.pdf`)


2. **Test User Account**
   - Active user account with authentication
   - Enrolled in a course with PDF content
   - Known credentials for login

3. **Browser Requirements**
   - **Chrome**: Version 90 or later
   - **Firefox**: Version 88 or later
   - JavaScript enabled
   - Cookies enabled
   - No ad blockers or security extensions that might interfere

4. **Test Route Access**
   - Test route available at: `/secure-pdf/test`
   - This route creates/uses a test PDF and redirects to the secure viewer

### Recommended Tools

- Browser Developer Tools (for console inspection)
- Network tab (for monitoring requests)
- Responsive design mode (for viewport testing)
- Screenshot tool (for documenting issues)

---

## Browser Testing Matrix

### Chrome Testing

| Feature | Test Case | Expected Result | Status |
|---------|-----------|-----------------|--------|
| Viewer Load | Access test route | Viewer loads without errors | ⬜ |
| Watermark | Check overlay visibility | Watermark visible with user info | ⬜ |
| Right-Click | Right-click on PDF | Context menu blocked | ⬜ |
| Ctrl+S | Press Ctrl+S | Alert shown, save blocked | ⬜ |
| Ctrl+P | Press Ctrl+P | Alert shown, print blocked | ⬜ |
| Ctrl+C | Press Ctrl+C | Alert shown, copy blocked | ⬜ |
| Navigation | Use page controls | Pages change smoothly | ⬜ |
| Zoom | Use zoom controls | PDF scales correctly | ⬜ |
| Responsive | Resize window | Layout adapts properly | ⬜ |

### Firefox Testing

| Feature | Test Case | Expected Result | Status |
|---------|-----------|-----------------|--------|
| Viewer Load | Access test route | Viewer loads without errors | ⬜ |
| Watermark | Check overlay visibility | Watermark visible with user info | ⬜ |
| Right-Click | Right-click on PDF | Context menu blocked | ⬜ |
| Ctrl+S | Press Ctrl+S | Alert shown, save blocked | ⬜ |
| Ctrl+P | Press Ctrl+P | Alert shown, print blocked | ⬜ |
| Ctrl+C | Press Ctrl+C | Alert shown, copy blocked | ⬜ |
| Navigation | Use page controls | Pages change smoothly | ⬜ |
| Zoom | Use zoom controls | PDF scales correctly | ⬜ |
| Responsive | Resize window | Layout adapts properly | ⬜ |

---

## Testing Procedures

### Test 1: Initial Viewer Access

**Objective**: Verify the secure PDF viewer loads correctly with valid authentication.

**Steps**:
1. Log in to the application with test user credentials
2. Navigate to `/secure-pdf/test` in your browser
3. Observe the page load and initial rendering

**Expected Results**:
- ✅ Redirected to viewer URL with format: `/secure-pdf/viewer/{content_id}/{token}`
- ✅ Loading indicator appears briefly
- ✅ PDF renders on canvas within 2-3 seconds
- ✅ Custom toolbar displays at top of page
- ✅ Security notice banner shows: "🔒 This document is protected..."
- ✅ No browser console errors

**Pass Criteria**: All expected results achieved

**Failure Actions**: 
- Check browser console for JavaScript errors
- Verify PDF file exists in storage
- Check Laravel logs for server errors

---

### Test 2: Watermark Verification

**Objective**: Verify watermark overlay displays user identification information.

**Steps**:
1. Load the secure PDF viewer (Test 1)
2. Look for semi-transparent text overlaid on the PDF
3. Identify the watermark content
4. Check watermark styling and positioning

**Expected Results**:
- ✅ Watermark overlay visible across entire viewport
- ✅ Watermark displays: `[User Name] | [Email] | [Timestamp]`
- ✅ Optional IP address included if enabled in config
- ✅ Watermark text rotated at -45 degrees
- ✅ Watermark opacity approximately 0.2 (semi-transparent)
- ✅ Watermark font size 24px (readable but not obstructive)
- ✅ Watermark repeats in a grid pattern
- ✅ Watermark stays on top of PDF content (z-index: 9999)
- ✅ Watermark does not interfere with mouse clicks on toolbar

**Pass Criteria**: All watermark elements visible and correctly styled

**Failure Actions**:
- Inspect element in browser DevTools
- Check `#watermark-overlay` div exists
- Verify `.watermark-item` elements are created
- Check console for watermark rendering errors

---

### Test 3: Custom Toolbar Verification

**Objective**: Verify custom toolbar contains only allowed controls.

**Steps**:
1. Load the secure PDF viewer
2. Examine the toolbar at the top of the page
3. Identify all visible buttons and controls
4. Attempt to find any download/print buttons

**Expected Results**:
- ✅ Document title displayed on left side
- ✅ Page navigation controls present:
  - "← Previous" button
  - Page number input field
  - "of [total]" page count
  - "Next →" button
- ✅ Zoom controls present:
  - Zoom percentage dropdown (50%, 75%, 100%, 125%, 150%, 200%, Fit Width)
  - "Zoom In" button
  - "Zoom Out" button
- ✅ **NO download button visible**
- ✅ **NO print button visible**
- ✅ **NO open-in-new-tab button visible**
- ✅ **NO save button visible**
- ✅ Default PDF.js toolbar completely hidden
- ✅ Toolbar has dark gradient background (#1f1f1f to #1a1a1a)
- ✅ Buttons have hover effects (lighter background on hover)

**Pass Criteria**: Only navigation and zoom controls visible, no download/print options

**Failure Actions**:
- Inspect page HTML for hidden PDF.js toolbar elements
- Check CSS rules hiding default toolbar
- Verify custom toolbar ID is `#toolbar`

---


### Test 4: Right-Click Protection

**Objective**: Verify context menu is blocked on PDF viewer.

**Steps**:
1. Load the secure PDF viewer
2. Right-click anywhere on the PDF canvas
3. Right-click on the toolbar
4. Right-click on the watermark overlay
5. Check browser console for warnings

**Expected Results**:
- ✅ Context menu does NOT appear when right-clicking PDF
- ✅ Context menu does NOT appear when right-clicking toolbar
- ✅ Context menu does NOT appear when right-clicking watermark
- ✅ Console warning appears: "Right-click is disabled on this secure viewer"
- ✅ No browser default context menu shown

**Pass Criteria**: Context menu completely blocked throughout viewer

**Failure Actions**:
- Check if `contextmenu` event listener is attached
- Verify `e.preventDefault()` is called
- Test in different areas of the page

---

### Test 5: Keyboard Shortcut Protection

**Objective**: Verify download/print/copy keyboard shortcuts are blocked.

**Steps**:
1. Load the secure PDF viewer
2. Press **Ctrl+S** (or Cmd+S on Mac)
3. Observe alert and console
4. Press **Ctrl+P** (or Cmd+P on Mac)
5. Observe alert and console
6. Press **Ctrl+C** (or Cmd+C on Mac)
7. Observe alert and console

**Expected Results for Ctrl+S**:
- ✅ Browser save dialog does NOT appear
- ✅ Alert displays: "Downloading is disabled for this document."
- ✅ Console warning: "Download attempt blocked: Ctrl+S"

**Expected Results for Ctrl+P**:
- ✅ Browser print dialog does NOT appear
- ✅ Alert displays: "Printing is disabled for this document."
- ✅ Console warning: "Print attempt blocked: Ctrl+P"

**Expected Results for Ctrl+C**:
- ✅ Copy operation blocked
- ✅ Alert displays: "Copying is disabled for this document."
- ✅ Console warning: "Copy attempt blocked: Ctrl+C"

**Pass Criteria**: All three shortcuts blocked with appropriate alerts

**Failure Actions**:
- Check if `keydown` event listener is attached
- Verify both `e.ctrlKey` and `e.metaKey` are checked (for Mac)
- Test with different keyboard layouts

---

### Test 6: Text Selection and Drag Protection

**Objective**: Verify text selection and drag-and-drop are disabled.

**Steps**:
1. Load the secure PDF viewer
2. Try to select text on the PDF canvas by clicking and dragging
3. Try to drag the PDF canvas element
4. Inspect canvas element styles

**Expected Results**:
- ✅ Cannot select text on PDF canvas
- ✅ Cannot drag PDF canvas
- ✅ Canvas has `user-select: none` style
- ✅ Canvas has `-webkit-user-select: none` style
- ✅ `dragstart` event is prevented
- ✅ Console warning on drag attempt: "Drag-and-drop is disabled on this secure viewer"

**Pass Criteria**: Text selection and dragging completely disabled

**Failure Actions**:
- Inspect `#pdf-canvas` element styles
- Check if `dragstart` event listener is attached
- Verify CSS user-select properties

---

### Test 7: Page Navigation Functionality

**Objective**: Verify page navigation controls work correctly.

**Steps**:
1. Load the secure PDF viewer with multi-page PDF
2. Click "Next →" button
3. Observe page change and button states
4. Click "← Previous" button
5. Type a page number in the input field and press Enter
6. Use arrow keys (Left/Right) for navigation

**Expected Results**:
- ✅ "Next →" button advances to next page
- ✅ "← Previous" button goes to previous page
- ✅ Page number input updates to current page
- ✅ Total page count displays correctly
- ✅ "← Previous" disabled on page 1
- ✅ "Next →" disabled on last page
- ✅ Typing page number and pressing Enter navigates to that page
- ✅ Invalid page numbers (0, negative, > total) are corrected
- ✅ Arrow Left key goes to previous page
- ✅ Arrow Right key goes to next page
- ✅ Smooth transition effect during page changes
- ✅ Canvas briefly shows "rendering" state (opacity 0.6)

**Pass Criteria**: All navigation methods work smoothly

**Failure Actions**:
- Check console for rendering errors
- Verify PDF has multiple pages
- Test page number validation logic

---

### Test 8: Zoom Functionality

**Objective**: Verify zoom controls adjust PDF scale correctly.

**Steps**:
1. Load the secure PDF viewer
2. Select "50%" from zoom dropdown
3. Observe PDF size
4. Select "200%" from zoom dropdown
5. Click "Zoom In" button multiple times
6. Click "Zoom Out" button multiple times
7. Select "Fit Width" from dropdown
8. Resize browser window with "Fit Width" selected

**Expected Results**:
- ✅ 50% zoom makes PDF smaller
- ✅ 200% zoom makes PDF larger
- ✅ "Zoom In" button increases zoom level
- ✅ "Zoom Out" button decreases zoom level
- ✅ Zoom levels: 50%, 75%, 100%, 125%, 150%, 200%
- ✅ "Fit Width" scales PDF to container width
- ✅ "Fit Width" re-scales on window resize
- ✅ Zoom changes apply immediately
- ✅ Smooth transition effect during zoom
- ✅ Button hover effects work (scale animation)

**Pass Criteria**: All zoom operations work correctly

**Failure Actions**:
- Check if `currentScale` variable updates
- Verify `renderPage()` is called on zoom change
- Test viewport calculation for "Fit Width"

---

### Test 9: Responsive Design

**Objective**: Verify viewer adapts to different viewport sizes.

**Steps**:
1. Load the secure PDF viewer in desktop size (1920px width)
2. Open browser DevTools and enable responsive design mode
3. Test the following viewport sizes:
   - 1920px (Large Desktop)
   - 1200px (Standard Desktop)
   - 992px (Small Desktop / Large Tablet Landscape)
   - 768px (Tablet Portrait)
   - 600px (Small Tablet)
4. Test landscape orientation on tablet size
5. Observe layout changes at each breakpoint

**Expected Results**:

**Large Desktop (1920px+)**:
- ✅ Toolbar padding: 15px 40px
- ✅ PDF container padding: 40px
- ✅ Watermark font size: 28px
- ✅ All controls in single row

**Standard Desktop (1200-1919px)**:
- ✅ Toolbar padding: 12px 30px
- ✅ PDF container padding: 30px
- ✅ Watermark font size: 24px
- ✅ All controls in single row

**Small Desktop / Large Tablet (992-1199px)**:
- ✅ Toolbar padding: 12px 20px
- ✅ Reduced button padding and font size
- ✅ PDF container padding: 20px
- ✅ Watermark font size: 22px

**Tablet Portrait (768-991px)**:
- ✅ Toolbar wraps to multiple rows
- ✅ Document title centered on first row
- ✅ Navigation controls on second row
- ✅ Zoom controls on third row
- ✅ Watermark font size: 20px
- ✅ PDF container padding: 15px

**Small Tablet (< 768px)**:
- ✅ Toolbar stacks vertically
- ✅ All control groups centered
- ✅ Smaller button sizes
- ✅ Watermark font size: 18px
- ✅ PDF container padding: 10px

**Landscape Orientation (height < 600px)**:
- ✅ Reduced toolbar padding
- ✅ Reduced watermark opacity (0.15)
- ✅ Smaller watermark font (18px)

**Pass Criteria**: Layout adapts smoothly at all breakpoints

**Failure Actions**:
- Inspect CSS media queries
- Check responsive watermark rendering
- Verify toolbar flex wrapping

---


### Test 10: Session Token Validation

**Objective**: Verify session tokens expire and invalid tokens are rejected.

**Steps**:
1. Load the secure PDF viewer and copy the URL
2. Note the session token in the URL
3. Wait for token expiration (default: 60 minutes, or adjust config for testing)
4. Refresh the page with expired token
5. Try accessing viewer with manually modified token
6. Try accessing viewer without authentication

**Expected Results for Expired Token**:
- ✅ 403 Forbidden error displayed
- ✅ Error message: "Invalid or expired viewing session"
- ✅ Access attempt logged to `pdf_access_logs` table
- ✅ Log entry shows `access_granted: false`
- ✅ Log entry shows `failure_reason: 'invalid_or_expired_token'`

**Expected Results for Invalid Token**:
- ✅ 403 Forbidden error displayed
- ✅ Error message: "Invalid or expired viewing session"
- ✅ Failed attempt logged to database

**Expected Results for Unauthenticated Access**:
- ✅ Redirected to login page
- ✅ After login, redirected back to viewer (if token still valid)

**Pass Criteria**: All invalid access attempts properly rejected and logged

**Failure Actions**:
- Check `SecurePdfService::validateSessionToken()` method
- Verify token expiration timestamp
- Check HMAC signature validation
- Review database logs

**Testing Tip**: To test expiration quickly, temporarily change `token_expiration_minutes` in `config/secure-pdf.php` to 1 minute.

---

### Test 11: Access Logging

**Objective**: Verify all access attempts are logged to database.

**Steps**:
1. Clear or note current `pdf_access_logs` table count
2. Access the secure PDF viewer successfully
3. Navigate to multiple pages
4. Try accessing with invalid token
5. Query the `pdf_access_logs` table
6. Check log entries

**Expected Results**:
- ✅ Successful viewer access creates log entry with:
  - `user_id`: Current user ID
  - `content_id`: PDF content ID
  - `session_token`: First 32 chars of token
  - `ip_address`: User's IP address
  - `access_granted`: true
  - `failure_reason`: null
  - `accessed_at`: Current timestamp
- ✅ Failed access creates log entry with:
  - `access_granted`: false
  - `failure_reason`: Specific reason (e.g., 'invalid_or_expired_token')
- ✅ Page views logged separately (check Laravel logs)
- ✅ Admin interface at `/admin/pdf-access-logs` displays logs

**Pass Criteria**: All access attempts logged with correct data

**Failure Actions**:
- Check database migration ran successfully
- Verify `PdfAccessLog` model exists
- Check controller logging code
- Review Laravel logs for errors

**SQL Query for Verification**:
```sql
SELECT * FROM pdf_access_logs 
ORDER BY accessed_at DESC 
LIMIT 10;
```

---

### Test 12: Security Headers

**Objective**: Verify PDF stream includes proper security headers.

**Steps**:
1. Load the secure PDF viewer
2. Open browser DevTools → Network tab
3. Find the PDF stream request (to `/secure-pdf/stream/{content_id}`)
4. Click on the request to view details
5. Check the Response Headers section

**Expected Response Headers**:
- ✅ `Content-Type: application/pdf`
- ✅ `Cache-Control: no-cache, no-store, must-revalidate`
- ✅ `Pragma: no-cache`
- ✅ `Expires: 0`
- ✅ `X-Content-Type-Options: nosniff`
- ✅ `X-Frame-Options: SAMEORIGIN`
- ✅ `X-XSS-Protection: 1; mode=block`
- ✅ `Referrer-Policy: strict-origin-when-cross-origin`
- ✅ `Content-Security-Policy: default-src 'none'; object-src 'self'; ...`

**Pass Criteria**: All security headers present with correct values

**Failure Actions**:
- Check `SecurePdfController::stream()` method
- Verify response headers array
- Test with curl command:
  ```bash
  curl -I [PDF_STREAM_URL]
  ```

---

### Test 13: Developer Tools Detection

**Objective**: Verify developer tools usage is detected and logged.

**Steps**:
1. Load the secure PDF viewer
2. Keep browser console open
3. Open browser DevTools (F12 or right-click → Inspect)
4. Observe console warnings
5. Check server logs

**Expected Results**:
- ✅ Console warning appears: "⚠️ SECURITY WARNING: Developer tools detected..."
- ✅ Warning mentions logging and potential access revocation
- ✅ Detection runs every second (check timestamp updates)
- ✅ Server receives DevTools detection POST request
- ✅ Detection resets when DevTools closed

**Pass Criteria**: DevTools detection works and logs to server

**Failure Actions**:
- Check `detectDevTools()` function in viewer.blade.php
- Verify interval is running (every 1000ms)
- Check network tab for POST to `/secure-pdf/log-devtools-detection/{content_id}`
- Review server route and controller method

**Note**: This is a heuristic detection method that can be bypassed. It serves as a deterrent and logging mechanism, not absolute prevention.

---

### Test 14: Error Handling

**Objective**: Verify graceful error handling for various failure scenarios.

**Steps**:
1. **Test PDF Not Found**:
   - Modify content record to point to non-existent file
   - Access viewer
   - Expected: 404 error with message "PDF file not found"

2. **Test Invalid Content Type**:
   - Try accessing viewer with non-PDF content ID
   - Expected: 404 error with message "Content not found"

3. **Test Insufficient Permissions**:
   - Access viewer as user not enrolled in course
   - Expected: 403 error with message "You do not have permission to view this PDF"

4. **Test Inactive Content**:
   - Set content `is_active` to false
   - Access viewer
   - Expected: 403 error with message "This content is not available"

5. **Test PDF.js Load Failure**:
   - Block PDF.js CDN in browser (using ad blocker or hosts file)
   - Access viewer
   - Expected: Error message in viewer "Failed to load PDF viewer. Please refresh the page."

6. **Test PDF Rendering Failure**:
   - Use corrupted PDF file
   - Access viewer
   - Expected: Error message "Failed to render PDF. The file may be corrupted."

**Pass Criteria**: All error scenarios handled gracefully with appropriate messages

**Failure Actions**:
- Check error handling in controller methods
- Verify try-catch blocks in JavaScript
- Review error messages for clarity

---

## Feature Verification Checklists

### Security Features Checklist

Use this checklist to verify all security features are working:

- [ ] **Token Validation**
  - [ ] Valid tokens allow access
  - [ ] Expired tokens rejected with 403
  - [ ] Invalid signatures rejected
  - [ ] Malformed tokens rejected
  - [ ] Token includes user_id, content_id, expires_at

- [ ] **Anti-Download Protections**
  - [ ] Right-click context menu blocked
  - [ ] Ctrl+S (Save) blocked with alert
  - [ ] Ctrl+P (Print) blocked with alert
  - [ ] Ctrl+C (Copy) blocked with alert
  - [ ] Text selection disabled on canvas
  - [ ] Drag-and-drop disabled
  - [ ] F12 (DevTools) detected and logged

- [ ] **Watermark**
  - [ ] Watermark visible across viewport
  - [ ] Contains user name
  - [ ] Contains user email
  - [ ] Contains timestamp
  - [ ] Contains IP address (if enabled)
  - [ ] Rotated at -45 degrees
  - [ ] Opacity 0.15-0.25
  - [ ] Font size 24px
  - [ ] Repeating pattern
  - [ ] Z-index 9999 (on top)

- [ ] **Custom Toolbar**
  - [ ] Default PDF.js toolbar hidden
  - [ ] Custom toolbar visible
  - [ ] NO download button
  - [ ] NO print button
  - [ ] NO open-in-new-tab button
  - [ ] Only navigation controls present
  - [ ] Only zoom controls present

- [ ] **Access Logging**
  - [ ] Successful access logged
  - [ ] Failed access logged
  - [ ] Logs include user_id, content_id, IP
  - [ ] Logs include session_token
  - [ ] Logs include access_granted status
  - [ ] Logs include failure_reason (if failed)
  - [ ] Admin interface displays logs

- [ ] **Security Headers**
  - [ ] Cache-Control: no-cache, no-store
  - [ ] Pragma: no-cache
  - [ ] X-Content-Type-Options: nosniff
  - [ ] X-Frame-Options: SAMEORIGIN
  - [ ] Content-Security-Policy present

### UI/UX Features Checklist

- [ ] **Visual Design**
  - [ ] Dark background (#2c3e50)
  - [ ] Modern toolbar styling
  - [ ] Smooth button hover effects
  - [ ] Loading indicator appears
  - [ ] Security notice banner visible

- [ ] **Navigation**
  - [ ] Previous button works
  - [ ] Next button works
  - [ ] Page input accepts numbers
  - [ ] Page input validates range
  - [ ] Arrow keys navigate pages
  - [ ] Buttons disabled at boundaries

- [ ] **Zoom Controls**
  - [ ] Zoom dropdown works
  - [ ] Zoom In button works
  - [ ] Zoom Out button works
  - [ ] Fit Width option works
  - [ ] Zoom levels: 50%, 75%, 100%, 125%, 150%, 200%
  - [ ] Smooth zoom transitions

- [ ] **Responsive Design**
  - [ ] Works on 1920px+ (Large Desktop)
  - [ ] Works on 1200-1919px (Desktop)
  - [ ] Works on 992-1199px (Small Desktop)
  - [ ] Works on 768-991px (Tablet Portrait)
  - [ ] Works on < 768px (Small Tablet)
  - [ ] Toolbar adapts at breakpoints
  - [ ] Watermark scales responsively
  - [ ] PDF fits container at all sizes

- [ ] **Performance**
  - [ ] PDF loads within 3 seconds
  - [ ] Page changes are smooth
  - [ ] Zoom changes are immediate
  - [ ] No lag during navigation
  - [ ] Watermark renders quickly

---


## Expected Behavior Reference

### Normal Operation Flow

1. **User Requests PDF Access**
   - User clicks link to PDF content
   - System generates session token (valid for 60 minutes)
   - User redirected to `/secure-pdf/viewer/{content_id}/{token}`

2. **Viewer Initialization**
   - Token validated (signature, expiration, user/content match)
   - Access logged to database
   - Viewer page rendered with user data
   - Loading indicator displayed

3. **PDF Loading**
   - PDF.js library loads from CDN
   - Signed URL generated for PDF data (valid 5 minutes)
   - PDF fetched from `/secure-pdf/stream/{content_id}`
   - Security headers applied to response
   - PDF rendered on canvas

4. **Watermark Rendering**
   - Watermark overlay created
   - User info (name, email, timestamp, IP) formatted
   - Grid pattern calculated based on viewport
   - Watermark items positioned and styled
   - Overlay displayed on top of PDF

5. **User Interaction**
   - User navigates pages using toolbar or arrow keys
   - Each page view logged to server
   - User zooms in/out using controls
   - PDF re-renders at new scale
   - Smooth transitions applied

6. **Security Enforcement**
   - Right-click attempts blocked
   - Keyboard shortcuts intercepted
   - Alerts shown for blocked actions
   - DevTools detection runs every second
   - All attempts logged to console

### Visual Appearance

**Toolbar**:
```
┌─────────────────────────────────────────────────────────────────┐
│ Document Title    ← Previous  Page [1] of 10  Next →   [100%] ▼│
│                                                    Zoom In  Zoom Out│
└─────────────────────────────────────────────────────────────────┘
```

**Watermark Pattern**:
```
John Doe | john@example.com | 2024-01-15 10:30:00
    John Doe | john@example.com | 2024-01-15 10:30:00
        John Doe | john@example.com | 2024-01-15 10:30:00
(Rotated -45°, repeated across viewport, semi-transparent)
```

**Security Notice**:
```
┌─────────────────────────────────────────────────────────────────┐
│ 🔒 This document is protected. Downloading, printing, and      │
│    copying are disabled for security.                           │
└─────────────────────────────────────────────────────────────────┘
```

### Color Scheme

- **Background**: #2c3e50 (Dark blue-gray)
- **Toolbar**: Linear gradient #1f1f1f to #1a1a1a (Dark gray)
- **Buttons**: #3a3a3a to #2d2d2d (Medium gray)
- **Button Hover**: #4a4a4a to #3d3d3d (Lighter gray)
- **Text**: #ffffff (White)
- **Watermark**: #000000 at 0.2 opacity (Semi-transparent black)
- **Security Notice**: #ff6b6b background (Red)

### Timing Expectations

- **PDF Load Time**: 1-3 seconds (depends on file size)
- **Page Render Time**: < 500ms
- **Zoom Transition**: Immediate (< 100ms)
- **Watermark Render**: < 200ms
- **Token Expiration**: 60 minutes (configurable)
- **Signed URL Expiration**: 5 minutes
- **DevTools Check Interval**: 1 second

---

## Troubleshooting Guide

### Issue: Viewer Page Shows 403 Forbidden

**Possible Causes**:
1. Session token expired
2. Invalid or tampered token
3. User not authenticated
4. User lacks permission to view content

**Diagnostic Steps**:
1. Check if user is logged in
2. Verify token in URL is not expired (check timestamp)
3. Check `pdf_access_logs` table for failure_reason
4. Verify user enrollment in course
5. Check content permissions

**Solutions**:
- Request new viewing link
- Log in again
- Contact administrator for access
- Check course enrollment status

---

### Issue: PDF Not Loading / Blank Canvas

**Possible Causes**:
1. PDF file not found in storage
2. Incorrect file path in database
3. PDF.js library failed to load
4. Corrupted PDF file
5. Network connectivity issues

**Diagnostic Steps**:
1. Check browser console for errors
2. Check Network tab for failed requests
3. Verify PDF file exists: `storage/app/protected/{file_path}`
4. Check `contents` table for correct `file_path` and `storage_disk`
5. Test PDF.js CDN accessibility

**Solutions**:
- Verify file exists in storage
- Update file path in database
- Check internet connection
- Try different browser
- Check Laravel logs for file access errors

---

### Issue: Watermark Not Visible

**Possible Causes**:
1. JavaScript error preventing watermark rendering
2. CSS z-index conflict
3. Watermark opacity too low
4. User data not passed to view

**Diagnostic Steps**:
1. Check browser console for JavaScript errors
2. Inspect `#watermark-overlay` element in DevTools
3. Check if `.watermark-item` elements exist
4. Verify user data in page source
5. Check watermark opacity in CSS

**Solutions**:
- Refresh page to re-render watermark
- Check `renderWatermark()` function in console
- Verify user data passed to view: `{{ $user['name'] }}`
- Adjust opacity in config if too transparent
- Clear browser cache

---

### Issue: Keyboard Shortcuts Not Blocked

**Possible Causes**:
1. JavaScript event listener not attached
2. Browser extension interfering
3. Focus on input field (page number)
4. Browser security settings

**Diagnostic Steps**:
1. Check console for event listener errors
2. Disable browser extensions
3. Test with focus on PDF canvas (not input)
4. Try different browser
5. Check if `keydown` event fires in console

**Solutions**:
- Refresh page
- Disable ad blockers and extensions
- Click on PDF canvas before testing shortcuts
- Test in incognito/private mode
- Update browser to latest version

---

### Issue: Right-Click Context Menu Appears

**Possible Causes**:
1. JavaScript not loaded
2. Event listener not attached
3. Browser extension overriding
4. Testing on wrong element

**Diagnostic Steps**:
1. Check if JavaScript loaded completely
2. Verify `contextmenu` event listener in DevTools
3. Disable browser extensions
4. Test directly on PDF canvas

**Solutions**:
- Refresh page
- Disable extensions
- Test in incognito mode
- Check console for errors

---

### Issue: Navigation Buttons Not Working

**Possible Causes**:
1. PDF not loaded yet
2. JavaScript error
3. Single-page PDF (buttons disabled)
4. Event listeners not attached

**Diagnostic Steps**:
1. Wait for PDF to fully load
2. Check console for errors
3. Verify PDF has multiple pages
4. Check button disabled state
5. Test with different PDF

**Solutions**:
- Wait for loading indicator to disappear
- Refresh page
- Use multi-page PDF for testing
- Check `renderPage()` function

---

### Issue: Zoom Not Working

**Possible Causes**:
1. JavaScript error in zoom handler
2. Invalid zoom value
3. Canvas rendering issue
4. PDF not loaded

**Diagnostic Steps**:
1. Check console for errors
2. Verify `currentScale` variable updates
3. Check if `renderPage()` is called
4. Test with different zoom levels
5. Check viewport calculation

**Solutions**:
- Refresh page
- Try different zoom level
- Check browser console
- Test "Fit Width" option
- Verify PDF loaded successfully

---

### Issue: Responsive Layout Not Adapting

**Possible Causes**:
1. CSS media queries not applied
2. Browser zoom interfering
3. Viewport meta tag missing
4. CSS cache issue

**Diagnostic Steps**:
1. Check viewport meta tag in HTML head
2. Verify browser zoom is 100%
3. Inspect element to see applied CSS
4. Check media query breakpoints
5. Test in responsive design mode

**Solutions**:
- Reset browser zoom to 100%
- Clear browser cache
- Hard refresh (Ctrl+Shift+R)
- Test in different browser
- Verify viewport meta tag present

---

### Issue: Access Not Logged to Database

**Possible Causes**:
1. Database migration not run
2. `pdf_access_logs` table missing
3. Database connection issue
4. Logging code not executed

**Diagnostic Steps**:
1. Check if table exists: `SHOW TABLES LIKE 'pdf_access_logs';`
2. Verify migration ran: `php artisan migrate:status`
3. Check Laravel logs for database errors
4. Test database connection
5. Check controller logging code

**Solutions**:
- Run migration: `php artisan migrate`
- Check database credentials in `.env`
- Review Laravel logs
- Test database connection
- Verify `PdfAccessLog` model exists

---

### Issue: Security Headers Missing

**Possible Causes**:
1. Middleware interfering
2. Server configuration overriding
3. Proxy/CDN stripping headers
4. Controller not setting headers

**Diagnostic Steps**:
1. Check Network tab for response headers
2. Test with curl: `curl -I [URL]`
3. Check middleware stack
4. Review server configuration (nginx/Apache)
5. Check controller `stream()` method

**Solutions**:
- Verify headers in controller response
- Check server configuration
- Disable conflicting middleware
- Test without proxy/CDN
- Review Laravel response headers

---

## Test Results Template

Use this template to document your testing results:

```markdown
# Secure PDF Viewer - Test Results

**Test Date**: [Date]
**Tester**: [Name]
**Environment**: [Local/Staging/Production]
**Browser**: [Chrome/Firefox] [Version]

## Test Summary

- Total Tests: [Number]
- Passed: [Number]
- Failed: [Number]
- Blocked: [Number]

## Detailed Results

### Test 1: Initial Viewer Access
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]
- **Screenshots**: [If applicable]

### Test 2: Watermark Verification
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]
- **Screenshots**: [If applicable]

### Test 3: Custom Toolbar Verification
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]
- **Screenshots**: [If applicable]

### Test 4: Right-Click Protection
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 5: Keyboard Shortcut Protection
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 6: Text Selection and Drag Protection
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 7: Page Navigation Functionality
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 8: Zoom Functionality
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 9: Responsive Design
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 10: Session Token Validation
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 11: Access Logging
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 12: Security Headers
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 13: Developer Tools Detection
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

### Test 14: Error Handling
- **Status**: ✅ PASS / ❌ FAIL / ⚠️ BLOCKED
- **Notes**: [Any observations or issues]

## Issues Found

### Issue 1: [Title]
- **Severity**: Critical / High / Medium / Low
- **Description**: [Detailed description]
- **Steps to Reproduce**: [Steps]
- **Expected**: [Expected behavior]
- **Actual**: [Actual behavior]
- **Screenshots**: [If applicable]

### Issue 2: [Title]
[Same format as above]

## Recommendations

[Any recommendations for improvements or fixes]

## Sign-off

- **Tester Signature**: _______________
- **Date**: _______________
- **Approved for Production**: YES / NO / WITH CONDITIONS
```

---

## Quick Reference Commands

### Testing Commands

```bash
# Access test route
http://localhost:8000/secure-pdf/test

# Check database logs
php artisan tinker
>>> \App\Models\PdfAccessLog::latest()->take(10)->get()

# View Laravel logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Check migration status
php artisan migrate:status
```

### SQL Queries

```sql
-- View recent access logs
SELECT * FROM pdf_access_logs 
ORDER BY accessed_at DESC 
LIMIT 20;

-- Count successful vs failed access
SELECT access_granted, COUNT(*) as count 
FROM pdf_access_logs 
GROUP BY access_granted;

-- View failure reasons
SELECT failure_reason, COUNT(*) as count 
FROM pdf_access_logs 
WHERE access_granted = 0 
GROUP BY failure_reason;

-- View access by user
SELECT u.name, u.email, COUNT(*) as access_count
FROM pdf_access_logs p
JOIN users u ON p.user_id = u.id
GROUP BY u.id, u.name, u.email
ORDER BY access_count DESC;
```

### Browser Console Commands

```javascript
// Check if watermark rendered
document.querySelectorAll('.watermark-item').length

// Check current page
currentPage

// Check total pages
totalPages

// Check current scale
currentScale

// Manually trigger watermark render
renderWatermark()

// Check PDF loaded
pdfDoc !== null

// View session token
SESSION_TOKEN

// View watermark data
WATERMARK_DATA
```

---

## Appendix: Configuration Reference

### Environment Variables

```env
# Token expiration (minutes)
SECURE_PDF_TOKEN_EXPIRATION=60

# Watermark settings
SECURE_PDF_WATERMARK_OPACITY=0.2
SECURE_PDF_WATERMARK_FONT_SIZE=24
SECURE_PDF_WATERMARK_ROTATION=-45
SECURE_PDF_WATERMARK_COLOR=#000000

# IP tracking
SECURE_PDF_ENABLE_IP_TRACKING=false

# Access logging
SECURE_PDF_LOG_ACCESS=true

# UI settings
SECURE_PDF_VIEWER_BG_COLOR=#2c3e50
SECURE_PDF_ENABLE_PAGE_NAV=true
SECURE_PDF_ENABLE_ZOOM=true

# Security settings
SECURE_PDF_BLOCK_RIGHT_CLICK=true
SECURE_PDF_BLOCK_SHORTCUTS=true
SECURE_PDF_BLOCK_TEXT_SELECT=true
SECURE_PDF_DETECT_DEVTOOLS=true

# Performance settings
SECURE_PDF_CHUNK_SIZE=8192
SECURE_PDF_CACHE_TOKENS=true
SECURE_PDF_TOKEN_CACHE_TTL=300
```

### Routes

```php
// Viewer route
GET /secure-pdf/viewer/{content}/{token}

// Stream route (signed URL)
GET /secure-pdf/stream/{content}

// Page view logging
POST /secure-pdf/log-page-view/{content}

// DevTools detection logging
POST /secure-pdf/log-devtools-detection/{content}

// Test route (development only)
GET /secure-pdf/test

// Admin interface
GET /admin/pdf-access-logs
```

---

## Contact and Support

For issues or questions about this testing guide:

- **Documentation**: See `SECURE_PDF_STREAMING_IMPLEMENTATION.md`
- **Quick Start**: See `SECURE_PDF_QUICK_START.md`
- **Laravel Logs**: `storage/logs/laravel.log`
- **Database**: Check `pdf_access_logs` table

---

**Document Version**: 1.0  
**Last Updated**: January 2024  
**Requirement**: 8.8 - Manual testing documentation
