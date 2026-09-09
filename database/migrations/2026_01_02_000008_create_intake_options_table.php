<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_options', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 32);
            $table->string('group')->nullable();
            $table->string('value');
            $table->string('label');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kind', 'value']);
            $table->index(['kind', 'is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_options');
    }
};
