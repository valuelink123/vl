<?php

use Illuminate\Database\Migrations\Migration;

class AddIndexToTable extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement('ALTER TABLE `fbm_stock` ADD UNIQUE INDEX `item_code` (`item_code`)');
        $this->statement('ALTER TABLE `asin` ADD INDEX `brand` (`brand`(10)), ADD INDEX `item_group` (`item_group`(10)), ADD INDEX `item_model` (`item_model`(10))');
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
