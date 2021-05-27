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
    protected $signature = 'scan:send {Id}';

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
		set_time_limit(1140);
		$Id =  $this->argument('Id');
        $count = $smtp_array  = $smtp_arrays = $signature_arrays = array();
        $smtp_config =  Accounts::whereNotNull('smtp_host')->whereNotNull('smtp_port')->whereNotNull('smtp_ssl')->get();
		$select_mail =  Accounts::where('id',$Id)->value('account_email');
		$select_mail =  strtolower(trim($select_mail));
        foreach($smtp_config as $smtp_value){
            $smtp_arrays[strtolower(trim($smtp_value->account_email))] = array('password'=>$smtp_value->password,'smtp_host'=>$smtp_value->smtp_host,'smtp_port'=>$smtp_value->smtp_port,'smtp_ssl'=>$smtp_value->smtp_ssl);
        }
		$signature_config =  Accounts::whereNotNull('signature')->get();
		foreach($signature_config as $signature_value){
			$signature_arrays[strtolower(trim($signature_value->account_email))] = $signature_value->signature;  
		}

		$blackEmail = blackEmail();
        $tasks = Sendbox::where('status','Waiting')->where('from_address',$select_mail)->whereNotIn('to_address',$blackEmail)->where('plan_date','<',strtotime(date('Y-m-d H:i:s')))->where('error_count','<',6)->orderBy('error_count','asc')->take(120)->get();
		$this->run_email = '';
		$configTime = array(1=>5*60,2=>15*60,3=>30*60,4=>60*60,5=>240*60);
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
				$content=preg_replace('/(?<=[\'="])\/uploads\/ueditor\/php\/upload/', url('/uploads/ueditor/php/upload'), $task->text_html).array_get($signature_arrays,strtolower(trim($task->from_address)));
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
					$m->to(preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",trim($to)));
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
                    $task->plan_date = isset($configTime[$task->error_count]) ? time() + $configTime[$task->error_count] : $task->plan_date;
				}
				print_r($result);
			} catch (\Exception $e) {
				//\Log::error('Send Mail '.$task->id.' Error' . $e->getMessage());
				$task->error = $this->filterEmoji($e->getMessage());
				$task->error_count = $task->error_count + 1;
                $task->plan_date = isset($configTime[$task->error_count]) ? time() + $configTime[$task->error_count] : $task->plan_date;
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
			$m->to(preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",trim($to)));
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
	
	
	
	function filterEmoji($str)
	{
	 $str = preg_replace_callback(
	   '/./u',
	   function (array $match) {
		return strlen($match[0]) >= 4 ? '' : $match[0];
	   },
	   $str);
	  $str = str_replace(PHP_EOL, '', $str);
	  return $str;
	 }
}
