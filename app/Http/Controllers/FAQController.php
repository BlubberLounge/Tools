<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Http\Requests\StoreFAQRequest;
use App\Http\Requests\UpdateFAQRequest;

class FAQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['FAQList'] = FAQ::orderBy('created_at', 'asc')->get();
        return view('faq.index', $data);
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
    public function store(StoreFAQRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FAQ $fAQ)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FAQ $fAQ)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFAQRequest $request, FAQ $fAQ)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FAQ $fAQ)
    {
        //
    }
}
