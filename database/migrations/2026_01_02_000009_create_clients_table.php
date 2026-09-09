<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('wordmark');
            $table->string('sector');
            $table->string('country');
            $table->string('website')->nullable();
            $table->unsignedBigInteger('logo_media_id')->nullable();
            $table->string('status', 32)->default('prospect');
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('source_inquiry_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_featured');
            $table->index('logo_media_id');
            $table->index('source_inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
