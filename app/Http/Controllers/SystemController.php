<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 *
	 */

	public function __construct()
	{
		$this->middleware('auth');
		parent::__construct();
	}
	//跳转进入到禅道系统
	public function itRequirement(Request $req)
	{
		if(!Auth::user()->can(['It-requirement-show'])) die('Permission denied -- It-requirement-show');
		$email = Auth::user()->email;
		$email = str_replace('@valuelinkcorp.com','@valuelinkltd.com',$email);//把邮箱中的corp全部替换成ltd,因为之前邮箱为valuelinkcorp现在邮箱全部为valuelinkltd，但是vop系统中还有一部分人的邮箱未改
		$time = time();
		$code  = 'CqVmQJiefDOjeWQ5';//code，固定写死
		$key   = '2c5c27c0c3b6cc188676410fc9ca8afa';
		$token = md5($code . $key . $time);//生成加密签名
		$url = 'http://pms.vlerp.com:8088/api.php?m=user&f=apilogin&account='.$email.'&code='.$code.'&time='.$time.'&token='.$token;
		header("location:$url");

	}
	//下载浏览器插件包
	public function pluginDownload(Request $req)
	{
		$filepath = 'plugin.crx';
		$file=fopen($filepath,"r");
		header("Content-type:text/html;charset=utf-8");
		header("Content-Type: application/octet-stream");
		header("Accept-Ranges: bytes");
		header("Accept-Length: ".filesize($filepath));
		header("Content-Disposition: attachment; filename=".$filepath);
		echo fread($file,filesize($filepath));
		fclose($file);
	}



}