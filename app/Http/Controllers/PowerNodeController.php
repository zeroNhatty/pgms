<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePowerNodesRequest;
use App\Http\Requests\UpdatePowerNodesRequest;
use App\Models\PowerNode;
use App\Models\PowerNodeRelation;

class PowerNodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PowerNode::all();
    }

    /**
     * Returns Node Relationship
     */

    public function nodeRelations()
    {
        return PowerNodeRelation::all();
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
    public function show(PowerNode $powerNodes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PowerNode $powerNodes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePowerNodesRequest $request, PowerNode $powerNode)
    {
        $validated = $request->validated();

        $powerNode->update($validated);

        return response()->json($powerNode);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PowerNode $powerNodes)
    {
        //
    }
}
