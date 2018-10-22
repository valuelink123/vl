<?php

use Illuminate\Database\Migrations\Migration;

class AlterKmsTable3 extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        $this->statements("
            ALTER TABLE `kms_notice`
            ADD COLUMN `brand` varchar(50) NOT NULL AFTER `content`,
            ADD COLUMN `tags`  varchar(2048) NOT NULL DEFAULT '' AFTER `item_model`,
            DROP INDEX `item_group` ,
            ADD INDEX `item_group` (`item_group`(10)) USING BTREE ,
            DROP INDEX `item_model` ,
            ADD INDEX `item_model` (`item_model`(10)) USING BTREE ,
            ADD FULLTEXT INDEX `tags` (`tags`),
            ADD INDEX `brand` (`brand`(10)) USING BTREE;
        ");

        $this->statements("
            ALTER TABLE `kms_learn`
            ADD COLUMN `brand` varchar(50) NOT NULL AFTER `content`,
            ADD COLUMN `tags`  varchar(2048) NOT NULL DEFAULT '' AFTER `item_model`,
            DROP INDEX `item_group` ,
            ADD INDEX `item_group` (`item_group`(10)) USING BTREE ,
            DROP INDEX `item_model` ,
            ADD INDEX `item_model` (`item_model`(10)) USING BTREE ,
            ADD FULLTEXT INDEX `tags` (`tags`),
            ADD INDEX `brand` (`brand`(10)) USING BTREE;
        ");

        $this->statement("
          CREATE TABLE `kms_tag` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `type` varchar(10) NOT NULL,
              `tag` varchar(50) NOT NULL DEFAULT '',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `tag` (`type`,`tag`) USING BTREE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropTableIfEmpty('kms_tag');
        $this->dropColumnIfEmpty('kms_notice', 'brand');
        $this->dropColumnIfEmpty('kms_learn', 'brand');
        $this->dropColumnIfEmpty('kms_notice', 'tags');
        $this->dropColumnIfEmpty('kms_learn', 'tags');
        $this->dropIndex('kms_notice', 'brand');
        $this->dropIndex('kms_learn', 'brand');
    }
}
