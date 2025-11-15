<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            // Campos para suportar pause/resume sem alterar a enum existente
            $table->boolean('is_paused')->default(false)->after('status');
            $table->integer('remaining_seconds')->nullable()->after('is_paused');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pomodoro_sessions', function (Blueprint $table) {
            $table->dropColumn(['is_paused', 'remaining_seconds']);
        });
    }
};
