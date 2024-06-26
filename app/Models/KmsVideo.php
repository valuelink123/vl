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

class KmsVideo extends Model {
    protected $table = 'kms_video';
    protected $fillable = ['brand', 'item_group', 'item_model', 'type', 'descr', 'link', 'link_hash', 'note'];

    public static function create($row) {
        $find['link_hash'] = md5($row['link']);
        // 它的底层可能执行了不止一句 SQL
        // 既然有表的字段信息，其实可以在写入前判断
        // 如果 $find 包含唯一索引、主键，则使用如下语句
        // ISERT INTO ON DUPLICATE KEY UPDATE
        // 或者 REPLACE INTO
        // Laravel 最终会把 $find 和 $row 都写到数据里
        return parent::updateOrCreate($find, $row);
    }

    /**
     * @throws DataImportException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function parseExcel($filepath) {

        $spreadsheet = IOFactory::load($filepath);

        // 所有 Sheet 都读取；
        // $sheets = $spreadsheet->getAllSheets();
        // foreach ($sheets as $sheet) { }

        $rows = [];

        // 根据指定名称读取，避免存在隐藏工作表，内容不对应的问题；
        $sheet = $spreadsheet->getSheetByName('Video List');

        if (empty($sheet)) throw new DataImportException('Unable to find the Sheet named "Video List".', 100);

        for ($nul = 0, $i = 3; true; ++$i) {
            // 从指定行开始，一行一行读取；
            $row = $sheet->rangeToArray("A{$i}:G{$i}")[0];

            // foreach ($row as &$cell) $cell = trim($cell);
            $row = array_map('trim', $row);

            // 第F列是视频地址，不允许空；
            // 出现连续多个空行结束读取；
            if (empty($row[5])) {

                ++$nul;
                if ($nul > 5) break;

            } else {
                $nul = 0;
                $rows[] = array(
                    'brand' => $row[0],
                    'item_group' => $row[1],
                    'item_model' => $row[2],
                    'type' => $row[3],
                    'descr' => $row[4],
                    'link' => $row[5],
                    'note' => $row[6]
                );
            }

        }

        return $rows;
    }

    /**
     * 从 Excel 导入到 MySQL
     * @param Request $req
     * @param array $types 视频类型枚举
     * @throws DataImportException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function import(Request $req, $types) {

        if ($req->has('link')) {

            // 默认值仅对不存在的 key 生效
            // Laravel 自动对表单进行 trim 操作
            // 空格及空字符串会被转成 null，导致数据库报错
            // 可以通过配置中间件改变此行为
            // https://laravel.com/docs/5.4/requests#input-trimming-and-normalization

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

        $types = array_flip($types);

        foreach ($rows as $row) {
            if (!isset($types[$row['type']])) {
                $row['type'] = 'Others';
            }
            self::create($row);
        }

        return count($rows);

    }
}
