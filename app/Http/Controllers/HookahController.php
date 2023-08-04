<?php

namespace App\Http\Controllers;

use App\Models\Hookah;
use App\Models\Manufacturer;
use App\Http\Requests\StoreHookahRequest;
use App\Http\Requests\UpdateHookahRequest;

class HookahController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['hookahs'] = Hookah::orderBy('id','asc')->paginate(15);

        return view('hookah.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['manufacturers'] = Manufacturer::orderBy('id','desc')->get();

        return view('hookah.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreHookahRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHookahRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function show(Hookah $hookah)
    {
        return view('user.show', $hookah);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function edit(Hookah $hookah)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateHookahRequest  $request
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateHookahRequest $request, Hookah $hookah)
    {
        $h = User::find($h->id);
        $h->name = $request->name;
        $h->firstname = $request->firstname;
        $h->lastname = $request->lastname;
        $h->email = $request->email;
        $h->password = $request->password ? Hash::make($request->password) : $hookah->password;
        $h->role_id = $request->role;

        $u->save();

        return redirect()->route('hookah.index')
            ->with('success','Hookah has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hookah $hookah)
    {
        $hookah->delete();
        return redirect()->back()
            ->with('success','Hookah has been deleted successfully');
    }
}
