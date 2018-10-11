<?php

use Illuminate\Database\Migrations\Migration;

class AlterKmsTable extends Migration {
    use \App\Traits\Migration;

    private function deleteDuplicatedRows($table, $field) {
        // 为了添加唯一索引，需要删除重复的行
        // 目前为止写过的最复杂的 DELETE 语句
        // DELETE table FROM，简单的 DELETE 不用指定表名
        // JOIN 多个表以构造 WHERE，此时 DELETE 后面需要指定删哪个表
        $this->statement("
DELETE t1
FROM $table t1
INNER JOIN (
	SELECT max(id) AS maxid,$field FROM $table GROUP BY $field HAVING COUNT($field) > 1
) t2
USING($field)
WHERE id < maxid
");
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // 此处事务不起作用？
        // DB::beginTransaction();
        $this->upOrDown(function () {
            $this->statement("ALTER TABLE `kms_user_manual` ADD COLUMN `link_hash` char(32) NOT NULL COMMENT 'md5 去重' AFTER `link`");
            $this->statement('UPDATE `kms_user_manual` SET `link_hash`=MD5(`link`)');
            $this->deleteDuplicatedRows('kms_user_manual', 'link_hash');
            $this->statement('ALTER TABLE `kms_user_manual` ADD UNIQUE INDEX `link_hash` (`link_hash`)');

            $this->statement("ALTER TABLE `kms_video` ADD COLUMN `link_hash` char(32) NOT NULL COMMENT 'md5 去重' AFTER `link`");
            $this->statement('UPDATE `kms_video` SET `link_hash`=MD5(`link`)');
            $this->deleteDuplicatedRows('kms_video', 'link_hash');
            $this->statement('ALTER TABLE `kms_video` ADD UNIQUE INDEX `link_hash` (`link_hash`)');
        });
        // DB::rollback();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropColumnIfExists('kms_user_manual', 'link_hash');
        $this->dropColumnIfExists('kms_video', 'link_hash');
    }
}
