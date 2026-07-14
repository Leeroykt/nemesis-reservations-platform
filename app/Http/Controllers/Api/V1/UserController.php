<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users (owner-only).
     */
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $query = User::where('restaurant_id', $user->restaurant_id);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        $perPage = $request->input('per_page', 25);
        $users = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'total' => $users->total(),
                'page' => $users->currentPage(),
                'perPage' => $users->perPage(),
                'hasMore' => $users->hasMorePages(),
            ],
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $data = $request->validated();
        $data['restaurant_id'] = $user->restaurant_id;
        $data['password'] = Hash::make($data['password']);

        // Generate avatar initials
        $nameParts = explode(' ', $data['name']);
        $initials = '';
        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        $data['avatar_initials'] = substr($initials, 0, 2);

        /** @var User $newUser */
        $newUser = User::create($data);

        AuditLogger::log(
            $user,
            "Created staff account for {$newUser->name} ({$newUser->role})",
            'user',
            $newUser->id,
            'bi-person-plus',
            'gold'
        );

        return response()->json([
            'data' => $newUser,
            'message' => 'Staff account created successfully.',
        ], 201);
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(401, 'Unauthenticated.');
        }

        // Ensure user belongs to the same restaurant
        if ($user->restaurant_id !== $currentUser->restaurant_id) {
            abort(404, 'User not found.');
        }

        // Prevent owner from being demoted by someone else
        if ($user->role === 'owner' && $currentUser->id !== $user->id) {
            abort(403, 'Cannot modify the owner account.');
        }

        $data = $request->validated();

        // If password is provided, hash it
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        AuditLogger::log(
            $currentUser,
            "Updated staff account for {$user->name}",
            'user',
            $user->id,
            'bi-person',
            'slate'
        );

        return response()->json([
            'data' => $user->fresh(),
            'message' => 'Staff account updated successfully.',
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user)
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(401, 'Unauthenticated.');
        }

        // Ensure user belongs to the same restaurant
        if ($user->restaurant_id !== $currentUser->restaurant_id) {
            abort(404, 'User not found.');
        }

        // Prevent deleting yourself
        if ($currentUser->id === $user->id) {
            abort(403, 'You cannot delete your own account.');
        }

        // Prevent deleting the only owner
        if ($user->role === 'owner') {
            $ownerCount = User::where('restaurant_id', $currentUser->restaurant_id)
                ->where('role', 'owner')
                ->count();
            if ($ownerCount <= 1) {
                abort(403, 'Cannot delete the only owner account.');
            }
        }

        AuditLogger::log(
            $currentUser,
            "Deleted staff account for {$user->name}",
            'user',
            $user->id,
            'bi-person-x',
            'rust'
        );

        $user->delete();

        return response()->json([
            'message' => 'Staff account deleted successfully.',
        ]);
    }

    /**
     * Get single user.
     */
    public function show(User $user)
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->restaurant_id !== $currentUser->restaurant_id) {
            abort(404, 'User not found.');
        }

        return response()->json([
            'data' => $user,
        ]);
    }
}