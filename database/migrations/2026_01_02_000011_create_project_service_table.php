<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_service', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('service_id');

            $table->primary(['project_id', 'service_id']);
            $table->index('service_id');

            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_service');
    }
};
