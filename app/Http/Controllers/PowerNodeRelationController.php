<?php

namespace App\Http\Controllers;

use App\Models\PowerNodeRelation;
use Illuminate\Http\Request;

class PowerNodeRelationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PowerNodeRelation::all();
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
    public function show(PowerNodeRelation $powerNodeRelation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PowerNodeRelation $powerNodeRelation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PowerNodeRelation $powerNodeRelation)
    {
        //
    }
}
