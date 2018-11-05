<?php

use Illuminate\Database\Migrations\Migration;

class AlterCtgTable2 extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement(
            "
            ALTER TABLE `ctg`
            ADD COLUMN `commented`  tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '客户是否已留评' AFTER `id`,
            ADD COLUMN `processor`  int UNSIGNED NOT NULL DEFAULT 0 COMMENT 'users.id 处理人、负责人' AFTER `commented`,
            ADD COLUMN `status`  varchar(15) NOT NULL DEFAULT '' COMMENT '状态' AFTER `processor`,
            ADD COLUMN `steps`  text NOT NULL COMMENT '分步处理数据' AFTER `status`;
            "
        );
        $this->dropColumnIfExists('ctg', 'product_asin');
        $this->dropColumnIfExists('ctg', 'product_sku');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down() {
        $this->dropColumnIfEmpty('ctg', 'commented');
        $this->dropColumnIfEmpty('ctg', 'processor');
        $this->dropColumnIfEmpty('ctg', 'status');
        $this->dropColumnIfEmpty('ctg', 'steps');
    }
}
