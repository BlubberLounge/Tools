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
    /**
     * @OA\Post(
     *      path="/throw",
     *      operationId="storeThrow",
     *      tags={"Throw"},
     *      summary="Store a newly created resource in storage",
     *      description="Store a newly created resource in storage.",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="game",
     *                     description="game uuid",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     description="user id",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="set",
     *                     description="set number",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="leg",
     *                     description="leg number",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="turn",
     *                     description="turn number",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="throw",
     *                     description="throw number",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="value",
     *                     description="calculated field value (field * ringMultiplier)",
     *                     type="int"
     *                 ),
     *                 @OA\Property(
     *                     property="field",
     *                     description="0 to 20 and 25 aka bull",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="ring",
     *                     description="aka. multiplier; O = Out 0x, S = Single 1x, D = Double 2x, T = Tripple 3x",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="x",
     *                     description="normalized cartesian x value",
     *                     type="float"
     *                 ),
     *                 @OA\Property(
     *                     property="y",
     *                     description="normalized cartesian y value",
     *                     type="float"
     *                 ),
     *                 example={
     *                      "game": "9a334dcd-1d68-4b11-aa17-c21e7a5c9411",
     *                      "user": 1337,
     *                      "set": 1,
     *                      "leg": 1,
     *                      "turn": 3,
     *                      "throw": 26,
     *                      "value": 12,
     *                      "field": "4",
     *                      "ring": "T",
     *                      "x": 0.4398,
     *                      "y": 0.2594,
     *                  }
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
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
            $newThrow->field = (string)$throw['field'];
            $newThrow->ring = $throw['ring'];
            $newThrow->x = $throw['x'];
            $newThrow->y = $throw['y'];
            $newThrow->save();

            if(!$throw['valid'])
                $newThrow->delete();
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
