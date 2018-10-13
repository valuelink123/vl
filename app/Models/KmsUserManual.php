<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.21
 * Time: 16:31
 */

namespace App\Models;

use App\Exceptions\DataImportException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KmsUserManual extends Model {
    protected $table = 'kms_user_manual';
    protected $fillable = ['brand', 'item_group', 'item_model', 'link', 'link_hash', 'note'];

    public static function create($row) {
        $find['link_hash'] = md5($row['link']);
        // Laravel 的代码提示是个问题
        return parent::updateOrCreate($find, $row);
    }

    /**
     * @param $filepath
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
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
     * @throws DataImportException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function import(Request $req) {

        if ($req->has('link')) {

            $rows[] = $req->all(); // 单个添加的情况，也一并处理

        } elseif (empty($_FILES['excelfile']['size'])) {

            throw new DataImportException('Please check the validity of the Excel file!', 100);

        } elseif ($_FILES['excelfile']['error']) {

            throw new DataImportException("Upload error: {$_FILES['excelfile']['error']}", 101);

        } elseif ($_FILES['excelfile']['size'] > 5 * 1204 * 1204) {

            throw new DataImportException('File exceeds 5M limit!', 102);

        } else {

            $rows = self::parseExcel($_FILES['excelfile']['tmp_name']);

        }

        if (empty($rows)) {

            throw new DataImportException('Import failed: Excel is empty or format error!', 103);

        }

        foreach ($rows as $row) {
            self::create($row);
        }

    }
}
