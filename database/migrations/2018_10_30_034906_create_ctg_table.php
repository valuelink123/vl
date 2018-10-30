<?php

use Illuminate\Database\Migrations\Migration;

class CreateCtgTable extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        $this->statement(
            "
            CREATE TABLE IF NOT EXISTS `ctg` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` char(19) NOT NULL DEFAULT '',
              `product_asin` varchar(55) NOT NULL DEFAULT '',
              `product_sku` varchar(55) NOT NULL DEFAULT '',
              `gift_sku` varchar(55) NOT NULL DEFAULT '',
              `name` varchar(55) NOT NULL DEFAULT '' COMMENT 'customer name',
              `email` varchar(55) NOT NULL DEFAULT '',
              `phone` varchar(55) NOT NULL DEFAULT '',
              `address` varchar(255) NOT NULL DEFAULT '',
              `rating` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评论星级 (1 to 5)',
              `buy_again` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '再次购买意愿 (1 to 10)',
              `comment` varchar(2048) NOT NULL DEFAULT '',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `order_id` (`order_id`),
              KEY `email` (`email`(20))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            "
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropTableIfEmpty('ctg');
    }
}
