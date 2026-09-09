<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 24)->unique();
            $table->string('kind', 32);
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('project_type')->nullable();
            $table->string('project_group')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();
            $table->text('problem')->nullable();
            $table->json('services')->nullable();
            $table->text('scope')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('new');
            $table->string('priority', 32)->default('normal');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->text('declined_reason')->nullable();
            $table->string('source')->nullable();
            $table->json('utm')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index('email');
            $table->index(['assigned_to', 'status']);
            $table->index('project_group');
            $table->index('client_id');
            $table->index('project_id');

            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
