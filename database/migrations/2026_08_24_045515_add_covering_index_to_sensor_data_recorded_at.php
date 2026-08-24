<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlsrv') {
            DB::statement('
                CREATE NONCLUSTERED INDEX sensor_data_recorded_at_output_index
                ON sensor_data (recorded_at)
                INCLUDE (output)
            ');

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('
                CREATE INDEX sensor_data_recorded_at_output_index
                ON sensor_data (recorded_at)
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlsrv') {
            DB::statement('
                DROP INDEX sensor_data_recorded_at_output_index
                ON sensor_data
            ');

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('
                DROP INDEX sensor_data_recorded_at_output_index
            ');
        }
    }
};
