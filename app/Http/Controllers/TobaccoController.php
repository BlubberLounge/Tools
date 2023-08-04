<?php

namespace App\Http\Controllers;

use App\Models\Tobacco;
use App\Http\Requests\StoreTobaccoRequest;
use App\Http\Requests\UpdateTobaccoRequest;

class TobaccoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTobaccoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTobaccoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tobacco  $tobacco
     * @return \Illuminate\Http\Response
     */
    public function show(Tobacco $tobacco)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tobacco  $tobacco
     * @return \Illuminate\Http\Response
     */
    public function edit(Tobacco $tobacco)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTobaccoRequest  $request
     * @param  \App\Models\Tobacco  $tobacco
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTobaccoRequest $request, Tobacco $tobacco)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tobacco  $tobacco
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tobacco $tobacco)
    {
        //
    }
}
