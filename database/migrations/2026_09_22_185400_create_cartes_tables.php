<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->unique();
            $table->string('nom');
            $table->string('source_filename')->nullable();
            $table->longText('geojson');
            $table->unsignedInteger('traces_count')->default(0);
            $table->timestamps();
        });

        Schema::create('carte_points', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carte_points');
        Schema::dropIfExists('cartes');
    }
};
