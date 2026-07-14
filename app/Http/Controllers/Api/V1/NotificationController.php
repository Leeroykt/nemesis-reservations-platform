<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $query = Notification::where('restaurant_id', $restaurant->id);

        if ($request->has('read')) {
            $query->where('is_read', filter_var($request->input('read'), FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = $request->input('per_page', 15);
        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'total' => $notifications->total(),
                'page' => $notifications->currentPage(),
                'perPage' => $notifications->perPage(),
                'hasMore' => $notifications->hasMorePages(),
            ],
        ]);
    }

    public function markAllRead(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $count = Notification::where('restaurant_id', $restaurant->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => "{$count} notification(s) marked as read.",
        ]);
    }

    public function markRead(Request $request, $id)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $notification = Notification::where('restaurant_id', $restaurant->id)
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }
}
