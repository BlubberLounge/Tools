<?php

namespace App\Http\Controllers;

use App\Models\Cocktail;
use Illuminate\Http\Request;

class CocktailController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Cocktail::class, 'cocktail');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['cocktails'] = Cocktail::all();
        return view('cocktail.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Cocktail $cocktail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cocktail $cocktail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cocktail $cocktail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cocktail $cocktail)
    {
        //
    }
}
