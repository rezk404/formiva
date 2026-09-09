<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediables', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id');
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('collection');
            $table->unsignedInteger('position')->default(0);

            $table->primary(['media_id', 'mediable_type', 'mediable_id', 'collection']);
            $table->index(['mediable_type', 'mediable_id', 'collection', 'position']);

            $table->foreign('media_id')->references('id')->on('media')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
    }
};
