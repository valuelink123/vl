<?php

use Illuminate\Database\Migrations\Migration;

class CreateKmsUserManualTable extends Migration {
    use App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `kms_user_manual` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `link` varchar(512) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_group` (`item_group`(10)) USING BTREE,
  KEY `item_model` (`item_model`(10)) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;
        self::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        self::dropTableIfEmpty('kms_user_manual');
    }
}
