<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use Illuminate\Http\Response;

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
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *      path="/user/{userID}",
     *      operationId="getUser",
     *      tags={"User"},
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
     * )
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
     * search a resouce
     */
    /**
     * @OA\Get(
     *      path="/user/search/{userName}",
     *      operationId="searchUser",
     *      tags={"User"},
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
    public function search(Request $request, string $name)
    {
        $data['users'] = User::like('name', $name)->get();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    /**
     * @OA\Get(
     *      path="/user/showDashboardData",
     *      operationId="showDashboardData",
     *      tags={"User"},
     *      summary="Get a list of statistics used in the dart dashboard from currently logged in user",
     *      description="Get a list of statistics used in the dart dashboard from currently logged in user",
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
    public function showDashboardData(Request $request)
    {
        $data = [];
        $data['places'] = Auth::user()->getDartPlaces();
        $data['positions'] = Auth::user()->getDartPositions();
        $data['activity'] = Auth::user()->getDartActivity();
        $data['gameTypes'] = Auth::user()->getDartGameTypes();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    /**
     * @OA\Get(
     *      path="/user/showPlaces",
     *      operationId="showPlaces",
     *      tags={"User"},
     *      summary="Get a list of places from currently logged in user",
     *      description="Get a list of places from currently logged in user",
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
    public function showPlaces(Request $request)
    {
        $data['places'] = Auth::user()->getDartPlaces();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    /**
     * @OA\Get(
     *      path="/user/showPositions",
     *      operationId="showPositions",
     *      tags={"User"},
     *      summary="Get a list of positions from currently logged in user",
     *      description="Get a list of positions from currently logged in user",
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
    public function showPositions(Request $request)
    {
        $data['positions'] = Auth::user()->getDartPositions();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    /**
     * @OA\Get(
     *      path="/user/showDartActivity",
     *      operationId="showDartActivity",
     *      tags={"User"},
     *      summary="Get a list of dates from currently logged in user",
     *      description="Get a list of dates from currently logged in user",
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
    public function showDartActivity(Request $request)
    {
        $data['activity'] = Auth::user()->getDartActivity();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    /**
     * @OA\Get(
     *      path="/user/showGameTypes",
     *      operationId="showGameTypes",
     *      tags={"User"},
     *      summary="Get a list of game types from currently logged in user",
     *      description="Get a list of game types from currently logged in user",
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
    public function showGameTypes(Request $request)
    {
        $data['gameTypes'] = Auth::user()->getDartGameTypes();

        return $this->sendResponse($data, 'ok');
    }

    /**
     *
     */
    /**
     * @OA\Get(
     *      path="/user/showThrowsByGame/{dartGame}",
     *      operationId="showThrowsByGame",
     *      tags={"User"},
     *      summary="Get a list of throws from currently logged in user by game id",
     *      description="Get a list of throws from currently logged in user by game id",
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
    public function showThrowsByGame(Request $request, string $id)
    {
        // $game = DartGame::find($id);
        $data['throws'] = Auth::user()->DartThrows()->where('dart_game_id', $id)->get();

        return $this->sendResponse($data, 'ok');
    }



    /**
     *
     */
    public function updateSettings(Request $request)
    {
        Auth::user()->settings->set($request->input('id'), $request->input('value'));

        return $this->sendResponse(null, 'ok');
    }
}
