# PDF Stream 403 Fix - Browser-Specific Testing Guide

## Overview

This document provides browser-specific testing instructions for Chrome, Firefox, and Edge, including known differences, workarounds, and expected behavior variations for the PDF Stream 403 Fix.

---

## Chrome Testing (Version 90+)

### Setup

1. **Open Chrome**
2. **Navigate to**: Your application URL
3. **Open DevTools**: Press F12 or Right-click → Inspect
4. **Open Network tab**: To monitor requests
5. **Enable "Preserve log"**: To keep request history

### Chrome-Specific Features

#### Network Tab Inspection
Chrome's Network tab provides excellent debugging:
- Clear request/response headers
- Detailed timing information
- Easy filtering by type
- Request payload inspection

#### Console Warnings
Chrome displays clear console warnings:
- JavaScript errors are well-formatted
- Network errors show status codes
- CORS issues are clearly indicated

#### PDF.js Compatibility
Chrome works excellently with PDF.js:
- Fast canvas rendering
- Smooth scrolling
- Efficient range request handling

### Chrome Testing Checklist

- [ ] **Basic Streaming**
  - [ ] PDF loads without 403 error
  - [ ] Loading time < 3 seconds
  - [ ] No console errors

- [ ] **Signed URL**
  - [ ] URL is absolute
  - [ ] Contains signature parameter
  - [ ] Contains expires parameter
  - [ ] Expiration >= 5 minutes

- [ ] **Range Requests**
  - [ ] Multiple range requests visible in Network tab
  - [ ] All requests show 206 status
  - [ ] Content-Range headers present
  - [ ] Same signed URL used for all requests

- [ ] **URL Encoding**
  - [ ] Special characters handled correctly
  - [ ] No double-encoding visible
  - [ ] Signature remains valid

- [ ] **Session Independence**
  - [ ] Works after clearing cookies (DevTools → Application → Cookies)
  - [ ] No redirect to login
  - [ ] Access based on signature only

- [ ] **Error Handling**
  - [ ] Expired signature shows 403
  - [ ] Invalid signature shows 403
  - [ ] Error messages clear in console

### Chrome-Specific Tips

1. **Use Incognito Mode**: Eliminates extension interference
   - Shortcut: `Ctrl+Shift+N` (Windows/Linux) or `Cmd+Shift+N` (Mac)

2. **Clear Cache**: For fresh testing
   - Shortcut: `Ctrl+Shift+Delete`
   - Select "Cached images and files"

3. **Hard Refresh**: Bypass cache
   - Shortcut: `Ctrl+Shift+R` or `Ctrl+F5`

4. **Copy as cURL**: Test requests outside browser
   - Right-click request in Network tab → Copy → Copy as cURL

5. **Throttle Network**: Test slow connections
   - Network tab → Throttling dropdown → Slow 3G

### Chrome Known Issues

**Issue**: Extensions may interfere with requests
- **Solution**: Test in incognito mode
- **Impact**: Low - easy to work around

**Issue**: Aggressive caching may hide issues
- **Solution**: Use hard refresh or disable cache in DevTools
- **Impact**: Low - standard testing practice

---

## Firefox Testing (Version 88+)

### Setup

1. **Open Firefox**
2. **Navigate to**: Your application URL
3. **Open Developer Tools**: Press F12 or Right-click → Inspect Element
4. **Open Network tab**: To monitor requests
5. **Enable "Persist Logs"**: To keep request history

### Firefox-Specific Features

#### Network Tab Inspection
Firefox's Network tab is comprehensive:
- Detailed request/response headers
- Timing breakdown
- Security information
- Request blocking capabilities

#### Console Warnings
Firefox provides detailed console output:
- Clear error messages
- Stack traces for JavaScript errors
- Security warnings highlighted

#### PDF.js Compatibility
Firefox has native PDF.js integration:
- Excellent PDF rendering
- Optimized performance
- Native PDF viewer as fallback

### Firefox Testing Checklist

- [ ] **Basic Streaming**
  - [ ] PDF loads without 403 error
  - [ ] Loading time < 3 seconds
  - [ ] No console errors

- [ ] **Signed URL**
  - [ ] URL is absolute
  - [ ] Contains signature parameter
  - [ ] Contains expires parameter
  - [ ] Expiration >= 5 minutes

- [ ] **Range Requests**
  - [ ] Multiple range requests visible in Network tab
  - [ ] All requests show 206 status
  - [ ] Content-Range headers present
  - [ ] Same signed URL used for all requests

- [ ] **URL Encoding**
  - [ ] Special characters handled correctly
  - [ ] No double-encoding visible
  - [ ] Signature remains valid

- [ ] **Session Independence**
  - [ ] Works after clearing cookies (DevTools → Storage → Cookies)
  - [ ] No redirect to login
  - [ ] Access based on signature only

- [ ] **Error Handling**
  - [ ] Expired signature shows 403
  - [ ] Invalid signature shows 403
  - [ ] Error messages clear in console

### Firefox-Specific Tips

1. **Use Private Window**: Eliminates extension interference
   - Shortcut: `Ctrl+Shift+P` (Windows/Linux) or `Cmd+Shift+P` (Mac)

2. **Clear Cache**: For fresh testing
   - Shortcut: `Ctrl+Shift+Delete`
   - Select "Cache"

3. **Hard Refresh**: Bypass cache
   - Shortcut: `Ctrl+Shift+R` or `Ctrl+F5`

4. **Copy as cURL**: Test requests outside browser
   - Right-click request in Network tab → Copy → Copy as cURL

5. **Throttle Network**: Test slow connections
   - Network tab → Throttling icon → Select speed

### Firefox Known Issues

**Issue**: PDF.js version may differ from Chrome
- **Solution**: Test with both browsers
- **Impact**: Low - usually compatible

**Issue**: Different cookie handling
- **Solution**: Verify session independence works
- **Impact**: Low - signed URLs should be session-independent

---

## Edge Testing (Version 90+)

### Setup

1. **Open Edge**
2. **Navigate to**: Your application URL
3. **Open DevTools**: Press F12 or Right-click → Inspect
4. **Open Network tab**: To monitor requests
5. **Enable "Preserve log"**: To keep request history

### Edge-Specific Features

#### Network Tab Inspection
Edge's Network tab (Chromium-based):
- Similar to Chrome
- Clear request/response headers
- Detailed timing information
- Request filtering

#### Console Warnings
Edge provides clear console output:
- JavaScript errors well-formatted
- Network errors with status codes
- Security warnings highlighted

#### PDF.js Compatibility
Edge (Chromium-based) works well with PDF.js:
- Fast rendering
- Smooth scrolling
- Efficient range requests

### Edge Testing Checklist

- [ ] **Basic Streaming**
  - [ ] PDF loads without 403 error
  - [ ] Loading time < 3 seconds
  - [ ] No console errors

- [ ] **Signed URL**
  - [ ] URL is absolute
  - [ ] Contains signature parameter
  - [ ] Contains expires parameter
  - [ ] Expiration >= 5 minutes

- [ ] **Range Requests**
  - [ ] Multiple range requests visible in Network tab
  - [ ] All requests show 206 status
  - [ ] Content-Range headers present
  - [ ] Same signed URL used for all requests

- [ ] **URL Encoding**
  - [ ] Special characters handled correctly
  - [ ] No double-encoding visible
  - [ ] Signature remains valid

- [ ] **Session Independence**
  - [ ] Works after clearing cookies (DevTools → Application → Cookies)
  - [ ] No redirect to login
  - [ ] Access based on signature only

- [ ] **Error Handling**
  - [ ] Expired signature shows 403
  - [ ] Invalid signature shows 403
  - [ ] Error messages clear in console

### Edge-Specific Tips

1. **Use InPrivate Window**: Eliminates extension interference
   - Shortcut: `Ctrl+Shift+N` (Windows) or `Cmd+Shift+N` (Mac)

2. **Clear Cache**: For fresh testing
   - Shortcut: `Ctrl+Shift+Delete`
   - Select "Cached images and files"

3. **Hard Refresh**: Bypass cache
   - Shortcut: `Ctrl+Shift+R` or `Ctrl+F5`

4. **Copy as cURL**: Test requests outside browser
   - Right-click request in Network tab → Copy → Copy as cURL

5. **Throttle Network**: Test slow connections
   - Network tab → Network conditions → Network throttling

### Edge Known Issues

**Issue**: Similar to Chrome (Chromium-based)
- **Solution**: Same as Chrome solutions
- **Impact**: Low - very similar behavior

---

## Cross-Browser Comparison

### Feature Support Matrix

| Feature | Chrome | Firefox | Edge | Notes |
|---------|--------|---------|------|-------|
| PDF.js Rendering | ✅ Excellent | ✅ Excellent | ✅ Excellent | All browsers support well |
| Signed URL Validation | ✅ Perfect | ✅ Perfect | ✅ Perfect | Works identically |
| Range Requests | ✅ Perfect | ✅ Perfect | ✅ Perfect | Works identically |
| URL Encoding | ✅ Perfect | ✅ Perfect | ✅ Perfect | Works identically |
| Session Independence | ✅ Perfect | ✅ Perfect | ✅ Perfect | Works identically |
| Error Handling | ✅ Perfect | ✅ Perfect | ✅ Perfect | Works identically |
| DevTools Quality | ✅ Excellent | ✅ Excellent | ✅ Excellent | All provide good debugging |
| Network Tab | ✅ Excellent | ✅ Excellent | ✅ Excellent | All show detailed info |
| Console Output | ✅ Clear | ✅ Clear | ✅ Clear | All provide clear errors |

### Visual Differences

**Chrome**:
- Slightly sharper text rendering
- Chromium-based rendering engine
- Standard scrollbar styling

**Firefox**:
- Slightly smoother PDF rendering
- Gecko rendering engine
- Different scrollbar appearance
- Native PDF.js optimizations

**Edge**:
- Similar to Chrome (Chromium-based)
- Slightly different UI styling
- Standard scrollbar styling

**All browsers should**:
- Load PDFs without 403 errors
- Handle signed URLs identically
- Support range requests
- Display same error messages
- Work without sessions

---

## Cross-Browser Testing Procedure

### Step 1: Test in Chrome

1. Complete all Chrome tests
2. Document any issues
3. Take screenshots of Network tab
4. Note console warnings/errors
5. Record timing information

### Step 2: Test in Firefox

1. Complete all Firefox tests
2. Compare with Chrome results
3. Document any differences
4. Note console warnings/errors
5. Record timing information

### Step 3: Test in Edge

1. Complete all Edge tests
2. Compare with Chrome and Firefox results
3. Document any differences
4. Note console warnings/errors
5. Record timing information

### Step 4: Compare Results

1. Review all test results
2. Identify browser-specific issues
3. Determine if differences are acceptable
4. Document any required fixes

### Step 5: Document Findings

Use this template:

```markdown
## Browser Comparison Results

**Test Date**: [Date]
**Tester**: [Name]

### Chrome Results
- **Version**: [Version]
- **Tests Passed**: [Number]
- **Tests Failed**: [Number]
- **Issues**: [List]

### Firefox Results
- **Version**: [Version]
- **Tests Passed**: [Number]
- **Tests Failed**: [Number]
- **Issues**: [List]

### Edge Results
- **Version**: [Version]
- **Tests Passed**: [Number]
- **Tests Failed**: [Number]
- **Issues**: [List]

### Differences Found
1. [Difference 1]
2. [Difference 2]

### Acceptable Differences
- [List of minor cosmetic differences]

### Critical Issues
- [List of issues requiring fixes]

### Recommendation
⬜ Approve for production
⬜ Fix issues and retest
⬜ Document known limitations
```

---

## Testing Tips

### General Tips

1. **Test at 100% Zoom**: Browser zoom can affect behavior
2. **Disable Extensions**: Ad blockers may interfere
3. **Check JavaScript**: Ensure JavaScript enabled
4. **Test Cookies**: Ensure cookies enabled (for initial auth)
5. **Update Browser**: Use latest stable version

### Chrome Tips

- Use incognito mode for clean testing
- Enable "Disable cache" in DevTools Network tab
- Use "Copy as cURL" for command-line testing
- Check "Preserve log" to see all requests

### Firefox Tips

- Use private window for clean testing
- Enable "Persist Logs" in Network tab
- Use "Copy as cURL" for command-line testing
- Check Storage tab for cookie inspection

### Edge Tips

- Use InPrivate window for clean testing
- Enable "Preserve log" in Network tab
- Use "Copy as cURL" for command-line testing
- Similar to Chrome (Chromium-based)

---

## Troubleshooting Browser-Specific Issues

### Chrome Issues

**Issue**: Extensions blocking requests
- **Solution**: Test in incognito mode (`Ctrl+Shift+N`)

**Issue**: Cache causing old version to load
- **Solution**: Hard refresh (`Ctrl+Shift+R`)

**Issue**: CORS errors
- **Solution**: Check server CORS configuration

### Firefox Issues

**Issue**: Different PDF.js version
- **Solution**: Verify compatibility, test thoroughly

**Issue**: Extensions blocking requests
- **Solution**: Test in private window (`Ctrl+Shift+P`)

**Issue**: Cache causing old version to load
- **Solution**: Hard refresh (`Ctrl+Shift+R`)

### Edge Issues

**Issue**: Similar to Chrome (Chromium-based)
- **Solution**: Use same solutions as Chrome

**Issue**: Extensions blocking requests
- **Solution**: Test in InPrivate window (`Ctrl+Shift+N`)

---

## Acceptance Criteria

### Chrome Acceptance
- [ ] All core features work
- [ ] No critical console errors
- [ ] Signed URLs validate correctly
- [ ] Range requests work
- [ ] Session independence verified
- [ ] Error handling works

### Firefox Acceptance
- [ ] All core features work
- [ ] No critical console errors
- [ ] Signed URLs validate correctly
- [ ] Range requests work
- [ ] Session independence verified
- [ ] Error handling works

### Edge Acceptance
- [ ] All core features work
- [ ] No critical console errors
- [ ] Signed URLs validate correctly
- [ ] Range requests work
- [ ] Session independence verified
- [ ] Error handling works

### Cross-Browser Acceptance
- [ ] Core functionality identical in all browsers
- [ ] No critical browser-specific bugs
- [ ] Minor cosmetic differences documented
- [ ] Performance acceptable in all browsers
- [ ] Error handling consistent

---

## Final Checklist

Before approving for production:

- [ ] Tested in latest Chrome
- [ ] Tested in latest Firefox
- [ ] Tested in latest Edge
- [ ] All core features verified
- [ ] Cross-browser differences documented
- [ ] Known limitations documented
- [ ] Performance acceptable
- [ ] No critical bugs found
- [ ] Test results documented
- [ ] Screenshots captured
- [ ] Sign-off obtained

---

**Document Version**: 1.0  
**Last Updated**: January 2024  
**Requirement**: 6.4 - Browser-specific testing documentation  
**Spec**: pdf-stream-403-fix

