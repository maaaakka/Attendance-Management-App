<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('correction_request_attendances')->cascadeOnDelete();
            $table->foreignId('break_id')->constrained('break_times')->cascadeOnDelete();
            $table->dateTime('break_start');
            $table->dateTime('break_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correction_request_breaks');
    }
}
