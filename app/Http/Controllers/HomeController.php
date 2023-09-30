<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
