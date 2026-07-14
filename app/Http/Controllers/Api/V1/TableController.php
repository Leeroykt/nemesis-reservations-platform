<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $tables = Table::where('restaurant_id', $restaurant->id)->get();

        return response()->json(['data' => $tables]);
    }

    public function updateStatus(Request $request, $id)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $table = Table::where('restaurant_id', $user->restaurant_id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Available,Occupied,Reserved,Cleaning',
        ]);

        $table->update(['status' => $validated['status']]);

        return response()->json(['data' => $table]);
    }
}
