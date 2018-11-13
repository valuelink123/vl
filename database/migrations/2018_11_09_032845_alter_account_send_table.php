<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAccountSendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		DB::unprepared("ALTER TABLE `sendbox`
ADD COLUMN `plan_date`  int(10) UNSIGNED ZEROFILL NULL DEFAULT 0 AFTER `error_count`;
ALTER TABLE `accounts`
ADD COLUMN `signature`  text NULL AFTER `type`;");
		

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
