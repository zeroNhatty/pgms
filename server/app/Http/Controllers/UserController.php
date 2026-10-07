<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::withCount([
            // active ticekts
            'assignedTickets as active_tickets_count' => function ($query) {
                $query->where('status', 'assigned');
            },
            // solved tickets
            'assignedTickets as solved_tickets_count' => function ($query) {
                $query->where('status', 'solved');
            },
        ])
    ->with(['assignedTickets' => function ($query) {
        $query->where('status', 'assigned')->with('node:id,longitude,latitude');
    }])
    ->orderBy('role', 'asc')
    ->orderBy('firstname', 'asc')
    ->get();

    return response()->json($users);
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
    public function show(User $user)
    {
       return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
