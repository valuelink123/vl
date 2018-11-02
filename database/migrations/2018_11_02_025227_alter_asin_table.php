<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAsinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		DB::unprepared("ALTER TABLE `asin`
			ADD COLUMN `sap_seller_id`  varchar(50) NULL AFTER `item_group`,
			ADD COLUMN `sap_site_id`  varchar(50) NULL AFTER `sap_seller_id`,
			ADD COLUMN `sap_store_id`  varchar(50) NULL AFTER `sap_site_id`,
			ADD COLUMN `sap_warehouse_id`  varchar(50) NULL AFTER `sap_store_id`,
			ADD COLUMN `sap_factory_id`  varchar(50) NULL AFTER `sap_warehouse_id`,
			ADD COLUMN `sap_shipment_id`  varchar(50) NULL AFTER `sap_factory_id`;
		");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
