<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->string('title');
            $table->string('form');
            $table->text('summary');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'position']);
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
