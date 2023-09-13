<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\UpdateInvitationRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Enums\InvitationStatus;
use App\Models\Invitation;


class InvitationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // override base controller constructor
        $this->middleware('auth', ['except' => ['store', 'request']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        $invite = new Invitation();
        $invite->token = Str::orderedUuid();
        $invite->status = InvitationStatus::NEW;
        $invite->firstname = $request->firstname ?? '';
        $invite->lastname = $request->lastname ?? '';
        $invite->email = $request->email ?? '';

        $invite->save();

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

        $invitation->status = $request->status ?? $invitation->status;

        if($invitation->status === InvitationStatus::APPROVED)
            $invitation->expires_at == now()->addDays(7);

        $invitation->save();

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
        return view('auth.request');
    }

    public function approve(UpdateInvitationRequest $request, Invitation $invitation)
    {
        $invitation->status = InvitationStatus::APPROVED;
        Mail::send(new \App\Mail\InvitationMail());

        return $this->update($request, $invitation);
    }

    public function denie(UpdateInvitationRequest $request, Invitation $invitation)
    {
        $invitation->status = InvitationStatus::DENIED;
        return $this->update($request, $invitation);
    }
}
