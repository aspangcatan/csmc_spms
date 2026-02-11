<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop old tables if they exist from previous attempt
        Schema::dropIfExists('spcr_logs');
        Schema::dropIfExists('spcr_entries');
        Schema::dropIfExists('spcrs');

        // 1. Create SPCRs table (Main Header)
        Schema::create('spcrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid'); // Creator (usually Division Head)
            $table->unsignedBigInteger('division_id');
            $table->integer('year');
            $table->integer('semester');
            
            // Signatories to match IPCR workflow
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->unsignedBigInteger('division_head_id')->nullable();
            $table->unsignedBigInteger('pmt_id')->nullable(); // Highest supervisor/PMT
            
            $table->string('status')->default('Draft Target');
            
            $table->decimal('final_average_rating', 5, 2)->nullable();
            $table->decimal('final_rating', 5, 2)->nullable();
            $table->string('final_rating_adjective')->nullable();
            
            $table->decimal('core_dist', 5, 2)->default(50);
            $table->decimal('support_dist', 5, 2)->default(10);
            $table->decimal('strategic_dist', 5, 2)->default(40);
            
            $table->timestamps();

            $table->index('division_id');
            $table->index('year');
            $table->index('semester');
        });

        // 2. Create SPCR Entries table
        Schema::create('spcr_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spcr_id')->constrained('spcrs')->onDelete('cascade');
            $table->string('category'); // core, support, strategic
            
            $table->text('output')->nullable(); // Strategic Goals & Objectives
            $table->text('success_indicator')->nullable();
            $table->text('accountability')->nullable();
            $table->text('actual_accomplishment')->nullable();
            $table->text('accomplishment_rate')->nullable();
            
            // Rating columns
            $table->decimal('quantity_rating', 3, 2)->nullable();
            $table->decimal('efficiency_rating', 3, 2)->nullable();
            $table->decimal('timeliness_rating', 3, 2)->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 3. SPCR Logs
        Schema::create('spcr_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spcr_id')->constrained('spcrs')->onDelete('cascade');
            $table->string('subject');
            $table->text('content')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spcr_logs');
        Schema::dropIfExists('spcr_entries');
        Schema::dropIfExists('spcrs');
    }
};
