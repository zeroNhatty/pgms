<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['longitude','latitude', 'status'])]
class PowerNode extends Model
{
    /** @use HasFactory<\Database\Factories\PowerNodeFactory> */
    use HasFactory;

    /**
     * Get the parent nodes for this node.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            PowerNode::class,
            'power_node_relations',
            'node_id',
            'parent_node_id'
        );
    }

    /**
     * Get the child nodes for this node.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            PowerNode::class,
            'power_node_relations',
            'parent_node_id',
            'node_id'
        );
    }

    public function node()
    {
        return $this->belongsTo(PowerNode::class, 'node_id');
    }

    protected $casts = [
            'longitude' => 'float',
            'latitude' => 'float',
    ];
}
