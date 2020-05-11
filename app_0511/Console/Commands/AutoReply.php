<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Sendbox;
use App\Inbox;
use PDO;
use DB;
use Log;

class AutoReply extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:auto';

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
        Inbox::where('reply',0)->where('auto_id',0)->chunk( 200,function($tasks) {
            foreach ($tasks as $task) {
                try {
                    $result = $this->matchAuto($task);
                    if($result){
                        $sendbox = new Sendbox;
                        $sendbox->user_id = env('SYSTEM_AUTO_REPLY_USER_ID',1);
                        $sendbox->from_address = $task->to_address;
                        $sendbox->to_address = $task->from_address;
                        $sendbox->subject = 'Re:'.$task->subject;
                        $sendbox->text_html = $result->content;
                        $sendbox->date = date('Y-m-d H:i:s');
                        $sendbox->inbox_id = $task->id;
                        $sendbox->save();
                    }
                } catch (\Exception $e) {
                    \Log::error('Create Auto Reply '.$task->id.' Error' . $e->getMessage());
                }
            }
        });
    }

    public function matchAuto($mailData){
        $rules = DB::table('auto')->orderBy('priority','asc')->get()->toArray();
        foreach($rules as $rule){
            //标题匹配
            if($rule->subject){
                $matched = false;
                $subject_match_array = explode(';',$rule->subject);
                foreach($subject_match_array as $subject_match_string){
                    if($subject_match_string && stripos($mailData->subject,$subject_match_string) !== false){
                        $matched = true;
                    }
                }
                if(!$matched) continue;
            }

            //发件人匹配
            if($rule->from_email){
                $matched = false;
                $from_match_array = explode(';',$rule->from_email);
                foreach($from_match_array as $from_match_string){
                    if($from_match_string && stripos($mailData->from_address,$from_match_string) !== false){
                        $matched = true;
                    }
                }
                if(!$matched) continue;
            }

            //收件人匹配
            if($rule->to_email){
                $matched = false;
                $to_match_array = explode(';',$rule->to_email);
                if(in_array($mailData->to_address,$to_match_array) ){
                    $matched = true;
                }
                if(!$matched) continue;
            }

            if($rule->users){
                $matched = false;
                $users_match_array = explode(';',$rule->users);
                if(in_array($mailData->user_id,$users_match_array) ){
                    $matched = true;
                }
                if(!$matched) continue;
            }

            if($rule->date_from){
                $matched = false;
                if(date('Y-m-d',strtotime($mailData->date))>=$rule->date_from) $matched=true;
                if(!$matched) continue;
            }


            if($rule->date_to){
                $matched = false;
                if(date('Y-m-d',strtotime($mailData->date))<=$rule->date_to) $matched=true;
                if(!$matched) continue;
            }

            if($rule->time_from){
                $matched = false;
                if(date('G:i',strtotime($mailData->date))>=$rule->time_from) $matched=true;
                if(!$matched) continue;
            }


            if($rule->time_to){
                $matched = false;
                if(date('G:i',strtotime($mailData->date))<=$rule->time_to) $matched=true;
                if(!$matched) continue;
            }


            //Sku匹配
            if($rule->weeks){
                $matched = false;
                $weeks_match_array = explode(';',$rule->weeks);
                if(in_array(date('w',strtotime($mailData->date)),$weeks_match_array)) $matched=true;
                if(!$matched) continue;
            }
            return $rule;
        }
        return array();
    }

}
