<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // One row per PMT member who approved an IPCR/SPCR in the PMT phase.
    public function up(): void
    {
        if (!Schema::hasTable('pmt_approvals')) {
            Schema::create('pmt_approvals', function (Blueprint $table) {
                $table->id();
                $table->string('record_type', 10); // ipcr | spcr
                $table->unsignedBigInteger('record_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->unique(['record_type', 'record_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pmt_approvals');
    }
};
