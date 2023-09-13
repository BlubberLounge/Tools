<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Invitation;
use App\Enums\InvitationStatus;

class VerifyInvitationToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $invitationToken = $request->route()->parameters()['invitationToken'];
        $invitation = Invitation::where('token', $invitationToken);

        abort_if(!$invitation->exists(), 404);

        $invitationFirst = $invitation->first();

        if($invitationFirst->status != InvitationStatus::APPROVED)
            abort(403, 'Invitation not approved or request to access denied.');

        if($invitationFirst->isExpired())
            abort(403, 'Invitation is expired');

        return $next($request);
    }
}
