<?php

namespace App\Observers;

use App\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        if ($ticket -> wasChanged('status')) {
            $this->syncNodeStatus($ticket);
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }


    protected function syncNodeStatus(Ticket $ticket): void
    {
        $node = $ticket->node; // Uses the belongsTo(PowerNode::class) relationship
        if (!$node) {
            return;
        }

        switch ($ticket->status) {
            case 'assigned':
                $node->update(['status' => 'being_maintained']);
                break;

            case 'solved':
                $node->update(['status' => 'active']);
                break;

            case 'pending':
                $node->update(['status' => 'inactive']);
                break;
        }
    }
}
