<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\User;
use App\Services\SecurePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests for anti-download protection features in the secure PDF viewer
 * 
 * Task: 5.3 Implement anti-download protection JavaScript
 * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7
 */
class SecurePdfAntiDownloadProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected SecurePdfService $pdfService;
    protected User $user;
    protected Content $content;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdfService = app(SecurePdfService::class);
        
        // Setup storage
        Storage::fake('protected');
        
        // Create test data
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        $course = Course::factory()->create(['teacher_id' => $this->user->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        Storage::disk('protected')->put('test.pdf', 'fake pdf content');
        $this->content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'file_path' => 'test.pdf',
            'storage_disk' => 'protected',
            'title' => 'Test PDF Document',
        ]);
    }

    /**
     * Test that right-click context menu blocking is implemented
     * Requirement 3.1: Block right-click context menu
     * 
     * @test
     */
    public function it_includes_right_click_blocking_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify right-click blocking code is present
        $response->assertSee("addEventListener('contextmenu'", false);
        $response->assertSee('e.preventDefault()', false);
        $response->assertSee('Right-click is disabled on this secure viewer', false);
    }

    /**
     * Test that Ctrl+S (Save) keyboard shortcut is blocked
     * Requirement 3.2: Block Ctrl+S with alert
     * 
     * @test
     */
    public function it_includes_ctrl_s_blocking_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify Ctrl+S blocking code is present
        $response->assertSee("e.key === 's'", false);
        $response->assertSee('Downloading is disabled for this document', false);
        $response->assertSee('Download attempt blocked: Ctrl+S', false);
    }

    /**
     * Test that Ctrl+P (Print) keyboard shortcut is blocked
     * Requirement 3.3: Block Ctrl+P with alert
     * 
     * @test
     */
    public function it_includes_ctrl_p_blocking_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify Ctrl+P blocking code is present
        $response->assertSee("e.key === 'p'", false);
        $response->assertSee('Printing is disabled for this document', false);
        $response->assertSee('Print attempt blocked: Ctrl+P', false);
    }

    /**
     * Test that Ctrl+C (Copy) keyboard shortcut is blocked
     * Requirement 3.4: Block Ctrl+C with alert
     * 
     * @test
     */
    public function it_includes_ctrl_c_blocking_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify Ctrl+C blocking code is present
        $response->assertSee("e.key === 'c'", false);
        $response->assertSee('Copying is disabled for this document', false);
        $response->assertSee('Copy attempt blocked: Ctrl+C', false);
    }

    /**
     * Test that drag-and-drop is prevented on canvas
     * Requirement 3.5: Prevent drag-and-drop operations
     * 
     * @test
     */
    public function it_includes_drag_and_drop_blocking_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify drag-and-drop blocking code is present
        $response->assertSee("addEventListener('dragstart'", false);
        $response->assertSee('Drag-and-drop is disabled on this secure viewer', false);
    }

    /**
     * Test that text selection is disabled on canvas
     * Requirement 3.6: Disable text selection
     * 
     * @test
     */
    public function it_includes_text_selection_disabling_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify text selection disabling code is present
        $response->assertSee("canvas.style.userSelect = 'none'", false);
        $response->assertSee("canvas.style.webkitUserSelect = 'none'", false);
        $response->assertSee("canvas.style.mozUserSelect = 'none'", false);
        $response->assertSee("canvas.style.msUserSelect = 'none'", false);
    }

    /**
     * Test that text selection is disabled via CSS
     * Requirement 3.6: Disable text selection
     * 
     * @test
     */
    public function it_includes_css_text_selection_disabling()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify CSS text selection disabling is present
        $response->assertSee('-webkit-user-select: none;', false);
        $response->assertSee('-moz-user-select: none;', false);
        $response->assertSee('-ms-user-select: none;', false);
        $response->assertSee('user-select: none;', false);
    }

    /**
     * Test that developer tools detection is implemented
     * Requirement 3.7: Add developer tools detection warning
     * 
     * @test
     */
    public function it_includes_developer_tools_detection_code()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify developer tools detection code is present
        $response->assertSee('function detectDevTools()', false);
        $response->assertSee('Developer tools detected', false);
        $response->assertSee('setInterval(detectDevTools, 1000)', false);
        $response->assertSee('SECURITY WARNING', false);
    }

    /**
     * Test that all keyboard shortcuts check for both Ctrl and Meta keys
     * This ensures compatibility with Mac (Cmd key) and Windows/Linux (Ctrl key)
     * 
     * @test
     */
    public function it_checks_both_ctrl_and_meta_keys_for_shortcuts()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify both Ctrl and Meta keys are checked
        $content = $response->getContent();
        
        // Count occurrences of the pattern (e.ctrlKey || e.metaKey)
        $pattern = '/(e\.ctrlKey \|\| e\.metaKey)/';
        preg_match_all($pattern, $content, $matches);
        
        // Should have at least 3 occurrences (Ctrl+S, Ctrl+P, Ctrl+C)
        $this->assertGreaterThanOrEqual(3, count($matches[0]), 
            'Should check both Ctrl and Meta keys for keyboard shortcuts');
    }

    /**
     * Test that security notice is displayed to users
     * 
     * @test
     */
    public function it_displays_security_notice()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify security notice is displayed
        $response->assertSee('This document is protected', false);
        $response->assertSee('Downloading, printing, and copying are disabled', false);
    }

    /**
     * Test that all anti-download protections are present in the viewer
     * Comprehensive check for all requirements
     * 
     * @test
     */
    public function it_includes_all_anti_download_protections()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Verify all protection mechanisms are present
        $protections = [
            'contextmenu' => "addEventListener('contextmenu'",
            'ctrl_s' => "e.key === 's'",
            'ctrl_p' => "e.key === 'p'",
            'ctrl_c' => "e.key === 'c'",
            'dragstart' => "addEventListener('dragstart'",
            'user_select' => "canvas.style.userSelect = 'none'",
            'devtools' => 'function detectDevTools()',
        ];
        
        foreach ($protections as $name => $code) {
            $this->assertStringContainsString($code, $content, 
                "Missing protection: {$name}");
        }
    }

    /**
     * Test that console warnings are logged for security events
     * 
     * @test
     */
    public function it_includes_console_warning_logging()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify console warnings are present
        $response->assertSee('console.warn', false);
        $response->assertSee('Right-click is disabled', false);
        $response->assertSee('Download attempt blocked', false);
        $response->assertSee('Print attempt blocked', false);
        $response->assertSee('Copy attempt blocked', false);
        $response->assertSee('Drag-and-drop is disabled', false);
        $response->assertSee('Developer tools access attempt detected', false);
    }

    /**
     * Test that F12 key is also blocked
     * Additional protection against DevTools
     * 
     * @test
     */
    public function it_blocks_f12_key()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify F12 blocking code is present
        $response->assertSee("e.key === 'F12'", false);
        $response->assertSee('Developer tools access attempt detected', false);
    }

    /**
     * Test that DevTools detection logs to server
     * Requirement 3.7: Log developer tools detection
     * 
     * @test
     */
    public function it_logs_devtools_detection_to_server()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify server logging code is present
        $response->assertSee('/secure-pdf/log-devtools-detection/', false);
        $response->assertSee('session_token: SESSION_TOKEN', false);
        $response->assertSee('X-CSRF-TOKEN', false);
    }

    /**
     * Test that anti-download protections have proper comments
     * Ensures code is well-documented for maintenance
     * 
     * @test
     */
    public function it_includes_requirement_comments()
    {
        $token = $this->pdfService->generateViewerUrl($this->content, $this->user);
        $tokenPart = last(explode('/', $token));

        $response = $this->actingAs($this->user)->get(route('secure.pdf.viewer', [
            'content' => $this->content->id,
            'token' => $tokenPart,
        ]));

        $response->assertStatus(200);
        
        // Verify requirement comments are present
        $response->assertSee('ANTI-DOWNLOAD PROTECTIONS', false);
        $response->assertSee('Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7', false);
        $response->assertSee('Requirement 3.1', false);
        $response->assertSee('Requirement 3.2', false);
        $response->assertSee('Requirement 3.3', false);
        $response->assertSee('Requirement 3.4', false);
        $response->assertSee('Requirement 3.5', false);
        $response->assertSee('Requirement 3.6', false);
        $response->assertSee('Requirement 3.7', false);
    }
}

