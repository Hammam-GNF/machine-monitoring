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
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')
                ->constrained('machines')
                ->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('type', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['machine_id', 'code']);
            $table->index('machine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};
