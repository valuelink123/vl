<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;

class UpdateSendboxStatus extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'update:sendboxStatus';

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

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Log::info('start update:sendboxStatus');
		$yestoday = date('Y-m-d 00:00:00',strtotime("-1 day"));
		$sql = "UPDATE sendbox_out,sendbox SET sendbox_out.`status`='Waiting' 
					WHERE sendbox_out.id=sendbox.id 
					AND sendbox_out.`status`='Draft' 
					AND sendbox.`status`='Waiting' 
					AND sendbox.date >= '".$yestoday."'";
		DB::select($sql);
		Log::info('end update:sendboxStatus');

	}
}