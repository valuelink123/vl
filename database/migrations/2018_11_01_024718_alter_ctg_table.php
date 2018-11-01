<?php

use Illuminate\Database\Migrations\Migration;

class AlterCtgTable extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement('ALTER TABLE `ctg` DROP INDEX `order_id`, ADD UNIQUE INDEX `order_id` (`order_id`)');
        $this->statement('ALTER TABLE `ctg` DROP COLUMN `buy_again`, DROP COLUMN `comment`, ADD COLUMN `note` text NOT NULL AFTER `rating`');
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
