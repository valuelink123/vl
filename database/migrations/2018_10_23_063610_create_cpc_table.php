<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCpcTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       DB::unprepared("CREATE TABLE if not exists `aws_report` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `bg` varchar(255) DEFAULT '',
		  `bu` varchar(255) DEFAULT '',
		  `seller_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `marketplace_id` varchar(50) NOT NULL,
		  `campaign_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `ad_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `cost` float NOT NULL DEFAULT '0',
		  `sales` float NOT NULL DEFAULT '0',
		  `profit` float NOT NULL DEFAULT '0',
		  `orders` int(11) NOT NULL DEFAULT '0',
		  `acos` decimal(5,4) NOT NULL DEFAULT '0.0000',
		  `impressions` int(11) NOT NULL DEFAULT '0',
		  `clicks` int(11) NOT NULL DEFAULT '0',
		  `ctr` decimal(5,4) NOT NULL DEFAULT '0.0000',
		  `cpc` float NOT NULL DEFAULT '0',
		  `ad_conversion_rate` decimal(5,4) NOT NULL DEFAULT '0.0000',
		  `default_bid` float NOT NULL DEFAULT '0',
		  `date` date NOT NULL,
		  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		  `created_at` datetime DEFAULT NULL,
		  `updated_at` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
		CREATE TABLE if not exists `aws_report_time` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `seller_id` varchar(50) NOT NULL,
		  `marketplace_id` varchar(50) NOT NULL,
		  `date` date NOT NULL,
		  `created_at` datetime NOT NULL,
		  `updated_at` datetime NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `seller_marketplace` (`seller_id`,`marketplace_id`) USING BTREE
		) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
		");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aws_report_time');
		Schema::dropIfExists('aws_report');
    }
}
