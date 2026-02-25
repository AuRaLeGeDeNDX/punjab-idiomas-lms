# File Upload User Guide

## Overview

This guide helps you successfully upload files to the Content Builder system. Whether you're uploading images, documents, audio, or video files, this guide provides everything you need to know about supported file types, size limits, troubleshooting common issues, and best practices.

## Table of Contents

1. [Supported File Types and Limits](#supported-file-types-and-limits)
2. [How to Upload Files](#how-to-upload-files)
3. [Troubleshooting Common Issues](#troubleshooting-common-issues)
4. [Best Practices](#best-practices)
5. [Browser Compatibility](#browser-compatibility)
6. [Frequently Asked Questions](#frequently-asked-questions)

## Supported File Types and Limits

### Image Files
- **Supported formats**: JPG, JPEG, PNG, GIF, WebP
- **Maximum file size**: 10 MB
- **Recommended use**: Course illustrations, diagrams, photos, graphics
- **Best formats**: 
  - JPG for photographs
  - PNG for graphics with transparency
  - WebP for modern browsers (smaller file sizes)

### PDF Documents
- **Supported formats**: PDF only
- **Maximum file size**: 50 MB
- **Recommended use**: Course materials, handouts, assignments, reference documents
- **Tips**: Optimize PDFs before uploading to reduce file size

### Audio Files
- **Supported formats**: MP3, WAV, OGG, M4A
- **Maximum file size**: 100 MB
- **Recommended use**: Lectures, podcasts, audio instructions, music
- **Best formats**:
  - MP3 for general use (good compression)
  - WAV for high-quality audio
  - M4A for Apple ecosystem compatibility

### Video Files
- **Supported formats**: MP4, WebM, OGG, MOV
- **Maximum file size**: 500 MB
- **Recommended use**: Lectures, demonstrations, tutorials
- **Best formats**:
  - MP4 for maximum compatibility
  - WebM for web optimization
  - MOV for high-quality content

## How to Upload Files

### Step-by-Step Upload Process

1. **Select Content Type**
   - Choose the appropriate content type (Image, PDF, Audio, or Video)
   - The system will automatically configure the correct file type restrictions

2. **Choose Your File**
   - Click the "Choose File" button or drag and drop your file
   - The system will immediately validate your file before upload

3. **File Validation**
   - File size and type are checked instantly
   - You'll see immediate feedback if there are any issues
   - Valid files will show a preview with file details

4. **Upload Progress**
   - Watch the progress bar for large files
   - Upload status updates in real-time
   - You can continue working while files upload

5. **Completion**
   - Successful uploads show a green checkmark
   - Failed uploads display specific error messages
   - You can retry failed uploads with one click

### Upload Interface Features

- **Drag and Drop**: Simply drag files from your computer directly onto the upload area
- **File Preview**: Images show thumbnails; other files display name, size, and type
- **Progress Tracking**: Real-time upload progress for files over 1 MB
- **Instant Validation**: Immediate feedback on file compatibility
- **Retry Mechanism**: One-click retry for failed uploads

## Troubleshooting Common Issues

### File Size Too Large

**Error Message**: "File size (X MB) exceeds maximum allowed size (Y MB)"

**Solutions**:
1. **Compress your file**:
   - Images: Use online tools like TinyPNG or reduce image dimensions
   - PDFs: Use PDF compression tools or reduce image quality within the PDF
   - Audio: Convert to MP3 with lower bitrate (128-192 kbps is usually sufficient)
   - Video: Reduce resolution or use more efficient encoding

2. **Check file size limits**:
   - Images: 10 MB maximum
   - PDFs: 50 MB maximum
   - Audio: 100 MB maximum
   - Video: 500 MB maximum

### Unsupported File Type

**Error Message**: "File type '.xyz' is not allowed. Allowed types: [list]"

**Solutions**:
1. **Convert your file**:
   - Use online converters or software to change to a supported format
   - Ensure the file extension matches the actual file type

2. **Check supported formats**:
   - Images: .jpg, .jpeg, .png, .gif, .webp
   - Documents: .pdf only
   - Audio: .mp3, .wav, .ogg, .m4a
   - Video: .mp4, .webm, .ogg, .mov

### Upload Interrupted or Failed

**Error Message**: "File upload was interrupted" or "Upload failed"

**Solutions**:
1. **Check your internet connection**:
   - Ensure stable internet connectivity
   - Try uploading during off-peak hours for large files

2. **Retry the upload**:
   - Click the "Retry" button that appears after failed uploads
   - Clear your browser cache if problems persist

3. **Try a different browser**:
   - Switch to Chrome, Firefox, Safari, or Edge
   - Ensure your browser is up to date

### Server Configuration Errors

**Error Message**: "Server configuration error" or "Contact administrator"

**Solutions**:
1. **Contact your system administrator**:
   - Provide the correlation ID shown in the error message
   - Include details about the file you were trying to upload

2. **Try again later**:
   - Server issues may be temporary
   - Check if other users are experiencing similar problems

### File Appears Corrupted

**Error Message**: "File appears to be corrupted or has an incorrect extension"

**Solutions**:
1. **Verify file integrity**:
   - Try opening the file on your computer
   - Re-save or re-export the file from its original application

2. **Check file extension**:
   - Ensure the file extension matches the actual file type
   - Don't just rename the extension; use proper conversion tools

## Best Practices

### File Preparation

1. **Optimize Before Upload**:
   - Compress images without losing quality
   - Use appropriate resolution (web images rarely need more than 1920px width)
   - Convert audio to MP3 for smaller file sizes
   - Use efficient video codecs (H.264 for MP4)

2. **File Naming**:
   - Use descriptive, meaningful file names
   - Avoid special characters and spaces
   - Use hyphens or underscores instead of spaces
   - Example: `lesson-1-introduction.pdf` instead of `Lesson 1 Introduction!.pdf`

3. **File Organization**:
   - Plan your content structure before uploading
   - Group related files logically
   - Consider creating a naming convention for your course

### Upload Strategy

1. **Upload During Off-Peak Hours**:
   - Large files upload faster during low-traffic periods
   - Early morning or late evening often work best

2. **Stable Internet Connection**:
   - Use wired connection for large files when possible
   - Avoid uploading during network maintenance windows
   - Close other bandwidth-intensive applications

3. **Batch Processing**:
   - Upload multiple small files together
   - Process large files one at a time
   - Take breaks between large uploads to avoid timeouts

### Quality Guidelines

1. **Images**:
   - Use 72-96 DPI for web display
   - Optimize for web (JPEG quality 80-90% is usually sufficient)
   - Consider using WebP format for better compression

2. **Audio**:
   - 128-192 kbps MP3 is sufficient for most educational content
   - Use 44.1 kHz sample rate for compatibility
   - Normalize audio levels for consistent playback

3. **Video**:
   - 720p (1280x720) is often sufficient for educational content
   - Use H.264 codec for maximum compatibility
   - Include captions when possible for accessibility

## Browser Compatibility

### Fully Supported Browsers

- **Google Chrome** 60+ (recommended)
- **Mozilla Firefox** 55+
- **Safari** 12+
- **Microsoft Edge** 79+

### Features by Browser

| Feature | Chrome | Firefox | Safari | Edge |
|---------|--------|---------|--------|------|
| Drag & Drop Upload | ✅ | ✅ | ✅ | ✅ |
| Upload Progress | ✅ | ✅ | ✅ | ✅ |
| File Preview | ✅ | ✅ | ✅ | ✅ |
| Large File Support | ✅ | ✅ | ✅ | ✅ |
| WebP Images | ✅ | ✅ | ✅ | ✅ |

### Browser-Specific Notes

- **Safari**: May require enabling "Allow all cookies" for upload progress tracking
- **Firefox**: Ensure JavaScript is enabled for full functionality
- **Edge**: Legacy Edge (pre-Chromium) has limited support
- **Mobile Browsers**: Full functionality on iOS Safari 12+ and Chrome Mobile 60+

### Troubleshooting Browser Issues

1. **Clear Browser Cache**:
   - Chrome: Ctrl+Shift+Delete (Windows) or Cmd+Shift+Delete (Mac)
   - Firefox: Ctrl+Shift+Delete (Windows) or Cmd+Shift+Delete (Mac)
   - Safari: Develop menu → Empty Caches

2. **Disable Browser Extensions**:
   - Try uploading in incognito/private mode
   - Disable ad blockers temporarily
   - Check if security extensions are blocking uploads

3. **Update Your Browser**:
   - Ensure you're using the latest version
   - Enable automatic updates for security and compatibility

## Frequently Asked Questions

### General Questions

**Q: Can I upload multiple files at once?**
A: Currently, you can upload one file per content block. To add multiple files, create separate content blocks for each file.

**Q: What happens to my files after upload?**
A: Files are securely stored on the server with unique names to prevent conflicts. Original filenames are preserved for display purposes.

**Q: Can I replace an uploaded file?**
A: Yes, you can edit a content block and upload a new file to replace the existing one. The old file will be automatically removed.

**Q: Are my uploaded files backed up?**
A: Yes, uploaded files are included in regular system backups. Contact your administrator for specific backup policies.

### Technical Questions

**Q: Why do I see a correlation ID in error messages?**
A: Correlation IDs help technical support quickly locate and diagnose upload issues. Always include this ID when reporting problems.

**Q: Can I upload files larger than the stated limits?**
A: No, file size limits are enforced for system stability and performance. You'll need to compress or split large files.

**Q: What's the difference between file size and upload size limits?**
A: File size limits apply to individual files. Upload size limits (set by the server) apply to the total data in a single request.

**Q: Why was my file rejected even though it has the right extension?**
A: The system checks both file extension and actual file content. Files with mismatched extensions (e.g., a text file renamed to .jpg) will be rejected.

### Security Questions

**Q: Are uploaded files scanned for viruses?**
A: The system performs basic security checks on file headers and content. However, you should still scan files with your own antivirus software before uploading.

**Q: Can other users access my uploaded files?**
A: File access is controlled by the content visibility settings. Files in "teacher_only" content are not accessible to students.

**Q: What file information is logged?**
A: The system logs file names, sizes, upload times, and user information for security and debugging purposes. File contents are not logged.

### Performance Questions

**Q: Why do large files take so long to upload?**
A: Upload speed depends on your internet connection, server load, and file size. The system shows progress indicators for files over 1 MB.

**Q: Can I continue using the system while files upload?**
A: Yes, you can navigate to other pages and continue working while files upload in the background.

**Q: What happens if I close my browser during upload?**
A: The upload will be interrupted and you'll need to start over. Keep your browser open until you see the success confirmation.

## Getting Help

### Before Contacting Support

1. **Check this guide** for solutions to common problems
2. **Try the troubleshooting steps** listed above
3. **Note any error messages** and correlation IDs
4. **Test with a different file** to isolate the issue

### When Contacting Support

Include the following information:
- **File details**: Name, size, type, and source
- **Error messages**: Complete text including correlation IDs
- **Browser information**: Name, version, and operating system
- **Steps taken**: What you tried before contacting support
- **Screenshots**: If helpful for explaining the issue

### Support Channels

- **System Administrator**: For server configuration issues
- **Technical Support**: For application-related problems
- **User Community**: For tips and best practices from other users

---

*Last updated: [Current Date]*
*For technical issues, contact your system administrator*
*For feature requests, use the feedback system in the application*