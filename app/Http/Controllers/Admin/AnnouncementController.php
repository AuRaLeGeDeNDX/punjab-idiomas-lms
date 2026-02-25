<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Course;
use App\Models\User;
use App\Services\NotificationService;
use App\Notifications\SystemAnnouncementNotification;
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
     * Display system-wide announcements management.
     */
    public function index()
    {
        $announcements = Announcement::whereNull('course_id')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new system-wide announcement.
     */
    public function create()
    {
        $courses = Course::published()->with('teacher')->get();
        
        return view('admin.announcements.create', compact('courses'));
    }

    /**
     * Store a newly created system-wide announcement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'announcement_type' => 'required|in:system,course,selective',
            'target_courses' => 'nullable|array',
            'target_courses.*' => 'exists:courses,id',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:Student,Teacher,Admin',
            'display_duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validated['announcement_type'] === 'system') {
            // System-wide announcement
            $announcement = $this->createSystemAnnouncement($validated);
            $this->sendSystemWideNotification($announcement, $validated['target_roles'] ?? ['Student', 'Teacher']);
            
        } elseif ($validated['announcement_type'] === 'course') {
            // Send to all courses
            $this->sendToAllCourses($validated);
            
        } elseif ($validated['announcement_type'] === 'selective') {
            // Send to selected courses
            $this->sendToSelectedCourses($validated, $validated['target_courses'] ?? []);
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement sent successfully.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement)
    {
        if ($announcement->course_id !== null) {
            abort(404);
        }

        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the announcement.
     */
    public function edit(Announcement $announcement)
    {
        if ($announcement->course_id !== null) {
            abort(404);
        }

        $courses = Course::published()->with('teacher')->get();
        
        return view('admin.announcements.edit', compact('announcement', 'courses'));
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Announcement $announcement)
    {
        if ($announcement->course_id !== null) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'display_duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        // Calculate display_until if duration is provided
        if (isset($validated['display_duration_days'])) {
            $validated['display_until'] = now()->addDays((int) $validated['display_duration_days']);
        }

        $announcement->update($validated);

        return redirect()
            ->route('admin.announcements.show', $announcement)
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Announcement $announcement)
    {
        if ($announcement->course_id !== null) {
            abort(404);
        }

        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    /**
     * Display trashed announcements.
     */
    public function trashed()
    {
        $announcements = Announcement::onlyTrashed()
            ->whereNull('course_id')
            ->with('user')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.announcements.trashed', compact('announcements'));
    }

    /**
     * Restore a trashed announcement.
     */
    public function restore($id)
    {
        $announcement = Announcement::onlyTrashed()->findOrFail($id);
        $announcement->restore();

        return redirect()->route('admin.announcements.trashed')
            ->with('success', 'Announcement restored successfully.');
    }

    /**
     * Force delete an announcement permanently.
     */
    public function forceDelete($id)
    {
        $announcement = Announcement::onlyTrashed()->findOrFail($id);
        $announcement->forceDelete();

        return redirect()->route('admin.announcements.trashed')
            ->with('success', 'Announcement permanently deleted.');
    }

    /**
     * Empty trash.
     */
    public function emptyTrash()
    {
        Announcement::onlyTrashed()->forceDelete();

        return redirect()->route('admin.announcements.trashed')
            ->with('success', 'Trash emptied successfully.');
    }

    /**
     * Create a system-wide announcement.
     */
    private function createSystemAnnouncement(array $data): Announcement
    {
        $announcementData = [
            'title' => $data['title'],
            'message' => $data['message'],
            'priority' => $data['priority'],
            'user_id' => Auth::id(),
            'course_id' => null, // System-wide announcement
            'is_published' => true,
            'published_at' => now(),
        ];

        // Calculate display_until if duration is provided
        if (isset($data['display_duration_days'])) {
            $announcementData['display_duration_days'] = $data['display_duration_days'];
            $announcementData['display_until'] = now()->addDays((int) $data['display_duration_days']);
        }

        return Announcement::create($announcementData);
    }

    /**
     * Send system-wide notification to all users with specified roles.
     */
    private function sendSystemWideNotification(Announcement $announcement, array $targetRoles): void
    {
        $users = User::role($targetRoles)->get();

        foreach ($users as $user) {
            $user->notify(new SystemAnnouncementNotification($announcement));
        }
    }

    /**
     * Send announcement to all courses.
     */
    private function sendToAllCourses(array $data): void
    {
        $courses = Course::published()->get();

        foreach ($courses as $course) {
            $this->notificationService->sendCourseAnnouncement(
                $course,
                $data['title'],
                $data['message'],
                Auth::user()
            );
        }
    }

    /**
     * Send announcement to selected courses.
     */
    private function sendToSelectedCourses(array $data, array $courseIds): void
    {
        $courses = Course::whereIn('id', $courseIds)->get();

        foreach ($courses as $course) {
            $this->notificationService->sendCourseAnnouncement(
                $course,
                $data['title'],
                $data['message'],
                Auth::user()
            );
        }
    }
}