@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Notification Preferences</h1>
            <p class="text-gray-600">Manage how you receive notifications from the system</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Preferences Form -->
        <form method="POST" action="{{ route('notifications.preferences.update') }}" class="bg-white rounded-lg shadow-md overflow-hidden">
            @csrf
            @method('PUT')

            <!-- Channel Preferences -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Notification Channels</h2>
                <p class="text-sm text-gray-600 mb-4">Choose how you want to receive notifications</p>

                <div class="space-y-4">
                    <!-- Email Notifications -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="email_notifications" 
                                id="email_notifications"
                                value="1"
                                {{ $preferences->email_notifications ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="email_notifications" class="font-medium text-gray-900">Email Notifications</label>
                            <p class="text-sm text-gray-600">Receive notifications via email</p>
                        </div>
                    </div>

                    <!-- In-App Notifications -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="database_notifications" 
                                id="database_notifications"
                                value="1"
                                {{ $preferences->database_notifications ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="database_notifications" class="font-medium text-gray-900">In-App Notifications</label>
                            <p class="text-sm text-gray-600">Receive notifications within the application</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Types -->
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Notification Types</h2>
                <p class="text-sm text-gray-600 mb-4">Select which types of notifications you want to receive</p>

                <div class="space-y-4">
                    <!-- Assignment Published -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="assignment_published" 
                                id="assignment_published"
                                value="1"
                                {{ $preferences->assignment_published ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="assignment_published" class="font-medium text-gray-900">Assignment Published</label>
                            <p class="text-sm text-gray-600">Get notified when new assignments are published</p>
                        </div>
                    </div>

                    <!-- Assignment Reminder -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="assignment_reminder" 
                                id="assignment_reminder"
                                value="1"
                                {{ $preferences->assignment_reminder ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="assignment_reminder" class="font-medium text-gray-900">Assignment Due Reminders</label>
                            <p class="text-sm text-gray-600">Get reminded when assignments are due soon (24 hours before)</p>
                        </div>
                    </div>

                    <!-- Grade Published -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="grade_published" 
                                id="grade_published"
                                value="1"
                                {{ $preferences->grade_published ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="grade_published" class="font-medium text-gray-900">Grade Published</label>
                            <p class="text-sm text-gray-600">Get notified when your grades are published</p>
                        </div>
                    </div>

                    <!-- Course Announcements -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="course_announcement" 
                                id="course_announcement"
                                value="1"
                                {{ $preferences->course_announcement ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="course_announcement" class="font-medium text-gray-900">Course Announcements</label>
                            <p class="text-sm text-gray-600">Get notified about course announcements</p>
                        </div>
                    </div>

                    <!-- Course Updates -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="course_update" 
                                id="course_update"
                                value="1"
                                {{ $preferences->course_update ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="course_update" class="font-medium text-gray-900">Course Updates</label>
                            <p class="text-sm text-gray-600">Get notified about course content updates and submissions</p>
                        </div>
                    </div>

                    <!-- Direct Messages -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="direct_message" 
                                id="direct_message"
                                value="1"
                                {{ $preferences->direct_message ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="direct_message" class="font-medium text-gray-900">Direct Messages</label>
                            <p class="text-sm text-gray-600">Get notified when you receive direct messages</p>
                        </div>
                    </div>

                    <!-- Forum Replies -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="forum_reply" 
                                id="forum_reply"
                                value="1"
                                {{ $preferences->forum_reply ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="forum_reply" class="font-medium text-gray-900">Forum Replies</label>
                            <p class="text-sm text-gray-600">Get notified when someone replies to your forum posts</p>
                        </div>
                    </div>

                    <!-- System Alerts -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                type="checkbox" 
                                name="system_alert" 
                                id="system_alert"
                                value="1"
                                {{ $preferences->system_alert ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            >
                        </div>
                        <div class="ml-3">
                            <label for="system_alert" class="font-medium text-gray-900">System Alerts</label>
                            <p class="text-sm text-gray-600">Get notified about important system updates and maintenance</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                <button 
                    type="button"
                    onclick="if(confirm('Are you sure you want to reset all preferences to defaults?')) { document.getElementById('reset-form').submit(); }"
                    class="text-sm text-gray-600 hover:text-gray-900 font-medium"
                >
                    Reset to Defaults
                </button>

                <div class="flex space-x-3">
                    <a 
                        href="{{ url()->previous() }}" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
                    >
                        Save Preferences
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Reset Form -->
        <form id="reset-form" method="POST" action="{{ route('notifications.preferences.reset') }}" class="hidden">
            @csrf
        </form>
    </div>
</div>
@endsection
