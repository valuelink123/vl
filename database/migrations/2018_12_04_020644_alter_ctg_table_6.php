<?php

use Illuminate\Database\Migrations\Migration;

class AlterCtgTable6 extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement('ALTER TABLE `ctg_order_item` ADD INDEX (`MarketPlaceSite`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropIndex('ctg_order_item', 'MarketPlaceSite');
    }
}
