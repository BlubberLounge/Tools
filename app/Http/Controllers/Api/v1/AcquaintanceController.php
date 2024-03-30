<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Acquaintance;

class AcquaintanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/acquaintance/{acquaintanceID}",
     *      operationId="updateAcquaintance",
     *      tags={"Acquaintances"},
     *      summary="Acquaintance update",
     *      description="Update the specified resource by ID",
     *      @OA\Parameter(
     *          name="acquaintanceID",
     *          description="acquaintanceID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="showOnHomeView",
     *          description="showOnHomeView",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function update(Request $request, string $id)
    {
        $acquaintance = Acquaintance::findOrFail($id);

        if(isset($request->status) && !is_null($request->status))
            $acquaintance->status = $request->status;

        if(isset($request->showOnHomeView) && !is_null($request->showOnHomeView))
            $acquaintance->showOnHomeView = $request->showOnHomeView;

        $acquaintance->save();

        $data = $acquaintance;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/acquaintance/byReceiver/{acquaintanceID}",
     *      operationId="updateAcquaintanceByReceiver",
     *      tags={"Acquaintances"},
     *      summary="Acquaintance update by receiver user",
     *      description="Update the specified resource by ID",
     *      @OA\Parameter(
     *          name="acquaintanceID",
     *          description="acquaintanceID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="showOnHomeView",
     *          description="showOnHomeView",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function updateByReceiver(Request $request, string $id)
    {
        $acquaintance = Acquaintance::where('receiver_user_id', $id);

        if(isset($request->status) && !is_null($request->status))
            $acquaintance->status = $request->status;

        if(isset($request->showOnHomeView) && !is_null($request->showOnHomeView))
            $acquaintance->showOnHomeView = $request->showOnHomeView;

        $acquaintance->save();

        $data = $acquaintance;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/acquaintance/byTransmitter/{acquaintanceID}",
     *      operationId="updateAcquaintanceByTransmitter",
     *      tags={"Acquaintances"},
     *      summary="Acquaintance update by transmitter user",
     *      description="Update the specified resource by ID",
     *      @OA\Parameter(
     *          name="acquaintanceID",
     *          description="acquaintanceID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="showOnHomeView",
     *          description="showOnHomeView",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function updateByTransmitter(Request $request, string $id)
    {
        $acquaintance = Acquaintance::where('transmitter_user_id', $id);

        if(isset($request->status) && !is_null($request->status))
            $acquaintance->status = $request->status;

        if(isset($request->showOnHomeView) && !is_null($request->showOnHomeView))
            $acquaintance->showOnHomeView = $request->showOnHomeView;

        $acquaintance->save();

        $data = $acquaintance;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/acquaintance/byReceiverOrTransmitter/{acquaintanceID}",
     *      operationId="updateAcquaintanceByReceiverOrTransmitter",
     *      tags={"Acquaintances"},
     *      summary="Acquaintance update by receiver OR transmitter user",
     *      description="Update the specified resource by ID",
     *      @OA\Parameter(
     *          name="acquaintanceID",
     *          description="acquaintanceID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="showOnHomeView",
     *          description="showOnHomeView",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *       @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function updateByReceiverOrTransmitter(Request $request, string $id)
    {
        $acquaintance = Acquaintance::where('transmitter_user_id', $id)
            ->where('receiver_user_id', Auth::user()->id)
            ->first();

        // try switching the roles
        if(is_null($acquaintance))
            $acquaintance = Acquaintance::where('transmitter_user_id', Auth::user()->id)
                ->where('receiver_user_id', $id)
                ->first();

        if(isset($request->status) && !is_null($request->status))
            $acquaintance->status = $request->status;

        if(isset($request->showOnHomeView) && !is_null($request->showOnHomeView))
            $acquaintance->showOnHomeView = $request->showOnHomeView;

        $acquaintance->save();

        $data = $acquaintance;
        return $this->sendResponse($data, 'ok');
    }
}
