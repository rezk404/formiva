<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('index_label');
            $table->string('name');
            $table->string('title');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedSmallInteger('year');
            $table->text('statement');
            $table->text('description');
            $table->text('challenge');
            $table->text('solution');
            $table->text('outcome');
            $table->string('result_value');
            $table->string('result_label');
            $table->json('disciplines')->nullable();
            $table->json('stack')->nullable();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->unsignedInteger('plate_seed');
            $table->string('plate_variant');
            $table->string('plate_ratio');
            $table->string('alt');
            $table->boolean('is_featured')->default(false);
            $table->string('status', 32)->default('draft');
            $table->string('stage', 32)->default('scoping');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'position']);
            $table->index('client_id');
            $table->index('category_id');
            $table->index('stage');
            $table->index('created_by');
            $table->index('cover_media_id');

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
