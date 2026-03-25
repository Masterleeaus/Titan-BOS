<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('contractor_activity_logs')) {
            Schema::create('contractor_activity_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('contractor_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('contractor_activity_logs');
    }
};
