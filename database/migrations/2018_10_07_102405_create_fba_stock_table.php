<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFbaStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
		ALTER TABLE `fbm_stock`
			MODIFY COLUMN `cost`  decimal(10,2) UNSIGNED NULL DEFAULT 0 AFTER `item_code`,
			MODIFY COLUMN `fbm_stock`  int(10) UNSIGNED NULL DEFAULT 0 AFTER `cost`,
			MODIFY COLUMN `fbm_amount`  decimal(10,2) UNSIGNED NULL DEFAULT 0 AFTER `fbm_stock`;
		ALTER TABLE `qa`
			ADD COLUMN `etype`  varchar(50) NULL AFTER `confirm`,
			ADD COLUMN `epoint`  varchar(200) NULL AFTER `etype`;
		CREATE TABLE `fba_stock` (
			`seller_id`  int NOT NULL ,
			`seller_name`  varchar(50) NULL ,
			`asin`  varchar(50) NOT NULL ,
			`seller_sku`  varchar(50) NOT NULL ,
			`item_code`  varchar(50) NULL ,
			`fba_stock`  int(10) NULL ,
			`fba_transfer`  int(10) NULL ,
			`updated_at`  varchar(50) NULL ,
		PRIMARY KEY (`seller_id`, `asin`, `seller_sku`),
		INDEX (`item_code`) USING BTREE 
		)
		;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fba_stock');
    }
}
