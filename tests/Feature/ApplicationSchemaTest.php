<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ApplicationSchemaTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const APPLICATION_TABLES = [
        'categories',
        'services',
        'service_items',
        'projects',
        'project_service',
        'case_studies',
        'case_study_beats',
        'case_study_metrics',
        'insights',
        'testimonials',
        'team_members',
        'studio_positions',
        'studio_stats',
        'process_stages',
        'settings',
        'intake_options',
        'inquiries',
        'inquiry_notes',
        'clients',
        'client_contacts',
        'media_folders',
        'media',
        'mediables',
        'activity_logs',
    ];

    /** @var list<string> */
    private const SOFT_DELETE_TABLES = [
        'users',
        'services',
        'projects',
        'case_studies',
        'insights',
        'testimonials',
        'team_members',
        'inquiries',
        'clients',
        'media',
    ];

    /** @var list<string> */
    private const HARD_DELETE_TABLES = [
        'categories',
        'service_items',
        'case_study_beats',
        'case_study_metrics',
        'inquiry_notes',
        'client_contacts',
        'studio_positions',
        'studio_stats',
        'process_stages',
        'settings',
        'intake_options',
        'media_folders',
        'mediables',
        'activity_logs',
        'project_service',
    ];

    public function test_all_application_tables_exist(): void
    {
        foreach (self::APPLICATION_TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} to exist.");
        }
    }

    public function test_content_source_remains_static(): void
    {
        $this->assertSame('static', config('formiva.content_source'));
    }

    public function test_soft_deletes_exist_only_where_intended(): void
    {
        foreach (self::SOFT_DELETE_TABLES as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'deleted_at'), "Expected {$table}.deleted_at.");
        }

        foreach (self::HARD_DELETE_TABLES as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'deleted_at'), "Did not expect {$table}.deleted_at.");
        }
    }

    public function test_activity_logs_are_append_only(): void
    {
        $this->assertTrue(Schema::hasColumn('activity_logs', 'created_at'));
        $this->assertFalse(Schema::hasColumn('activity_logs', 'updated_at'));
    }

    public function test_required_columns_exist(): void
    {
        $this->assertColumns('categories', ['id', 'type', 'name', 'slug', 'position', 'created_at', 'updated_at']);
        $this->assertColumns('services', ['slug', 'index_label', 'title', 'form', 'lede', 'why', 'outcome', 'note', 'position', 'status', 'published_at', 'meta_title', 'meta_description']);
        $this->assertColumns('service_items', ['service_id', 'title', 'form', 'summary', 'position']);
        $this->assertColumns('projects', ['slug', 'index_label', 'name', 'title', 'category_id', 'client_id', 'year', 'statement', 'description', 'challenge', 'solution', 'outcome', 'result_value', 'result_label', 'disciplines', 'stack', 'cover_media_id', 'plate_seed', 'plate_variant', 'plate_ratio', 'alt', 'is_featured', 'status', 'stage', 'started_at', 'completed_at', 'published_at', 'position', 'meta_title', 'meta_description', 'created_by']);
        $this->assertColumns('project_service', ['project_id', 'service_id']);
        $this->assertColumns('case_studies', ['project_id', 'eyebrow', 'title', 'summary', 'client_name', 'duration', 'team_size', 'role', 'status', 'published_at']);
        $this->assertColumns('case_study_beats', ['case_study_id', 'index_label', 'label', 'heading', 'body', 'plate_seed', 'plate_variant', 'plate_ratio', 'alt', 'position']);
        $this->assertColumns('case_study_metrics', ['case_study_id', 'value', 'label', 'note', 'position']);
        $this->assertColumns('insights', ['slug', 'index_label', 'category_id', 'title', 'dek', 'body', 'reading_minutes', 'author_id', 'plate_seed', 'plate_variant', 'plate_ratio', 'alt', 'cover_media_id', 'status', 'published_at']);
        $this->assertColumns('testimonials', ['quote', 'author_name', 'author_role', 'company', 'client_id', 'project_id', 'is_published', 'position']);
        $this->assertColumns('team_members', ['name', 'slug', 'role', 'bio', 'since_year', 'email', 'photo_media_id', 'position', 'is_published']);
        $this->assertColumns('studio_positions', ['index_label', 'title', 'body', 'position']);
        $this->assertColumns('studio_stats', ['value', 'suffix', 'label', 'note', 'position']);
        $this->assertColumns('process_stages', ['index_label', 'title', 'window', 'body', 'output', 'span_start', 'span_end', 'weight', 'position']);
        $this->assertColumns('settings', ['group', 'key', 'value', 'type', 'is_public']);
        $this->assertColumns('intake_options', ['kind', 'group', 'value', 'label', 'position', 'is_active']);
        $this->assertColumns('inquiries', ['reference', 'kind', 'name', 'company', 'email', 'phone', 'country', 'project_type', 'project_group', 'industry', 'company_size', 'problem', 'services', 'scope', 'budget_range', 'timeline', 'message', 'notes', 'status', 'priority', 'assigned_to', 'client_id', 'project_id', 'reviewed_at', 'qualified_at', 'converted_at', 'declined_reason', 'source', 'utm', 'ip_address', 'user_agent']);
        $this->assertColumns('inquiry_notes', ['inquiry_id', 'user_id', 'body', 'is_pinned']);
        $this->assertColumns('clients', ['name', 'slug', 'wordmark', 'sector', 'country', 'website', 'logo_media_id', 'status', 'is_featured', 'source_inquiry_id', 'notes']);
        $this->assertColumns('client_contacts', ['client_id', 'name', 'email', 'phone', 'role', 'is_primary']);
        $this->assertColumns('media_folders', ['name', 'slug', 'parent_id']);
        $this->assertColumns('media', ['disk', 'path', 'filename', 'original_name', 'mime_type', 'extension', 'size', 'width', 'height', 'alt', 'caption', 'folder_id', 'uploaded_by', 'checksum']);
        $this->assertColumns('mediables', ['media_id', 'mediable_type', 'mediable_id', 'collection', 'position']);
        $this->assertColumns('activity_logs', ['user_id', 'subject_type', 'subject_id', 'event', 'description', 'properties', 'ip_address', 'user_agent', 'created_at']);
    }

    public function test_case_studies_are_not_featured_via_column(): void
    {
        $this->assertFalse(Schema::hasColumn('case_studies', 'is_featured'));
    }

    public function test_foreign_keys_and_delete_behaviors(): void
    {
        $this->assertForeignKey('service_items', 'service_id', 'services', 'cascade');
        $this->assertForeignKey('projects', 'category_id', 'categories', 'set null');
        $this->assertForeignKey('projects', 'client_id', 'clients', 'set null');
        $this->assertForeignKey('projects', 'created_by', 'users', 'set null');
        $this->assertForeignKey('projects', 'cover_media_id', 'media', 'set null');
        $this->assertForeignKey('project_service', 'project_id', 'projects', 'cascade');
        $this->assertForeignKey('project_service', 'service_id', 'services', 'cascade');
        $this->assertForeignKey('case_studies', 'project_id', 'projects', 'cascade');
        $this->assertForeignKey('case_study_beats', 'case_study_id', 'case_studies', 'cascade');
        $this->assertForeignKey('case_study_metrics', 'case_study_id', 'case_studies', 'cascade');
        $this->assertForeignKey('insights', 'category_id', 'categories', 'set null');
        $this->assertForeignKey('insights', 'author_id', 'users', 'set null');
        $this->assertForeignKey('insights', 'cover_media_id', 'media', 'set null');
        $this->assertForeignKey('testimonials', 'client_id', 'clients', 'set null');
        $this->assertForeignKey('testimonials', 'project_id', 'projects', 'set null');
        $this->assertForeignKey('team_members', 'photo_media_id', 'media', 'set null');
        $this->assertForeignKey('inquiries', 'assigned_to', 'users', 'set null');
        $this->assertForeignKey('inquiries', 'client_id', 'clients', 'set null');
        $this->assertForeignKey('inquiries', 'project_id', 'projects', 'set null');
        $this->assertForeignKey('inquiry_notes', 'inquiry_id', 'inquiries', 'cascade');
        $this->assertForeignKey('inquiry_notes', 'user_id', 'users', 'restrict');
        $this->assertForeignKey('clients', 'source_inquiry_id', 'inquiries', 'set null');
        $this->assertForeignKey('clients', 'logo_media_id', 'media', 'set null');
        $this->assertForeignKey('client_contacts', 'client_id', 'clients', 'cascade');
        $this->assertForeignKey('media_folders', 'parent_id', 'media_folders', 'set null');
        $this->assertForeignKey('media', 'folder_id', 'media_folders', 'set null');
        $this->assertForeignKey('media', 'uploaded_by', 'users', 'set null');
        $this->assertForeignKey('mediables', 'media_id', 'media', 'cascade');
        $this->assertForeignKey('activity_logs', 'user_id', 'users', 'set null');
        $this->assertForeignKey('users', 'avatar_media_id', 'media', 'set null');
    }

    public function test_unique_constraints_exist(): void
    {
        $this->assertIndexCovers('categories', ['type', 'slug'], unique: true);
        $this->assertIndexCovers('services', ['slug'], unique: true);
        $this->assertIndexCovers('projects', ['slug'], unique: true);
        $this->assertIndexCovers('case_studies', ['project_id'], unique: true);
        $this->assertIndexCovers('insights', ['slug'], unique: true);
        $this->assertIndexCovers('clients', ['slug'], unique: true);
        $this->assertIndexCovers('team_members', ['slug'], unique: true);
        $this->assertIndexCovers('media_folders', ['slug'], unique: true);
        $this->assertIndexCovers('settings', ['group', 'key'], unique: true);
        $this->assertIndexCovers('intake_options', ['kind', 'value'], unique: true);
        $this->assertIndexCovers('inquiries', ['reference'], unique: true);
    }

    public function test_required_indexes_exist(): void
    {
        $this->assertIndexCovers('categories', ['type', 'position']);
        $this->assertIndexCovers('services', ['status', 'position']);
        $this->assertIndexCovers('service_items', ['service_id', 'position']);
        $this->assertIndexCovers('projects', ['status', 'published_at']);
        $this->assertIndexCovers('projects', ['is_featured', 'position']);
        $this->assertIndexCovers('projects', ['stage']);
        $this->assertIndexCovers('case_study_beats', ['case_study_id', 'position']);
        $this->assertIndexCovers('case_study_metrics', ['case_study_id', 'position']);
        $this->assertIndexCovers('insights', ['status', 'published_at']);
        $this->assertIndexCovers('testimonials', ['is_published', 'position']);
        $this->assertIndexCovers('settings', ['is_public']);
        $this->assertIndexCovers('intake_options', ['kind', 'is_active', 'position']);
        $this->assertIndexCovers('inquiries', ['status', 'created_at']);
        $this->assertIndexCovers('inquiries', ['email']);
        $this->assertIndexCovers('inquiries', ['assigned_to', 'status']);
        $this->assertIndexCovers('inquiries', ['project_group']);
        $this->assertIndexCovers('inquiry_notes', ['inquiry_id', 'created_at']);
        $this->assertIndexCovers('clients', ['status']);
        $this->assertIndexCovers('clients', ['is_featured']);
        $this->assertIndexCovers('client_contacts', ['client_id', 'is_primary']);
        $this->assertIndexCovers('client_contacts', ['email']);
        $this->assertIndexCovers('media', ['checksum']);
        $this->assertIndexCovers('mediables', ['mediable_type', 'mediable_id', 'collection', 'position']);
        $this->assertIndexCovers('activity_logs', ['subject_type', 'subject_id']);
        $this->assertIndexCovers('activity_logs', ['user_id', 'created_at']);
        $this->assertIndexCovers('activity_logs', ['event', 'created_at']);
    }

    public function test_nullable_relationships_are_nullable(): void
    {
        $schema = Schema::getConnection()->getSchemaBuilder();

        $this->assertTrue($this->columnIsNullable($schema, 'projects', 'category_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'projects', 'client_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'projects', 'created_by'));
        $this->assertTrue($this->columnIsNullable($schema, 'projects', 'cover_media_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'insights', 'category_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'insights', 'author_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'testimonials', 'client_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'testimonials', 'project_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'inquiries', 'assigned_to'));
        $this->assertTrue($this->columnIsNullable($schema, 'inquiries', 'client_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'inquiries', 'project_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'clients', 'logo_media_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'clients', 'source_inquiry_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'media', 'folder_id'));
        $this->assertTrue($this->columnIsNullable($schema, 'media', 'uploaded_by'));
        $this->assertTrue($this->columnIsNullable($schema, 'activity_logs', 'user_id'));
        $this->assertFalse($this->columnIsNullable($schema, 'inquiries', 'reference'));
        $this->assertFalse($this->columnIsNullable($schema, 'projects', 'slug'));
    }

    public function test_no_duplicate_non_primary_indexes(): void
    {
        foreach (array_merge(['users'], self::APPLICATION_TABLES) as $table) {
            $seen = [];

            foreach (Schema::getIndexes($table) as $index) {
                if ($index['primary'] ?? false) {
                    continue;
                }

                $key = implode(',', $index['columns']);

                $this->assertArrayNotHasKey(
                    $key,
                    $seen,
                    "Duplicate index on {$table} covering ({$key})."
                );

                $seen[$key] = $index['name'];
            }
        }
    }

    /** @param list<string> $columns */
    private function assertColumns(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "Expected {$table}.{$column}.");
        }
    }

    private function assertForeignKey(string $table, string $column, string $foreignTable, string $onDelete): void
    {
        $matches = array_values(array_filter(
            Schema::getForeignKeys($table),
            fn (array $foreign): bool => in_array($column, $foreign['columns'], true)
        ));

        $this->assertNotEmpty($matches, "Expected FK {$table}.{$column} → {$foreignTable}.");

        $foreign = $matches[0];
        $this->assertSame($foreignTable, $foreign['foreign_table']);
        $this->assertContains($onDelete, $this->normalizedDeleteActions($foreign['on_delete'] ?? ''), "Unexpected ON DELETE for {$table}.{$column}.");
    }

    /** @return list<string> */
    private function normalizedDeleteActions(string $action): array
    {
        $normalized = strtolower(str_replace(['_', ' '], '', $action));

        return match ($normalized) {
            'setnull', 'null' => ['set null'],
            'cascade' => ['cascade'],
            'restrict', 'noaction', '' => ['restrict', 'no action'],
            default => [$action],
        };
    }

    /** @param list<string> $columns */
    private function assertIndexCovers(string $table, array $columns, bool $unique = false): void
    {
        foreach (Schema::getIndexes($table) as $index) {
            if ($index['columns'] !== $columns) {
                continue;
            }

            if ($unique && ! ($index['unique'] ?? false)) {
                continue;
            }

            $this->assertTrue(true);

            return;
        }

        $this->fail(
            sprintf(
                'Expected %s index on %s covering (%s).',
                $unique ? 'unique' : '',
                $table,
                implode(', ', $columns)
            )
        );
    }

    private function columnIsNullable(Builder $schema, string $table, string $column): bool
    {
        foreach ($schema->getColumns($table) as $definition) {
            if ($definition['name'] === $column) {
                return (bool) $definition['nullable'];
            }
        }

        $this->fail("Missing column {$table}.{$column}.");
    }
}
