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
            $sql = "SELECT sams.asin,sams.marketplace_id,sams.sku_status,sams.sku,asins.reviews,asins.rating from sap_asin_match_sku as sams LEFT JOIN asins on asins.asin= sams.asin WHERE sap_seller_id =" . $user['sap_seller_id'] . " GROUP BY
	                sams.marketplace_id,sams.asin;";
            $user_asin_list_obj = DB::connection('vlz')->select($sql);
            $user_asin_list = (json_decode(json_encode($user_asin_list_obj), true));            //asin 站点 suk suk状态
            if (!empty($user_asin_list)) {
                foreach ($user_asin_list as $k => $v) {
                    if (strlen($v['asin']) < 8) {
                        unset($user_asin_list[$k]);
                    }
                }

            }
            //查询所有汇率信息
            $currency_rates = DB::connection('vlz')->table('currency_rates')
                ->select('currency', 'rate', 'id', 'updated_at')
                ->get()->map(function ($value) {
                    return (array)$value;
                })->toArray();
        }
        echo '<pre>';
        var_dump($user_asin_list);
        exit;
        return view('marketingPlan.index', ['user_asin_list' => $user_asin_list]);
    }

    /**
     * 获取asin 详情
     * @param Request $request
     */
    public function getAsinDetail(Request $request)
    {

    }

    /**
     * 编辑修改 rsg plan
     * @author DYS
     * @copyright 2020年4月17日
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(Request $request)
    {
        if ($request['id'] > 0) {

        }
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