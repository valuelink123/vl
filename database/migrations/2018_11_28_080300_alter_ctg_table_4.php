<?php

use Illuminate\Database\Migrations\Migration;

class AlterCtgTable4 extends Migration {
    use \App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $this->statement('ALTER TABLE `ctg` DROP PRIMARY KEY;');
        $this->statement('ALTER TABLE `ctg` ADD INDEX `order_id` (`order_id`) ;');
        $this->statement(
            '
            ALTER TABLE `ctg` PARTITION BY RANGE (TO_DAYS(created_at))(
                PARTITION `past`
            VALUES
                LESS THAN (TO_DAYS("2018-01-01")),
                PARTITION `18:1-3`
            VALUES
                LESS THAN (TO_DAYS("2018-04-01")),
                PARTITION `18:4-6`
            VALUES
                LESS THAN (TO_DAYS("2018-07-01")),
                PARTITION `18:7-9`
            VALUES
                LESS THAN (TO_DAYS("2018-10-01")),
                PARTITION `18:10-12`
            VALUES
                LESS THAN (TO_DAYS("2019-01-01")),
                PARTITION `19:1-3`
            VALUES
                LESS THAN (TO_DAYS("2019-04-01")),
                PARTITION `19:4-6`
            VALUES
                LESS THAN (TO_DAYS("2019-07-01")),
                PARTITION `19:7-9`
            VALUES
                LESS THAN (TO_DAYS("2019-10-01")),
                PARTITION `19:10-12`
            VALUES
                LESS THAN (TO_DAYS("2020-01-01")),
                PARTITION `20:1-3`
            VALUES
                LESS THAN (TO_DAYS("2020-04-01")),
                PARTITION `20:4-6`
            VALUES
                LESS THAN (TO_DAYS("2020-07-01")),
                PARTITION `20:7-9`
            VALUES
                LESS THAN (TO_DAYS("2020-10-01")),
                PARTITION `20:10-12`
            VALUES
                LESS THAN (TO_DAYS("2021-01-01")),
                PARTITION `future`
            VALUES
                LESS THAN (MAXVALUE)
            );
            '
        );
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
