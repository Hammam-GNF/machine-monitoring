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
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();

            $table->uuid('event_id')->unique();

            $table->foreignId('machine_id')
                ->constrained('machines')
                ->noActionOnDelete();

            $table->foreignId('sensor_id')
                ->constrained('sensors')
                ->noActionOnDelete();

            $table->string('status', 3);
            $table->decimal('temperature', 6, 2)->nullable();
            $table->integer('output');
            $table->dateTime('recorded_at');
            $table->dateTime('received_at');

            $table->timestamp('created_at')->nullable();

            $table->index(['machine_id', 'recorded_at']);
            $table->index(['sensor_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
