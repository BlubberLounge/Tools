<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Notifications\DartGameStarted;


class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Auth::user()->notify(new DartGameStarted(Auth::user()));
        $data['dartGames'] = Auth::user()->dartGameInvites()->get();
        $data['activeDartGames'] = Auth::user()->activeDartGames()->get();
        $data['activeDartGame'] = Auth::user()->activeDartGame();

        return view('home.index', $data);
    }

    public function ShowMovingAverage()
    {
        return view('home.moving-average');
    }

    // todo for later.
    // a microservice needs to be written to make BlubberLounges' spotfiy data available for this application.
    // public function callback(Request $request)
    // {
    //     $resp = Http::withBasicAuth('client_id', 'secret')->asForm()->post('https://accounts.spotify.com/api/token', [
    //         'grant_type' => 'client_credentials',
    //     ]);

    //     $bearer = $resp->json('access_token');

    //     $r = Http::withToken($bearer)->withQueryParameters([
    //         'market' => 'DE'
    //     ])->get('https://api.spotify.com/v1/me/player/currently-playing');

    //     $token = request()->get('access_token');
    // }
}
