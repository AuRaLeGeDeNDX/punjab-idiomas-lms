# Secure PDF Viewer - Quick Testing Checklist

**Print this page for manual testing sessions**

---

## Pre-Test Setup

- [ ] Test environment running
- [ ] User logged in
- [ ] Sample PDF in storage
- [ ] Database migrated
- [ ] Browser DevTools ready

---

## Chrome Testing

### Basic Functionality
- [ ] Viewer loads at `/secure-pdf/test`
- [ ] PDF renders on canvas
- [ ] Loading indicator appears/disappears
- [ ] No console errors

### Watermark
- [ ] Watermark visible
- [ ] Shows user name
- [ ] Shows email
- [ ] Shows timestamp
- [ ] Rotated -45 degrees
- [ ] Semi-transparent (opacity ~0.2)
- [ ] Repeating pattern

### Toolbar
- [ ] Custom toolbar visible
- [ ] Document title shown
- [ ] Page navigation buttons work
- [ ] Page number input works
- [ ] Zoom dropdown works
- [ ] Zoom In/Out buttons work
- [ ] NO download button
- [ ] NO print button

### Security Protections
- [ ] Right-click blocked
- [ ] Ctrl+S blocked (alert shown)
- [ ] Ctrl+P blocked (alert shown)
- [ ] Ctrl+C blocked (alert shown)
- [ ] Text selection disabled
- [ ] Drag-and-drop disabled
- [ ] DevTools detection works

### Navigation
- [ ] Next button advances page
- [ ] Previous button goes back
- [ ] Arrow keys work
- [ ] Page input accepts numbers
- [ ] Buttons disabled at boundaries

### Responsive Design
- [ ] Works at 1920px
- [ ] Works at 1200px
- [ ] Works at 992px
- [ ] Works at 768px
- [ ] Works at 600px
- [ ] Toolbar adapts
- [ ] Watermark scales

---

## Firefox Testing

### Basic Functionality
- [ ] Viewer loads at `/secure-pdf/test`
- [ ] PDF renders on canvas
- [ ] Loading indicator appears/disappears
- [ ] No console errors

### Watermark
- [ ] Watermark visible
- [ ] Shows user name
- [ ] Shows email
- [ ] Shows timestamp
- [ ] Rotated -45 degrees
- [ ] Semi-transparent (opacity ~0.2)
- [ ] Repeating pattern

### Toolbar
- [ ] Custom toolbar visible
- [ ] Document title shown
- [ ] Page navigation buttons work
- [ ] Page number input works
- [ ] Zoom dropdown works
- [ ] Zoom In/Out buttons work
- [ ] NO download button
- [ ] NO print button

### Security Protections
- [ ] Right-click blocked
- [ ] Ctrl+S blocked (alert shown)
- [ ] Ctrl+P blocked (alert shown)
- [ ] Ctrl+C blocked (alert shown)
- [ ] Text selection disabled
- [ ] Drag-and-drop disabled
- [ ] DevTools detection works

### Navigation
- [ ] Next button advances page
- [ ] Previous button goes back
- [ ] Arrow keys work
- [ ] Page input accepts numbers
- [ ] Buttons disabled at boundaries

### Responsive Design
- [ ] Works at 1920px
- [ ] Works at 1200px
- [ ] Works at 992px
- [ ] Works at 768px
- [ ] Works at 600px
- [ ] Toolbar adapts
- [ ] Watermark scales

---

## Backend Testing

### Token Validation
- [ ] Valid token allows access
- [ ] Expired token returns 403
- [ ] Invalid token returns 403
- [ ] Tampered token returns 403

### Access Logging
- [ ] Successful access logged
- [ ] Failed access logged
- [ ] Logs include user_id
- [ ] Logs include content_id
- [ ] Logs include IP address
- [ ] Logs include timestamp
- [ ] Logs include failure_reason (if failed)

### Security Headers
- [ ] Cache-Control: no-cache, no-store
- [ ] Pragma: no-cache
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: SAMEORIGIN
- [ ] Content-Security-Policy present

### Permissions
- [ ] Enrolled user can access
- [ ] Non-enrolled user blocked (403)
- [ ] Teacher can access
- [ ] Admin can access
- [ ] Unauthenticated redirected to login

---

## Error Handling

- [ ] PDF not found → 404 error
- [ ] Invalid content type → 404 error
- [ ] Insufficient permissions → 403 error
- [ ] Inactive content → 403 error
- [ ] PDF.js load failure → Error message
- [ ] Corrupted PDF → Error message

---

## Performance

- [ ] PDF loads in < 3 seconds
- [ ] Page changes smooth
- [ ] Zoom changes immediate
- [ ] No lag during navigation
- [ ] Watermark renders quickly

---

## Database Verification

```sql
-- Check recent logs
SELECT * FROM pdf_access_logs 
ORDER BY accessed_at DESC LIMIT 10;

-- Count access attempts
SELECT access_granted, COUNT(*) 
FROM pdf_access_logs 
GROUP BY access_granted;
```

---

## Common Issues

### Viewer shows 403
- Check token expiration
- Verify user logged in
- Check permissions

### PDF not loading
- Check file exists in storage
- Check file_path in database
- Check console for errors

### Watermark not visible
- Check console for JS errors
- Inspect #watermark-overlay element
- Verify user data passed to view

### Shortcuts not blocked
- Disable browser extensions
- Test in incognito mode
- Click on PDF canvas first

---

## Test Sign-off

**Tester**: _______________  
**Date**: _______________  
**Browser**: _______________  
**Version**: _______________  

**Chrome Tests**: ⬜ PASS ⬜ FAIL  
**Firefox Tests**: ⬜ PASS ⬜ FAIL  
**Backend Tests**: ⬜ PASS ⬜ FAIL  

**Issues Found**: _______________

**Approved for Production**: ⬜ YES ⬜ NO ⬜ WITH CONDITIONS

**Notes**:
_______________________________________
_______________________________________
_______________________________________
_______________________________________
