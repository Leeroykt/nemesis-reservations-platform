<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    /**
     * Get all email templates.
     */
    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $templates = EmailTemplate::where('restaurant_id', $user->restaurant_id)
            ->orderBy('key')
            ->get();

        return response()->json([
            'data' => $templates,
        ]);
    }

    /**
     * Get a single email template.
     */
    public function show($key, Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $template = EmailTemplate::where('restaurant_id', $user->restaurant_id)
            ->where('key', $key)
            ->firstOrFail();

        return response()->json([
            'data' => $template,
        ]);
    }

    /**
     * Update an email template.
     */
    public function update(UpdateEmailTemplateRequest $request, $key)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $template = EmailTemplate::where('restaurant_id', $user->restaurant_id)
            ->where('key', $key)
            ->firstOrFail();

        $data = $request->validated();
        $template->update($data);

        AuditLogger::log(
            $user,
            "Updated email template: {$template->name} ({$template->key})",
            'email_template',
            $template->id,
            'bi-envelope',
            'slate'
        );

        return response()->json([
            'data' => $template->fresh(),
            'message' => 'Email template updated successfully.',
        ]);
    }
}