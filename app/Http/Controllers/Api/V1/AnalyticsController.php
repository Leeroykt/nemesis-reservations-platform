<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get peak hours data (number of reservations per hour).
     */
    public function peakHours(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $peakData = Reservation::where('restaurant_id', $restaurant->id)
            ->select(DB::raw('EXTRACT(HOUR FROM time) as hour'), DB::raw('COUNT(*) as count'))
            ->whereNotIn('status', ['Cancelled'])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $labels = [];
        $data = [];
        foreach ($peakData as $row) {
            $labels[] = sprintf('%02d:00', $row->hour);
            $data[] = $row->count;
        }

        return response()->json([
            'data' => [
                'labels' => $labels,
                'covers' => $data,
            ],
        ]);
    }

    /**
     * Get popular tables (top 5 by bookings).
     */
    public function popularTables(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $popular = Reservation::query()
            ->where('restaurant_id', $restaurant->id)
            ->select('table_id', DB::raw('COUNT(*) as bookings'))
            ->whereNotNull('table_id')
            ->whereNotIn('status', ['Cancelled'])
            ->groupBy('table_id')
            ->orderBy('bookings', 'desc')
            ->limit(5)
            ->get();

        $tables = Table::whereIn('id', $popular->pluck('table_id'))
            ->get()
            ->keyBy('id');

        $labels = [];
        $data = [];
        foreach ($popular as $row) {
            $table = $tables->get($row->table_id);
            $labels[] = $table ? $table->code : 'Unknown';
            $data[] = $row->bookings;
        }

        return response()->json([
            'data' => [
                'labels' => $labels,
                'bookings' => $data,
            ],
        ]);
    }

    /**
     * Get customer growth (new vs returning customers per month).
     */
    public function customerGrowth(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $reservations = Reservation::where('restaurant_id', $restaurant->id)
            ->whereNotNull('customer_id')
            ->whereNotIn('status', ['Cancelled'])
            ->select('customer_id', 'date')
            ->orderBy('date')
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json([
                'data' => [
                    'labels' => [],
                    'newCustomers' => [],
                    'returning' => [],
                ],
            ]);
        }

        $months = $reservations->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('Y-m');
        })->unique()->sort()->values();

        $labels = [];
        $newCustomers = [];
        $returning = [];

        // Compute first reservation month per customer
        $firstMonths = [];
        foreach ($reservations->groupBy('customer_id') as $customerId => $res) {
            $firstDate = $res->min('date');
            $firstMonths[$customerId] = Carbon::parse($firstDate)->format('Y-m');
        }

        foreach ($months as $month) {
            $labels[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $newCount = 0;
            $returnCount = 0;

            foreach ($firstMonths as $customerId => $firstMonth) {
                if ($firstMonth === $month) {
                    $newCount++;
                } else {
                    $hasResInMonth = $reservations->filter(function ($res) use ($customerId, $month) {
                        return $res->customer_id === $customerId && Carbon::parse($res->date)->format('Y-m') === $month;
                    })->isNotEmpty();

                    if ($hasResInMonth) {
                        $returnCount++;
                    }
                }
            }

            $newCustomers[] = $newCount;
            $returning[] = $returnCount;
        }

        return response()->json([
            'data' => [
                'labels' => $labels,
                'newCustomers' => $newCustomers,
                'returning' => $returning,
            ],
        ]);
    }
}
