<?php

use Illuminate\Database\Migrations\Migration;

class AlterKmsTable extends Migration {
    use App\Traits\Migration;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        self::dropColumnIfEmpty('kms_video', 'brand');
        self::statement('ALTER TABLE `kms_video` ADD COLUMN `brand` varchar(50) NOT NULL AFTER `id`');
        self::statement('ALTER TABLE `kms_video` ADD INDEX `brand` (`brand`(10)) USING BTREE');

        self::dropColumnIfEmpty('kms_user_manual', 'brand');
        self::dropColumnIfEmpty('kms_user_manual', 'note');
        self::statement('ALTER TABLE `kms_user_manual` ADD COLUMN `brand` varchar(50) NOT NULL AFTER `id`');
        self::statement('ALTER TABLE `kms_user_manual` ADD INDEX `brand` (`brand`(10)) USING BTREE');
        self::statement('ALTER TABLE `kms_user_manual` ADD COLUMN `note` varchar(512) NOT NULL DEFAULT "" AFTER `status`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        self::dropColumnIfEmpty('kms_video', 'brand');
        self::dropColumnIfEmpty('kms_user_manual', 'brand');
        self::dropColumnIfEmpty('kms_user_manual', 'note');
    }
}
