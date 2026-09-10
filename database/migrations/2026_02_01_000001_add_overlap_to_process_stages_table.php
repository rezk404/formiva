<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A process stage can deliberately run alongside the one before it — design
 * and build overlap by eight weeks, and that overlap is the studio's actual
 * argument about how it works. resources/content/process.php has always
 * carried the flag; the table did not, so a stage edited in the CMS lost it
 * on the way to the public page. Additive and defaulted, so existing rows
 * keep their current (non-overlapping) meaning.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('process_stages', function (Blueprint $table) {
            $table->boolean('overlap')->default(false)->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('process_stages', function (Blueprint $table) {
            $table->dropColumn('overlap');
        });
    }
};
