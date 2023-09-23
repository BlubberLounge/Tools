<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Models\User;

class AuthController extends BaseController
{
	/**
	 * Generate sanctum token on successful login
	 */
    /**
     * @OA\Post(
     *      path="/login",
     *      operationId="login",
     *      tags={"Authentification"},
     *      summary="Get a Bearer Token",
     *      description="Get a bearer token by login in with username and password",
     *      @OA\Parameter(
     *          name="name",
     *          description="Username",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          description="Users password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Login")
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
	public function Login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
            // 'device_name' => 'required',
        ]);

		$user = User::where('name', $request->name)->first();

		if (!$user || !Hash::check($request->password, $user->password)) {
			throw ValidationException::withMessages([
				'email' => ['The provided credentials are incorrect.'],
			]);
		}

		return response()->json([
			'user' => $user,
			'access_token' => $user->createToken($request->name)->plainTextToken
		], 200);
	}

    /**
	 * Revoke token; only remove token that is used to perform logout (i.e. will not revoke all tokens)
	 */
    /**
     * @OA\Post(
     *      path="/logout",
     *      operationId="logout",
     *      tags={"Authentification"},
     *      summary="Logout from current login",
     *      description="Logout from current login",
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
	public function logout(Request $request)
    {
		// Revoke the token that was used to authenticate the current request
		$request->user()->currentAccessToken()->delete();
		//$request->user->tokens()->delete(); // use this to revoke all tokens (logout from all devices)
		return response()->json(null, 200);
	}
}
