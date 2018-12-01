<?php

use Illuminate\Database\Migrations\Migration;

class AlterCtgTable5 extends Migration {
    use \App\Traits\Migration;


    private function create_partition($table) {
        $this->statement(
            "
            ALTER TABLE `$table` PARTITION BY RANGE COLUMNS(created_at) (
    
            PARTITION `bygone`
                VALUES LESS THAN ('2018-01-01'),

            PARTITION `18:1-3`
                VALUES LESS THAN ('2018-04-01'),

            PARTITION `18:4-6`
                VALUES LESS THAN ('2018-07-01'),

            PARTITION `18:7-9`
                VALUES LESS THAN ('2018-10-01'),

            PARTITION `18:10-12`
                VALUES LESS THAN ('2019-01-01'),

            PARTITION `19:1-3`
                VALUES LESS THAN ('2019-04-01'),

            PARTITION `19:4-6`
                VALUES LESS THAN ('2019-07-01'),

            PARTITION `19:7-9`
                VALUES LESS THAN ('2019-10-01'),

            PARTITION `19:10-12`
                VALUES LESS THAN ('2020-01-01'),

            PARTITION `20:1-3`
                VALUES LESS THAN ('2020-04-01'),

            PARTITION `20:4-6`
                VALUES LESS THAN ('2020-07-01'),

            PARTITION `20:7-9`
                VALUES LESS THAN ('2020-10-01'),

            PARTITION `20:10-12`
                VALUES LESS THAN ('2021-01-01'),

            PARTITION `21:1-3`
                VALUES LESS THAN ('2021-04-01'),

            PARTITION `21:4-6`
                VALUES LESS THAN ('2021-07-01'),

            PARTITION `21:7-9`
                VALUES LESS THAN ('2021-10-01'),

            PARTITION `21:10-12`
                VALUES LESS THAN ('2022-01-01'),

            PARTITION `22:1-3`
                VALUES LESS THAN ('2022-04-01'),

            PARTITION `22:4-6`
                VALUES LESS THAN ('2022-07-01'),

            PARTITION `22:7-9`
                VALUES LESS THAN ('2022-10-01'),

            PARTITION `22:10-12`
                VALUES LESS THAN ('2023-01-01'),

            PARTITION `23:1-3`
                VALUES LESS THAN ('2023-04-01'),

            PARTITION `23:4-6`
                VALUES LESS THAN ('2023-07-01'),

            PARTITION `23:7-9`
                VALUES LESS THAN ('2023-10-01'),

            PARTITION `23:10-12`
                VALUES LESS THAN ('2024-01-01'),

            PARTITION `24:1-3`
                VALUES LESS THAN ('2024-04-01'),

            PARTITION `24:4-6`
                VALUES LESS THAN ('2024-07-01'),

            PARTITION `24:7-9`
                VALUES LESS THAN ('2024-10-01'),

            PARTITION `24:10-12`
                VALUES LESS THAN ('2025-01-01'),

            PARTITION `25:1-3`
                VALUES LESS THAN ('2025-04-01'),

            PARTITION `25:4-6`
                VALUES LESS THAN ('2025-07-01'),

            PARTITION `25:7-9`
                VALUES LESS THAN ('2025-10-01'),

            PARTITION `25:10-12`
                VALUES LESS THAN ('2026-01-01'),

            PARTITION `future`
                VALUES LESS THAN (MAXVALUE)        
        
            );
            "
        );
    }

    public function up() {

        $this->statement(
            "
            ALTER TABLE `ctg_order`
            MODIFY COLUMN `created_at`  datetime NOT NULL AFTER `ImportToSap`,
            MODIFY COLUMN `updated_at`  datetime NOT NULL AFTER `created_at`;
            "
        );

        $this->statement(
            "
            ALTER TABLE `ctg_order_item`
            MODIFY COLUMN `created_at`  datetime NOT NULL AFTER `CODFeeDiscountCurrencyCode`,
            MODIFY COLUMN `updated_at`  datetime NOT NULL AFTER `created_at`;
            "
        );

        $this->create_partition('ctg');
        $this->create_partition('ctg_order');
        $this->create_partition('ctg_order_item');
    }

    public function down() {
        //
    }
}
