<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\Controller;
use App\Models\DartGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Notifications\DatabaseNotification;


class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = Auth::user()->unreadNotifications()->get(['id', 'type', 'data', 'created_at']);

        foreach( $notifications as $notification) {
                $notification->type = class_basename($notification->type);
        }

        $data['notifications'] = $notifications;

        return $this->sendResponse($data, 'ok');
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
    public function show(DatabaseNotification $notification)
    {
        $notification->type = class_basename($notification->type);

        if(array_key_exists('gameId', $notification->data)) {
            $game = DartGame::where('id', $notification['data']['gameId'])->with('users:name,firstname,lastname,img')->first();
            $notification->game = $game;
        }

        $data['notification'] = $notification;

        return $this->sendResponse($data, 'ok');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return $this->sendResponse(null, 'ok');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
