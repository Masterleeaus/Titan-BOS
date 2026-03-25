<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('contractor_tags')) {
            Schema::create('contractor_tags', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('contractor_id');
                $t->string('tag', 64);
                $t->timestamps();
                $t->index(['contractor_id','tag']);
            });
        }
        if (!Schema::hasTable('contractor_notes')) {
            Schema::create('contractor_notes', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('contractor_id');
                $t->unsignedBigInteger('user_id')->nullable();
                $t->text('note');
                $t->timestamps();
                $t->index(['contractor_id']);
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('contractor_tags');
        Schema::dropIfExists('contractor_notes');
    }
};
