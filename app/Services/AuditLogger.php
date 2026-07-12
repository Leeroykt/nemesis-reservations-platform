<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an activity entry.
     */
    public static function log(
        ?User $actor,
        string $description,
        string $entityType = null,
        int $entityId = null,
        string $icon = 'bi-info-circle',
        string $tone = 'slate'
    ): void {
        $actor = $actor ?: Auth::user();

        ActivityLog::create([
            'restaurant_id' => $actor?->restaurant_id ?? 1, // fallback to first restaurant (will be adjusted)
            'actor_user_id' => $actor?->id,
            'actor_label' => $actor?->name ?? 'System',
            'icon' => $icon,
            'tone' => $tone,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }
}