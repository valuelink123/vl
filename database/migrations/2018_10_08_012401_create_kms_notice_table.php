<?php

use Illuminate\Database\Migrations\Migration;

class CreateKmsNoticeTable extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        $this->dropTableIfEmpty('kms_notice');

        $sql = <<<'SQL'
CREATE TABLE `kms_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_group` (`item_group`),
  KEY `item_model` (`item_model`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='KMS 公告中心';
SQL;

        $this->statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropTableIfEmpty('kms_notice');
    }
}
