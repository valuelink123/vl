<?php

use Illuminate\Database\Migrations\Migration;

class AlterCtgTable3 extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement("ALTER TABLE `ctg` MODIFY COLUMN `status` varchar(25) NOT NULL DEFAULT '' COMMENT '状态' AFTER `processor`");
        $this->statement("ALTER TABLE `ctg` DROP COLUMN `id`, DROP PRIMARY KEY, ADD PRIMARY KEY (`order_id`), DROP INDEX `order_id`");
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
