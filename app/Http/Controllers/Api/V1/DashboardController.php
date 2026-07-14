<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function kpis(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;
        $timezone = $restaurant->timezone;

        $today = Carbon::now($timezone)->toDateString();
        $yesterday = Carbon::now($timezone)->subDay()->toDateString();

        $todayReservations = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $today)
            ->count();

        $todayRevenue = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $today)
            ->whereNotIn('status', ['Cancelled'])
            ->sum('revenue');

        $todayWalkIns = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $today)
            ->where('source', 'Walk-in')
            ->count();

        $upcoming = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', '>=', $today)
            ->whereNotIn('status', ['Cancelled', 'Completed'])
            ->count();

        $totalTables = Table::where('restaurant_id', $restaurant->id)->count();
        $occupiedTables = Table::where('restaurant_id', $restaurant->id)
            ->where('status', 'Occupied')
            ->count();
        $availableTables = $totalTables - $occupiedTables;

        $yesterdayReservations = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $yesterday)
            ->count();
        $yesterdayRevenue = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $yesterday)
            ->whereNotIn('status', ['Cancelled'])
            ->sum('revenue');
        $yesterdayWalkIns = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $yesterday)
            ->where('source', 'Walk-in')
            ->count();

        $delta = function ($current, $previous) {
            if ($previous == 0) {
                return 0;
            }

            return round((($current - $previous) / $previous) * 100, 1);
        };

        $avgPartySize = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('date', $today)
            ->whereNotIn('status', ['Cancelled'])
            ->avg('party_size') ?? 0;

        return response()->json([
            'data' => [
                'todayReservations' => $todayReservations,
                'todayReservationsDelta' => $delta($todayReservations, $yesterdayReservations),
                'upcomingReservations' => $upcoming,
                'upcomingReservationsDelta' => 0,
                'tablesAvailable' => $availableTables,
                'tablesOccupied' => $occupiedTables,
                'walkIns' => $todayWalkIns,
                'walkInsDelta' => $delta($todayWalkIns, $yesterdayWalkIns),
                'revenueToday' => $todayRevenue,
                'revenueDelta' => $delta($todayRevenue, $yesterdayRevenue),
                'avgPartySize' => round($avgPartySize, 1),
                'noShowRate' => 0,
            ],
        ]);
    }

    public function revenueTrend(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;
        $timezone = $restaurant->timezone;

        $today = Carbon::now($timezone);
        $startOfWeek = $today->copy()->startOfWeek();

        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $thisWeek = [];
        $lastWeek = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i)->toDateString();
            $thisWeek[] = Reservation::where('restaurant_id', $restaurant->id)
                ->whereDate('date', $date)
                ->whereNotIn('status', ['Cancelled'])
                ->sum('revenue');

            $lastDate = $startOfWeek->copy()->subWeek()->addDays($i)->toDateString();
            $lastWeek[] = Reservation::where('restaurant_id', $restaurant->id)
                ->whereDate('date', $lastDate)
                ->whereNotIn('status', ['Cancelled'])
                ->sum('revenue');
        }

        return response()->json([
            'data' => [
                'labels' => $labels,
                'thisWeek' => $thisWeek,
                'lastWeek' => $lastWeek,
            ],
        ]);
    }

    public function statusBreakdown(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $statuses = ['Confirmed', 'Upcoming', 'Completed', 'Cancelled'];
        $values = [];
        foreach ($statuses as $status) {
            $values[] = Reservation::where('restaurant_id', $restaurant->id)
                ->where('status', $status)
                ->count();
        }

        return response()->json([
            'data' => [
                'labels' => $statuses,
                'values' => $values,
            ],
        ]);
    }

    public function activity(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $activities = ActivityLog::where('restaurant_id', $restaurant->id)
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'data' => $activities->map(function ($log) {
                return [
                    'id' => $log->id,
                    'icon' => $log->icon,
                    'tone' => $log->tone,
                    'text' => $log->description,
                    'time' => $log->created_at->diffForHumans(),
                ];
            }),
        ]);
    }
}
