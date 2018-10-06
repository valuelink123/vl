<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KmsUserManual extends Model {
    protected $table = 'kms_user_manual';
    protected $fillable = ['brand', 'item_group', 'item_model', 'link', 'note'];

    public static function parseExcel($filepath) {

        $spreadsheet = IOFactory::load($filepath);

        $rows = [];

        $sheet = $spreadsheet->getSheetByName('Manual List');

        for ($nul = 0, $i = 3; true; ++$i) {

            $row = $sheet->rangeToArray("A{$i}:E{$i}")[0];

            foreach ($row as &$cell) {
                $cell = trim($cell);
            }

            if (empty($row[3])) {

                ++$nul;
                if ($nul > 5) break;

            } else {
                $nul = 0;
                $rows[] = array(
                    'brand' => $row[0],
                    'item_group' => $row[1],
                    'item_model' => $row[2],
                    'link' => $row[3],
                    'note' => $row[4]
                );
            }

        }

        return $rows;
    }

    /**
     * 从 Excel 导入到 MySQL
     * @param Request $req
     * @throws \Exception
     */
    public static function import(Request $req) {

        if ($req->has('link')) {

            $rows[] = $req->all(); // 单个添加的情况，也一并处理

        } elseif (empty($_FILES['excelfile']['size'])) {

            throw new \Exception('Please check the validity of the Excel file!');

        } elseif ($_FILES['excelfile']['error']) {

            throw new \Exception("Upload error: {$_FILES['excelfile']['error']}");

        } elseif ($_FILES['excelfile']['size'] > 5 * 1204 * 1204) {

            throw new \Exception('File exceeds 5M limit!');

        } else {

            $rows = self::parseExcel($_FILES['excelfile']['tmp_name']);

        }

        if (empty($rows)) {

            throw new \Exception('Import failed: Excel is empty or format error!');

        }

        foreach ($rows as $row) {
            self::create($row);
        }

    }
}
