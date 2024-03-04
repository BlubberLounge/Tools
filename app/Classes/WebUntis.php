<?php

namespace App\Classes;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use \Illuminate\Http\Client\Response;


/**
 * WebUntis
 */
class WebUntis
{
    /**
     * General URL
     */
    protected string $url;

    /**
     * Session ID
     */
    protected string $sessionId;

    /**
     * Base64 encoded school ID
     */
    protected string $school64;

    /**
     *
     */
    private string $personId;

    /**
     *
     */
    private string $personType;

    /**
     *
     */
    private string $classId;


    /**
     * Constructor
     */
    public function __construct(
        protected string $school,
        protected string $username,
        protected string $password,
        protected string $baseUrl,
        protected string $identity = "MyApplication"
    ) {
        $this->url = $baseUrl . '/WebUntis/jsonrpc.do?school=' . $school;
        $this->school64 = base64_encode($school);
    }

    /**
     * Log-in
     */
    public function login()
    {
        $response = Http::post( $this->url, [
            'id' => $this->identity,
            'method' => 'authenticate',
            'params' => [
                'user' => $this->username,
                'password' => $this->password,
                'client' => $this->identity,
            ],
            'jsonrpc' => '2.0'
        ]);

        $this->sessionId = $response->json()['result']['sessionId'];
        $this->personId = $response->json()['result']['personId'];
        $this->personType = $response->json()['result']['personType'];
        $this->classId = $response->json()['result']['klasseId'];

        return $response;
    }


    /**
     * Get the last time data got imported in WebUntis
     *
     */
    public function getLatestImportTime()
    {
        return $this->sendRequest('getLatestImportTime')->json();
    }

    /**
     *
     *
     */
    public function getSubjects()
    {
        return $this->sendRequest('getSubjects')->json();
    }

    /**
     *
     *
     */
    public function getTeachers()
    {
        return $this->sendRequest('getTeachers')->json();
    }

    /**
     *
     *
     */
    public function getStudents()
    {
        return $this->sendRequest('getStudents')->json();
    }

    /**
     *
     *
     */
    public function getRooms()
    {
        return $this->sendRequest('getRooms')->json();
    }

    /**
     *
     *
     */
    public function getClasses(int $schoolyearId)
    {
        return $this->sendRequest('getKlassen')->json();
    }
    //   async getClasses(validateSession = true, schoolyearId) {
    //     const data = typeof schoolyearId !== "number" ? {} : { schoolyearId };
    //     return await this._request("getKlassen", data, validateSession);
    //   }

    /**
     *
     *
     */
    public function getDepartments()
    {
        return $this->sendRequest('getDepartments')->json();
    }

    /**
     *
     *
     */
    public function getHolidays()
    {
        return $this->sendRequest('getHolidays')->json();
    }

    /**
     *
     *
     */
    public function getStatusData()
    {
        return $this->sendRequest('getStatusData')->json();
    }


    /**
     *
     *
     */
    public function getTimegrid()
    {
        return $this->sendRequest('getTimegridUnits')->json();
    }

    /**
     *
     */
    public function getOwnTimetableFor(Carbon $date)
    {
        return $this->sendTimetableRequest( $this->personId, $this->personType, $date, $date )->json();
    }

    /**
     *
     */
    public function getOwnTimetableForToday()
    {
        return $this->sendTimetableRequest( $this->personId, $this->personType, null, null )->json();
    }

    /**
     *
     */
    public function getOwnTimetableForRange(Carbon $startDate, Carbon $endDate)
    {
        return $this->sendTimetableRequest( $this->personId, $this->personType, $startDate, $endDate )->json();
    }

    /**
     *
     */
    public function getOwnClassTimetableFor(Carbon $date)
    {
        return $this->sendTimetableRequest( $this->classId, 1, $date, $date )->json();
    }

    /**
     *
     */
    public function getOwnClassTimetableForToday()
    {
        return $this->sendTimetableRequest( $this->classId, 1, null, null )->json();
    }

    /**
     *
     *
     */
    public function getOwnTimetableForWeek(Carbon $date, int $formatId = 1)
    {
        $url = $this->baseUrl.'/WebUntis/api/public/timetable/weekly/data?elementType='.$this->personType.'&elementId='.$this->personId.'&date='.$date->format('Y-m-d').'&formatId=1';

        return Http::withHeaders([
            'Cookie' => $this->getCookie(),
        ])->get( $url )
        ->json();
    }

    /**
     *
     *
     */
    private function sendTimetableRequest(int $id, int $type, Carbon|null $startDate, Carbon|null $endDate): Response
    {
        $fields = ["id", "name", "longname", "externalkey"];

        return $this->sendRequest('getTimetable', [
            'options' => [
                'id' => 1693665594848,
                'element' => [
                    "id" => 1224,
                    "type" => 1
                ],
                'startDate' => $startDate->format('Ymd'),
                'endDate' => $endDate->format('Ymd'),
                'showLsText' => true,
                'showStudentgroup' => true,
                'showLsNumber' => true,
                'showSubstText' => true,
                'showInfo' => true,
                'showBooking' => true,
                'klasseFields' => $fields,
                'roomFields' => $fields,
                'subjectFields' => $fields,
                'teacherFields' => $fields
            ],
        ]);
    }

    /**
     * Helper for sending jsonrpc requests
     *
     */
    private function sendRequest($method, $parameter = null): Response
    {
        return Http::withHeaders([
            'Cookie' => $this->getCookie(),
        ])->post( $this->url, [
            'id' => $this->identity,
            'method' => $method,
            'params' => $parameter,
            'jsonrpc' => '2.0',
        ]);
    }

    /**
     * Is used to check if session is valid
     *
     */
    protected function validateSession()
    {
        return is_numeric($this->getLatestImportTime());
    }

    /**
     * Build cookie data
     *
     */
    private function getCookie()
    {
        $params = [
            'JSESSIONID' => $this->sessionId,
            'schoolname' => $this->school64,
        ];

        return array_map( fn ($k, $v) => "$k=$v;", array_keys($params), array_values($params) );
    }
}
