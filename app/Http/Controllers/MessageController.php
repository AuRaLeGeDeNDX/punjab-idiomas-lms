<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the user's messages.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'inbox');

        $query = Message::query();

        if ($tab === 'inbox') {
            $query->forRecipient($user);
        } elseif ($tab === 'sent') {
            $query->fromSender($user);
        }

        $messages = $query->with(['sender', 'recipient'])
            ->orderBy('sent_at', 'desc')
            ->paginate(15);

        $unreadCount = Message::forRecipient($user)->unread()->count();

        return view('messages.index', compact('messages', 'tab', 'unreadCount'));
    }

    /**
     * Show the form for creating a new message.
     */
    public function create(Request $request)
    {
        $recipientId = $request->get('recipient');
        $recipient = $recipientId ? User::find($recipientId) : null;

        // Get users that the current user can message
        $availableRecipients = $this->getAvailableRecipients();

        return view('messages.create', compact('recipient', 'availableRecipients'));
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $recipient = User::findOrFail($validated['recipient_id']);

        try {
            $message = $this->notificationService->sendDirectMessage(
                Auth::user(),
                $recipient,
                $validated['subject'],
                $validated['message']
            );

            return redirect()
                ->route('messages.show', $message)
                ->with('success', 'Message sent successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['recipient_id' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified message.
     */
    public function show(Message $message)
    {
        $user = Auth::user();

        // Check if user can view this message
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            abort(403);
        }

        // Mark as read if user is the recipient
        if ($message->recipient_id === $user->id) {
            $message->markAsRead();
        }

        // Get conversation thread if this is a reply
        $conversationThread = $message->isReply() ? $message->getConversationThread() : collect();

        return view('messages.show', compact('message', 'conversationThread'));
    }

    /**
     * Show the form for replying to a message.
     */
    public function reply(Message $message)
    {
        $user = Auth::user();

        // Check if user can reply to this message
        if ($message->recipient_id !== $user->id) {
            abort(403);
        }

        return view('messages.reply', compact('message'));
    }

    /**
     * Store a reply to a message.
     */
    public function storeReply(Request $request, Message $message)
    {
        $user = Auth::user();

        // Check if user can reply to this message
        if ($message->recipient_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        try {
            $reply = $this->notificationService->sendDirectMessage(
                $user,
                $message->sender,
                'Re: ' . $message->subject,
                $validated['message']
            );

            // Set parent relationship
            $reply->update(['parent_id' => $message->isReply() ? $message->parent_id : $message->id]);

            // Update original message replied_at timestamp
            $originalMessage = $message->isReply() ? $message->parent : $message;
            $originalMessage->update(['replied_at' => now()]);

            return redirect()
                ->route('messages.show', $reply)
                ->with('success', 'Reply sent successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Delete a message.
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();

        // Users can only delete messages they sent or received
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            abort(403);
        }

        $message->delete();

        return redirect()
            ->route('messages.index')
            ->with('success', 'Message deleted successfully.');
    }

    /**
     * Get users that the current user can send messages to.
     */
    private function getAvailableRecipients()
    {
        $user = Auth::user();
        $recipients = collect();

        if ($user->hasRole('admin')) {
            // Admins can message anyone
            $recipients = User::where('id', '!=', $user->id)->get();
        } elseif ($user->hasRole('teacher')) {
            // Teachers can message students in their courses and other teachers/admins
            $studentIds = $user->teachingCourses()
                ->with('enrollments.user')
                ->get()
                ->pluck('enrollments')
                ->flatten()
                ->pluck('user.id')
                ->unique();

            $teacherAndAdminIds = User::role(['teacher', 'admin'])
                ->where('id', '!=', $user->id)
                ->pluck('id');

            $allIds = $studentIds->merge($teacherAndAdminIds)->unique();
            $recipients = User::whereIn('id', $allIds)->get();
        } elseif ($user->hasRole('student')) {
            // Students can message teachers of their courses and admins
            $teacherIds = $user->enrollments()
                ->with('course.teacher')
                ->get()
                ->pluck('course.teacher.id')
                ->unique();

            $adminIds = User::role('admin')->pluck('id');

            $allIds = $teacherIds->merge($adminIds)->unique();
            $recipients = User::whereIn('id', $allIds)->get();
        }

        return $recipients->sortBy('name');
    }
}