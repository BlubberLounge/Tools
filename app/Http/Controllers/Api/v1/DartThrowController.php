<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDartThrowRequest;

use App\Models\DartThrow;
use App\Models\User;

class DartThrowController extends Controller
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
    public function store(StoreDartThrowRequest $request)
    {
        // dd($request->input('*.user'));
        // dd($request);

        foreach($request->input('*') as $throw) {
            $newThrow = new DartThrow();
            $newThrow->dart_game_id = $throw['game'];
            $newThrow->user_id = $throw['user'];
            $newThrow->set = $throw['set'];
            $newThrow->leg = $throw['leg'];
            $newThrow->turn = $throw['turn'];
            $newThrow->throw = $throw['throw'];
            $newThrow->value = $throw['value'];
            $newThrow->x = $throw['x'];
            $newThrow->y = $throw['y'];
            $newThrow->data_input_type = $throw['data_input_type'];
            $newThrow->save();
        }

        $data['set'] = $request->input('*.set')[0];
        $data['leg'] = $request->input('*.leg')[0];
        $data['turn'] = $request->input('*.turn')[0];

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Display the specified resource.
     */
    public function show(DartThrow $dartThrow)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DartThrow $dartThrow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DartThrow $dartThrow)
    {
        //
    }

}
