<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.1.0",
 *      title="Tools Api",
 *      description="BlubberLounge Tools REST Api",
 *      @OA\Contact(
 *          email="contect@blubber-lounge.de"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Current Server"
 * )
 *
 * @OA\Tag(
 *     name="Authentification",
 *     description="Authentification Endpoints"
 * )
 *
 * @OA\Tag(
 *     name="User",
 *     description="User Endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Throw",
 *     description="Dart Throw Endpoints"
 * )
 */
class Controller extends BaseController
{
    // use AuthorizesRequests, ValidatesRequests;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // is already set in api_v1.php routing file would interrupt api AuthProcess
        // $this->middleware('auth:sanctum');
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
