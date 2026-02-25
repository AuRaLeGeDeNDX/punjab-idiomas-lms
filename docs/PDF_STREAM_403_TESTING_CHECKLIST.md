# PDF Stream 403 Fix - Quick Testing Checklist

**Print this page for manual testing sessions**

---

## Pre-Test Setup

- [ ] Test environment running
- [ ] User logged in
- [ ] Sample PDF files prepared (small, medium, large)
- [ ] Browser DevTools ready
- [ ] Diagnostic scripts available

---

## Chrome Testing

### Basic Functionality
- [ ] PDF loads without 403 error
- [ ] Signed URL contains signature and expires params
- [ ] URL is absolute (includes scheme and host)
- [ ] No console errors

### Signed URL Validation
- [ ] Signature parameter present (64 chars)
- [ ] Expires parameter present (Unix timestamp)
- [ ] Expiration time >= 5 minutes in future
- [ ] Request succeeds with 200 or 206 status

### Range Requests
- [ ] Multiple range requests made for large PDFs
- [ ] All range requests succeed
- [ ] Responses have 206 Partial Content status
- [ ] Content-Range header present
- [ ] Accept-Ranges: bytes header present

### URL Encoding
- [ ] PDF with special characters loads
- [ ] Signature remains valid
- [ ] No double-encoding issues

### Session Independence
- [ ] PDF loads after clearing cookies
- [ ] No redirect to login
- [ ] Access granted based on signature alone

### Error Handling
- [ ] Expired signature returns 403
- [ ] Invalid signature returns 403
- [ ] Missing parameters return 403
- [ ] Error messages are meaningful

### Multiple Requests
- [ ] Same URL works on reload
- [ ] No "signature already used" errors
- [ ] All reloads succeed

### HTTP Headers
- [ ] Content-Type: application/pdf
- [ ] Accept-Ranges: bytes
- [ ] Cache-Control: no-cache, no-store
- [ ] X-Content-Type-Options: nosniff

---

## Firefox Testing

### Basic Functionality
- [ ] PDF loads without 403 error
- [ ] Signed URL contains signature and expires params
- [ ] URL is absolute (includes scheme and host)
- [ ] No console errors

### Signed URL Validation
- [ ] Signature parameter present (64 chars)
- [ ] Expires parameter present (Unix timestamp)
- [ ] Expiration time >= 5 minutes in future
- [ ] Request succeeds with 200 or 206 status

### Range Requests
- [ ] Multiple range requests made for large PDFs
- [ ] All range requests succeed
- [ ] Responses have 206 Partial Content status
- [ ] Content-Range header present
- [ ] Accept-Ranges: bytes header present

### URL Encoding
- [ ] PDF with special characters loads
- [ ] Signature remains valid
- [ ] No double-encoding issues

### Session Independence
- [ ] PDF loads after clearing cookies
- [ ] No redirect to login
- [ ] Access granted based on signature alone

### Error Handling
- [ ] Expired signature returns 403
- [ ] Invalid signature returns 403
- [ ] Missing parameters return 403
- [ ] Error messages are meaningful

### Multiple Requests
- [ ] Same URL works on reload
- [ ] No "signature already used" errors
- [ ] All reloads succeed

### HTTP Headers
- [ ] Content-Type: application/pdf
- [ ] Accept-Ranges: bytes
- [ ] Cache-Control: no-cache, no-store
- [ ] X-Content-Type-Options: nosniff

---

## Edge Testing

### Basic Functionality
- [ ] PDF loads without 403 error
- [ ] Signed URL contains signature and expires params
- [ ] URL is absolute (includes scheme and host)
- [ ] No console errors

### Signed URL Validation
- [ ] Signature parameter present (64 chars)
- [ ] Expires parameter present (Unix timestamp)
- [ ] Expiration time >= 5 minutes in future
- [ ] Request succeeds with 200 or 206 status

### Range Requests
- [ ] Multiple range requests made for large PDFs
- [ ] All range requests succeed
- [ ] Responses have 206 Partial Content status
- [ ] Content-Range header present
- [ ] Accept-Ranges: bytes header present

### URL Encoding
- [ ] PDF with special characters loads
- [ ] Signature remains valid
- [ ] No double-encoding issues

### Session Independence
- [ ] PDF loads after clearing cookies
- [ ] No redirect to login
- [ ] Access granted based on signature alone

### Error Handling
- [ ] Expired signature returns 403
- [ ] Invalid signature returns 403
- [ ] Missing parameters return 403
- [ ] Error messages are meaningful

### Multiple Requests
- [ ] Same URL works on reload
- [ ] No "signature already used" errors
- [ ] All reloads succeed

### HTTP Headers
- [ ] Content-Type: application/pdf
- [ ] Accept-Ranges: bytes
- [ ] Cache-Control: no-cache, no-store
- [ ] X-Content-Type-Options: nosniff

---

## PDF File Testing

### Small PDF (< 1 MB)
- [ ] Loads quickly (< 1 second)
- [ ] All pages render correctly
- [ ] No 403 errors

### Medium PDF (1-10 MB)
- [ ] Loads within 2-3 seconds
- [ ] Uses range requests
- [ ] Pages render progressively
- [ ] No 403 errors on range requests

### Large PDF (> 10 MB)
- [ ] Loads progressively
- [ ] Multiple range requests made
- [ ] All range requests succeed
- [ ] No timeout errors
- [ ] No 403 errors

### Multi-Page PDF (100+ pages)
- [ ] All pages load correctly
- [ ] Page navigation works smoothly
- [ ] Range requests fetch only needed pages
- [ ] No 403 errors when jumping between pages

### PDF with Special Characters
- [ ] PDF loads successfully
- [ ] Special characters properly encoded
- [ ] Signature remains valid
- [ ] No encoding-related 403 errors

---

## Error Logging

### Expired Signature
- [ ] Error logged to Laravel logs
- [ ] Log includes URL
- [ ] Log includes reason: "expired"
- [ ] Log includes signature
- [ ] Log includes expires timestamp
- [ ] Log includes IP address

### Invalid Signature
- [ ] Error logged to Laravel logs
- [ ] Log includes URL
- [ ] Log includes reason: "invalid_signature"
- [ ] Log includes expected vs actual signature
- [ ] Log includes IP address

### Successful Stream
- [ ] Success logged to Laravel logs
- [ ] Log includes content_id
- [ ] Log includes URL
- [ ] Log includes IP address

---

## Diagnostic Tools

### CLI Diagnostic Script
```bash
php diagnose_signed_url.php [content_id]
```
- [ ] All checks pass
- [ ] URL validation passes
- [ ] Signature validation passes
- [ ] Route configuration correct
- [ ] File access successful

### Streaming Diagnostic Script
```bash
php diagnose_pdf_streaming.php [content_id]
```
- [ ] Basic streaming works
- [ ] Range requests work
- [ ] Expired signature handled correctly
- [ ] Invalid signature handled correctly
- [ ] Multiple requests succeed
- [ ] Session independence verified

### Automated Test Suite
```bash
php artisan test --filter PdfStreamingDiagnosticTest
```
- [ ] All tests pass
- [ ] No failures
- [ ] No warnings

---

## Common Issues Checklist

### If 403 Forbidden on All Requests
- [ ] APP_URL is absolute in .env
- [ ] APP_KEY is set in .env
- [ ] 'signed' middleware applied to route
- [ ] No middleware conflicts

### If 403 After Reload
- [ ] No "one-time use" logic exists
- [ ] Signed URLs can be used multiple times
- [ ] Session independence verified

### If 403 on Range Requests
- [ ] Same signed URL used for all requests
- [ ] Signature validation doesn't modify request
- [ ] Range header not stripped by middleware

### If URL Encoding Issues
- [ ] URL passed to PDF.js without modification
- [ ] No double-encoding
- [ ] Consistent encoding throughout pipeline

### If Session Dependency
- [ ] Auth middleware not applied to signed route
- [ ] Controller doesn't check session
- [ ] Route only uses 'signed' middleware

---

## Test Sign-off

**Tester**: _______________  
**Date**: _______________  
**Environment**: _______________

**Chrome Tests**: ⬜ PASS ⬜ FAIL  
**Firefox Tests**: ⬜ PASS ⬜ FAIL  
**Edge Tests**: ⬜ PASS ⬜ FAIL  
**PDF File Tests**: ⬜ PASS ⬜ FAIL  
**Error Logging**: ⬜ PASS ⬜ FAIL  
**Diagnostic Tools**: ⬜ PASS ⬜ FAIL

**Total Pass Rate**: _____ %

**Critical Issues Found**: _______________

**Approved for Production**: ⬜ YES ⬜ NO ⬜ WITH CONDITIONS

**Notes**:
_______________________________________
_______________________________________
_______________________________________
_______________________________________

---

**Document Version**: 1.0  
**Requirement**: 6.4  
**Spec**: pdf-stream-403-fix

