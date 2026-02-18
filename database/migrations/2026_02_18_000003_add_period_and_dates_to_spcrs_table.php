<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spcrs', function (Blueprint $table) {
            if (!Schema::hasColumn('spcrs', 'period_from')) {
                $table->date('period_from')->nullable()->after('semester');
            }
            if (!Schema::hasColumn('spcrs', 'period_to')) {
                $table->date('period_to')->nullable()->after('period_from');
            }
            if (!Schema::hasColumn('spcrs', 'spcr_date')) {
                $table->date('spcr_date')->nullable()->after('period_to');
            }
            if (!Schema::hasColumn('spcrs', 'date_done')) {
                $table->date('date_done')->nullable()->after('spcr_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('spcrs', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('spcrs', 'period_from')) $drop[] = 'period_from';
            if (Schema::hasColumn('spcrs', 'period_to')) $drop[] = 'period_to';
            if (Schema::hasColumn('spcrs', 'spcr_date')) $drop[] = 'spcr_date';
            if (Schema::hasColumn('spcrs', 'date_done')) $drop[] = 'date_done';
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};

