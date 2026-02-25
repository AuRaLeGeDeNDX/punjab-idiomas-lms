# File Upload Frequently Asked Questions (FAQ)

## 🤔 General Questions

### Q: What file types can I upload?
**A:** The system supports four main categories:
- **Images**: JPG, JPEG, PNG, GIF, WebP (max 10 MB)
- **Documents**: PDF only (max 50 MB)
- **Audio**: MP3, WAV, OGG, M4A (max 100 MB)
- **Video**: MP4, WebM, OGG, MOV (max 500 MB)

### Q: Why are there different size limits for different file types?
**A:** Size limits are set based on typical use cases and server performance:
- Images are usually smaller and load frequently
- PDFs can contain many pages and images
- Audio files need higher quality for clarity
- Video files require the most storage space

### Q: Can I upload multiple files at once?
**A:** Currently, you upload one file per content block. To add multiple files, create separate content blocks for each file. This gives you better control over organization and visibility settings.

### Q: What happens to my files after I upload them?
**A:** Your files are:
- Stored securely on the server with unique names
- Backed up according to system backup policies
- Accessible only to users with appropriate permissions
- Preserved with their original names for display

### Q: Can I replace a file after uploading?
**A:** Yes! Edit the content block and upload a new file. The old file will be automatically removed and replaced with the new one.

## 🔧 Technical Questions

### Q: What's a correlation ID and why do I see it in error messages?
**A:** A correlation ID is a unique identifier that helps technical support quickly locate and diagnose your specific upload issue. Always include this ID when reporting problems - it's like a tracking number for your upload attempt.

### Q: Why do I need to keep my browser open during uploads?
**A:** File uploads happen through your browser's connection to the server. If you close the browser or navigate away, the upload connection is lost and the upload fails. For large files, plan to keep your browser open until you see the success confirmation.

### Q: Can I use the system while files are uploading?
**A:** Yes! You can navigate to other pages and continue working while files upload in the background. Just don't close your browser entirely.

### Q: What browsers work best for file uploads?
**A:** For the best experience, use:
- **Chrome 60+** (recommended)
- **Firefox 55+**
- **Safari 12+**
- **Edge 79+**

Avoid Internet Explorer and very old browser versions.

### Q: Why does my upload seem slow?
**A:** Upload speed depends on:
- Your internet connection speed
- File size
- Server load
- Time of day (peak hours are slower)
- Other applications using your bandwidth

Try uploading during off-peak hours or using a wired internet connection.

## 📁 File-Specific Questions

### Q: Why was my image rejected even though it's a JPG?
**A:** The system checks both the file extension and the actual file content. If you renamed a different file type to .jpg, it will be rejected. Use proper image conversion tools instead of just renaming files.

### Q: Can I upload Word documents or PowerPoint presentations?
**A:** No, only PDF documents are supported. Convert your Word docs and PowerPoint presentations to PDF before uploading. This ensures consistent display across all devices and browsers.

### Q: Why can't I upload my video even though it's under the size limit?
**A:** Check these common issues:
- **File format**: Ensure it's MP4, WebM, OGG, or MOV
- **File corruption**: Try playing the video on your computer first
- **Encoding**: Some video codecs aren't supported
- **Network**: Large videos need stable internet connections

### Q: My PDF looks different after upload. Why?
**A:** PDFs should display identically after upload. If they look different:
- Check if you're using proprietary fonts
- Ensure the PDF isn't password-protected
- Verify the PDF isn't corrupted
- Try re-creating the PDF with standard fonts

### Q: Can I upload audio recordings from my phone?
**A:** Yes! Most phone recordings are in supported formats (M4A, MP3). You can upload directly from your phone's browser or transfer the file to your computer first.

## 🚨 Error Messages and Troubleshooting

### Q: I get "File size exceeds maximum allowed size" - what do I do?
**A:** You need to reduce your file size:
- **Images**: Compress using tools like TinyPNG or reduce dimensions
- **PDFs**: Use PDF compression tools or reduce image quality
- **Audio**: Convert to MP3 with lower bitrate (128-192 kbps)
- **Video**: Reduce resolution or use better compression

### Q: What does "File appears to be corrupted" mean?
**A:** This usually means:
- The file extension doesn't match the actual file type
- The file was damaged during transfer or storage
- The file was created with incompatible software

**Solutions**:
1. Try opening the file on your computer - can you view/play it normally?
2. If yes, re-save or re-export the file
3. If no, get a fresh copy of the file

### Q: I get "Upload interrupted" errors. How do I fix this?
**A:** This indicates a network problem:
1. **Check your internet connection**
2. **Try again during off-peak hours**
3. **Use a wired connection** instead of WiFi
4. **Close other bandwidth-heavy applications**
5. **Click the "Retry" button** when it appears

### Q: What does "Server configuration error" mean?
**A:** This indicates a server-side issue that you can't fix yourself:
1. **Note the correlation ID** from the error message
2. **Try again in a few minutes** (may be temporary)
3. **Contact your administrator** if the problem persists
4. **Include the correlation ID** when reporting the issue

### Q: Why do I get different error messages for the same file?
**A:** The system performs multiple validation checks in sequence:
1. **PHP upload errors** (server-level issues)
2. **File size validation**
3. **File type validation**
4. **Content validation**
5. **Security checks**

You'll see the error for whichever check fails first.

## 📱 Mobile and Device Questions

### Q: Can I upload files from my phone or tablet?
**A:** Yes! Mobile browsers support file uploads with access to:
- **Camera** (take new photos/videos)
- **Photo gallery**
- **File manager**
- **Cloud storage** (Google Drive, iCloud, etc.)

### Q: Why can't I drag and drop files on my tablet?
**A:** Drag and drop is designed for mouse/trackpad use. On touch devices, use the "Choose File" button instead, which will give you access to your device's file selection interface.

### Q: My upload stops when I switch apps on my phone. Why?
**A:** Mobile browsers may pause uploads when the app goes to the background. Keep the browser active during uploads, especially for large files.

### Q: Can I upload files from cloud storage (Google Drive, Dropbox, etc.)?
**A:** Yes, if your mobile browser can access these services. The exact options depend on your device and installed apps.

## 🔒 Security and Privacy Questions

### Q: Are my uploaded files secure?
**A:** Yes, the system implements multiple security measures:
- Files are stored outside the web root (not directly accessible)
- Unique file names prevent conflicts and enumeration
- File content is validated for security threats
- Access is controlled by content visibility settings

### Q: Can other users see my uploaded files?
**A:** File visibility depends on your content block settings:
- **Student**: Visible to students and teachers
- **Teacher Only**: Visible only to teachers and administrators

### Q: What information is logged when I upload files?
**A:** The system logs:
- File name, size, and type
- Upload timestamp and user
- Success/failure status
- Error details (for troubleshooting)
- **Note**: File contents are never logged

### Q: Can I delete uploaded files?
**A:** Yes, you can:
- **Replace files** by editing the content block and uploading a new file
- **Delete content blocks** entirely (removes the file)
- **Contact administrators** for bulk file management

### Q: Are there any file types that are blocked for security?
**A:** Yes, executable files and potentially dangerous formats are blocked:
- .exe, .bat, .cmd, .scr (executable files)
- .js, .vbs, .php (script files)
- Files with suspicious content signatures

## 💡 Best Practices Questions

### Q: What's the best way to prepare files for upload?
**A:** Follow these guidelines:
1. **Optimize file sizes** without losing quality
2. **Use descriptive filenames** (no spaces or special characters)
3. **Test files** on your computer before uploading
4. **Choose appropriate formats** for your content type
5. **Plan uploads** during stable internet periods

### Q: How should I name my files?
**A:** Use this format: `course-type-description-version`
- **Good**: `math101-lecture-introduction-v1.mp4`
- **Bad**: `My Video File (Final Version)!!!.mp4`

### Q: When is the best time to upload large files?
**A:** Upload during off-peak hours:
- **Early morning** (6-8 AM)
- **Late evening** (10 PM - midnight)
- **Weekends** (generally less network traffic)

### Q: Should I compress my files before uploading?
**A:** It depends on the file type:
- **Images**: Yes, compress to reduce size while maintaining quality
- **PDFs**: Yes, if they contain many images
- **Audio**: Convert to MP3 with appropriate bitrate
- **Video**: Yes, optimize for web delivery

## 🆘 Getting Help Questions

### Q: I've tried everything in the troubleshooting guide. What now?
**A:** Contact your system administrator with:
- **Exact error messages** (including correlation IDs)
- **File details** (name, size, type)
- **Browser information** (name and version)
- **Steps you've already tried**
- **Screenshots** if helpful

### Q: How do I report a bug or request a feature?
**A:** Use your organization's standard support channels:
- **IT Help Desk** for technical issues
- **System Administrator** for configuration problems
- **Feedback System** (if available) for feature requests

### Q: Can I get training on using the file upload system?
**A:** Check with your organization for:
- **User training sessions**
- **Documentation workshops**
- **Peer mentoring programs**
- **Online tutorials** specific to your system

### Q: Where can I find the latest updates to this system?
**A:** Stay informed through:
- **System announcements**
- **Release notes** (if provided)
- **Administrator communications**
- **User community forums** (if available)

## 📊 Performance and Limits Questions

### Q: Why are there file size limits?
**A:** Limits ensure:
- **System stability** and performance
- **Fair resource usage** among all users
- **Reasonable upload times**
- **Storage management**
- **Network bandwidth** conservation

### Q: Can file size limits be increased?
**A:** Limits are set by system administrators based on:
- Server capacity
- Network bandwidth
- Storage availability
- User needs assessment

Contact your administrator if you have legitimate needs for larger files.

### Q: How many files can I upload?
**A:** There's no specific limit on the number of files, but consider:
- **Storage quotas** (if applicable)
- **Course organization** (too many files can be overwhelming)
- **Performance impact** (many large files may slow loading)

### Q: What happens if the server runs out of storage space?
**A:** The system monitors storage and will:
- **Prevent new uploads** when space is low
- **Display appropriate error messages**
- **Alert administrators** to the issue
- **Provide guidance** on contacting support

## 🔄 Backup and Recovery Questions

### Q: What happens if I accidentally delete a file?
**A:** Recovery options depend on your system configuration:
- **Recent deletions** may be recoverable from backups
- **Contact administrators** immediately for recovery requests
- **Provide specific details** about what was deleted and when

### Q: Are my files backed up automatically?
**A:** Yes, uploaded files are typically included in regular system backups, but:
- **Backup frequency** varies by organization
- **Recovery procedures** differ by system
- **Contact administrators** for specific backup policies

### Q: Can I download my uploaded files?
**A:** This depends on system configuration:
- **Some systems** allow direct file downloads
- **Others** may require administrator assistance
- **Check with your administrator** about download policies

---

## 🎯 Quick Answer Summary

**Most Common Issues:**
1. **File too large** → Compress the file
2. **Wrong file type** → Convert to supported format
3. **Upload interrupted** → Check internet connection and retry
4. **File corrupted** → Re-save the file and try again
5. **Browser issues** → Update browser or try a different one

**Best File Formats:**
- **Images**: JPG for photos, PNG for graphics
- **Documents**: PDF (convert from Word/PowerPoint)
- **Audio**: MP3 for most uses
- **Video**: MP4 for maximum compatibility

**When to Contact Support:**
- Server configuration errors
- Repeated failures with different files/browsers
- Any error message with a correlation ID
- Questions about system policies or limits

---

*Can't find your question here? Check the full user guide or contact your system administrator for personalized help.*