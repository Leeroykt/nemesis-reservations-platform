<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        ?User $actor,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        string $icon = 'bi-info-circle',
        string $tone = 'slate'
    ): void {
        $actor = $actor ?? Auth::user();

        /** @var User|null $actor */
        $actor = $actor;

        ActivityLog::create([
            'restaurant_id' => $actor ? $actor->restaurant_id : 1,
            'actor_user_id' => $actor?->id,
            'actor_label' => $actor ? $actor->name : 'System',
            'icon' => $icon,
            'tone' => $tone,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}
