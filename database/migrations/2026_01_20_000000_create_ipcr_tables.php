<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create IPCRs table
        Schema::create('ipcrs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->integer('year');
            $table->integer('semester');
            $table->unsignedBigInteger('section_head')->nullable();
            $table->unsignedBigInteger('division_head')->nullable();
            $table->unsignedBigInteger('highest_supervisor')->nullable();
            $table->date('period_from');
            $table->date('period_to');
            $table->date('ipcr_date')->nullable();
            $table->date('date_done')->nullable();
            $table->text('comments')->nullable();
            $table->string('status')->default('Draft Target');
            
            $table->decimal('core_percentage_distribution', 5, 2)->default(50);
            $table->decimal('support_percentage_distribution', 5, 2)->default(10);
            $table->decimal('strategic_percentage_distribution', 5, 2)->default(40);
            
            $table->decimal('final_average_rating', 5, 2)->nullable();
            $table->decimal('final_rating', 5, 2)->nullable();
            $table->string('final_rating_adjective')->nullable();
            
            $table->timestamps();
        });

        // 2. Helper for function tables
        $createFunctionTable = function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipcr_id')->constrained('ipcrs')->onDelete('cascade');
            $table->text('output')->nullable();
            $table->text('success_indicator')->nullable();
            $table->text('actual_accomplishment')->nullable();
            $table->decimal('quantity_rating', 3, 2)->nullable();
            $table->decimal('efficiency_rating', 3, 2)->nullable();
            $table->decimal('timeliness_rating', 3, 2)->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        };

        Schema::create('ipcr_core_functions', $createFunctionTable);
        Schema::create('ipcr_support_functions', $createFunctionTable);
        Schema::create('ipcr_strategic_functions', $createFunctionTable);

        // 3. IPCR Logs
        Schema::create('ipcr_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ipcr_id')->constrained('ipcrs')->onDelete('cascade');
            $table->string('subject');
            $table->text('content')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ipcr_logs');
        Schema::dropIfExists('ipcr_strategic_functions');
        Schema::dropIfExists('ipcr_support_functions');
        Schema::dropIfExists('ipcr_core_functions');
        Schema::dropIfExists('ipcrs');
    }
};
