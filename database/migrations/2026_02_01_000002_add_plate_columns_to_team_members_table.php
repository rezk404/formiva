<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portraits on /studio are drawn plates, not photographs — the same
 * generated artwork projects, insights and case-study beats already store
 * as plate_seed / plate_variant / plate_ratio, plus the alt text that
 * describes it. team_members was the one content table without them, which
 * meant the CMS could not control how a person appears on the public site
 * and the alt text had to be invented at render time. Nullable throughout:
 * a row that leaves them empty still renders, falling back to a seed
 * derived deterministically from the slug.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->unsignedInteger('plate_seed')->nullable()->after('photo_media_id');
            $table->string('plate_variant')->nullable()->after('plate_seed');
            $table->string('plate_ratio')->nullable()->after('plate_variant');
            $table->string('alt')->nullable()->after('plate_ratio');
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['plate_seed', 'plate_variant', 'plate_ratio', 'alt']);
        });
    }
};
