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
 * Test custom toolbar functionality in secure PDF viewer
 * 
 * Task 5.4: Implement custom toolbar functionality
 * Requirements: 5.4, 5.6
 * 
 * This test verifies that the viewer.blade.php template includes:
 * - Page navigation buttons (previous, next, page input)
 * - Zoom controls (zoom in, zoom out, fit to page)
 * - Loading indicator
 * - Proper HTML structure for smooth transitions
 */
class SecurePdfToolbarFunctionalityTest extends TestCase
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
        Storage::fake('public');
        
        // Create test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Create course structure
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        // Enroll the user in the course
        $this->user->enrollments()->create([
            'course_id' => $course->id,
            'status' => 'active',
        ]);
        
        // Create PDF content
        $this->content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'title' => 'Test PDF Document',
            'file_path' => 'test.pdf',
        ]);
        
        // Create a fake PDF file
        Storage::disk('public')->put('test.pdf', 'fake pdf content');
    }

    /**
     * Helper method to get viewer response
     */
    protected function getViewerResponse()
    {
        $this->actingAs($this->user);
        $viewerUrl = $this->pdfService->generateViewerUrl($this->content, $this->user);
        return $this->get($viewerUrl);
    }

    /**
     * Test that viewer page contains page navigation buttons
     * Requirement 5.4: Wire up page navigation buttons
     */
    public function test_viewer_contains_page_navigation_buttons(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for previous button
        $response->assertSee('id="prev-page"', false);
        $response->assertSee('Previous', false);
        
        // Check for next button
        $response->assertSee('id="next-page"', false);
        $response->assertSee('Next', false);
    }

    /**
     * Test that viewer page contains page input field
     * Requirement 5.4: Wire up page navigation buttons (page input)
     */
    public function test_viewer_contains_page_input_field(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for page input field
        $response->assertSee('id="page-input"', false);
        $response->assertSee('type="number"', false);
        $response->assertSee('min="1"', false);
    }

    /**
     * Test that viewer page contains page count display
     * Requirement 5.4: Wire up page navigation buttons
     */
    public function test_viewer_contains_page_count_display(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for total pages display
        $response->assertSee('id="total-pages"', false);
        $response->assertSee('id="page-info"', false);
    }

    /**
     * Test that viewer page contains zoom controls
     * Requirement 5.6: Wire up zoom controls
     */
    public function test_viewer_contains_zoom_controls(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for zoom in button
        $response->assertSee('id="zoom-in"', false);
        $response->assertSee('Zoom In', false);
        
        // Check for zoom out button
        $response->assertSee('id="zoom-out"', false);
        $response->assertSee('Zoom Out', false);
        
        // Check for zoom select dropdown
        $response->assertSee('id="zoom-select"', false);
    }

    /**
     * Test that zoom select contains proper zoom levels
     * Requirement 5.6: Wire up zoom controls (zoom in, zoom out, fit to page)
     */
    public function test_zoom_select_contains_zoom_levels(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for various zoom levels
        $response->assertSee('value="0.5"', false); // 50%
        $response->assertSee('value="0.75"', false); // 75%
        $response->assertSee('value="1"', false); // 100%
        $response->assertSee('value="1.25"', false); // 125%
        $response->assertSee('value="1.5"', false); // 150%
        $response->assertSee('value="2"', false); // 200%
        $response->assertSee('value="fit"', false); // Fit Width
        $response->assertSee('Fit Width', false);
    }

    /**
     * Test that viewer page contains loading indicator
     * Requirement 5.4: Add loading indicator during PDF load
     */
    public function test_viewer_contains_loading_indicator(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for loading indicator
        $response->assertSee('id="loading-indicator"', false);
        $response->assertSee('Loading PDF...', false);
        $response->assertSee('class="spinner"', false);
    }

    /**
     * Test that viewer page contains smooth transition CSS
     * Requirement 5.4: Implement smooth page transitions
     */
    public function test_viewer_contains_smooth_transition_css(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for transition CSS
        $response->assertSee('transition:', false);
        $response->assertSee('opacity', false);
    }

    /**
     * Test that viewer page contains JavaScript for page navigation
     * Requirement 5.4: Wire up page navigation buttons
     */
    public function test_viewer_contains_page_navigation_javascript(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for navigation event listeners
        $response->assertSee('prevButton.addEventListener', false);
        $response->assertSee('nextButton.addEventListener', false);
        $response->assertSee('pageInput.addEventListener', false);
    }

    /**
     * Test that viewer page contains JavaScript for zoom controls
     * Requirement 5.6: Wire up zoom controls
     */
    public function test_viewer_contains_zoom_control_javascript(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for zoom event listeners
        $response->assertSee('zoomSelect.addEventListener', false);
        $response->assertSee('zoomInButton.addEventListener', false);
        $response->assertSee('zoomOutButton.addEventListener', false);
    }

    /**
     * Test that viewer page contains renderPage function
     * Requirement 5.4: Implement smooth page transitions
     */
    public function test_viewer_contains_render_page_function(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for renderPage function
        $response->assertSee('async function renderPage', false);
        $response->assertSee('canvas.classList.add(\'rendering\')', false);
        $response->assertSee('canvas.classList.remove(\'rendering\')', false);
    }

    /**
     * Test that viewer page contains keyboard navigation support
     * Requirement 5.4: Wire up page navigation buttons
     */
    public function test_viewer_contains_keyboard_navigation(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for keyboard navigation
        $response->assertSee('ArrowLeft', false);
        $response->assertSee('ArrowRight', false);
    }

    /**
     * Test that viewer page contains page input validation
     * Requirement 5.4: Wire up page navigation buttons (page input)
     */
    public function test_viewer_contains_page_input_validation(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for page input validation logic
        $response->assertSee('parseInt(pageInput.value)', false);
        $response->assertSee('isNaN(pageNum)', false);
    }

    /**
     * Test that viewer page updates navigation button states
     * Requirement 5.4: Wire up page navigation buttons
     */
    public function test_viewer_contains_navigation_button_state_updates(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for updateNavigationButtons function
        $response->assertSee('function updateNavigationButtons', false);
        $response->assertSee('prevButton.disabled', false);
        $response->assertSee('nextButton.disabled', false);
    }

    /**
     * Test that viewer page contains proper toolbar structure
     * Requirement 5.4: Custom toolbar functionality
     */
    public function test_viewer_contains_proper_toolbar_structure(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for toolbar structure
        $response->assertSee('id="toolbar"', false);
        $response->assertSee('id="toolbar-left"', false);
        $response->assertSee('id="toolbar-center"', false);
        $response->assertSee('id="toolbar-right"', false);
    }

    /**
     * Test that viewer page contains PDF canvas
     * Requirement 5.4: Custom toolbar functionality
     */
    public function test_viewer_contains_pdf_canvas(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for PDF canvas
        $response->assertSee('id="pdf-canvas"', false);
        $response->assertSee('id="pdf-canvas-container"', false);
    }

    /**
     * Test that viewer page contains document title
     * Requirement 5.4: Custom toolbar functionality
     */
    public function test_viewer_displays_document_title(): void
    {
        $response = $this->getViewerResponse();
        
        $response->assertStatus(200);
        
        // Check for document title
        $response->assertSee($this->content->title);
        $response->assertSee('class="document-title"', false);
    }
}
