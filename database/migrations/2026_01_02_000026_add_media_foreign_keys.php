<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('avatar_media_id');
            $table->foreign('avatar_media_id')->references('id')->on('media')->nullOnDelete();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete();
        });

        Schema::table('insights', function (Blueprint $table) {
            $table->foreign('cover_media_id')->references('id')->on('media')->nullOnDelete();
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->foreign('photo_media_id')->references('id')->on('media')->nullOnDelete();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('logo_media_id')->references('id')->on('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['logo_media_id']);
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropForeign(['photo_media_id']);
        });

        Schema::table('insights', function (Blueprint $table) {
            $table->dropForeign(['cover_media_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['cover_media_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_media_id']);
            $table->dropIndex(['avatar_media_id']);
        });
    }
};
