<?php
/**
 * Created by PhpStorm.
 * User: liaozt
 * Date: 18.10.8
 * Time: 12:45
 */

namespace Tests\Kms;


use Tests\TestCase;

use App\Models\KmsNotice;
use App\Asin;


class FakeDataGenerateTest extends TestCase {

    public function testMock() {
        // $this->createKmsNoticeFakeData();
        $this->assertTrue(true);
    }

    public function createKmsNoticeFakeData() {

        $rows = Asin::limit(50)->get();

        foreach ($rows as $row) {

            $notice['item_group'] = $row->item_group;
            $notice['item_model'] = $row->item_model;
            $notice['title'] = "{$row->seller} {$row->brand_line}";
            $notice['content'] = str_repeat("{$row->brand} {$row->seller} {$row->sellersku} {$row->brand_line} {$row->asin} {$row->item_no} ", 100);

            KmsNotice::create($notice);
        }

    }
}
