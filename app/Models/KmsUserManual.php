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
		if(isset($row['id']) && $row['id']){//有id的时候为更新数据
			$find['id'] = $row['id'];
		}else{
			$find['link_hash'] = md5($row['link']);
		}

        // Laravel 的代码提示是个问题
        return parent::updateOrCreate($find, $row);
    }

    /**
     * @param $filepath
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws DataImportException
     */
    public static function parseExcel($filepath) {

        $spreadsheet = IOFactory::load($filepath);

        $rows = [];

        $sheet = $spreadsheet->getSheetByName('Manual List');

        if (empty($sheet)) {

            throw new DataImportException('Unable to find the Sheet named "Manual List".', 100);

        }

        for ($nul = 0, $i = 3; true; ++$i) {

            $row = $sheet->rangeToArray("A{$i}:E{$i}")[0];

            $row = array_map('trim', $row);

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

        if ($req->has('item_group')) {
        	//此处为表单数据单个添加的情况
			// $id = isset($req['id']) && $req['id'] ? $req['id'] : '';//有id的时候为更新数据
			$link = isset($_POST['link']) && $_POST['link'] ? $_POST['link'] : '';
			$rows[] = $req->all(); // 单个添加的情况，也一并处理
			//link为空的时候，判断是否有上传文件
			if(empty($link)){
				if(isset($_FILES['uploadfile']) && $_FILES['uploadfile']['name']){
					if($_FILES['uploadfile']['size'] > 3 * 1024 * 1024){
						throw new DataImportException('File exceeds 3M limit!', 102);
					}
					$file = $req->file('uploadfile');
					$fileInfo = $_FILES['uploadfile'];
					if($file->isValid()) {
						$ext = $file->getClientOriginalExtension();//文件后缀名
						$filename = $fileInfo['name'];
						// $newname = date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
						$newpath = '/uploads/usermanual/' . date('Ymd') . '/';
						// $inputFileName = public_path() . $newpath . $newname;
						$bool = $file->move(public_path() . $newpath, $filename);
						if ($bool) {
							$rows[0]['link'] = $newpath.$filename;
						}
					}
				}else{
					throw new DataImportException('Please fill in link or select upload file！', 100);
				}
			}
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

        return count($rows);

    }
}
