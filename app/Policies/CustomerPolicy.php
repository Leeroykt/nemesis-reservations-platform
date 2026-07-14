<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['manager', 'owner']);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->viewAny($user);
    }
}
