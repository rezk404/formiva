<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_stats', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('suffix');
            $table->string('label');
            $table->text('note');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_stats');
    }
};
