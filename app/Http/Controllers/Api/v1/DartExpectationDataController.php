<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use App\Models\DartExpectationData;
use Illuminate\Http\Request;

class DartExpectationDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $expectationData = new DartExpectationData();
        $expectationData->sigma = $request->sigma;
        $expectationData->score = $request->expectedPoints;

        $expectationData->save();

        $data['data'] = 'ok';

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Display the specified resource.
     */
    public function show(DartExpectationData $dartExpectationData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DartExpectationData $dartExpectationData)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DartExpectationData $dartExpectationData)
    {
        //
    }
}