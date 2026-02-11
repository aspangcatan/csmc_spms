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
        Schema::table('ipcrs', function (Blueprint $table) {
            $table->unsignedBigInteger('supervisor_id')->nullable()->after('userid');
            $table->date('date_done')->nullable()->after('ipcr_date');
            $table->decimal('final_average_rating', 5, 2)->nullable();
            $table->decimal('final_rating', 5, 2)->nullable();
            $table->string('final_rating_adjective')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ipcrs', function (Blueprint $table) {
            $table->dropColumn(['supervisor_id', 'date_done', 'final_average_rating', 'final_rating', 'final_rating_adjective']);
        });
    }
};
