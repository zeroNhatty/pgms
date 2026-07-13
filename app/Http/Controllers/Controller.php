<?php

namespace App\Http\Controllers;

use App\Models\PowerNodeRelation;

abstract class Controller
{
    /*
     * Returns Node Relationship
     * */

    public function nodeRelations(){
        return PowerNodeRelation::all();
    }
}
