<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('index_label');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('dek');
            $table->longText('body');
            $table->unsignedTinyInteger('reading_minutes');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedInteger('plate_seed');
            $table->string('plate_variant');
            $table->string('plate_ratio');
            $table->string('alt');
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->string('status', 32)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('category_id');
            $table->index('author_id');
            $table->index('cover_media_id');

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
