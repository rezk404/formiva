<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role');
            $table->text('bio');
            $table->unsignedSmallInteger('since_year');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('photo_media_id')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('photo_media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
