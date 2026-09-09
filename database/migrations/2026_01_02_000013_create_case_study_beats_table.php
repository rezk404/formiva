<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_study_beats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_study_id');
            $table->string('index_label');
            $table->string('label');
            $table->string('heading');
            $table->text('body');
            $table->unsignedInteger('plate_seed');
            $table->string('plate_variant');
            $table->string('plate_ratio');
            $table->string('alt');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['case_study_id', 'position']);
            $table->foreign('case_study_id')->references('id')->on('case_studies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_study_beats');
    }
};
