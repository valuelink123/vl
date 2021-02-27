<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Ddeboer\Imap\Server;
use Ddeboer\Imap\SearchExpression;
use Ddeboer\Imap\Search\Email\To;
use Ddeboer\Imap\Search\Email\From;
use Ddeboer\Imap\Search\Date\Since;
use Ddeboer\Imap\Search\RawExpression;
use DateTimeImmutable;
use DateInterval;
use Illuminate\Support\Facades\Input;
use PDO;
use DB;
use Log;

/*
 * user_id=252的李依因为规则id为226收到的邮件，
 * 有部分邮件分配错误，是因为邮件内容含有规则表中设定的subject为LY,例如邮件内容中某个单词含有LY就分配给了李依
 * 现将那部分分配错误的邮件重新匹配一下归属人，
 * 重新分配的规则更改如下：
 * 原先是规则表中设定的subject只要在邮件主题，邮件内容中含有就默认分配给该规则归属人
 * 现改为规则表中设定的subject只在邮件的主题中含有才分配给该规则归属人
 */
class UpdateEmails extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'update:emails';

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
		//查出从2021.2.20李依的所有收件箱内容，把不属于他的邮件给到相应的归属人，user_id=252
		$inboxData = DB::table('inbox')->where('user_id',252)->where('date','>','2021-02-20 00:00:00')->where('rule_id',226)->orderBy('date','asc')->get();
		$this->rules = DB::table('rules')->orderBy('priority','asc')->get()->toArray();
		foreach($inboxData as $key=>$val){
			$val = (array)$val;
			$match_rule = self::matchUser($val,array());

			$update_data = array();
			$update_data['etype'] = isset($match_rule['etype']) ? $match_rule['etype'] : '';
			$update_data['remark'] = isset($match_rule['remark']) ? $match_rule['remark'] : '';
			$update_data['sku'] = isset($match_rule['sku']) ? $match_rule['sku'] : '';
			$update_data['asin'] = isset($match_rule['asin']) ? $match_rule['asin'] : '';
			$update_data['mark'] = isset($match_rule['mark']) ? $match_rule['mark'] : '';
			$update_data['item_no'] = isset($match_rule['item_no']) ? $match_rule['item_no'] : '';
			$update_data['epoint'] = isset($match_rule['epoint']) ? $match_rule['epoint'] : '';
			$update_data['user_id'] = $match_rule['user_id'];
			$update_data['group_id'] = $match_rule['group_id'];
			$update_data['rule_id'] = $match_rule['rule_id'];
			$update_data['reply'] = $match_rule['reply_status'];
			DB::table('inbox')->where('id',$val['id'])->update($update_data);
		}
	}

	public function matchUser($mailData,$orderData){
		$return_data= array();
		$orderId = array_get($mailData,'amazon_order_id','');
		$orderItems = array_get($orderData,'OrderItems',array());
		$orderSkus = ''; $orderAsins = array();
		foreach($orderItems as $item){
			$orderSkus.= $item['SellerSKU'].';';
			$orderAsins[]=$item['ASIN'];
		}

		$rules = $this->rules;
		foreach($rules as $rule){
			if($rule->priority<0){
				if($this->checkRule($rule,$mailData,$orderSkus,$orderAsins)) {
					$return_data['user_id'] = $this->assignGroupUser($rule->group_id, $mailData);
					$return_data['group_id'] = $rule->group_id;
					$return_data['reply_status'] = $rule->reply_status;
					$return_data['rule_id'] = $rule->id;
					return $return_data;
				}
			}
		}

		//查出该账号最近收到的该客户的邮件信息
		$lastUser = DB::table('inbox')->where('from_address',array_get($mailData,'from_address',''))
			->where('to_address',array_get($mailData,'to_address',''))->where('date','<','2021-02-20 00:00:00')->orderBy('date','desc')->first();
		if($lastUser){
			$return_data = array('etype'=>$lastUser->etype,'remark'=>$lastUser->remark,'sku'=>$lastUser->sku,'asin'=>$lastUser->asin,'item_no'=>$lastUser->item_no,'mark'=>$lastUser->mark,'epoint'=>$lastUser->epoint);

		}
		//查出该账号最近发送给该客户的邮件信息
		$lastSend = DB::table('sendbox')->where('to_address',array_get($mailData,'from_address',''))
			->where('from_address',array_get($mailData,'to_address',''))->orderBy('date','desc')->first();
		if($lastSend){
			if($lastSend->inbox_id==0){//inbox_id==0表示不是对某封邮件进行回复
				//根据user_id得到此用户的一条组别信息
				$exists = DB::table('group_detail')->where('user_id',$lastSend->user_id)->first();
				if($exists){
					$return_data['user_id'] = $lastSend->user_id;
					$return_data['group_id'] = $this->getUserGroupRand($lastSend->user_id);
					$return_data['reply_status'] =0;
					$return_data['rule_id'] = 888888;
					return $return_data;
				}
			}
		}
		if($lastUser){
			$exists = DB::table('group_detail')->where('user_id',$lastUser->user_id)->where('group_id',$lastUser->group_id)->first();
			if($exists){
				$return_data['user_id'] = $lastUser->user_id;
				$return_data['group_id'] = $lastUser->group_id;
				$return_data['reply_status'] =0;
				$return_data['rule_id'] = $lastUser->rule_id;
				return $return_data;
			}
		}


		//从邮件的subject中匹配出订单号或者订单信息，如果有订单信息，则从asin表中根据sellersku，asin，site查出一条符合条件的记录，再根据记录里的内容赋值给user_id，rule_id和group_id，item_no
		$orderItems = array_get($orderData,'OrderItems',array());

		if($orderItems){
			$asin_rule = DB::table('asin')->where('sellersku',array_get($orderItems[0],'SellerSKU'))->where('asin',array_get($orderItems[0],'ASIN'))->where('site','www'.array_get($orderData,'SalesChannel'))->first();
			if($asin_rule){
				if($asin_rule->group_id){
					$return_data['user_id'] = $this->assignGroupUser($asin_rule->group_id,$mailData);
					$return_data['group_id'] = $asin_rule->group_id;
					$return_data['reply_status'] =0;
					$return_data['item_no'] = array_get($return_data,'item_no',$asin_rule->item_no);
					$return_data['rule_id'] = (900000+($asin_rule->id));

					return $return_data;
				}
			}
		}
		foreach($rules as $rule){
			if($rule->priority>0) {
				if ($this->checkRule($rule, $mailData, $orderSkus, $orderAsins)) {
					$return_data['user_id'] = $this->assignGroupUser($rule->group_id, $mailData);
					$return_data['group_id'] = $rule->group_id;
					$return_data['reply_status'] = $rule->reply_status;
					$return_data['rule_id'] = $rule->id;
					return $return_data;
				}
			}
		}

		$return_data['user_id'] = env('SYSTEM_AUTO_REPLY_USER_ID',1);
		$return_data['group_id'] = 15;
		$return_data['reply_status'] =0;
		$return_data['rule_id'] = 0;
		return $return_data;

	}

	public function checkRule($rule,$mailData,$orderSkus,$orderAsins){
		//发件人匹配
		if($rule->from_email){
			$from_match_array = explode(';',$rule->from_email);
			foreach($from_match_array as $from_match_string){
				if($from_match_string && stripos(array_get($mailData,'from_address',''),$from_match_string) !== false){
					return true;
				}
			}
		}

		//收件人匹配
		if($rule->to_email){
			$to_match_array = explode(';',$rule->to_email);
			foreach($to_match_array as $to_address){
				if($to_address == array_get($mailData,'to_address','') ){
					return true;
				}
			}
		}

		//标题匹配
		if($rule->subject){
			$subject_match_array = explode(';',$rule->subject);
			foreach($subject_match_array as $subject_match_string){
				if($subject_match_string && stripos(array_get($mailData,'subject',''),$subject_match_string) !== false){
					return true;
				}
			}
		}

		//Asin匹配
		if($rule->asin){
			$asin_match_array = explode(';',$rule->asin);
			foreach($orderAsins as $asin){
				if($asin   && in_array($asin,$asin_match_array)){
					return true;
				}
			}
			//规则里面填写的asin，加上匹配邮件的主题和内容
			foreach($asin_match_array as $match_asin){
				if($match_asin && stripos(array_get($mailData,'subject',''),$match_asin) !== false) {
					return true;
				}
				if($match_asin && stripos(array_get($mailData,'text_plain',''),$match_asin) !== false){
					return true;
				}
				if($match_asin && stripos(strip_tags(array_get($mailData,'text_html','')),$match_asin) !== false){
					return true;
				}
			}

		}


		//Sku匹配
		if($rule->sku){
			$sku_match_array = explode(';',$rule->sku);
			foreach($sku_match_array as $sku){
				$str=array();
				preg_match('/'.$sku.'(\d{4})/i', $orderSkus,$str);
				if($sku && $str){
					return true;
				}
			}
		}

		return false;

	}

	public function assignGroupUser($group_id,$mailData){
		$time =  date('Hi',strtotime(array_get($mailData,'date')));

		$date =  date('Y-m-d',strtotime(array_get($mailData,'date')));
		$users = DB::table('group_detail')->where('group_id',$group_id)->whereRaw("replace(time_from,':','')<=".$time)->whereRaw("replace(time_to,':','')>=".$time)->get();

		$users_arr =array();
		foreach($users as $user){
			$users_arr[] = $user->user_id;
		}
		if($users_arr){
			$user_mail_count = DB::table('inbox')->select(DB::raw('count(*) as count,user_id,group_id'))->where('group_id',$group_id)->whereIn('user_id',$users_arr)->where('date','<=',$mailData['date'].' 23:59:59')->where('date','>=',$mailData['date'].' 00:00:00')->groupBy(['user_id','group_id'])
				->orderBy('count', 'asc')->get();
			if(count($user_mail_count)>0){
				return $user_mail_count[0]->user_id;
			}else{
				return $users_arr[0];
			}
		}else{
			return 0;
		}

	}
	public function getUserGroupRand($user_id){
		$group = DB::table('group_detail')->where('user_id',$user_id)->first();
		if($group){
			$group_id = $group->group_id;
		}else{
			$group_id = 0;
		}
		return $group_id;
	}


}