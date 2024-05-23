<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use App\SendboxOut;
use App\Accounts;
use Swift_Mailer;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Illuminate\Support\Facades\Mail;

use PDO;
use DB;
use Log;
use phpDocumentor\Reflection\Types\Null_;

class TextSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:send {Id}';

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
       
        $tasks = SendboxOut::where('id',$Id)->get();
		
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
				$content=preg_replace('/(?<=[\'="])\/uploads\/ueditor\/php\/upload/', url('/uploads/ueditor/php/upload'), $task->text_html);
				$result = $this->sendEmail($from,$to,$subject,$content,$attachs,[
                    'smtp_host'=>'smtp.bestthankyou.com',
                    'smtp_port'=>25,
                    'username'=>'OXA003@qq.com',
                    'password'=>'wahygbzxdmtscjhe',
                ]);
                print_r($result);
			} catch (\Exception $e) {
				print_r($e->getMessage());
			}
		}
    }

    public function sendEmail($from,$to,$subject,$content,$attachs=array(),$smtp_array=array())
    {
		Mail::clearResolvedInstances();
		$https['ssl']['verify_peer'] = FALSE;
		$https['ssl']['verify_peer_name'] = FALSE;
		$transport = (new Swift_SmtpTransport(array_get($smtp_array,'smtp_host'), array_get($smtp_array,'smtp_port'),array_get($smtp_array,'smtp_ssl')))
			->setUsername(array_get($smtp_array,'username'))
			->setPassword(array_get($smtp_array,'password'))
			->setStreamOptions($https)
		;
		Mail::setSwiftMailer(new Swift_Mailer($transport));
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
            $result = false;
        }else{
            $result = true;
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
