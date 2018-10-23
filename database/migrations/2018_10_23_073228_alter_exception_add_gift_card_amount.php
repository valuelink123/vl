<?php

use Illuminate\Database\Migrations\Migration;

class AlterExceptionAddGiftCardAmount extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement("
            ALTER TABLE `exception`
            ADD COLUMN `gift_card_amount` decimal(10,2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '礼品卡金额' AFTER `refund`;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
