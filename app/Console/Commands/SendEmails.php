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
		set_time_limit(0);
        $count = $smtp_array = array();
        $command = $this;
        $smtp_config =  Accounts::whereNotNull('smtp_host')->whereNotNull('smtp_port')->whereNotNull('smtp_ssl')->get();
		
        foreach($smtp_config as $smtp_value){
            $smtp_arrays[strtolower(trim($smtp_value->account_email))] = array('password'=>$smtp_value->password,'smtp_host'=>$smtp_value->smtp_host,'smtp_port'=>$smtp_value->smtp_port,'smtp_ssl'=>$smtp_value->smtp_ssl);
        }
        $tasks = Sendbox::where('status','Waiting')->where('error_count','<',1)->orderBy('from_address','asc')->take(100)->get();
		$this->run_email = '';
		foreach ($tasks as $task) {

			try {
				if($task->attachs){
					$attachs = unserialize($task->attachs);
				}else{
					$attachs = array();
				}
				$from=trim($task->from_address);
				$to = trim($task->to_address);
				$subject=$task->subject;
				$content=$task->text_html;
				$smtp_array= array_get($smtp_arrays,strtolower(trim($task->from_address)))?array_get($smtp_arrays,strtolower(trim($task->from_address))):array();

				if($this->run_email!=$from){
					Mail::clearResolvedInstances();
					$this->run_email=$from;
					if(array_get($smtp_array,'smtp_host') && array_get($smtp_array,'smtp_port') && array_get($smtp_array,'smtp_ssl') && array_get($smtp_array,'password')){
						$https['ssl']['verify_peer'] = FALSE;
						$https['ssl']['verify_peer_name'] = FALSE;
						$transport = (new Swift_SmtpTransport(array_get($smtp_array,'smtp_host'), array_get($smtp_array,'smtp_port'),array_get($smtp_array,'smtp_ssl')))
							->setUsername($from)
							->setPassword(array_get($smtp_array,'password'))
							->setStreamOptions($https)
						;
					}else{
			
						$transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
			
					}
					Mail::setSwiftMailer(new Swift_Mailer($transport));
				}
		 
				$html = new \Html2Text\Html2Text($content);
				Mail::send(['emails.common','emails.common-text'],['content'=>$content,'contentText'=>$html->getText()], function($m) use($subject,$to,$from,$attachs)
				{
					$m->to(trim($to));
					$m->subject($subject);
					$m->from(trim($from));
					if ($attachs && count($attachs)>0){
						foreach($attachs as $attachment) {
							$m->attach(public_path().$attachment);
						}
					}
				});
				if (count(Mail::failures()) > 0) {
					//print_r(Mail::failures());
					$result = false ;
				}else{
					$result = true ;
				}

				if ($result){
					$task->send_date = date("Y-m-d H:i:s");
					$task->status = 'Send';
				}else{
					$task->error = 'Failed to send to '.trim($task->to_address);
					$task->error_count = $task->error_count + 1;
				}
				
			} catch (\Exception $e) {
				//\Log::error('Send Mail '.$task->id.' Error' . $e->getMessage());
				$task->error = $e->getMessage();
				$task->error_count = $task->error_count + 1;
			}
			sleep(1);
			$task->save();
		}
    }

    public function sendEmail($from,$to,$subject = null,$content,$attachs=array(),$smtp_array=array())
    {
		if($this->run_email!=$from){
			unset($transport);
        	Mail::clearResolvedInstances();
			$this->run_email=$from;
			if(array_get($smtp_array,'smtp_host') && array_get($smtp_array,'smtp_port') && array_get($smtp_array,'smtp_ssl') && array_get($smtp_array,'password')){
				$https['ssl']['verify_peer'] = FALSE;
				$https['ssl']['verify_peer_name'] = FALSE;
				$transport = (new Swift_SmtpTransport(array_get($smtp_array,'smtp_host'), array_get($smtp_array,'smtp_port'),array_get($smtp_array,'smtp_ssl')))
					->setUsername($from)
					->setPassword(array_get($smtp_array,'password'))
					->setStreamOptions($https)
				;
			}else{
	
				$transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
	
			}
			Mail::setSwiftMailer(new Swift_Mailer($transport));
		}
 
        $html = new \Html2Text\Html2Text($content);
        Mail::send(['emails.common','emails.common-text'],['content'=>$content,'contentText'=>$html->getText()], function($m) use($subject,$to,$from,$attachs)
        {
            $m->to(trim($to));
            $m->subject($subject);
            $m->from(trim($from));
            if ($attachs && count($attachs)>0){
                foreach($attachs as $attachment) {
                    $m->attach(public_path().$attachment);
                }
            }
        });
        if (count(Mail::failures()) > 0) {
			//print_r(Mail::failures());
            $result = false ;
        }else{
            $result = true ;
        }
        
        return $result;
    }
}
