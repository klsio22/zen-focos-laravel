<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\PomodoroSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        $user = User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@zenfocos.com',
            'password' => Hash::make('senha123'),
        ]);

        // Criar tasks de exemplo
        $tasks = [
            [
                'title' => 'Estudar Laravel',
                'description' => 'Completar os módulos 4 a 8 do curso de Laravel',
                'status' => 'in_progress',
                'estimated_pomodoros' => 6,
                'completed_pomodoros' => 3,
            ],
            [
                'title' => 'Desenvolver API REST',
                'description' => 'Criar endpoints para o sistema de gerenciamento',
                'status' => 'pending',
                'estimated_pomodoros' => 4,
                'completed_pomodoros' => 0,
            ],
            [
                'title' => 'Documentar Projeto',
                'description' => 'Escrever documentação técnica e guia de uso',
                'status' => 'pending',
                'estimated_pomodoros' => 2,
                'completed_pomodoros' => 0,
            ],
            [
                'title' => 'Implementar Testes',
                'description' => 'Criar testes unitários e de integração',
                'status' => 'pending',
                'estimated_pomodoros' => 5,
                'completed_pomodoros' => 0,
            ],
            [
                'title' => 'Setup do Ambiente',
                'description' => 'Configurar ambiente de desenvolvimento',
                'status' => 'completed',
                'estimated_pomodoros' => 2,
                'completed_pomodoros' => 2,
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create(array_merge($taskData, ['user_id' => $user->id]));

            // Criar algumas sessões pomodoro completadas para tasks em progresso ou completas
            if ($task->completed_pomodoros > 0) {
                for ($i = 0; $i < $task->completed_pomodoros; $i++) {
                    PomodoroSession::create([
                        'user_id' => $user->id,
                        'task_id' => $task->id,
                        'duration' => 25,
                        'start_time' => now()->subDays(rand(1, 7))->subMinutes(25),
                        'end_time' => now()->subDays(rand(1, 7)),
                        'status' => 'completed',
                    ]);
                }
            }
        }

        $this->command->info('✅ Database seeded com sucesso!');
        $this->command->info('📧 Email: teste@zenfocos.com');
        $this->command->info('🔑 Senha: senha123');
    }
}
