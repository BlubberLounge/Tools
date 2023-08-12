<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Login",
 *     description="Login model",
 *     @OA\Xml(
 *         name="Login"
 *     )
 * )
 */
class Login
{
    /**
     * @OA\Property(
     *     title="User",
     *     description="User model"
     * )
     *
     * @var \App\Virtual\Models\User
     */
    public $user;

    /**
     * @OA\Property(
     *      title="Access Token",
     *      description="Access Token (Bearer Token / Sanctum Token)",
     *      example="5|gtAK1319CZAFvsrpQibyWjHiM54hV3pfrkdJkwuB"
     * )
     *
     * @var string
     */
    public $access_token;
}
