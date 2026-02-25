/**
 * FileUploadValidator - Client-side file validation for content builder
 * 
 * Provides comprehensive file validation including:
 * - File size validation with detailed error messages
 * - File extension validation against whitelist
 * - Empty file detection
 * - MIME type validation
 * - Immediate feedback without server round-trip
 * 
 * Requirements: 5.1, 5.2
 */
class FileUploadValidator {
    /**
     * Initialize the validator with configuration
     * @param {Object} config - Configuration object
     * @param {number} config.max_file_size - Maximum file size in bytes
     * @param {Array} config.allowed_extensions - Array of allowed file extensions
     * @param {Array} config.allowed_mime_types - Array of allowed MIME types
     * @param {string} config.content_type - Content type (image, pdf, audio, video, etc.)
     */
    constructor(config = {}) {
        this.config = config;
        this.maxFileSize = config.max_file_size || (10 * 1024 * 1024); // 10MB default
        this.allowedExtensions = config.allowed_extensions || [];
        this.allowedMimeTypes = config.allowed_mime_types || [];
        this.contentType = config.content_type || 'file';
        
        // Default MIME type mappings for common extensions
        this.defaultMimeTypes = {
            // Images
            'jpg': ['image/jpeg'],
            'jpeg': ['image/jpeg'],
            'png': ['image/png'],
            'gif': ['image/gif'],
            'webp': ['image/webp'],
            'svg': ['image/svg+xml'],
            'bmp': ['image/bmp'],
            'ico': ['image/x-icon', 'image/vnd.microsoft.icon'],
            
            // Documents
            'pdf': ['application/pdf'],
            'doc': ['application/msword'],
            'docx': ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls': ['application/vnd.ms-excel'],
            'xlsx': ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'ppt': ['application/vnd.ms-powerpoint'],
            'pptx': ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'txt': ['text/plain'],
            'rtf': ['application/rtf'],
            
            // Audio
            'mp3': ['audio/mpeg', 'audio/mp3'],
            'wav': ['audio/wav', 'audio/wave'],
            'ogg': ['audio/ogg'],
            'aac': ['audio/aac'],
            'flac': ['audio/flac'],
            'm4a': ['audio/mp4', 'audio/m4a'],
            'wma': ['audio/x-ms-wma'],
            
            // Video
            'mp4': ['video/mp4'],
            'avi': ['video/x-msvideo'],
            'mov': ['video/quicktime'],
            'wmv': ['video/x-ms-wmv'],
            'flv': ['video/x-flv'],
            'webm': ['video/webm'],
            'mkv': ['video/x-matroska'],
            '3gp': ['video/3gpp'],
            
            // Archives
            'zip': ['application/zip'],
            'rar': ['application/x-rar-compressed'],
            '7z': ['application/x-7z-compressed'],
            'tar': ['application/x-tar'],
            'gz': ['application/gzip']
        };
    }
    
    /**
     * Validate a file against all configured rules
     * @param {File} file - The file to validate
     * @returns {Object} Validation result with valid flag and errors array
     */
    validateFile(file) {
        const errors = [];
        const warnings = [];
        
        // Basic file object validation
        if (!file || !(file instanceof File)) {
            errors.push('Invalid file object. Please select a valid file.');
            return { valid: false, errors, warnings };
        }
        
        // Check for empty file
        if (file.size === 0) {
            errors.push('File is empty. Please select a valid file with content.');
            return { valid: false, errors, warnings };
        }
        
        // Validate file size
        const sizeValidation = this.validateFileSize(file);
        if (!sizeValidation.valid) {
            errors.push(...sizeValidation.errors);
        }
        if (sizeValidation.warnings) {
            warnings.push(...sizeValidation.warnings);
        }
        
        // Validate file extension
        const extensionValidation = this.validateFileExtension(file);
        if (!extensionValidation.valid) {
            errors.push(...extensionValidation.errors);
        }
        
        // Validate MIME type
        const mimeValidation = this.validateMimeType(file);
        if (!mimeValidation.valid) {
            errors.push(...mimeValidation.errors);
        }
        
        // Additional security checks
        const securityValidation = this.performSecurityChecks(file);
        if (!securityValidation.valid) {
            errors.push(...securityValidation.errors);
        }
        if (securityValidation.warnings) {
            warnings.push(...securityValidation.warnings);
        }
        
        return {
            valid: errors.length === 0,
            errors,
            warnings,
            fileInfo: this.getFileInfo(file)
        };
    }
    
    /**
     * Validate file size against configured limits
     * @param {File} file - The file to validate
     * @returns {Object} Validation result
     */
    validateFileSize(file) {
        const errors = [];
        const warnings = [];
        
        // Check against maximum file size
        if (file.size > this.maxFileSize) {
            const actualSize = this.formatFileSize(file.size);
            const maxSize = this.formatFileSize(this.maxFileSize);
            errors.push(`File size (${actualSize}) exceeds maximum allowed size (${maxSize}). Please choose a smaller file.`);
        }
        
        // Warn about very large files that might cause performance issues
        const performanceThreshold = 100 * 1024 * 1024; // 100MB
        if (file.size > performanceThreshold && file.size <= this.maxFileSize) {
            warnings.push(`Large file detected (${this.formatFileSize(file.size)}). Upload may take longer than usual.`);
        }
        
        // Check for extremely large files that might cause browser issues
        const browserLimit = 500 * 1024 * 1024; // 500MB
        if (file.size > browserLimit) {
            errors.push('File is extremely large and may cause browser performance issues. Consider using a smaller file or uploading via alternative methods.');
        }
        
        return { valid: errors.length === 0, errors, warnings };
    }
    
    /**
     * Validate file extension against allowed extensions
     * @param {File} file - The file to validate
     * @returns {Object} Validation result
     */
    validateFileExtension(file) {
        const errors = [];
        
        // Extract file extension
        const extension = this.getFileExtension(file.name);
        
        if (!extension) {
            errors.push('File has no extension. Please ensure the file has a proper extension (e.g., .jpg, .pdf, .mp3).');
            return { valid: false, errors };
        }
        
        // Check against allowed extensions if configured
        if (this.allowedExtensions.length > 0) {
            if (!this.allowedExtensions.includes(extension)) {
                const allowedList = this.allowedExtensions.map(ext => `.${ext}`).join(', ');
                errors.push(`File type '.${extension}' is not allowed. Allowed types: ${allowedList}`);
            }
        }
        
        return { valid: errors.length === 0, errors };
    }
    
    /**
     * Validate MIME type against expected types for the file extension
     * @param {File} file - The file to validate
     * @returns {Object} Validation result
     */
    validateMimeType(file) {
        const errors = [];
        
        const extension = this.getFileExtension(file.name);
        const fileMimeType = file.type;
        
        // Skip MIME validation if no MIME type is available
        if (!fileMimeType) {
            // This is common for some file types, so we'll just warn
            return { valid: true, errors };
        }
        
        // Get expected MIME types for this extension
        const expectedMimeTypes = this.getExpectedMimeTypes(extension);
        
        if (expectedMimeTypes.length > 0) {
            // Check if the file's MIME type matches any expected type
            const mimeMatches = expectedMimeTypes.some(expectedType => {
                // Allow partial matches for generic types
                return fileMimeType === expectedType || 
                       fileMimeType.startsWith(expectedType.split('/')[0] + '/');
            });
            
            if (!mimeMatches) {
                errors.push(`File MIME type '${fileMimeType}' does not match the file extension '.${extension}'. The file may be corrupted or have an incorrect extension.`);
            }
        }
        
        return { valid: errors.length === 0, errors };
    }
    
    /**
     * Perform additional security checks on the file
     * @param {File} file - The file to validate
     * @returns {Object} Validation result
     */
    performSecurityChecks(file) {
        const errors = [];
        const warnings = [];
        
        const extension = this.getFileExtension(file.name);
        const fileName = file.name.toLowerCase();
        
        // Check for potentially dangerous file extensions
        const dangerousExtensions = [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar',
            'app', 'deb', 'pkg', 'rpm', 'dmg', 'iso', 'msi', 'run'
        ];
        
        if (dangerousExtensions.includes(extension)) {
            errors.push(`File type '.${extension}' is not allowed for security reasons. Please choose a different file type.`);
        }
        
        // Check for suspicious file names
        const suspiciousPatterns = [
            /^\./, // Hidden files starting with dot
            /\.(php|asp|jsp|cgi)$/i, // Server-side script files
            /\.(htaccess|htpasswd)$/i, // Apache config files
        ];
        
        suspiciousPatterns.forEach(pattern => {
            if (pattern.test(fileName)) {
                errors.push('File name contains potentially unsafe characters or patterns. Please rename the file.');
            }
        });
        
        // Check file name length
        if (file.name.length > 255) {
            errors.push('File name is too long. Please use a shorter file name (maximum 255 characters).');
        }
        
        // Check for null bytes in filename (security issue)
        if (file.name.includes('\0')) {
            errors.push('File name contains invalid characters. Please rename the file.');
        }
        
        return { valid: errors.length === 0, errors, warnings };
    }
    
    /**
     * Get expected MIME types for a file extension
     * @param {string} extension - File extension
     * @returns {Array} Array of expected MIME types
     */
    getExpectedMimeTypes(extension) {
        // Use configured MIME types if available
        if (this.allowedMimeTypes.length > 0) {
            return this.allowedMimeTypes;
        }
        
        // Fall back to default mappings
        return this.defaultMimeTypes[extension] || [];
    }
    
    /**
     * Extract file extension from filename
     * @param {string} filename - The filename
     * @returns {string} File extension in lowercase
     */
    getFileExtension(filename) {
        if (!filename || typeof filename !== 'string') {
            return '';
        }
        
        const parts = filename.split('.');
        return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }
    
    /**
     * Format file size in human-readable format
     * @param {number} bytes - File size in bytes
     * @returns {string} Formatted file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        // Handle very large numbers that might exceed array bounds
        const sizeIndex = Math.min(i, sizes.length - 1);
        
        return parseFloat((bytes / Math.pow(k, sizeIndex)).toFixed(2)) + ' ' + sizes[sizeIndex];
    }
    
    /**
     * Get comprehensive file information
     * @param {File} file - The file object
     * @returns {Object} File information object
     */
    getFileInfo(file) {
        return {
            name: file.name,
            size: file.size,
            sizeFormatted: this.formatFileSize(file.size),
            type: file.type || 'Unknown',
            extension: this.getFileExtension(file.name),
            lastModified: file.lastModified,
            lastModifiedDate: new Date(file.lastModified).toLocaleString(),
            sizePercentage: Math.round((file.size / this.maxFileSize) * 100)
        };
    }
    
    /**
     * Validate multiple files at once
     * @param {FileList|Array} files - Files to validate
     * @returns {Object} Validation results for all files
     */
    validateFiles(files) {
        const results = [];
        const filesArray = Array.from(files);
        
        filesArray.forEach((file, index) => {
            const result = this.validateFile(file);
            results.push({
                index,
                file,
                ...result
            });
        });
        
        const allValid = results.every(result => result.valid);
        const allErrors = results.flatMap(result => result.errors);
        const allWarnings = results.flatMap(result => result.warnings);
        
        return {
            valid: allValid,
            results,
            errors: allErrors,
            warnings: allWarnings,
            fileCount: filesArray.length
        };
    }
    
    /**
     * Get validation summary for display
     * @param {Object} validationResult - Result from validateFile()
     * @returns {Object} Summary object for UI display
     */
    getValidationSummary(validationResult) {
        const { valid, errors, warnings, fileInfo } = validationResult;
        
        return {
            status: valid ? 'valid' : 'invalid',
            message: valid ? 'File is valid and ready for upload' : 'File validation failed',
            errorCount: errors.length,
            warningCount: warnings.length,
            errors,
            warnings,
            fileInfo,
            canUpload: valid,
            recommendations: this.getRecommendations(validationResult)
        };
    }
    
    /**
     * Get recommendations based on validation results
     * @param {Object} validationResult - Result from validateFile()
     * @returns {Array} Array of recommendation strings
     */
    getRecommendations(validationResult) {
        const recommendations = [];
        const { errors, fileInfo } = validationResult;
        
        errors.forEach(error => {
            const errorLower = error.toLowerCase();
            
            if (errorLower.includes('size') && errorLower.includes('exceed')) {
                if (fileInfo && fileInfo.extension) {
                    const ext = fileInfo.extension;
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                        recommendations.push('Try compressing the image or reducing its dimensions');
                        recommendations.push('Use online image compression tools to reduce file size');
                    } else if (ext === 'pdf') {
                        recommendations.push('Try reducing PDF quality or removing unnecessary pages');
                        recommendations.push('Use PDF compression tools to reduce file size');
                    } else if (['mp4', 'avi', 'mov', 'wmv'].includes(ext)) {
                        recommendations.push('Try reducing video quality or duration');
                        recommendations.push('Use video compression tools to reduce file size');
                    } else if (['mp3', 'wav', 'ogg', 'aac'].includes(ext)) {
                        recommendations.push('Try reducing audio quality or bitrate');
                        recommendations.push('Use audio compression tools to reduce file size');
                    }
                }
                recommendations.push('Consider splitting large files into smaller parts');
            }
            
            if (errorLower.includes('type') || errorLower.includes('extension')) {
                recommendations.push('Ensure the file has the correct extension for its content');
                recommendations.push('Try saving/exporting the file in a supported format');
                if (this.allowedExtensions.length > 0) {
                    recommendations.push(`Supported formats: ${this.allowedExtensions.map(ext => `.${ext}`).join(', ')}`);
                }
            }
            
            if (errorLower.includes('mime') || errorLower.includes('corrupted')) {
                recommendations.push('Try opening the file to verify it\'s not corrupted');
                recommendations.push('Re-save or re-export the file from its original application');
                recommendations.push('Ensure the file extension matches the actual file content');
            }
        });
        
        // Remove duplicates
        return [...new Set(recommendations)];
    }
    
    /**
     * Create a new validator instance with different configuration
     * @param {Object} newConfig - New configuration to merge
     * @returns {FileUploadValidator} New validator instance
     */
    withConfig(newConfig) {
        const mergedConfig = { ...this.config, ...newConfig };
        return new FileUploadValidator(mergedConfig);
    }
    
    /**
     * Static method to create validator from content type configuration
     * @param {string} contentType - Content type (image, pdf, audio, video)
     * @param {Object} contentTypeConfig - Configuration for the content type
     * @returns {FileUploadValidator} Configured validator instance
     */
    static forContentType(contentType, contentTypeConfig = {}) {
        const config = {
            content_type: contentType,
            max_file_size: contentTypeConfig.max_file_size || (10 * 1024 * 1024),
            allowed_extensions: contentTypeConfig.allowed_extensions || [],
            allowed_mime_types: contentTypeConfig.allowed_mime_types || [],
            ...contentTypeConfig
        };
        
        return new FileUploadValidator(config);
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FileUploadValidator;
} else if (typeof window !== 'undefined') {
    window.FileUploadValidator = FileUploadValidator;
}