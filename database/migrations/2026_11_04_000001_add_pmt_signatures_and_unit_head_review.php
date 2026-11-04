<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each PMT member signs when approving; the signature (PNG data URL) prints in the CSMC PMT box.
        Schema::table('pmt_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('pmt_approvals', 'signature')) {
                $table->mediumText('signature')->nullable()->after('user_id');
            }
        });

        // Non-blocking "Reviewed by Unit Head" mark on staff IPCRs.
        Schema::table('ipcrs', function (Blueprint $table) {
            if (!Schema::hasColumn('ipcrs', 'unit_head_reviewed_by')) {
                $table->unsignedBigInteger('unit_head_reviewed_by')->nullable()->after('pmt_id');
            }
            if (!Schema::hasColumn('ipcrs', 'unit_head_reviewed_at')) {
                $table->timestamp('unit_head_reviewed_at')->nullable()->after('unit_head_reviewed_by');
            }
            if (!Schema::hasColumn('ipcrs', 'unit_head_review_remarks')) {
                $table->text('unit_head_review_remarks')->nullable()->after('unit_head_reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pmt_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('pmt_approvals', 'signature')) {
                $table->dropColumn('signature');
            }
        });

        Schema::table('ipcrs', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['unit_head_reviewed_by', 'unit_head_reviewed_at', 'unit_head_review_remarks'],
                fn ($column) => Schema::hasColumn('ipcrs', $column)
            ));
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
