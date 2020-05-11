<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Sendbox;
use App\Inbox;
use App\User;
use PDO;
use DB;
use Log;

class Warning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:warn';

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

        $rules_array = $ruleid_array = array();
        $rules = DB::table('rules')->whereNotNull('timeout')->orderBy('priority','asc')->get()->toArray();
        foreach($rules as $rule){
            $rules_array[$rule->id] = $rule->timeout;
            $ruleid_array[] = $rule->id;
        }
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['email'];
        }


        Inbox::where('reply',0)->where('warn','<',3)->whereIn('rule_id',$ruleid_array)->chunk( 200,function($tasks) use($rules_array,$users_array) {
            foreach ($tasks as $task) {
                if (date('Y-m-d H:i:s', strtotime($rules_array[$task->rule_id], strtotime($task->date))) <= date('Y-m-d H:i:s'))
                {
                    try {
                        $content = ($task->text_html)?$task->text_html:$task->text_plain;
                        $subject = 'Mail timeout notification! From '.$task->from_address.' '.$task->subject;
                        $html = new \Html2Text\Html2Text($content);
                        $to = array_get($users_array,$task->user_id,'');
                        $from = env('MAIL_FROM_ADDRESS');
                        if($to && $from) {
                            Mail::send(['emails.common', 'emails.common-text'], ['content' => $content, 'contentText' => $html->getText()], function ($m) use ($subject, $to, $from) {
                                $m->to($to);
                                $m->subject($subject);
                                $m->from($from);
                            });

                            $task->increment('warn');
                            $task->save();
                        }


                    } catch (\Exception $e) {
                        \Log::error('Send Warning Error '.$task->id.' Error' . $e->getMessage());
                    }

                }

            }
        });
    }
}
