<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Appointment;
use DateTime;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Auth::user()->appointments()->where([
            ['start', '>=', request('start')],
            ['end', '<', request('end')],
        ])->get();
        $data = [];

        foreach($appointments as $appointment)
            $data[] = (object)[
                'start' => $appointment->start,
                'end' => $appointment->end,
                'title' => $appointment->title,
                'description' => $appointment->description,
                'status' => $appointment->pivot->status,
            ];

        return response()->json($data, 200);
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
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
