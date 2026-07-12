<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Determine if any user with host+ can view any reservations.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['host', 'manager', 'owner']);
    }

    /**
     * Determine if a user can view a specific reservation.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine if a user can create a reservation.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['host', 'manager', 'owner']);
    }

    /**
     * Determine if a user can update a reservation.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return $this->create($user);
    }

    /**
     * Determine if a user can delete a reservation (manager+ only).
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return in_array($user->role, ['manager', 'owner']);
    }

    /**
     * Determine if a user can restore a soft-deleted reservation (manager+ only).
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        return in_array($user->role, ['manager', 'owner']);
    }

    /**
     * Determine if a user can permanently delete a reservation (owner only).
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return $user->role === 'owner';
    }
}
