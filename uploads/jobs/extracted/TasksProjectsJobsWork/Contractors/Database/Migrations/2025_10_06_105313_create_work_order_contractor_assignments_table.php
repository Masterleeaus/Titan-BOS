<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_order_contractor_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_id');
            $table->unsignedBigInteger('contractor_id');
            $table->string('role')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('assigned'); // assigned | accepted | in_progress | completed | cancelled
            $table->timestamps();

            $table->index(['work_order_id','contractor_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('work_order_contractor_assignments');
    }
};
