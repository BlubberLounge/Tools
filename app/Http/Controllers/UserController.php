<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Helpers\FileHelper;
use ImageOptimizer;

use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(): View
    {
        $users = User::orderBy('id', 'asc');

        $data['onlineUsers'] = $users->get()->where(fn ($u) => $u->isOnline());
        $data['users'] = $users->paginate(15);

        return view('user.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create(): View
    {
        $data[] = null;

        return view('user.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     */
    public function store(StoreUserRequest $request)
    {
        $u = new User;
        $u->name = $request->name;
        $u->firstname = $request->firstname;
        $u->lastname = $request->lastname;
        $u->email = $request->email;
        $u->password = Hash::make($request->password);

        $u->save();

        return redirect()->route('user.index')
            ->with('success','User has been created successfully');
    }

    /**
     * Display the specified resource.
     *
     */
    public function show(User $user): View
    {
        return view('user.show', $user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $data['user'] = $user;
        return view('user.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $u = User::find($user->id);
        $u->name = $request->name;
        $u->firstname = $request->firstname;
        $u->lastname = $request->lastname;
        $u->email = $request->email;
        $u->password = $request->password ? Hash::make($request->password) : $user->password;

        $storagePathOriginal = storage_path('/app/private/uploads/user/original');
        $storagePathCropped = '/public/uploads/user';

        // Original image may be used later for further cropping
        if($request->has('originalImage')) {
            $originalImage = $request->file('originalImage');
            $originalImageFilename = time().'_'.$u->id.'.' . $originalImage->extension();
            $originalImagepath = $originalImage->move($storagePathOriginal, $originalImageFilename);
        }

        // Cropped image
        if($request->has('croppedImage')) {
            $croppedImagePath = FileHelper::fromBase64($request->croppedImage)->storePublicly($storagePathCropped);
            $croppedImagePath = Str::replace('public', 'storage', $croppedImagePath);
        }

        // optimize the image for web
        ImageOptimizer::optimize($croppedImagePath, $croppedImagePath.'-----');

        $u->save();

        return redirect()->route('user.index')
            ->with('success','User has been updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()
            ->with('success','User has been deleted successfully');
    }

    /**
     * Show the form for editing the image.
     */
    public function editImage(User $user): View
    {
        $data['user'] = $user;
        return view('user.edit-image', $data);
    }

    /**
     *  Update Language setting
     */
    public function languageUpdate(UpdateUserRequest $request)
    {
        $locale = $request->locale;

        app()->setLocale($locale);
        session()->put('locale', $locale);
        Auth::user()->settings->set('language', $locale);

        return redirect()->back()
            ->with('success','Language has been updated successfully');
    }
}
