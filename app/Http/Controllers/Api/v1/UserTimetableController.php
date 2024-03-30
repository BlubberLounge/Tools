<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;

use App\Models\User;

class UserTimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/user/{userID}/timetable",
     *      operationId="getUserAllTimetable",
     *      tags={"Users"},
     *      summary="Timetable index",
     *      description="Display a listing of the resource.",
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
    public function index(string $id)
    {
        $data['timetable'] = User::find($id)->timetableData->all();
        $data['user'] = User::find($id);
        return $this->sendResponse($data, 'ok');
    }

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
    /**
     * @OA\Get(
     *      path="/user/{userID}/timetable/{timetableID}",
     *      operationId="getUserTimetable",
     *      tags={"Users"},
     *      summary="Timetable show",
     *      description="Display the specified resource.",
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
    public function show(string $userId, string $timetableId)
    {
        $data['timetable'] = User::find($userId)->timetableData->find($timetableId);
        $data['user'] = User::find($userId);
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     $data = "";
    //     return $this->sendResponse($data, 'ok');
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     //
    // }

    /**
     * Display the specified resource between two dates.
     */
    /**
     * @OA\Get(
     *      path="/user/{userID}/timetable/between/{dateFrom}/{dateTo}",
     *      operationId="getUserTimetableBetween",
     *      tags={"Users"},
     *      summary="Timetable show between",
     *      description="Display the specified resource between two dates.",
     *      @OA\Parameter(
     *          name="userID",
     *          description="userID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="dateFrom",
     *          description="dateFrom",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
    *      @OA\Parameter(
     *          name="dateTo",
     *          description="dateTo",
     *          required=true,
     *          in="path",
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
    public function betweenDates(string $userId, string $dateFrom, string $dateTo)
    {
        $data['timetable'] = User::find($userId)->timetableData->whereBetween('date', [$dateFrom, $dateTo]);
        $data['user'] = User::find($userId);
        return $this->sendResponse($data, 'ok');
    }
}
