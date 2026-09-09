<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_boots(): void
    {
        $this->assertNotNull($this->app);
        $this->assertSame('FORMIVA', config('app.name'));
    }

    public function test_database_configuration_is_valid(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('utf8mb4', config('database.connections.mysql.charset'));
        $this->assertSame('utf8mb4_unicode_ci', config('database.connections.mysql.collation'));
        $this->assertTrue(config('database.connections.mysql.strict'));
        $this->assertTrue(config('database.connections.sqlite.foreign_key_constraints'));
    }

    public function test_migrations_run_successfully(): void
    {
        $this->assertTrue(Schema::hasTable('migrations'));
    }

    public function test_users_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('users'));

        foreach ([
            'id',
            'name',
            'email',
            'password',
            'role',
            'avatar_media_id',
            'is_active',
            'last_login_at',
            'remember_token',
            'created_at',
            'updated_at',
            'deleted_at',
        ] as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Expected users.{$column} to exist."
            );
        }
    }

    public function test_user_role_uses_enum(): void
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@formiva.test',
        ]);

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertSame('admin', $user->role->value);
    }

    public function test_core_infrastructure_tables_exist(): void
    {
        foreach ([
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} table to exist.");
        }
    }

    public function test_formiva_configuration_loads_with_static_default(): void
    {
        $this->assertSame('static', config('formiva.content_source'));
        $this->assertFalse(config('formiva.content_cache.enabled'));
        $this->assertSame(3600, config('formiva.content_cache.ttl'));
    }
}
