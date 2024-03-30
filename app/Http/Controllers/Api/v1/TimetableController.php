<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Timetable;


class TimetableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/timetable",
     *      operationId="getAllTimetable",
     *      tags={"Timetables"},
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
    public function index()
    {
        $data = Auth::user()->timetableData;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *      path="/timetable",
     *      operationId="createTimetable",
     *      tags={"Timetables"},
     *      summary="Timetable store",
     *      description="Store a newly created resource in storage.",
     *      @OA\Parameter(
     *          name="date",
     *          description="Date",
     *          required=true,
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
    public function store(Request $request)
    {
        $data = Auth::user()->timetableData;
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *      path="/timetable/{timetableID}",
     *      operationId="getTimetable",
     *      tags={"Timetables"},
     *      summary="Timetable show",
     *      description="Display the specified resource.",
     *      @OA\Parameter(
     *          name="timetableID",
     *          description="timetableID",
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
    public function show(string $id)
    {
        $data = Timetable::find($id);
        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/timetable/{timetableID}",
     *      operationId="updateTimetable",
     *      tags={"Timetables"},
     *      summary="Timetable update",
     *      description="Update the specified resource by ID or Date",
     *      @OA\Parameter(
     *          name="timetableID",
     *          description="timetableID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          required=true,
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
        if(!is_numeric($id))
            return $this->updateByDate($request, $id);

        $timetable = Timetable::findOrFail($id);
        $timetable->status = $request->status ?? $timetable->status;
        $timetable->save();

        // $data = Timetable::updateOrCreate([
        //     'id' => $id,
        //     'user_id' => Auth::user()->id,
        //     'date' => $request->date,
        //     'status' => $request->status,
        // ]);

        $data = $timetable;
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
     * Update the specified resource in storage by date.
     */
    private function updateByDate(Request $request, string $date)
    {
        $timetable = Timetable::where('date', Carbon::parse($date)->format('Y-m-d'))
            ->where('user_id', Auth::user()->id)
            ->first();
        if(is_null($timetable)) {
            $timetable = new Timetable;
            $timetable->user_id = Auth::user()->id;
            $timetable->date = $date;
        }
        $timetable->status = $request->status ?? $timetable->status;
        $timetable->save();

        $data = $timetable;

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Display the specified resource between two dates.
     */
    /**
     * @OA\Get(
     *      path="/timetable/between/{dateFrom}/{dateTo}",
     *      operationId="getTimetableBetween",
     *      tags={"Timetables"},
     *      summary="Timetable show between",
     *      description="Display the specified resource between two dates.",
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
    public function betweenDates(string $dateFrom, string $dateTo)
    {
        $data = Auth::user()->timetableData->whereBetween('date', [$dateFrom, $dateTo]);
        return $this->sendResponse($data, 'ok');
    }
}
