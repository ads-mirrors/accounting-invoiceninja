<?php
declare(strict_types=1);

use Elastic\Adapter\Indices\Mapping;
use Elastic\Adapter\Indices\Settings;
use Elastic\Migrations\Facades\Index;
use Elastic\Migrations\MigrationInterface;

final class CreateProjectsIndex implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        $mapping = [
            'properties' => [
                // Core project fields
                'id' => ['type' => 'keyword'],
                'name' => [
                    'type' => 'text',
                    'analyzer' => 'standard'
                ],
                'is_deleted' => ['type' => 'boolean'],
                'hashed_id' => ['type' => 'keyword'],
                'number' => ['type' => 'keyword'],
                'description' => [
                    'type' => 'text',
                    'analyzer' => 'standard'
                ],
                'budgeted_hours' => ['type' => 'float'],
                'task_rate' => ['type' => 'float'],
                'due_date' => ['type' => 'date'],
                'start_date' => ['type' => 'date'],
                
                // Custom fields
                'custom_value1' => ['type' => 'keyword'],
                'custom_value2' => ['type' => 'keyword'],
                'custom_value3' => ['type' => 'keyword'],
                'custom_value4' => ['type' => 'keyword'],
                
                // Additional fields
                'company_key' => ['type' => 'keyword'],
                'client_id' => ['type' => 'keyword'],
                'assigned_user_id' => ['type' => 'keyword'],
                'private_notes' => [
                    'type' => 'text',
                    'analyzer' => 'standard'
                ],
                'public_notes' => [
                    'type' => 'text',
                    'analyzer' => 'standard'
                ],
            ]
        ];

        Index::createRaw('projects_v2', $mapping);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Index::dropIfExists('projects_v2');
    }
}
