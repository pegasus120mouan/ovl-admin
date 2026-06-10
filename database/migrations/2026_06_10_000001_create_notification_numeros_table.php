<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_numeros')) {
            return;
        }

        Schema::create('notification_numeros', function (Blueprint $table) {
            $table->id();
            $table->string('telephone', 20)->unique();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_numeros');
    }
};
