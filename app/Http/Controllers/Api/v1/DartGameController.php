<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;

use App\Models\DartGame;

class DartGameController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['game'] = DartGame::with(['createdBy:id,name,firstname,lastname', 'users:id,name,firstname,lastname'])->find($id);

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DartGame $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DartGame $game)
    {
        //
    }
}
