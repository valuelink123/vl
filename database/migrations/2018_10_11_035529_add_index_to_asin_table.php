<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexToAsinTable extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement('ALTER TABLE `asin` ADD INDEX `item_no` (`item_no`(10))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
