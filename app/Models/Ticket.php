<?php

namespace App\Models;

use App\Observers\TicketObserver;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use BroadcastsEvents, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["status", "node_id", "assignee_id"];

    public function node()
    {
        return $this->belongsTo(PowerNode::class, "node_id");
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, "assignee_id");
    }
}
