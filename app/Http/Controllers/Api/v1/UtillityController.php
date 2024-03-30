<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use Illuminate\Http\Request;


class UtillityController extends Controller
{
    /**
     * @OA\Get(
     *      path="/ping",
     *      operationId="ping",
     *      tags={"Utillities"},
     *      summary="Ping the server",
     *      description="Test if the server is reachable",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function ping()
    {
        return $this->sendResponse("pong", "Server is online and ready");
    }
}
