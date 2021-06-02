<?php
/*
 * 更新邮箱状态
 *
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;

class UpdateEmailStatus extends Command
{
	protected $signature = 'update:email_status';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

	}

	public function __destruct()
	{

	}

	//添加历史客户数据
	function handle()
	{
		set_time_limit(0);
		Log::Info('start update email status');

		//查询出邮箱对应关系
		$sql = "UPDATE sendbox,sendbox_out 
			SET sendbox.send_date = sendbox_out.send_date,sendbox.updated_at = sendbox_out.updated_at,sendbox.error = sendbox_out.error,sendbox.error_count = sendbox_out.error_count,sendbox.`status` = sendbox_out.`status`,sendbox.plan_date = sendbox_out.plan_date 
			WHERE sendbox.id = sendbox_out.id 
  			AND sendbox.`status` = 'Waiting' 
  			AND sendbox.error_count < 6";
		DB::select($sql);

		Log::info('end update email status');
	}


}



