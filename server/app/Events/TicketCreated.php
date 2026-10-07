<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Ticket $ticket)
    {
        $this->ticket->load(["node", "assignee"]);
    }

     /**
      * Get the channels the event should broadcast on.
      *
      * @return array<int, Channel>
      */
     public function broadcastOn(): array
     {
         return [new Channel("tickets")];
     }

     public function broadcastAs(): string
     {
         return "ticket.created";
     }
}
