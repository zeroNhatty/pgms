<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['node_id', 'parent_node_id'])]
class PowerNodeRelation extends Model
{
    /** @use HasFactory<\Database\Factories\PowerNodeRelationFactory> */
    use HasFactory;
}
