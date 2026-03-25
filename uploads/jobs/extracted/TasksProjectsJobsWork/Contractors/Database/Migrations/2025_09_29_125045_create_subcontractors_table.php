<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('contractors')) {
            Schema::create('contractors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('company')->nullable();
                $table->string('abn')->nullable();
                $table->enum('rate_type', ['hourly','fixed'])->default('hourly');
                $table->decimal('rate_amount', 10, 2)->default(0);
                $table->string('status')->default('active');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
