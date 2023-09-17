<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\UpdateInvitationRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Mail\AccessRequestedMail;


class InvitationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // override base controller auth middleware
        $this->middleware('auth', ['except' => ['store', 'request']]);

        // $this->authorizeResource(Invitation::class, 'invitation');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Invitation::class);

        $data['invitations'] = Invitation::orderBy('status', 'ASC')->orderBy('expires_at', 'DESC')->orderBy('created_at', 'DESC')->paginate(20);
        return view('invitation.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvitationRequest $request)
    {
        // Only guests can request access
        if(Auth::check())
            abort(403);

        $invite = new Invitation();
        $invite->status = InvitationStatus::NEW;
        $invite->firstname = $request->firstname ?? '';
        $invite->lastname = $request->lastname ?? '';
        $invite->email = $request->email ?? '';

        $invite->save();

        Mail::to($invite->email)->send(new AccessRequestedMail($invite));

        return redirect()->route('index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invitation $invitation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invitation $invitation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvitationRequest $request, Invitation $invitation)
    {
        $this->authorize('update', Invitation::class);

        $invitation->status = $request->status ?? $invitation->status;

        if($invitation->status === InvitationStatus::APPROVED) {
            $invitation->token = Str::orderedUuid();
            $invitation->expires_at = now()->addDays(7);
        }

        $invitation->save();

        $this->notifyStatusChanged($invitation);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invitation $invitation)
    {
        //
    }

    public function request()
    {
        // Only guests can request access
        if(Auth::check())
            abort(403);

        return view('auth.request');
    }

    public function approve(UpdateInvitationRequest $request, Invitation $invitation)
    {
        $this->authorize('approve', $invitation);

        $invitation->status = InvitationStatus::APPROVED;
        return $this->update($request, $invitation);
    }

    public function denie(UpdateInvitationRequest $request, Invitation $invitation)
    {
        $this->authorize('denie', $invitation);

        $invitation->status = InvitationStatus::DENIED;
        return $this->update($request, $invitation);
    }

    protected function notifyStatusChanged(Invitation $invitation)
    {
        Mail::to($invitation->email)->send(new \App\Mail\InvitationMail($invitation));
    }
}
