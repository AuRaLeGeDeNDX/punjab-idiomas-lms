<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display announcements for a course.
     */
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $announcements = $course->announcements()
            ->published()
            ->ordered()
            ->with('user')
            ->paginate(10);

        return view('announcements.index', compact('course', 'announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('announcements.create', compact('course'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'publish_immediately' => 'boolean',
        ]);

        $announcement = $this->notificationService->sendCourseAnnouncement(
            $course,
            $validated['title'],
            $validated['message'],
            Auth::user()
        );

        $announcement->update(['priority' => $validated['priority']]);

        return redirect()
            ->route('courses.announcements.index', $course)
            ->with('success', 'Announcement created and sent to all enrolled students.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(Course $course, Announcement $announcement)
    {
        $this->authorize('view', $course);

        if ($announcement->course_id !== $course->id) {
            abort(404);
        }

        return view('announcements.show', compact('course', 'announcement'));
    }

    /**
     * Show the form for editing the announcement.
     */
    public function edit(Course $course, Announcement $announcement)
    {
        $this->authorize('update', $course);

        if ($announcement->course_id !== $course->id) {
            abort(404);
        }

        return view('announcements.edit', compact('course', 'announcement'));
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Course $course, Announcement $announcement)
    {
        $this->authorize('update', $course);

        if ($announcement->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $announcement->update($validated);

        return redirect()
            ->route('courses.announcements.show', [$course, $announcement])
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Course $course, Announcement $announcement)
    {
        $this->authorize('update', $course);

        if ($announcement->course_id !== $course->id) {
            abort(404);
        }

        $announcement->delete();

        return redirect()
            ->route('courses.announcements.index', $course)
            ->with('success', 'Announcement deleted successfully.');
    }
}