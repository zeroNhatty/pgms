<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = Ticket::with(['node', 'assignee'])->latest();

        // by ticket status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // by nide condition
        if ($request->filled('node_status') && $request->node_status !== 'all') {
            $query->whereHas('node', function ($q) use ($request) {
                $q->where('status', $request->node_status);
            });
        }

        // 8 element per page
        $perPage = $request->integer('per_page', 8);

        return response()->json($query->paginate($perPage));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "status" => "required|in:solved,pending,assigned",
            "node_id" => "required|exists:power_nodes,id",
            "assignee_id" => "nullable|exists:users,id",
        ]);

        $activeTicketExists = Ticket::where("node_id", $request->node_id)
            ->active()
            ->exists();

        if ($activeTicketExists) {
            return response()->json(
                ["error" => "Active ticket already exists for this node"],
                422,
            );
        }

        $ticket = Ticket::create($validated);
        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            "assignee_id" => "required|exists:users,id",
            "status" => "required|in:solved,pending,assigned",
        ]);

        $ticket->update($validated);

        return response()->json($ticket->load(["node", "assignee"]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
