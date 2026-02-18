<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ipcrs', function (Blueprint $table) {
            if (!Schema::hasColumn('ipcrs', 'pmt_id')) {
                $table->unsignedBigInteger('pmt_id')->nullable()->after('highest_supervisor');
            }
        });

        Schema::table('spcrs', function (Blueprint $table) {
            if (!Schema::hasColumn('spcrs', 'highest_supervisor')) {
                $table->unsignedBigInteger('highest_supervisor')->nullable()->default(35)->after('division_head_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ipcrs', function (Blueprint $table) {
            if (Schema::hasColumn('ipcrs', 'pmt_id')) {
                $table->dropColumn('pmt_id');
            }
        });

        Schema::table('spcrs', function (Blueprint $table) {
            if (Schema::hasColumn('spcrs', 'highest_supervisor')) {
                $table->dropColumn('highest_supervisor');
            }
        });
    }
};

