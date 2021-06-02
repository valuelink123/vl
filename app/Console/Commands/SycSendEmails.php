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
            $maxId = SendboxOut::max('id');
            $maxId = intval($maxId)?intval($maxId):0;
            $newSend = DB::statement("insert into sendbox_out select * from sendbox where sendbox.id>$maxId");
            Sendbox::where('id','>=',$maxId)->update(['synced'=>1]);
            $editArr = Sendbox::where('id','<=',$maxId)->where('synced',0)->pluck('id')->toArray();
            $editArr =  array_chunk($editArr,1000);
            foreach($editArr as $editIds){
                if($editIds){
                    $editIdsStr = implode(',',$editIds);
                    DB::statement("update sendbox_out,sendbox set 
                    sendbox_out.from_address=sendbox.from_address,
                    sendbox_out.to_address=sendbox.to_address,
                    sendbox_out.subject=sendbox.subject,
                    sendbox_out.text_html=sendbox.text_html,
                    sendbox_out.date=sendbox.date,
                    sendbox_out.attachs=sendbox.attachs,
                    sendbox_out.user_id=sendbox.user_id,
                    sendbox_out.updated_at=sendbox.updated_at,
                    sendbox_out.status=sendbox.status,
                    sendbox_out.plan_date=sendbox.plan_date,
                    sendbox_out.ip=sendbox.ip
                    where sendbox_out.id=sendbox.id and sendbox.id in ($editIdsStr)");
                    Sendbox::whereIn('id',$editIds)->update(['synced'=>1]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
        die(); //取消回写
        DB::beginTransaction();
        try{
            //whereRaw("(status='Send' or (status='Waiting' and error_count>5))")->
            $editArr = SendboxOut::whereRaw("(status='Send' or (status='Waiting' and error_count>0))")->where('synced',0)->pluck('id')->toArray();
            $editArr =  array_chunk($editArr,1000);
            foreach($editArr as $editIds){
                if($editIds){
                    $editIdsStr = implode(',',$editIds);
                    DB::statement("update sendbox,sendbox_out set 
                    sendbox.updated_at=sendbox_out.updated_at,
                    sendbox.send_date=sendbox_out.send_date,
                    sendbox.error=sendbox_out.error,
                    sendbox.status=sendbox_out.status,
                    sendbox.error_count=sendbox_out.error_count
                    where sendbox.id=sendbox_out.id and sendbox_out.id in ($editIdsStr)");
                    SendboxOut::whereIn('id',$editIds)->update(['synced'=>1]);    
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }
    }
}