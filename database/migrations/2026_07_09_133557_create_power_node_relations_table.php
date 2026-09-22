<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('power_node_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('power_nodes')->cascadeOnDelete();
            $table->foreignId('parent_node_id')->constrained('power_nodes')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_node_relations');
    }
};
