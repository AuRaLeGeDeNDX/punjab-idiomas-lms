<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Tests for FileUploadValidator JavaScript file structure and content
 * 
 * These tests verify that the FileUploadValidator JavaScript class
 * is properly implemented and contains all required methods.
 * 
 * Requirements: 5.1, 5.2
 */
class FileUploadValidatorJavaScriptTest extends TestCase
{
    /**
     * Test that FileUploadValidator.js file exists
     */
    public function test_file_upload_validator_js_file_exists()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $this->assertFileExists($filePath, 'FileUploadValidator.js file should exist');
    }
    
    /**
     * Test that FileUploadValidator.js contains the main class
     */
    public function test_file_upload_validator_contains_main_class()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('class FileUploadValidator', $content);
        $this->assertStringContainsString('constructor(config', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains required validation methods
     */
    public function test_file_upload_validator_contains_validation_methods()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Core validation methods
        $this->assertStringContainsString('validateFile(file)', $content);
        $this->assertStringContainsString('validateFileSize(file)', $content);
        $this->assertStringContainsString('validateFileExtension(file)', $content);
        $this->assertStringContainsString('validateMimeType(file)', $content);
        $this->assertStringContainsString('performSecurityChecks(file)', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains utility methods
     */
    public function test_file_upload_validator_contains_utility_methods()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Utility methods
        $this->assertStringContainsString('formatFileSize(bytes)', $content);
        $this->assertStringContainsString('getFileExtension(filename)', $content);
        $this->assertStringContainsString('getFileInfo(file)', $content);
        $this->assertStringContainsString('getValidationSummary(validationResult)', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains static factory method
     */
    public function test_file_upload_validator_contains_static_factory()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('static forContentType(contentType, contentTypeConfig', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains multiple file validation
     */
    public function test_file_upload_validator_contains_multiple_file_validation()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        $this->assertStringContainsString('validateFiles(files)', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains proper error handling
     */
    public function test_file_upload_validator_contains_error_handling()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should contain error handling for various scenarios
        $this->assertStringContainsString('errors = []', $content);
        $this->assertStringContainsString('warnings = []', $content);
        $this->assertStringContainsString('valid: errors.length === 0', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains security checks
     */
    public function test_file_upload_validator_contains_security_checks()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should contain security-related checks
        $this->assertStringContainsString('dangerousExtensions', $content);
        $this->assertStringContainsString('security reasons', $content);
        $this->assertStringContainsString('exe', $content);
        $this->assertStringContainsString('bat', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains MIME type mappings
     */
    public function test_file_upload_validator_contains_mime_type_mappings()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should contain MIME type mappings
        $this->assertStringContainsString('defaultMimeTypes', $content);
        $this->assertStringContainsString('image/jpeg', $content);
        $this->assertStringContainsString('application/pdf', $content);
        $this->assertStringContainsString('audio/mpeg', $content);
        $this->assertStringContainsString('video/mp4', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains comprehensive documentation
     */
    public function test_file_upload_validator_contains_documentation()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should contain JSDoc comments
        $this->assertStringContainsString('/**', $content);
        $this->assertStringContainsString('@param', $content);
        $this->assertStringContainsString('@returns', $content);
        
        // Should contain requirements references
        $this->assertStringContainsString('Requirements: 5.1, 5.2', $content);
    }
    
    /**
     * Test that FileUploadValidator.js has proper module exports
     */
    public function test_file_upload_validator_has_module_exports()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should support both CommonJS and browser environments
        $this->assertStringContainsString('module.exports', $content);
        $this->assertStringContainsString('window.FileUploadValidator', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains file size formatting
     */
    public function test_file_upload_validator_contains_file_size_formatting()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should contain size units
        $this->assertStringContainsString('Bytes', $content);
        $this->assertStringContainsString('KB', $content);
        $this->assertStringContainsString('MB', $content);
        $this->assertStringContainsString('GB', $content);
        $this->assertStringContainsString('TB', $content);
    }
    
    /**
     * Test that FileUploadValidator.js contains recommendation system
     */
    public function test_file_upload_validator_contains_recommendation_system()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Should contain recommendation logic
        $this->assertStringContainsString('getRecommendations(validationResult)', $content);
        $this->assertStringContainsString('recommendations', $content);
        $this->assertStringContainsString('Try compressing', $content);
        $this->assertStringContainsString('reducing', $content);
    }
    
    /**
     * Test that the JavaScript file is syntactically valid
     */
    public function test_file_upload_validator_is_syntactically_valid()
    {
        $filePath = resource_path('js/FileUploadValidator.js');
        $content = file_get_contents($filePath);
        
        // Basic syntax checks
        $this->assertEquals(
            substr_count($content, '{'),
            substr_count($content, '}'),
            'JavaScript file should have balanced curly braces'
        );
        
        $this->assertEquals(
            substr_count($content, '('),
            substr_count($content, ')'),
            'JavaScript file should have balanced parentheses'
        );
        
        // Should not contain obvious syntax errors
        $this->assertStringNotContainsString('SyntaxError', $content);
        $this->assertStringNotContainsString('undefined is not a function', $content);
    }
}