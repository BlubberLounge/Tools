<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use App\Models\Feedback;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\UpdateFeedbackRequest;
use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;


class FeedbackController extends Controller
{

    /**
     *
     */
    public array $areas = [
        'unkown' => 'Other',
        'registration' => 'Registration',
        'login' => 'Login',
        'home' => 'Home',
        'home.timetable' => 'Home > Timetable',
        'home.acquaintances' => 'Home > Acquaintances',
        'tickets' => 'Tickets',
        'events' => 'Events',
        'calendar' => 'Calendar',
        'profile' => 'Profile',
        'profile.account' => 'Profile > Account',
        'profile.settings' => 'Profile > Settings',
        'statistics' => 'Statistics',
        'feedback' => 'Feedback',
        // 'ui' => 'User Interface (design)',
        // 'translation' => 'Translation',
        'api' => 'REST API',
        'api.doc' => 'API Documentation',
    ];

    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Feedback::class, 'feedback');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['feedbackList'] = Feedback::orderBy('created_at', 'desc')->paginate(15);
        return view('feedback.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        // $exclude = [
        //     'sanctum',
        //     'ignition',
        //     'api',
        //     'docs',
        //     'password',
        //     'email',
        //     'register'
        // ];
        // Generate a option list based on alll registered routes
        // foreach(Route::getRoutes() as $route)
        // {
        //     if($route->methods()[0] != 'GET')
        //         continue;

        //     if(is_null($route->getName()))
        //         continue;

        //     // Exclusion list
        //     $path = $route->uri();
        //     $name = $route->getName();
        //     $skip = false;
        //     foreach($exclude as $e)
        //     {
        //         foreach(Str::of($path)->explode('/') as $pathPart)
        //             if(Str::contains($pathPart, $e)) {
        //                 $skip = true;
        //                 break;
        //             }

        //         if($skip)
        //             break;
        //     }

        //     if($skip)
        //         continue;

        //     $data['options'][$route->getName()] = $route->uri();
        // }

        $data['options'] = $this->areas;

        return view('feedback.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeedbackRequest $request)
    {
        $f = new Feedback;
        $f->type = FeedbackType::fromString($request->type);
        $f->status = FeedbackStatus::NEW;
        $f->user_id = Auth::user()->id;
        $f->subject = $request->subject;
        $f->message = $request->message;
        $f->area = $request->area;
        $f->device_id = Auth::user()->currentDevice()->id;

        $f->save();

        return redirect()->route('home')
            ->with('success','Your Feedback has been send successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeedbackRequest $request, Feedback $feedback)
    {
        $feedback->status = FeedbackStatus::fromString($request->status);
        $feedback->save();

        return;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        //
    }
}
