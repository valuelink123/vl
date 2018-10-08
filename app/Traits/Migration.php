<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.25
 * Time: 9:57
 */

namespace App\Traits;

use Illuminate\Support\Facades\DB;


trait Migration {

    public function statement($sql) {
        // https://laravel.com/docs/5.5/database#running-queries
        DB::statement($sql);
        // DB::unprepared($sql);
    }

    public function isTableExists($table) {
        $arr = DB::select("SHOW TABLES LIKE '$table'");
        return !empty($arr);
    }

    public function isTableEmpty($table) {
        $arr = DB::select("SELECT 1 FROM $table LIMIT 1");
        return empty($arr);
    }

    public function getColumnInfo($table, $column) {
        $info = DB::select("SHOW COLUMNS FROM $table WHERE Field='$column'");
        return empty($info) ? [] : get_object_vars($info[0]);
    }

    public function isColumnEmpty($table, $column) {
        $info = $this->getColumnInfo($table, $column);
        if (empty($info)) return true;
        $default = $info['Default'] ?? '';
        $arr = DB::select("SELECT 1 FROM $table WHERE `$column`!='' AND `$column`!='$default' AND `$column` IS NOT NULL LIMIT 1");
        return empty($arr);
    }

    public function dropTableIfEmpty($table) {

        if (!$this->isTableExists($table)) {
            // warning
            return;
        }

        if (!$this->isTableEmpty($table)) {
            throw new \Exception("Table `$table` is not empty, you may BACKUP and TRUNCATE the table before dropping.");
        }

        $this->statement("DROP TABLE IF EXISTS $table");
    }

    public function dropColumnIfEmpty($table, $column) {
        if (!$this->isTableExists($table)) return;
        if (empty($this->getColumnInfo($table, $column))) return;
        if (!$this->isColumnEmpty($table, $column)) throw new \Exception("You may Backup and Empty Column $table.$column before dropping.");
        $this->statement("ALTER TABLE `$table` DROP COLUMN `$column`");
    }
}
