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
        return view('home.index');
    }

    public function ShowMovingAverage()
    {
        return view('home.moving-average');
    }
}
