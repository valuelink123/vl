<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Sendbox;
use App\SendboxOut;
use DB;

class SycSendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:sendmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected static $mailDriverChanged = false;
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
        DB::beginTransaction();
        try{
            $blackEmail = blackEmail();
            $waitSend = Sendbox::where('status','Waiting')->whereNotIn('to_address',$blackEmail)->where('plan_date','<',strtotime(date('Y-m-d H:i:s')))->where('error_count','<',6)->where('updated_at','<=',date('Y-m-d H:i:s',strtotime('-10 min')))->where('synced',0)->get()->keyBy('id')->toArray();
            if(!empty($waitSend)){
                SendboxOut::insertOnDuplicateWithDeadlockCatching(array_values($waitSend),array_keys(array_get(array_values($waitSend),0)));
            }
            $ids = array_keys($waitSend);
            if($ids) Sendbox::whereIn('id',$ids)->update(['synced'=>1]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }

        DB::beginTransaction();
        try{
            $hasSend = SendboxOut::whereRaw("(status='Send' or (status='Waiting' and error_count>5))")->where('synced',0)->get(['id','updated_at','send_date','error','status','error_count','synced'])->keyBy('id')->toArray();
            foreach($hasSend as $k=>$v){
                Sendbox::where('id',$k)->update($v);
            }
            $ids = array_keys($hasSend);
            if($ids) SendboxOut::whereIn('id',$ids)->update(['synced'=>1]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }
}