<?php

use Illuminate\Database\Migrations\Migration;

class CreateKmsVideoTable extends Migration {
    use App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        $this->dropTableIfEmpty('kms_video');

        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `kms_video` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `type` enum('Others','Marketing Video','Buyers Video','Operation Instruction') NOT NULL DEFAULT 'Others',
  `descr` varchar(512) NOT NULL,
  `link` varchar(512) NOT NULL,
  `note` varchar(512) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` varchar(25) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_group` (`item_group`(10)) USING BTREE,
  KEY `item_model` (`item_model`(10)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
        $this->statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropTableIfEmpty('kms_video');
    }
}
