<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'avatar_url' => 'https://ui-avatars.com/api/?name=John+Doe',
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'avatar_url' => 'https://ui-avatars.com/api/?name=Jane+Smith',
        ]);

        // Create demo board
        $board = Board::create([
            'name' => 'Project Alpha',
            'slug' => 'project-alpha-' . uniqid(),
            'description' => 'Our main product development board',
            'created_by' => $user1->id,
        ]);

        // Add users to board
        $board->users()->attach([
            $user1->id => ['role' => 'owner'],
            $user2->id => ['role' => 'editor'],
        ]);

        // Create columns
        $columns = [
            ['name' => 'To Do', 'color' => '#ef4444'],
            ['name' => 'In Progress', 'color' => '#f59e0b'],
            ['name' => 'In Review', 'color' => '#3b82f6'],
            ['name' => 'Done', 'color' => '#10b981'],
        ];

        foreach ($columns as $index => $columnData) {
            $column = Column::create([
                'board_id' => $board->id,
                'name' => $columnData['name'],
                'color' => $columnData['color'],
                'order' => $index + 1,
            ]);

            // Add sample tasks
            $taskTitles = [
                'To Do' => [
                    'Design landing page',
                    'Setup API documentation',
                    'Create database schema',
                ],
                'In Progress' => [
                    'Implement authentication',
                    'Build dashboard UI',
                ],
                'In Review' => [
                    'Code review: Task deletion',
                ],
                'Done' => [
                    'Setup project repository',
                    'Create development environment',
                ],
            ];

            foreach ($taskTitles[$columnData['name']] ?? [] as $taskIndex => $title) {
                Task::create([
                    'column_id' => $column->id,
                    'title' => $title,
                    'description' => 'This is a sample task for demonstration purposes.',
                    'priority' => ['low', 'medium', 'high', 'urgent'][$taskIndex % 4],
                    'assigned_to' => $taskIndex % 2 === 0 ? $user1->id : $user2->id,
                    'order' => $taskIndex + 1,
                ]);
            }
        }
    }
}
