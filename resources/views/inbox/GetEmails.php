<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

class GetEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:email {Id} {time}';

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
        $Id =  $this->argument('Id');
        $time =  $this->argument('time');
        if(!$Id) die;
        if(!$time) $time = '1day';
        $accounts = DB::table('accounts')->where('id',$Id);
        $accountList = $accounts->get();
		Log::useFiles(storage_path().'/logs/'.$Id.'/'.date('Y-m-d').'_email.log','debug');
        foreach($accountList as $account){
            $this->runAccount = array(
                'id' => $account->id,
                'account_email'=> $account->account_email,
                'account_sellerid'=> $account->account_sellerid,
                'email' => $account->email,
                'password' => $account->password,
                'imap_host' => $account->imap_host,
                'imap_ssl' => $account->imap_ssl,
                'imap_port' => $account->imap_port,
            );
			$this->saveEmails($time);
        }
    }


    public function saveEmails($time){
        $date = date('j F Y',strtotime('- '.$time));
        $attDir = public_path('attachs').'/'.date('Ymd');
        if (!is_dir($attDir)) mkdir($attDir, 0777,true);
		Log::Info(' '.$this->runAccount['account_email'].' Since '.$date.' Emails Scan Start...');
        
	$mailbox = new PhpImap\Mailbox('{'.$this->runAccount['imap_host'].':'.$this->runAccount['imap_port'].'/imap/'.$this->runAccount['imap_ssl'].'}INBOX', $this->runAccount['email'], $this->runAccount['password'], $attDir);
        //print_r($this->runAccount['account_email']);
        $mailsIds = $mailbox->searchMailbox('TO "'.$this->runAccount['account_email'].'" SINCE "'.$date.'"'); //TO 参数不起作用 没办法，下面循环比较
        if(!$mailsIds) {
            Log::Info(' '.$this->runAccount['account_email'].' Since '.$date.' Mailbox is empty...');
        }else{
            foreach($mailsIds as $mailsId){
                $exists = DB::table('inbox')->where('mail_address', $this->runAccount['email'])->where('mail_id', $mailsId)->first();
                if(!$exists) {
                    try{
                        $insert_data = array();
                        $attach_data = array();
                        $mail = $mailbox->getMail($mailsId);
                        if(!$mail->to) continue;
                        if(!array_key_exists(strtolower($this->runAccount['account_email']),array_change_key_case($mail->to,CASE_LOWER))) continue;

                        if($mail){
                            $insert_data['mail_address'] = $this->runAccount['email'];
                            $insert_data['mail_id'] = $mail->id;
                            $insert_data['from_name'] = $mail->fromName;
                            $insert_data['from_address'] = $mail->fromAddress;
                            $insert_data['to_address'] = $this->runAccount['account_email'];
                            $insert_data['subject'] = $mail->subject;
                            $insert_data['text_html'] = $mail->textHtml;
                            $insert_data['text_plain'] = $mail->textPlain;
                            $insert_data['date'] = $mail->date;
                            if($mail->getAttachments()){
                                foreach($mail->getAttachments() as $k=>$v){
                                    $attach_data[]=str_ireplace(public_path(),'',$v->filePath);
                                }
                                $insert_data['attachs'] = serialize($attach_data);
                            }
                            $orderInfo = $this->matchOrder($mail);
                            //if($orderInfo){
                            $insert_data['amazon_order_id'] = array_get($orderInfo,'amazon_order_id','');
                            $match_rule = $this->matchUser($mail,array_get($orderInfo,'order',array()));

                            if($match_rule['reply_status']==99){
                                if(env('AFTER_GET_MAIL_DELETE',0)){
                                    $mailbox->deleteMail($mailsId);
                                }
                                Log::Info(' Mail From '.$mail->fromAddress.' To '.$this->runAccount['account_email'].' have been trashed...');
                                continue;
                            }

                            $insert_data['user_id'] = $match_rule['user_id'];
                            $insert_data['rule_id'] = $match_rule['rule_id'];
                            $insert_data['reply'] = $match_rule['reply_status'];
                            //}

                            $result = DB::table('inbox')->insert($insert_data);
                            if(env('AFTER_GET_MAIL_DELETE',0) && $result){
                                $mailbox->deleteMail($mailsId);
                            }
                            Log::Info(' '.$this->runAccount['account_email'].' MailID '.$mailsId.' Insert Success...');
                        }
                    }catch (\Exception $e){
                        Log::Info(' '.$this->runAccount['account_email'].' MailID '.$mailsId.' Insert Error...'.$e->getMessage());
                    }
                }else{
                    Log::Info(' '.$this->runAccount['account_email'].' MailID '.$mailsId.' AlReady Exists...');
                }
            }
        }
        Log::Info(' '.$this->runAccount['account_email'].' Since '.$date.' Emails Scan Complete...');
    }

    public function matchOrder($mail){
        //先匹配中间件1个月内订单，同步到导入到本地，再匹配本地订单
        //标题中含有订单号
        $data = array();
        preg_match('/\d{3}-\d{7}-\d{7}/i', $mail->subject, $order_str);
        if(isset($order_str[0])){
            $data['amazon_order_id'] = $order_str[0];
        }elseif(stripos($mail->fromAddress,'marketplace.amazon') !== false){
            $data['amazon_order_id'] = $this->getOrderByEmail($mail->fromAddress);
        }else{
            $data['amazon_order_id']='';
        }
        if($data['amazon_order_id'] && stripos($mail->fromAddress,'marketplace.amazon') !== false){
            $data['order'] = $this->SaveOrderToLocal($data['amazon_order_id']);
        }
        return $data;

    }

    public function matchUser($mailData,$orderData){
        $orderId = array_get($orderData,'AmazonOrderId','');
        $lastUser = DB::table('inbox')->where('from_address',$mailData->fromAddress)
            ->where('to_address',$this->runAccount['account_email'])
            ->where('amazon_order_id',$orderId)->orderBy('date','desc')->first();
        if($lastUser){
            return array('user_id'=>$lastUser->user_id,'reply_status'=>0,'rule_id'=>999999);
        }

        $rules = DB::table('rules')->orderBy('priority','asc')->get()->toArray();
        $orderItems = array_get($orderData,'OrderItems',array());
        $orderSkus = ''; $orderAsins = array();
        foreach($orderItems as $item){
            $orderSkus.= $item['SellerSKU'].';';
            $orderAsins[]=$item['ASIN'];
        }
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
                    if($from_match_string && stripos($mailData->fromAddress,$from_match_string) !== false){
                        $matched = true;
                    }
                }
                if(!$matched) continue;
            }

            //收件人匹配
            if($rule->to_email){
                $matched = false;
                $to_match_array = explode(';',$rule->to_email);
                foreach($to_match_array as $to_address){
                    if($to_address == $this->runAccount['account_email'] ){
                        $matched = true;
                    }
                }
                if(!$matched) continue;
            }

            //站点匹配
            /*
            if($rule->site){
                $matched = false;
                $site_match_array = explode(';',$rule->site);
                if(in_array($data->site,$site_match_array) ){
                    $matched = true;
                }
                if(!$matched) continue;
            }
            */
            //Asin匹配
            if($rule->asin){
                $matched = false;
                $asin_match_array = explode(';',$rule->asin);
                foreach($orderAsins as $asin){
                    if($asin   && in_array($asin,$asin_match_array)){
                        $matched = true;
                    }
                }
                if(!$matched) continue;
            }


            //Sku匹配
            if($rule->sku){
                $matched = false;
                $sku_match_array = explode(';',$rule->sku);
                foreach($sku_match_array as $sku){
                    $str=array();
                    preg_match('/'.$sku.'(\d{4})/i', $orderSkus,$str);
                    if($sku && $str){
                        $matched = true;
                    }
                }
                if(!$matched) continue;
            }
            return array('user_id'=>$rule->user_id,'reply_status'=>$rule->reply_status,'rule_id'=>$rule->id);
        }
        return array('user_id'=>env('SYSTEM_AUTO_REPLY_USER_ID',1),'reply_status'=>0,'rule_id'=>0);
    }

    public function getOrderByEmail($email){
        $order = DB::connection('order')->table('amazon_orders')->where('SellerId',$this->runAccount['account_sellerid'])->where('BuyerEmail',$email)->orderBy('LastUpdateDate','Desc')->first();
        if($order){
            return $order->AmazonOrderId;
        }

        $order = DB::table('amazon_orders')->where('SellerId',$this->runAccount['account_sellerid'])->where('BuyerEmail',$email)->orderBy('LastUpdateDate','Desc')->first();
        if($order){
            return $order->AmazonOrderId;
        }

        return '';
    }


    public function SaveOrderToLocal($orderId){
        $returnData = array();
        $exists = DB::table('amazon_orders')->where('SellerId', $this->runAccount['account_sellerid'])->where('AmazonOrderId', $orderId)->first();
        if(!$exists){
            $order = DB::connection('order')->table('amazon_orders')->where('SellerId',$this->runAccount['account_sellerid'])->where('AmazonOrderId',$orderId)->first();
            if($order){
                $orderItems = DB::connection('order')->table('amazon_orders_item')->where('SellerId',$this->runAccount['account_sellerid'])->where('AmazonOrderId',$order->AmazonOrderId)->get();
                $order->OrderItems= $orderItems;
                $order = json_decode(json_encode( $order),true);
                unset($order['ImportToSap']);
                $returnData = $order;
                DB::beginTransaction();
                try{
                    DB::table('amazon_orders_item')->insert($order['OrderItems']);
                    unset($order['OrderItems']);
                    DB::table('amazon_orders')->insert($order);
                    DB::commit();
                    Log::Info(' SellerID: '.$this->runAccount['account_sellerid'].' AmazonOrderId: '.array_get($order,'AmazonOrderId').' Save Success...');
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }
        }else{
            $order = $exists;
            $order->OrderItems = DB::table('amazon_orders_item')->where('SellerId',$this->runAccount['account_sellerid'])->where('AmazonOrderId',$order->AmazonOrderId)->get();
            $order = json_decode(json_encode( $order),true);
            $returnData = $order;
        }
        return $returnData;
    }
}
