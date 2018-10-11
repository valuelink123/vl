<?php
/**
 * Created by PhpStorm.
 * User: liaozt
 * Date: 18.10.8
 * Time: 12:45
 */

namespace Tests\Kms;


use Tests\TestCase;

use App\Models\KmsUserManual;
use Illuminate\Support\Facades\DB;


class LaravelUsingTest extends TestCase {

    public function testQueryLog() {
        // DB::enableQueryLog();
        // KmsUserManual::updateOrCreate(['link_hash' => 'sg64a'], ['brand' => '', 'item_group' => '', 'item_model' => '', 'link' => '', 'note' => '']);
        // $query = DB::getQueryLog();
        // print_r($query);
        // 看到 Laravel 底层使用大量的 SELECT *
        $this->assertTrue(true);
    }

}
