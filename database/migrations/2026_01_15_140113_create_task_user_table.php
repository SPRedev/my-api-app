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
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            
            // 1. Foreign key for the Task
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            
            // 2. Foreign key for the User
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 3. (Optional but recommended) Add a unique constraint
            // This prevents the same user from being assigned to the same task twice.
            $table->unique(['task_id', 'user_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};
