<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['ipcr_core_functions', 'ipcr_support_functions', 'ipcr_strategic_functions'];
        
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'average_rating')) {
                    $table->decimal('average_rating', 5, 2)->nullable()->after('timeliness_rating');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = ['ipcr_core_functions', 'ipcr_support_functions', 'ipcr_strategic_functions'];
        
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('average_rating');
            });
        }
    }
};
