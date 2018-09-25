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

    public static function dropIfEmpty($table) {

        $arr = DB::select("SHOW TABLES LIKE '$table'");

        if (empty($arr)) {
            // warning
            return;
        }

        $arr = DB::select("SELECT 1 FROM $table LIMIT 1");
        if (!empty($arr)) {
            throw new \Exception("Table `$table` is not empty, you may BACKUP and TRUNCATE the table before dropping.");
        }

        DB::statement("DROP TABLE IF EXISTS $table");
    }
}