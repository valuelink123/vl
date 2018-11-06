<?php

use Illuminate\Database\Migrations\Migration;

class AlterTableCtg3 extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // text 类型不能设置默认值，只好允许 NULL，否则写入出麻烦
        $this->statement("ALTER TABLE `ctg` MODIFY COLUMN `steps` text NULL COMMENT '分步处理数据' AFTER `status`");
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
