<?php

namespace App\Models;

use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['location', 'status'])]
class PowerNodes extends Model
{
    /** @use HasFactory<\Database\Factories\PowerNodesFactory> */
    use HasFactory;

    public function parents(): HasOne
    {
        return $this->hasOne(PowerNodes::class, 'id', 'parent_node_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(PowerNodes::class, 'power_nodes', 'parent_node_id', 'child_id');
    }

}
