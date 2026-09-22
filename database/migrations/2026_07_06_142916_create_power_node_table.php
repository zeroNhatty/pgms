<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("power_nodes", function (Blueprint $table) {
            $table->id();

            $table->decimal("longitude", 10, 7);
            $table->decimal("latitude", 10, 7);
            /*
             * active =>  actively pinging node
             * inactive => a node that stopped pinging
             * being_maintained => a node that is being maintained and doesn't require a pinging
             * */
            $table
                ->enum("status", ["active", "inactive", "being_maintained"])
                ->default("active");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("power_nodes");
    }
};
