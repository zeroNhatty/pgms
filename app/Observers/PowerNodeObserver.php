<?php

namespace App\Observers;

use App\Models\PowerNode;
use App\Events\PowerNodeUpdated;

class PowerNodeObserver
{
    /**
     * Handle the PowerNode "created" event.
     */
    public function created(PowerNode $powerNode): void
    {
        //
    }

    /**
     * Handle the PowerNode "updated" event.
     */
    public function updated(PowerNode $powerNode): void
    {
        event(new PowerNodeUpdated($powerNode));
    }

    /**
     * Handle the PowerNode "deleted" event.
     */
    public function deleted(PowerNode $powerNode): void
    {
        //
    }

    /**
     * Handle the PowerNode "restored" event.
     */
    public function restored(PowerNode $powerNode): void
    {
        //
    }

    /**
     * Handle the PowerNode "force deleted" event.
     */
    public function forceDeleted(PowerNode $powerNode): void
    {
        //
    }
}
