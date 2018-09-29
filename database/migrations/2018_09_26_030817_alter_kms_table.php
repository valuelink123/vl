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
        $this->dropColumnIfEmpty('kms_video', 'brand');
        $this->statement('ALTER TABLE `kms_video` ADD COLUMN `brand` varchar(50) NOT NULL AFTER `id`');
        $this->statement('ALTER TABLE `kms_video` ADD INDEX `brand` (`brand`(10)) USING BTREE');

        $this->dropColumnIfEmpty('kms_user_manual', 'brand');
        $this->dropColumnIfEmpty('kms_user_manual', 'note');
        $this->statement('ALTER TABLE `kms_user_manual` ADD COLUMN `brand` varchar(50) NOT NULL AFTER `id`');
        $this->statement('ALTER TABLE `kms_user_manual` ADD INDEX `brand` (`brand`(10)) USING BTREE');
        $this->statement('ALTER TABLE `kms_user_manual` ADD COLUMN `note` varchar(512) NOT NULL DEFAULT "" AFTER `status`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $this->dropColumnIfEmpty('kms_video', 'brand');
        $this->dropColumnIfEmpty('kms_user_manual', 'brand');
        $this->dropColumnIfEmpty('kms_user_manual', 'note');
    }
}
