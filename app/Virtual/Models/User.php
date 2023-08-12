<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="User",
 *     description="User model",
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 */
class User
{

    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var integer
     */
    private $id;

    /**
     * @OA\Property(
     *      title="Username",
     *      description="Username",
     *      example="charley81"
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *      title="Firstname",
     *      description="Firstname of the user",
     *      example="Eberhard"
     * )
     *
     * @var string
     */
    public $firstname;

    /**
     * @OA\Property(
     *      title="Lastname",
     *      description="Lastname of the user",
     *      example="Müller"
     * )
     *
     * @var string
     */
    public $lastname;

    /**
     * @OA\Property(
     *      title="Email",
     *      description="Email of the user",
     *      example="admin+EventManager@blubber-lounge.de"
     * )
     *
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *      title="Telefon mobile",
     *      description="Mobil telefon number",
     *      example="+18 456439282"
     * )
     *
     * @var string
     */
    public $telefon_mobil;

    /**
     * @OA\Property(
     *     title="Date Of Birth",
     *     description="Date Of Birth",
     *     example="1999-11-02 06:33:21",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    public $dob;

    /**
     * @OA\Property(
     *      title="Img",
     *      description="Profile image path",
     *      example="/storage/avatar/avatar-dummy.png"
     * )
     *
     * @var string
     */
    public $img;

    /**
     * @OA\Property(
     *      title="QRCode",
     *      description="QRCode for contact adding",
     *      example="http::// ... some link"
     * )
     *
     * @var string
     */
    public $qrcode;

    /**
     * @OA\Property(
     *     title="QRCode created at",
     *     description="QRCode created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    public $qrcode_created_at;

    /**
     * @OA\Property(
     *     title="Email created at",
     *     description="Email created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    public $email_verified_at;

    /**
     * @OA\Property(
     *     title="Locked",
     *     description="Flag if Account is Blocked/Locked",
     *     format="boolean",
     *     example=false
     * )
     *
     * @var integer
     */
    public $locked;

    /**
     * @OA\Property(
     *     title="Created at",
     *     description="Created at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated at",
     *     description="Updated at",
     *     example="2020-01-27 17:50:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $updated_at;
}
