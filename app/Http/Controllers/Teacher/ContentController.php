<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Show the form for creating new content.
     */
    public function create(Course $course, Module $module, Subpage $subpage): View
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        return view('teacher.content.create', compact('course', 'module', 'subpage'));
    }

    /**
     * Store newly created content.
     */
    public function store(Request $request, Course $course, Module $module, Subpage $subpage): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,pdf,image,audio,video,file',
            'content' => 'required_if:type,text|nullable|string',
            'file' => 'required_unless:type,text|nullable|file|max:102400', // 100MB max
            'visibility' => 'required|in:student,teacher_only',
            'is_active' => 'boolean',
        ]);

        $contentData = [
            'subpage_id' => $subpage->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'visibility' => $validated['visibility'],
            'is_active' => $validated['is_active'] ?? true,
            'order_index' => Content::getNextOrderIndex($subpage->id),
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Validate file type based on content type
            $this->validateFileType($file, $validated['type']);
            
            $filePath = $this->fileService->storeSecurely(
                $file,
                "courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}"
            );

            $contentData['file_path'] = $filePath;
            $contentData['file_name'] = $file->getClientOriginalName();
            $contentData['file_size'] = $file->getSize();
            $contentData['mime_type'] = $file->getMimeType();
        } else {
            $contentData['content'] = $validated['content'];
        }

        Content::create($contentData);

        return redirect()
            ->route('teacher.courses.modules.subpages.show', [$course, $module, $subpage])
            ->with('success', 'Content created successfully.');
    }

    /**
     * Show the form for editing content.
     */
    public function edit(Course $course, Module $module, Subpage $subpage, Content $content): View
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $content->subpage_id !== $subpage->id) {
            abort(404);
        }

        return view('teacher.content.edit', compact('course', 'module', 'subpage', 'content'));
    }

    /**
     * Update the specified content.
     */
    public function update(Request $request, Course $course, Module $module, Subpage $subpage, Content $content): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $content->subpage_id !== $subpage->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required_if:type,text|nullable|string',
            'file' => 'nullable|file|max:102400', // 100MB max
            'visibility' => 'required|in:student,teacher_only',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'visibility' => $validated['visibility'],
            'is_active' => $validated['is_active'] ?? $content->is_active,
        ];

        // Handle file replacement
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Validate file type
            $this->validateFileType($file, $content->type);
            
            // Delete old file if exists
            if ($content->file_path) {
                Storage::delete($content->file_path);
            }
            
            $filePath = $this->fileService->storeSecurely(
                $file,
                "courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}"
            );

            $updateData['file_path'] = $filePath;
            $updateData['file_name'] = $file->getClientOriginalName();
            $updateData['file_size'] = $file->getSize();
            $updateData['mime_type'] = $file->getMimeType();
        } elseif ($content->type === 'text') {
            $updateData['content'] = $validated['content'];
        }

        $content->update($updateData);

        return redirect()
            ->route('teacher.courses.modules.subpages.show', [$course, $module, $subpage])
            ->with('success', 'Content updated successfully.');
    }

    /**
     * Remove the specified content.
     */
    public function destroy(Course $course, Module $module, Subpage $subpage, Content $content): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $content->subpage_id !== $subpage->id) {
            abort(404);
        }

        // Soft delete - keeps the file in storage for potential restoration
        $content->delete();

        return redirect()
            ->route('teacher.courses.modules.subpages.show', [$course, $module, $subpage])
            ->with('success', 'Content moved to trash.');
    }

    /**
     * Restore soft-deleted content.
     */
    public function restore(Course $course, Module $module, Subpage $subpage, $contentId): RedirectResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            abort(404);
        }

        $content = Content::withTrashed()
            ->where('id', $contentId)
            ->where('subpage_id', $subpage->id)
            ->firstOrFail();

        $content->restore();

        return redirect()
            ->route('teacher.courses.modules.subpages.show', [$course, $module, $subpage])
            ->with('success', 'Content restored successfully.');
    }

    /**
     * Download content file.
     */
    public function download(Course $course, Module $module, Subpage $subpage, Content $content)
    {
        $this->authorize('view', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || 
            $subpage->module_id !== $module->id || 
            $content->subpage_id !== $subpage->id) {
            abort(404);
        }

        if (!$content->file_path || !Storage::exists($content->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($content->file_path, $content->file_name);
    }

    /**
     * Reorder content within a subpage.
     */
    public function reorder(Request $request, Course $course, Module $module, Subpage $subpage): JsonResponse
    {
        $this->authorize('update', $course);
        
        // Ensure relationships are correct
        if ($module->course_id !== $course->id || $subpage->module_id !== $module->id) {
            return response()->json(['success' => false, 'message' => 'Subpage not found'], 404);
        }

        $validated = $request->validate([
            'content_ids' => 'required|array',
            'content_ids.*' => 'exists:contents,id',
        ]);

        // Verify all content belongs to this subpage
        $contentCount = Content::whereIn('id', $validated['content_ids'])
            ->where('subpage_id', $subpage->id)
            ->count();

        if ($contentCount !== count($validated['content_ids'])) {
            return response()->json(['success' => false, 'message' => 'Invalid content IDs'], 400);
        }

        foreach ($validated['content_ids'] as $index => $contentId) {
            Content::where('id', $contentId)
                   ->where('subpage_id', $subpage->id)
                   ->update(['order_index' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Validate file type based on content type.
     */
    private function validateFileType($file, string $contentType): void
    {
        $allowedMimes = [
            'pdf' => ['application/pdf'],
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4'],
            'video' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/webm'],
            'file' => [], // Allow any file type for generic files
        ];

        if ($contentType !== 'file' && isset($allowedMimes[$contentType])) {
            $fileMime = $file->getMimeType();
            if (!in_array($fileMime, $allowedMimes[$contentType])) {
                throw new \InvalidArgumentException("Invalid file type for {$contentType} content.");
            }
        }

        // Special validation for PDF page count
        if ($contentType === 'pdf') {
            // This would require a PDF parsing library like smalot/pdfparser
            // For now, we'll just validate the file size as a proxy
            $maxPdfSize = 50 * 1024 * 1024; // 50MB as rough estimate for 300 pages
            if ($file->getSize() > $maxPdfSize) {
                throw new \InvalidArgumentException("PDF file is too large. Maximum 300 pages allowed.");
            }
        }
    }
}