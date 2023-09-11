<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\DartGame;


class UserController extends Controller
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
     * @OA\Get(
     *      path="/user/{userID}",
     *      operationId="getUser",
     *      tags={"Users"},
     *      summary="Get user by ID",
     *      description="Get user by ID",
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
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $data['user'] = $user;

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * @OA\Get(
     *      path="/user/search/{userName}",
     *      operationId="searchUser",
     *      tags={"Users"},
     *      summary="Get a list of users by username",
     *      description="Get a list of users by username",
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
    /**
     * search a resouce
     */
    public function search(Request $request, string $name)
    {
        $data['users'] = User::like('name', $name)->get();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    public function showThrowsByGame(Request $request, string $id)
    {
        // $game = DartGame::find($id);
        $data['throws'] = Auth::user()->DartThrows()->where('dart_game_id', $id)->get();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    public function showPlaces(Request $request)
    {
        $data['places'] = Auth::user()->DartGames()->get(['place'])->countBy('place');

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    public function showPositions(Request $request)
    {
        $data['positions'] = Auth::user()->DartGames()->get(['position'])->countBy('position');

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    public function showDartActivity(Request $request)
    {
        $column = DartGame::getTableName().'.created_at';
        $data['activity'] = Auth::user()->DartGames(false, false)->get([$column]);

        return $this->sendResponse($data, 'ok');
    }
}
