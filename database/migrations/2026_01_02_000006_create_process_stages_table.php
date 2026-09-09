<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_stages', function (Blueprint $table) {
            $table->id();
            $table->string('index_label');
            $table->string('title');
            $table->string('window');
            $table->text('body');
            $table->text('output');
            $table->smallInteger('span_start');
            $table->smallInteger('span_end');
            $table->decimal('weight', 3, 2);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_stages');
    }
};
