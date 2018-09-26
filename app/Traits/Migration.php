<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.25
 * Time: 9:57
 */

namespace App\Traits;

use Illuminate\Support\Facades\DB;


trait Migration {

    public static function statement($sql) {
        // https://laravel.com/docs/5.5/database#running-queries
        DB::statement($sql);
    }

    public static function isTableExists($table) {
        $arr = DB::select("SHOW TABLES LIKE '$table'");
        return !empty($arr);
    }

    public static function isTableEmpty($table) {
        $arr = DB::select("SELECT 1 FROM $table LIMIT 1");
        return empty($arr);
    }

    public static function getColumnInfo($table, $column) {
        $info = DB::select("SHOW COLUMNS FROM $table WHERE Field='$column'");
        return empty($info) ? [] : get_object_vars($info[0]);
    }

    public static function isColumnEmpty($table, $column) {
        $info = self::getColumnInfo($table, $column);
        if (empty($info)) return true;
        $default = $info['Default'] ?? '';
        $arr = DB::select("SELECT 1 FROM $table WHERE `$column`!='' AND `$column`!='$default' AND `$column` IS NOT NULL LIMIT 1");
        return empty($arr);
    }

    public static function dropTableIfEmpty($table) {

        if (!self::isTableExists($table)) {
            // warning
            return;
        }

        if (!self::isTableEmpty($table)) {
            throw new \Exception("Table `$table` is not empty, you may BACKUP and TRUNCATE the table before dropping.");
        }

        self::statement("DROP TABLE IF EXISTS $table");
    }

    public static function dropColumnIfEmpty($table, $column) {
        if (!self::isTableExists($table)) return;
        if (empty(self::getColumnInfo($table, $column))) return;
        if (!self::isColumnEmpty($table, $column)) throw new \Exception("You may Backup and Empty Column $table.$column before dropping.");
        self::statement("ALTER TABLE `$table` DROP COLUMN `$column`");
    }
}
