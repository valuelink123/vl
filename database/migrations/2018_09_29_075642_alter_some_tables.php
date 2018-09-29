<?php

use Illuminate\Database\Migrations\Migration;

class AlterSomeTables extends Migration {
    use App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // 解决新用户注册报错的问题
        $this->statement('ALTER TABLE `users` MODIFY COLUMN `id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST');

        // 处理不规范的数据
        $this->statement("
UPDATE asin SET
item_group=TRIM(IFNULL(item_group, '')),
item_model=TRIM(IFNULL(item_model, '')),
brand=TRIM(IFNULL(brand, ''))
");
        // todo 禁止字段 NULL
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->statement('ALTER TABLE `users` MODIFY COLUMN `id`  int(10) UNSIGNED NOT NULL FIRST');
    }
}
