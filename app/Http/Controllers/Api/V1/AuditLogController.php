<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Get audit log with filters.
     */
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $query = ActivityLog::where('restaurant_id', $user->restaurant_id);

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        // Filter by actor
        if ($request->has('actor')) {
            $actor = $request->input('actor');
            $query->where(function ($q) use ($actor) {
                $q->where('actor_label', 'like', "%{$actor}%")
                    ->orWhereHas('actor', function ($sub) use ($actor) {
                        $sub->where('name', 'like', "%{$actor}%");
                    });
            });
        }

        // Filter by entity type
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->input('entity_type'));
        }

        // Filter by tone (action type)
        if ($request->has('tone')) {
            $query->where('tone', $request->input('tone'));
        }

        // Search in description
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 25);
        $logs = $query->orderBy('created_at', 'desc')
            ->with('actor')
            ->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'meta' => [
                'total' => $logs->total(),
                'page' => $logs->currentPage(),
                'perPage' => $logs->perPage(),
                'hasMore' => $logs->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get entity types for filter dropdown.
     */
    public function entityTypes(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $types = ActivityLog::where('restaurant_id', $user->restaurant_id)
            ->whereNotNull('entity_type')
            ->distinct()
            ->pluck('entity_type')
            ->toArray();

        return response()->json([
            'data' => array_values(array_filter($types)),
        ]);
    }
}