<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationPreferenceController extends Controller
{
    /**
     * Display the notification preferences form
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        $preferences = NotificationPreference::getOrCreateDefaults($user->id);

        return view('notifications.preferences', compact('preferences'));
    }

    /**
     * Update the notification preferences
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'database_notifications' => 'boolean',
            'course_announcement' => 'boolean',
            'assignment_reminder' => 'boolean',
            'grade_published' => 'boolean',
            'direct_message' => 'boolean',
            'system_alert' => 'boolean',
            'forum_reply' => 'boolean',
            'assignment_published' => 'boolean',
            'course_update' => 'boolean',
        ]);

        // Convert checkbox values (null means unchecked = false)
        $preferences = [
            'email_notifications' => $request->has('email_notifications'),
            'database_notifications' => $request->has('database_notifications'),
            'course_announcement' => $request->has('course_announcement'),
            'assignment_reminder' => $request->has('assignment_reminder'),
            'grade_published' => $request->has('grade_published'),
            'direct_message' => $request->has('direct_message'),
            'system_alert' => $request->has('system_alert'),
            'forum_reply' => $request->has('forum_reply'),
            'assignment_published' => $request->has('assignment_published'),
            'course_update' => $request->has('course_update'),
        ];

        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            $preferences
        );

        Log::info('Notification preferences updated', [
            'user_id' => $user->id,
            'preferences' => $preferences
        ]);

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }

    /**
     * Reset notification preferences to defaults
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        $user = Auth::user();

        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications' => true,
                'database_notifications' => true,
                'course_announcement' => true,
                'assignment_reminder' => true,
                'grade_published' => true,
                'direct_message' => true,
                'system_alert' => true,
                'forum_reply' => true,
                'assignment_published' => true,
                'course_update' => true,
            ]
        );

        Log::info('Notification preferences reset to defaults', [
            'user_id' => $user->id
        ]);

        return redirect()->back()->with('success', 'Notification preferences reset to defaults.');
    }
}
