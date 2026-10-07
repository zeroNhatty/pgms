<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePowerNodesRequest;
use App\Http\Requests\UpdatePowerNodesRequest;
use App\Models\PowerNode;
use App\Models\PowerNodeRelation;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

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
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $nodeIds = [];

            foreach ($data['nodes'] as $node) {
                $powerNode = PowerNode::create([
                    'longitude' => $node['longitude'],
                    'latitude' => $node['latitude'],
                ]);

                $nodeIds[$node['clientId']] = $powerNode->id;
            }

            foreach ($data['relations'] ?? [] as $relation) {
                if (
                    !isset($nodeIds[$relation['from']]) ||
                    !isset($nodeIds[$relation['to']])
                ) {
                    throw new \InvalidArgumentException(
                        'Relation references an unknown node.'
                    );
                }

                PowerNodeRelation::create([
                    'node_id' => $nodeIds[$relation['to']],
                    'parent_node_id' => $nodeIds[$relation['from']],
                ]);
            }

            return [
                'nodes' => PowerNode::whereIn('id', array_values($nodeIds))->get(),
                'relations' => PowerNodeRelation::whereIn(
                    'node_id',
                    array_values($nodeIds)
                )->get(),
            ];
        });

        return response()->json($result, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PowerNode $powerNode)
    {
        return response()->json($powerNode);
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
    public function update(
        UpdatePowerNodesRequest $request,
        PowerNode $powerNode,
    ) {
        $validated = $request->validated();
        $status = $validated["status"];

        if ($status === "being_maintained" || $status === "inactive") {
            $hasActiveTicket = Ticket::where("node_id", $powerNode->id)
                ->active()
                ->exists();

            // only create new ticket if there is no active ticket
            if (! $hasActiveTicket) {
                Ticket::create([
                    "status" => $status === "being_maintained" ? "assigned" : "pending",
                    "node_id" => $powerNode->id,
                    "assignee_id" => null,
                ]);
            }
        }

        // always update node ststua
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
