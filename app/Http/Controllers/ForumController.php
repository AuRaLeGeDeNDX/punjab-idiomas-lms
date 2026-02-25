<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Forum;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display forums for a course.
     */
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $forums = $course->forums()
            ->active()
            ->with(['creator', 'topics' => function ($query) {
                $query->active()->latest()->limit(5);
            }])
            ->get();

        return view('forums.index', compact('course', 'forums'));
    }

    /**
     * Show the form for creating a new forum.
     */
    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('forums.create', compact('course'));
    }

    /**
     * Store a newly created forum.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_locked' => 'boolean',
        ]);

        $forum = $course->forums()->create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('courses.forums.show', [$course, $forum])
            ->with('success', 'Forum created successfully.');
    }

    /**
     * Display the specified forum.
     */
    public function show(Course $course, Forum $forum)
    {
        $this->authorize('view', $course);

        if ($forum->course_id !== $course->id) {
            abort(404);
        }

        $topics = $forum->activeTopics()
            ->with(['user', 'posts' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withCount('posts')
            ->paginate(15);

        return view('forums.show', compact('course', 'forum', 'topics'));
    }

    /**
     * Show the form for editing the forum.
     */
    public function edit(Course $course, Forum $forum)
    {
        $this->authorize('update', $course);

        if ($forum->course_id !== $course->id) {
            abort(404);
        }

        return view('forums.edit', compact('course', 'forum'));
    }

    /**
     * Update the specified forum.
     */
    public function update(Request $request, Course $course, Forum $forum)
    {
        $this->authorize('update', $course);

        if ($forum->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_locked' => 'boolean',
        ]);

        $forum->update($validated);

        return redirect()
            ->route('courses.forums.show', [$course, $forum])
            ->with('success', 'Forum updated successfully.');
    }

    /**
     * Remove the specified forum.
     */
    public function destroy(Course $course, Forum $forum)
    {
        $this->authorize('update', $course);

        if ($forum->course_id !== $course->id) {
            abort(404);
        }

        $forum->delete();

        return redirect()
            ->route('courses.forums.index', $course)
            ->with('success', 'Forum deleted successfully.');
    }

    /**
     * Show the form for creating a new topic.
     */
    public function createTopic(Course $course, Forum $forum)
    {
        $this->authorize('view', $course);

        if ($forum->course_id !== $course->id) {
            abort(404);
        }

        if (!$forum->canUserPost(Auth::user())) {
            abort(403, 'You do not have permission to post in this forum.');
        }

        return view('forums.topics.create', compact('course', 'forum'));
    }

    /**
     * Store a newly created topic.
     */
    public function storeTopic(Request $request, Course $course, Forum $forum)
    {
        $this->authorize('view', $course);

        if ($forum->course_id !== $course->id) {
            abort(404);
        }

        if (!$forum->canUserPost(Auth::user())) {
            abort(403, 'You do not have permission to post in this forum.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $topic = $forum->topics()->create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('courses.forums.topics.show', [$course, $forum, $topic])
            ->with('success', 'Topic created successfully.');
    }

    /**
     * Display the specified topic.
     */
    public function showTopic(Course $course, Forum $forum, ForumTopic $topic)
    {
        $this->authorize('view', $course);

        if ($forum->course_id !== $course->id || $topic->forum_id !== $forum->id) {
            abort(404);
        }

        // Increment views count
        $topic->incrementViews();

        $posts = $topic->posts()
            ->with('user')
            ->orderBy('created_at')
            ->paginate(10);

        return view('forums.topics.show', compact('course', 'forum', 'topic', 'posts'));
    }

    /**
     * Store a reply to a topic.
     */
    public function storeReply(Request $request, Course $course, Forum $forum, ForumTopic $topic)
    {
        $this->authorize('view', $course);

        if ($forum->course_id !== $course->id || $topic->forum_id !== $forum->id) {
            abort(404);
        }

        if (!$topic->canUserReply(Auth::user())) {
            abort(403, 'You do not have permission to reply to this topic.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $post = $topic->posts()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        // Notify topic creator if it's not the same user
        $this->notificationService->notifyForumReply($post);

        return redirect()
            ->route('courses.forums.topics.show', [$course, $forum, $topic])
            ->with('success', 'Reply posted successfully.');
    }

    /**
     * Edit a forum post.
     */
    public function editPost(Course $course, Forum $forum, ForumTopic $topic, ForumPost $post)
    {
        $this->authorize('view', $course);

        if (!$post->canUserEdit(Auth::user())) {
            abort(403, 'You do not have permission to edit this post.');
        }

        return view('forums.posts.edit', compact('course', 'forum', 'topic', 'post'));
    }

    /**
     * Update a forum post.
     */
    public function updatePost(Request $request, Course $course, Forum $forum, ForumTopic $topic, ForumPost $post)
    {
        $this->authorize('view', $course);

        if (!$post->canUserEdit(Auth::user())) {
            abort(403, 'You do not have permission to edit this post.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $post->update(['content' => $validated['content']]);
        $post->markAsEdited(Auth::user());

        return redirect()
            ->route('courses.forums.topics.show', [$course, $forum, $topic])
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Delete a forum post.
     */
    public function destroyPost(Course $course, Forum $forum, ForumTopic $topic, ForumPost $post)
    {
        $this->authorize('view', $course);

        if (!$post->canUserDelete(Auth::user())) {
            abort(403, 'You do not have permission to delete this post.');
        }

        $post->delete();

        return redirect()
            ->route('courses.forums.topics.show', [$course, $forum, $topic])
            ->with('success', 'Post deleted successfully.');
    }
}