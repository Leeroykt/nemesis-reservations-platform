<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Http\Requests\UpdateHoursRequest;
use App\Http\Requests\UpdateRulesRequest;
use App\Http\Requests\UpdateBrandingRequest;
use App\Models\Restaurant;
use App\Models\RestaurantHours;
use App\Models\RestaurantRules;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Get restaurant info.
     */
    public function getRestaurant(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        return response()->json([
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'tagline' => $restaurant->tagline,
                'email' => $restaurant->email,
                'phone' => $restaurant->phone,
                'address' => $restaurant->address,
                'timezone' => $restaurant->timezone,
                'currency' => $restaurant->currency,
                'primary_color_hex' => $restaurant->primary_color_hex,
                'logo_path' => $restaurant->logo_path ? Storage::url($restaurant->logo_path) : null,
            ],
        ]);
    }

    /**
     * Update restaurant info.
     */
    public function updateRestaurant(UpdateRestaurantRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;
        $data = $request->validated();

        $restaurant->update($data);

        AuditLogger::log(
            $user,
            "Updated restaurant information",
            'restaurant',
            $restaurant->id,
            'bi-building',
            'slate'
        );

        return response()->json([
            'data' => $restaurant->fresh(),
            'message' => 'Restaurant information updated.',
        ]);
    }

    /**
     * Get opening hours.
     */
    public function getHours(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $hours = RestaurantHours::where('restaurant_id', $user->restaurant_id)
            ->orderBy('day_of_week')
            ->get();

        return response()->json([
            'data' => $hours,
        ]);
    }

    /**
     * Update opening hours.
     */
    public function updateHours(UpdateHoursRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $data = $request->validated();

        foreach ($data['hours'] as $hourData) {
            RestaurantHours::where('restaurant_id', $user->restaurant_id)
                ->where('day_of_week', $hourData['day_of_week'])
                ->update([
                    'open_time' => $hourData['open_time'] ?? null,
                    'close_time' => $hourData['close_time'] ?? null,
                    'is_closed' => $hourData['is_closed'] ?? false,
                ]);
        }

        AuditLogger::log(
            $user,
            "Updated opening hours",
            'restaurant',
            $user->restaurant_id,
            'bi-clock',
            'slate'
        );

        return response()->json([
            'message' => 'Opening hours updated.',
        ]);
    }

    /**
     * Get booking rules.
     */
    public function getRules(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $rules = RestaurantRules::where('restaurant_id', $user->restaurant_id)->first();

        return response()->json([
            'data' => $rules,
        ]);
    }

    /**
     * Update booking rules.
     */
    public function updateRules(UpdateRulesRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $rules = RestaurantRules::where('restaurant_id', $user->restaurant_id)->firstOrFail();
        $data = $request->validated();

        $rules->update($data);

        AuditLogger::log(
            $user,
            "Updated booking rules",
            'restaurant',
            $user->restaurant_id,
            'bi-sliders',
            'slate'
        );

        return response()->json([
            'data' => $rules->fresh(),
            'message' => 'Booking rules updated.',
        ]);
    }

    /**
     * Update branding (logo and primary color).
     */
    public function updateBranding(UpdateBrandingRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($restaurant->logo_path) {
                Storage::delete($restaurant->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        $restaurant->update($data);

        AuditLogger::log(
            $user,
            "Updated branding",
            'restaurant',
            $restaurant->id,
            'bi-palette',
            'gold'
        );

        return response()->json([
            'data' => [
                'primary_color_hex' => $restaurant->primary_color_hex,
                'logo_url' => $restaurant->logo_path ? Storage::url($restaurant->logo_path) : null,
            ],
            'message' => 'Branding updated.',
        ]);
    }
}