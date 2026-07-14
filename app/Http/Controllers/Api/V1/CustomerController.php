<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $restaurant = $user->restaurant;

        $query = Customer::where('restaurant_id', $restaurant->id);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('vip') && $request->input('vip')) {
            $query->where('is_vip', true);
        }

        $perPage = $request->input('per_page', 25);
        $customers = $query->paginate($perPage);

        return CustomerResource::collection($customers);
    }

    public function show(Customer $customer)
    {
        $customer->load(['preferences', 'reservations']);

        return new CustomerResource($customer);
    }
}
