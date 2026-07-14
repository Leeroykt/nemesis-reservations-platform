<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an activity entry.
     *
     * @param  User|null  $actor  The authenticated user, or null for guest/system actions.
     * @param  string  $description  Human‑readable description.
     * @param  string|null  $entityType  e.g. 'reservation', 'customer', 'table'.
     * @param  int|null  $entityId  ID of the related entity.
     * @param  string  $icon  Bootstrap icon class (e.g. 'bi-plus-circle').
     * @param  string  $tone  Color tone: 'gold', 'emerald', 'rust', 'slate'.
     * @param  string|null  $actorLabel  Override the displayed actor name (e.g. 'Guest (website)').
     *
     * @throws \RuntimeException if restaurant_id cannot be determined.
     */
    public static function log(
        ?User $actor,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        string $icon = 'bi-info-circle',
        string $tone = 'slate',
        ?string $actorLabel = null
    ): void {
        // If no actor provided, try to get the current authenticated user.
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $actor = $actor ?? $currentUser;

        // Determine restaurant_id
        $restaurantId = null;

        if ($actor instanceof User) {
            $restaurantId = $actor->restaurant_id;
        } elseif ($entityType === 'reservation' && $entityId) {
            /** @var Reservation|null $reservation */
            $reservation = Reservation::withTrashed()->find($entityId);
            if ($reservation) {
                $restaurantId = $reservation->restaurant_id;
            }
        }

        // If we still have no restaurant, this is a critical error – must not log.
        if (! $restaurantId) {
            throw new \RuntimeException(
                'Unable to determine restaurant_id for audit log. Provide an actor or a reservation entity.'
            );
        }

        $actorLabel = $actorLabel ?? ($actor instanceof User ? $actor->name : 'System');

        ActivityLog::create([
            'restaurant_id' => $restaurantId,
            'actor_user_id' => $actor instanceof User ? $actor->id : null,
            'actor_label' => $actorLabel,
            'icon' => $icon,
            'tone' => $tone,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}
