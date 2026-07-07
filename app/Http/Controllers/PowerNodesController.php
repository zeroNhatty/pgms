<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePowerNodesRequest;
use App\Http\Requests\UpdatePowerNodesRequest;
use App\Models\PowerNodes;

class PowerNodesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PowerNodes::all();
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
    public function store(StorePowerNodesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PowerNodes $powerNodes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PowerNodes $powerNodes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePowerNodesRequest $request, PowerNodes $powerNodes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PowerNodes $powerNodes)
    {
        //
    }
}
