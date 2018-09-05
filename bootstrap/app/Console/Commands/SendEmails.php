<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\Sendbox;
use App\Accounts;
use Swift_Mailer;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Illuminate\Support\Facades\Mail;

use PDO;
use DB;
use Log;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:send';

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
        $count = $smtp_array = array();
        $count['all_tasks'] = 0;
        $count['success_tasks'] = 0;
        $command = $this;
        $smtp_config =  Accounts::whereNotNull('smtp_host')->whereNotNull('smtp_port')->whereNotNull('smtp_ssl')->get();
        foreach($smtp_config as $smtp_value){
            $smtp_array[strtolower($smtp_value->account_email)] = array('password'=>$smtp_value->password,'smtp_host'=>$smtp_value->smtp_host,'smtp_port'=>$smtp_value->smtp_port,'smtp_ssl'=>$smtp_value->smtp_ssl);
        }
        Sendbox::where('send_date',null)->where('error',null)->chunk( 200,function($tasks) use(&$count,$command,$smtp_array) {

            foreach ($tasks as $task) {
                $count['all_tasks']++;
                try {
                    if($task->attachs){
                        $attachs = unserialize($task->attachs);
                    }else{
                        $attachs = array();
                    }
                    if ($this->sendEmail($task->from_address,$task->to_address,$task->subject,$task->text_html,$attachs,isset($smtp_array[strtolower($task->from_address)])?$smtp_array[strtolower($task->from_address)]:array())){
                        $task->send_date = date("Y-m-d H:i:s");
                    }
                } catch (\Exception $e) {
                    \Log::error('Send Mail '.$task->id.' Error' . $e->getMessage());
                    $task->error = $e->getMessage();

                }
                $task->save();
            }
        });
    }

    public function sendEmail($from,$to,$subject = null,$content,$attachs=array(),$smtp_array=array())
    {
        if(array_get($smtp_array,'smtp_host') && array_get($smtp_array,'smtp_port') && array_get($smtp_array,'smtp_ssl') && array_get($smtp_array,'password')){

            $transport = (new Swift_SmtpTransport(array_get($smtp_array,'smtp_host'), array_get($smtp_array,'smtp_port'),array_get($smtp_array,'smtp_ssl')))
                ->setUsername($from)
                ->setPassword(array_get($smtp_array,'password'))
            ;
        }else{

            $transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');

        }
        Mail::setSwiftMailer(new Swift_Mailer($transport));

        $html = new \Html2Text\Html2Text($content);
        Mail::send(['emails.common','emails.common-text'],['content'=>$content,'contentText'=>$html->getText()], function($m) use($subject,$to,$from,$attachs)
        {
            $m->to($to);
            $m->subject($subject);
            $m->from($from);
            if ($attachs && count($attachs)>0){
                foreach($attachs as $attachment) {
                    $m->attach(public_path().$attachment);
                }
            }
        });
        if (count(Mail::failures()) > 0) {
            $result = false ;
        }else{
            $result = true ;
        }
        unset($transport);
        Mail::clearResolvedInstances();
        return $result;
    }


    public static function initAnotherMailDriver()
    {
        if (static::$mailDriverChanged) return;
        $transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
        Mail::setSwiftMailer(new Swift_Mailer($transport));
        static::$mailDriverChanged = true;
    }
}
