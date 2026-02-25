<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfAccessLog;
use App\Models\User;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * PDF Access Log Controller
 * 
 * Provides admin interface for viewing and filtering PDF access logs.
 * Requirements: 7.7
 */
class PdfAccessLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display PDF access logs with filtering and pagination.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class); // Admin only

        // Get filter parameters
        $userId = $request->get('user_id');
        $contentId = $request->get('content_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $accessGranted = $request->get('access_granted');
        $perPage = $request->get('per_page', 25);

        // Build query with filters
        $query = PdfAccessLog::with(['user', 'content'])
            ->orderBy('accessed_at', 'desc');

        // Apply filters
        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($contentId) {
            $query->where('content_id', $contentId);
        }

        if ($dateFrom) {
            $query->whereDate('accessed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('accessed_at', '<=', $dateTo);
        }

        if ($accessGranted !== null && $accessGranted !== '') {
            $query->where('access_granted', (bool) $accessGranted);
        }

        // Paginate results
        $logs = $query->paginate($perPage)->withQueryString();

        // Get statistics
        $stats = $this->getAccessStatistics($userId, $contentId, $dateFrom, $dateTo);

        // Get users and content for filter dropdowns
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $contents = Content::where('type', 'pdf')
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.pdf-access-logs.index', compact(
            'logs',
            'stats',
            'users',
            'contents',
            'userId',
            'contentId',
            'dateFrom',
            'dateTo',
            'accessGranted',
            'perPage'
        ));
    }

    /**
     * Get access statistics based on filters.
     * 
     * @param int|null $userId
     * @param int|null $contentId
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    private function getAccessStatistics(?int $userId, ?int $contentId, ?string $dateFrom, ?string $dateTo): array
    {
        $query = PdfAccessLog::query();

        // Apply same filters as main query
        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($contentId) {
            $query->where('content_id', $contentId);
        }

        if ($dateFrom) {
            $query->whereDate('accessed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('accessed_at', '<=', $dateTo);
        }

        $total = $query->count();
        $granted = (clone $query)->where('access_granted', true)->count();
        $denied = (clone $query)->where('access_granted', false)->count();
        $uniqueUsers = (clone $query)->distinct('user_id')->count('user_id');
        $uniqueContent = (clone $query)->distinct('content_id')->count('content_id');

        return [
            'total' => $total,
            'granted' => $granted,
            'denied' => $denied,
            'unique_users' => $uniqueUsers,
            'unique_content' => $uniqueContent,
        ];
    }
}
