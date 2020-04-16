<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Asin;


class MarketingPlanController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index()
    {
        $asinList = [];
        if (!empty(Auth::user()->toArray())) {
            $user = Auth::user()->toArray(); //当前用户信息
            $user_asin_list = DB::connection('vlz')->table('sap_asin_match_sku')
                ->select('asin', 'marketplace_id','sku_status','sku')
                ->where('sap_seller_id', $user['sap_seller_id'])
                ->groupBy('asin')
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();

            if (!empty($user_asin_list)) {
                foreach ($user_asin_list as $k => $v) {
                    if (strlen($v['asin']) > 8) {
                        $asinList[] = $v['asin'] . ',' . $v['marketplace_id'].','.$v['sku'].','.$v['sku_status'];
                    }
                }

            }
        }
        echo '<pre>';
        var_dump(@$asinList);
        exit;
        return view('marketingPlan.index', ['asinList' => $asinList]);
    }

    /**
     * 获取asin 详情
     * @param Request $request
     */
    public function getAsinDetail(Request $request)
    {

    }

    public function detail()
    {
        return view('marketingPlan.detail');
    }

    public function showData()
    {

    }

    /**
     * 上传文件接口
     * @param Request $request
     * @return mixed
     */
    public function uploadfiles(Request $request)
    {
        $file = $request->file('files');
        if ($file) {
            try {
                $file_name = $file[0]->getClientOriginalName();
                $file_size = $file[0]->getSize();
                $file_ex = $file[0]->getClientOriginalExtension();
                $newname = $file_name;
                $newpath = '/uploads/' . date('Ym') . '/' . date('d') . '/' . date('His') . rand(100, 999) . intval(Auth::user()->id) . '/';
                $file[0]->move(public_path() . $newpath, $newname);
            } catch (\Exception $exception) {
                $error = array(
                    'name' => $file[0]->getClientOriginalName(),
                    'size' => $file[0]->getSize(),
                    'error' => $exception->getMessage(),
                );
                // Return error
                return \Response::json($error, 400);
            }

            // If it now has an id, it should have been successful.
            if (file_exists(public_path() . $newpath . $newname)) {
                $newurl = $newpath . $newname;
                $success = new \stdClass();
                $success->name = $newname;
                $success->size = $file_size;
                $success->url = $newurl;
                $success->thumbnailUrl = $newurl;
                $success->deleteUrl = url('send/deletefile/' . base64_encode($newpath . $newname));
                $success->deleteType = 'get';
                $success->fileID = md5($newpath . $newname);
                return \Response::json(array('files' => array($success)), 200);
            } else {
                return \Response::json('Error', 400);
            }
            return \Response::json('Error', 400);
        }

    }
}