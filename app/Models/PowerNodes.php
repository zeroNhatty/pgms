<?php

namespace App\Models;

use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['location', 'status'])]
class PowerNodes extends Model
{
    /** @use HasFactory<\Database\Factories\PowerNodesFactory> */
    use HasFactory;

}
