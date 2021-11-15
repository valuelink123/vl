<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\PpcProfile;
use App\Models\PpcReportData;
use App\Models\PpcSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Classes\PpcRequest;
use DB;

class AdvController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     */
    
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index()
    {
        if(!Auth::user()->can(['adv-show'])) die('Permission denied -- adv-show');
        $profiles = PpcProfile::get();
        return view('adv/campaign_list',['profiles'=>$profiles]);
    }

    public function getReportData(array $params){
        $data  = PpcReportData::selectRaw($params['group_by'].',
        sum(impressions) as impressions,
        sum(clicks) as clicks,
        sum(cost) as cost,
        sum(attributed_sales1d) as attributed_sales1d,
        sum(attributed_units_ordered1d) as attributed_units_ordered1d,
        round(sum(clicks)/sum(impressions),4) as ctr,
        round(sum(cost)/sum(clicks),4) as cpc,
        round(sum(cost)/sum(attributed_sales1d),4) as acos,
        round(sum(attributed_sales1d)/sum(cost),4) as raos
        ')->where('profile_id',$params['profile_id'])->where('record_type',$params['record_type']);
        foreach($params['where'] as $key=>$val){
            $where = is_array($val)?'whereIn':'where';
            $data  = $data->$where($key,$val);
        }
        $data = $data->where('date','>=',$params['start_date'])->where('date','<=',$params['end_date'])->groupBy($params['group_by'])->get()->keyBy($params['group_by'])->toArray();
        return $data;
    }

    public function listSchedules(Request $request)
    {
        $datas = PpcSchedule::with('user');
        if($request->get('profile_id')) $datas = $datas->where('profile_id',$request->get('profile_id'));
        if($request->get('ad_type')) $datas = $datas->where('ad_type',$request->get('ad_type'));
        if($request->get('campaign_id')) $datas = $datas->where('campaign_id',$request->get('campaign_id'));
        if(array_get($_REQUEST,'record_name')){
            $datas = $datas->where('record_name','like','%'.array_get($_REQUEST,'record_name').'%');
        }
        if(array_get($_REQUEST,'status')!==NULL && array_get($_REQUEST,'status')!==''){
            $datas = $datas->where('status',array_get($_REQUEST,'status'));
        }
        if(array_get($_REQUEST,'record_type')){
            $datas = $datas->where('record_type',array_get($_REQUEST,'record_type'));
        }
        
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->get()->toArray();
        
        $records["data"] = array();
		foreach ( $lists as $list){
            $record_name = "";
            $record_data = DB::table('ppc_'.strtolower($request->get('ad_type')).'_'.unCamelize($list['record_type']).'s')->where(unCamelize($list['record_type']).'_id',$list['record_type_id'])->get()->toArray();
            if(!empty($record_data)){
                $record_data = $record_data[0];
                if($list['record_type']=='target'){
                    $data = json_decode($record_data->resolved_expression,true);
                    $record_name = $data[0]['type'].' - '.$data[0]['value'];
                }elseif($list['record_type']=='keyword'){
                    $record_name = $record_data->match_type.' - '.$record_data->keyword_text;
                }elseif($list['record_type']=='ad'){
                    $record_name = $record_data->asin.' - '.$record_data->sku;
                }else{
                    $record_name = $record_data->name;
                }
            }
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.$list['id'].'"  />',
                $list['record_type'],
                $record_name??$list['record_name'],
                array_get(\App\Models\PpcSchedule::STATUS,$list['status']),
                $list['date_from'],
                $list['date_to'],
                $list['time'],
                'State : '.$list['state'].'<BR>Bid : '.$list['bid'],
                $list['done_at'].' '.$list['message'],
                $list['updated_at'],
                array_get($list,'user.name'),
            );
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function editSchedule(Request $request)
    {
        $form=[
            'profile_id'=>$request->get('profile_id'),
            'ad_type'=>$request->get('ad_type'),
            'campaign_id'=>$request->get('campaign_id'),
            'record_type'=>$request->get('record_type'),
            'record_type_id'=>$request->get('record_type_id'),
            'record_name'=>$request->get('record_name'),
            'status'=>1,
            'date_from'=>date('Y-m-d'),
            'date_to'=>date('Y-m-d'),
            'time'=>'00:00',
            'state'=>'enabled',
            'bid'=>$request->get('bid'),
        ];
        if($request->get('id')) $form =  PpcSchedule::where('id',$request->get('id'))->first()->toArray();
        return view('adv/schedule_edit',['form'=>$form]);
    }
	
    public function saveSchedule(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
            $data = $id?(PpcSchedule::findOrFail($id)):(new PpcSchedule);
            $fileds = array(
                'profile_id',
                'ad_type',
                'campaign_id',
                'record_type',
                'record_name',
                'record_type_id',
                'status',
                'date_from',
                'date_to',
                'time',
                'state',
                'bid'
            );
            foreach($fileds as $filed){
                $data->{$filed} = $request->get($filed);
            }
			if($id && (date('Gi')<intval(str_replace(':','',$request->get('time')))) && ($request->get('status')==1)){
				$data->done_at = $data->message = NULL;
			}
            $data->user_id = Auth::user()->id;
            $data->save();
            DB::commit();
            $records["code"] = 'SUCCESS';
            $records["description"] = "更新成功!";
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["code"] = 'FAILED';
            $records["description"] = $e->getMessage();
        }
        echo json_encode($records);
    }

    public function createWhole(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $seller_id = PpcProfile::where('profile_id',$profile_id)->value('seller_id');
        $products = DB::connection('amazon')->select("select asin,seller_sku from seller_skus 
        left join seller_accounts on seller_skus.seller_account_id=seller_accounts.id where mws_seller_id ='".$seller_id."'
        group by asin,seller_sku");
        return view('adv/create_whole',['profile_id'=>$profile_id,'ad_type'=>$ad_type,'products'=>$products]);
    }
	
    public function saveWhole(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        try{
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $data = [
                'name' => $request->get('CampaignName'),
                'state' => 'enabled',
                'startDate' => date('Ymd',strtotime($request->get('startDate'))),
                'endDate' => ($request->get('endDate')?date('Ymd',strtotime($request->get('endDate'))):NULL),
            ];
            $data['campaignType'] = 'sponsoredProducts';
            $data['targetingType'] = $request->get('targetingType');
            $data['dailyBudget'] = round($request->get('dailyBudget'),2);
            //$data['premiumBidAdjustment'] = $request->get('premiumBidAdjustment');
            $data['bidding'] = [
                'strategy'=>$request->get('strategy'),
                'adjustments'=>[
                    [
                        'predicate'=>'placementTop',
                        'percentage'=>round($request->get('placementTop'),2),
                    ],  
                    [
                        'predicate'=>'placementProductPage',
                        'percentage'=>round($request->get('placementProductPage'),2),
                    ]
                ],
            ];
            $customActionMessage="";
            $results = $app->campaigns->CreateCampaigns([$data]);
            if(array_get($results,'success') != 1) throw new \Exception(array_get($results,'response'));
            foreach(array_get($results,'response') as $result){
                if(array_get($result,'campaignId')){
                    $customActionMessage.='campaign '.array_get($result,'campaignId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                    $campaignId = array_get($result,'campaignId');
                }else{
                    throw new \Exception(array_get($result,'code').' '.array_get($result,'description'));
                }
            }

            
            $results = $app->groups->createAdGroups([[
                'campaignId'=>$campaignId,
                'name'=>$request->get('adGroupName'),
                'defaultBid'=>$request->get('defaultBid'),
                'state'=>'enabled',
            ]]);
            if(array_get($results,'success') != 1) throw new \Exception(array_get($results,'response'));

            foreach(array_get($results,'response') as $result){
                if(array_get($result,'adGroupId')){
                    $customActionMessage.='adGroup '.array_get($result,'adGroupId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                    $adGroupId = array_get($result,'adGroupId');
                }else{
                    throw new \Exception(array_get($result,'code').' '.array_get($result,'description'));
                }
            }

            $ads=[];
            foreach($request->get('ads') as $ad){
                $ads[] = [
                    'campaignId'=>$campaignId,
                    'adGroupId'=>$adGroupId,
                    'sku'=>$ad,
                    'state'=>'enabled'
                ];
            }
            if($ads){
                $results = $app->product_ads->createProductAds($ads);
                if(array_get($results,'success') == 1){
                    foreach(array_get($results,'response') as $result){
                        $customActionMessage.='productAd '.array_get($result,'adId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                    }
                }else{
                    throw new \Exception(array_get($results,'response'));
                }
            }

            if($request->get('targetingType')!='auto'){
                if($request->get('target')=='keywords'){
                    $keywords = $tmpExpression = [];
                    $bid = round($request->get('keywordDefaultBid'),2);
                    foreach(['Broad','Phrase','Exact'] as $type){
                        $text = explode(PHP_EOL,$request->get($type));
                        if(empty($text)) continue;
                        foreach($text as $keyword){
                            $keywords[$keyword.$type] = [
                                'campaignId'=>$campaignId,
                                'adGroupId'=>$adGroupId,
                                'keywordText'=>$keyword,
                                'bid'=>$bid,
                                'state'=>'enabled',
                                'matchType'=>$type,
                            ];
                            $tmpExpression[]=[
                                'keyword'=>$keyword,
                                'matchType'=>$type,
                            ];
                        }
                    }
                    if($keywords){
                        if($request->get('keywordBidOption')=='suggested'){
                            $re['adGroupId'] =  $adGroupId;
                            $re['keywords'] =  $tmpExpression;
                            $results = $app->bidding->getKeywordsBidRecommendations($re);
                            if(!empty(array_get($results,'response.recommendations'))){
                                foreach(array_get($results,'response.recommendations') as $k=>$v){
                                    if(array_get($v,'suggestedBid.suggested')>0) $keywords[array_get($v,'keyword').array_get($v,'matchType')]['bid'] = array_get($v,'suggestedBid.suggested');
                                }
                            }
                        }
                        $results = $app->keywords->createKeywords(array_values($keywords));
                        if(array_get($results,'success') == 1){
                            foreach(array_get($results,'response') as $result){
                                $customActionMessage.='keyword '.array_get($result,'keywordId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                            }
                        }else{
                            throw new \Exception(array_get($results,'response'));
                        }
                    }
                }

                if($request->get('target')=='targets'){
                    $keywords = $tmpExpression = [];
                    $bid = round($request->get('targetDefaultBid'),2);
                    foreach(['asinSameAs','asinCategorySameAs','asinBrandSameAs'] as $type){
                        $text = explode(PHP_EOL,$request->get($type));
                        if(empty($text)) continue;
                        foreach($text as $keyword){
                            $keywords[$keyword.$type] = [
                                'campaignId'=>$campaignId,
                                'adGroupId'=>$adGroupId,
                                'expressionType'=>'manual',
                                'bid'=>$bid,
                                'state'=>'enabled',
                                'expression'=>[['value'=>$keyword,'type'=>$type]],
                                'resolvedExpression'=>[['value'=>$keyword,'type'=>$type]],
                            ];
                            $tmpExpression[]=[['value'=>$keyword,'type'=>$type]];
                        }
                    }
                    if($keywords){
                        if($request->get('targetBidOption')=='suggested'){
                            $chunk_result = array_chunk($tmpExpression, 10);
                            foreach($chunk_result as $chunk_value){
                                $re['adGroupId'] =  $adGroupId;
                                $re['expressions'] =  $chunk_value;
                                $results = $app->bidding->getBidRecommendations($re);
                                if(!empty(array_get($results,'response.recommendations'))){
                                    foreach(array_get($results,'response.recommendations') as $k=>$v){
                                        if(array_get($v,'suggestedBid.suggested')>0) $keywords[array_get($v,'expression.0.value').array_get($v,'expression.0.type')]['bid'] = array_get($v,'suggestedBid.suggested');
                                    }
                                }
                            }
                        }
                        $results = $app->product_targeting->createTargetingClauses(array_values($keywords));
                        if(array_get($results,'success') == 1){
                            foreach(array_get($results,'response') as $result){
                                $customActionMessage.='target '.array_get($result,'targetId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                            }
                        }else{
                            throw new \Exception(array_get($results,'response'));
                        }
                    }
                }
            }
            
            $records["code"] = 'SUCCESS';
            $records["description"] = $customActionMessage;
        }catch (\Exception $e) { 
            $records["code"] = '';
            $records["description"] = $e->getMessage();
        }    
        echo json_encode($records);

    }

    public function listCampaigns(Request $request)
    {
        $datas = $reportData = $campaignIds = $chartData = [];
        $profile_id = $request->get('profile_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $name = $request->get('name');            
        $client = new PpcRequest($profile_id);
        foreach(PpcProfile::AD_TYPE as $k=>$v){
            $params = [];
            if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
            //if($name) $params['name'] = $name;
            $app = $client->request($k);
            $result = $app->campaigns->listCampaignsEx($params);
            if(array_get($result,'success')==1){
                $datas = array_merge(array_get($result,'response'),$datas);
            }
        }
        
        $tmp=[];
        foreach($datas as $data){
            if($name){
                if(strpos($data['name'],$name) !== false){ 
                    $tmp[$data['campaignId']] = $data;
                    $campaignIds[] = $data['campaignId'];
                }
            }else{
                $tmp[$data['campaignId']] = $data;
                $campaignIds[] = $data['campaignId'];
            }
        }
        $datas = $tmp;
        
        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
            $reportData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'campaign',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'record_type_id',
                    'where'=>$campaignIds?['record_type_id'=>$campaignIds]:[],
                ]
            );

            $chartData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'campaign',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'date',
                    'where'=>$campaignIds?['record_type_id'=>$campaignIds]:[],
                ]
            );
        }
        foreach($datas as $key=>$val){
            $datas[$key]= array_merge($datas[$key],array_get($reportData,$key,[
                'impressions'=>0,
                'clicks'=>0,
                'ctr'=>0,
                'cost'=>0,
                'cpc'=>0,
                'attributed_units_ordered1d'=>0,
                'attributed_sales1d'=>0,
                'acos'=>0,
                'raos'=>0,
            ]));
        }
        $sortFields = [
            '9'=>'impressions',
            '10'=>'clicks',
            '11'=>'ctr',
            '12'=>'cost',
            '13'=>'cpc',
            '14'=>'attributed_units_ordered1d',
            '15'=>'attributed_sales1d',
            '16'=>'acos',
            '17'=>'raos',
        ];
        $sortTypes = [
            'asc'=>SORT_ASC,
            'desc'=>SORT_DESC,
        ];
        $field = array_column($datas,array_get($sortFields,array_get($request->get('order'),'0.column')));
        array_multisort($field,array_get($sortTypes,array_get($request->get('order'),'0.dir')),$datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'-'.array_get($datas,$i.'.campaignId').'"  />',
                array_get($datas,$i.'.state'),
                '<a href="/adv/campaign/'.$profile_id.'/'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'/'.array_get($datas,$i.'.campaignId').'/setting">'.array_get($datas,$i.'.name').'</a> '
                .(array_get($datas,$i.'.campaignType')?'<a data-target="#ajax" data-toggle="modal" href="/adv/campaign/'.$profile_id.'/'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'/'.array_get($datas,$i.'.campaignId').'/copy" class="badge badge-success"> Copy </a> ':'')
                .(array_get($datas,$i.'.campaignType')?'<a data-target="#ajax" data-toggle="modal" href="/adv/scheduleEdit?profile_id='.$profile_id.'&ad_type='.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'&campaign_id='.array_get($datas,$i.'.campaignId').'&record_type=campaign&record_type_id='.array_get($datas,$i.'.campaignId').'&record_name='.urlencode(array_get($datas,$i.'.name')).'&bid='.(array_get($datas,$i.'.dailyBudget')??array_get($datas,$i.'.budget')).'" class="badge badge-success"> Scheduled </a>':''),
                array_get($datas,$i.'.servingStatus'),
                (array_get($datas,$i.'.campaignType')??(array_get($datas,$i.'.adFormat')?'Sponsored Brands':'Sponsored Display')).' - '.(array_get($datas,$i.'.targetingType')??'manual'),
                array_get(PpcProfile::BIDDING,array_get($datas,$i.'.bidding.strategy','legacyForSales')),
                date('Y-m-d',strtotime(array_get($datas,$i.'.startDate'))),
                array_get($datas,$i.'.endDate')?date('Y-m-d',strtotime(array_get($datas,$i.'.endDate'))):'',
                '<button type="button" class="ajax_bid btn default" data-pk="'.(array_get($datas,$i.'.dailyBudget')?'dailyBudget':'budget').'" id="'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'-'.array_get($datas,$i.'.campaignId').'">'.((array_get($datas,$i.'.dailyBudget')??array_get($datas,$i.'.budget'))).'</button>',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.impressions')>0)?array_get($reportData,array_get($datas,$i.'.campaignId').'.impressions'):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.clicks')>0)?array_get($reportData,array_get($datas,$i.'.campaignId').'.clicks'):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.ctr')>0)?((array_get($reportData,array_get($datas,$i.'.campaignId').'.ctr')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.cost')>0)?array_get($reportData,array_get($datas,$i.'.campaignId').'.cost'):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.cpc')>0)?round(array_get($reportData,array_get($datas,$i.'.campaignId').'.cpc'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.attributed_units_ordered1d')>0)?array_get($reportData,array_get($datas,$i.'.campaignId').'.attributed_units_ordered1d'):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.attributed_sales1d')>0)?round(array_get($reportData,array_get($datas,$i.'.campaignId').'.attributed_sales1d'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.acos')>0)?((array_get($reportData,array_get($datas,$i.'.campaignId').'.acos')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.campaignId').'.raos')>0)?array_get($reportData,array_get($datas,$i.'.campaignId').'.raos'):'-',
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        $records["recordsForChart"] = $chartData;
        echo json_encode($records);
    }

    public function campaignBatchUpdate(Request $request){
        $status = $request->get('confirmStatus');
        $ids = $request->get('id');
        $profile_id = $request->get('profile_id');
        try{
            $customActionMessage="";
            $datas=[];
            foreach($ids as $campaignId){
                $campaign = explode('-',$campaignId);
                $datas[$campaign[0]][]=[
                    'campaignId'=>$campaign[1],
                    'state'=>$status,
                ];
            }
            $client = new PpcRequest($profile_id);
            foreach($datas as $k=>$data){
                $app = $client->request($k);
                $results = $app->campaigns->updateCampaigns($data);
                if(array_get($results,'success') == 1){
                    foreach(array_get($results,'response') as $result){
                        $customActionMessage.=array_get($result,'campaignId').' - '.array_get($result,'code').'<BR>';
                    }
                }else{
                    throw new \Exception(array_get($results,'response'));
                }
            }
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);   

    }

    public function scheduleBatchUpdate(Request $request){
        $status = $request->get('confirmStatus');
        $ids = $request->get('id');
        try{
            PpcSchedule::whereIn('id',$ids)->update(['status'=>$status]);
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = 'Success';     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);   

    }

    public function editCampaign(Request $request,$profile_id,$ad_type,$campaign_id,$tab)
    {
        $client = new PpcRequest($profile_id);
        $app = $client->request($ad_type);
        $result = $app->campaigns->getCampaignEx($campaign_id);
        $campaign = $suggestedKeywords = $suggestedProducts = $suggestedCategories = [];
        if(array_get($result,'success')==1){
            $campaign = array_get($result,'response');
            if($ad_type=='SBrands'){
                $result = $app->groups->listAdGroups(['campaignIdFilter'=>$campaign_id]);
                $campaign['adGroupId'] = array_get($result,'response.0.adGroupId');
                if($tab == 'targetproduct'){
                    $asins=array_get($campaign,'creative.asins',[]);
                    $result = $app->product_targeting->listProductTargetRecommendations([
                        'nextToken'=>'',
                        'maxResults'=>100,
                        'filters'=>[
                            [
                                'filterType'=>'ASINS',
                                'values'=>$asins,
                            ]
                        ],
                    ]);
                    if(array_get($result,'success')==1){
                        $suggestedProducts = array_get($result,'response.recommendedProducts');
                    }
                    $result = $app->product_targeting->listCategoryTargetRecommendations([
                        'asins'=>$asins,
                    ]);
                    if(array_get($result,'success')==1){
                        $suggestedCategories = array_get($result,'response.categoryRecommendationResults');
                    }
                }
            }
        }
        $profiles = [];
        if($tab=='copy') $profiles = PpcProfile::get();
        return view('adv/campaign_'.strtolower($ad_type).'_'.$tab,['profile_id'=>$profile_id,'ad_type'=>$ad_type,'campaign'=>$campaign,'suggestedKeywords'=>$suggestedKeywords,'suggestedProducts'=>$suggestedProducts,'suggestedCategories'=>$suggestedCategories,'profiles'=>$profiles]); 
    }


    public function copyCampaign(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $to_profile_id = $request->get('to_profile_id');
        try{
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $new_client = new PpcRequest($to_profile_id);
            $new_app = $new_client->request($ad_type);
            $campaign = $adgroups = $ads = $keywords = $targets = [];
            $result = $app->campaigns->getCampaignEx($campaign_id);
            if(array_get($result,'success')==1) $campaign = array_get($result,'response');
            $params['campaignIdFilter'] = $campaign_id;
            $campaign['name'] = $request->get('to_name');
            $campaign['state'] = 'enabled';
            $campaign['startDate'] = date('Ymd',strtotime($request->get('startDate')));
            $campaign['endDate'] = ($request->get('endDate')?date('Ymd',strtotime($request->get('endDate'))):NULL);
            unset($campaign['campaignId']);
            unset($campaign['premiumBidAdjustment']);
            unset($campaign['servingStatus']);
            unset($campaign['creationDate']);
            unset($campaign['lastUpdatedDate']);
            $customActionMessage="";
            $copy = [];
            $results = $new_app->campaigns->CreateCampaigns([$campaign]);
            if(array_get($results,'success') != 1) throw new \Exception(array_get($results,'response'));

            foreach(array_get($results,'response') as $result){
                if(array_get($result,'campaignId')){
                    $customActionMessage.='campaign '.array_get($result,'campaignId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                    $copy['campaignId'] = array_get($result,'campaignId');
                }else{
                    throw new \Exception(array_get($result,'code').' '.array_get($result,'description'));
                }
            }

            $result = $app->groups->listAdGroupsEx($params);
            if(array_get($result,'success')==1) $adgroups = array_get($result,'response');
            foreach($adgroups as $adgroup){
                $params['adGroupIdFilter'] = array_get($adgroup,'adGroupId');
                $results = $new_app->groups->createAdGroups([[
                    'campaignId'=>array_get($copy,'campaignId'),
                    'name'=>array_get($adgroup,'name'),
                    'defaultBid'=>array_get($adgroup,'defaultBid'),
                    'state'=>array_get($adgroup,'state'),
                ]]);
                if(array_get($results,'success') != 1) throw new \Exception(array_get($results,'response'));

                foreach(array_get($results,'response') as $result){
                    if(array_get($result,'adGroupId')){
                        $customActionMessage.='adGroup '.array_get($result,'adGroupId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                        $copy['adGroupId'] = array_get($result,'adGroupId');
                    }else{
                        throw new \Exception(array_get($result,'code').' '.array_get($result,'description'));
                    }
                }

                $copyAds=$ads=[];
                $result = $app->product_ads->listProductAds($params);
                if(array_get($result,'success')==1) $ads = array_get($result,'response');
                foreach($ads as $ad){
                    $copyAds[] = array_merge($copy,[
                        'sku'=>array_get($ad,'sku'),
                        'state'=>array_get($ad,'state')
                    ]);
                }
                if($copyAds){
                    $results = $new_app->product_ads->createProductAds($copyAds);
                    if(array_get($results,'success') == 1){
                        foreach(array_get($results,'response') as $result){
                            $customActionMessage.='productAd '.array_get($result,'adId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                        }
                    }else{
                        throw new \Exception(array_get($results,'response'));
                    }
                }

                if($campaign['targetingType']=='auto') continue;
                $copyKeywords = $keywords = [];
                $result = $app->keywords->listBiddableKeywordsEx($params);
                if(array_get($result,'success')==1) $keywords = array_get($result,'response');
                foreach($keywords as $keyword){
                    $copyKeywords[] = array_merge($copy,[
                        'keywordText'=>array_get($keyword,'keywordText'),
                        'bid'=>array_get($keyword,'bid'),
                        'state'=>array_get($keyword,'state'),
                        'matchType'=>array_get($keyword,'matchType'),
                    ]);
                }
                if($copyKeywords){
                    $results = $new_app->keywords->createKeywords($copyKeywords);
                    if(array_get($results,'success') == 1){
                        foreach(array_get($results,'response') as $result){
                            $customActionMessage.='keyword '.array_get($result,'keywordId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                        }
                    }else{
                        throw new \Exception(array_get($results,'response'));
                    }
                }

                $result = $app->product_targeting->listTargetingClausesEx($params);
                $copyTargets = $targets = [];
                if(array_get($result,'success')==1) $targets = array_get($result,'response');
                foreach($targets as $target){
                    $copyTargets[] = array_merge($copy,[
                        'expressionType'=>array_get($target,'expressionType'),
                        'bid'=>array_get($target,'bid'),
                        'state'=>array_get($target,'state'),
                        'expression'=>array_get($target,'expression'),
                        'state'=>array_get($target,'state'),
                        'resolvedExpression'=>array_get($target,'resolvedExpression'),
                    ]);
                }
                if($copyTargets){
                    $results = $new_app->product_targeting->createTargetingClauses($copyTargets);
                    if(array_get($results,'success') == 1){
                        foreach(array_get($results,'response') as $result){
                            $customActionMessage.='target '.array_get($result,'targetId').' '.array_get($result,'code').' '.array_get($result,'description').'<BR>';
                        }
                    }else{
                        throw new \Exception(array_get($results,'response'));
                    }
                }
            }
            $records["code"] = 'SUCCESS';
            $records["description"] = $customActionMessage;
        }catch (\Exception $e) { 
            $records["code"] = '';
            $records["description"] = $e->getMessage();
        }    
        echo json_encode($records);
    }

    public function updateCampaign(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $client = new PpcRequest($profile_id);
        $app = $client->request($ad_type);
        
        $data = [
            'campaignId' => $campaign_id,
            'name' => $request->get('name'),
            'state' => $request->get('state'),
            'startDate' => date('Ymd',strtotime($request->get('startDate'))),
            'endDate' => ($request->get('endDate')?date('Ymd',strtotime($request->get('endDate'))):NULL),
        ];
        if($request->has('dailyBudget')) $data['dailyBudget'] = $request->get('dailyBudget');
        if($request->has('budget')) $data['budget'] = round($request->get('budget'),2);
        if($request->has('bidOptimization')) $data['bidOptimization'] = $request->get('bidOptimization');
        //if($request->has('premiumBidAdjustment')) $data['premiumBidAdjustment'] = $request->get('premiumBidAdjustment');
        if($request->has('bidMultiplier')) $data['bidMultiplier'] = intval($request->get('budget'));
        if($request->has('strategy')){
            $data['bidding'] = [
                'strategy'=>$request->get('strategy'),
                'adjustments'=>[
                    [
                        'predicate'=>'placementTop',
                        'percentage'=>$request->get('placementTop'),
                    ],  
                    [
                        'predicate'=>'placementProductPage',
                        'percentage'=>$request->get('placementProductPage'),
                    ]
                ],
            ];
        }
        $result = $app->campaigns->updateCampaigns([$data]);
        if(array_get($result,'success')==1){
            echo json_encode(array_get($result,'response.0'));
        }else{
            echo json_encode([
                'code'=>array_get($result,'code'),
                'description'=>array_get($result,'response')
            ]);
        }

    }


    
    public function updateBid(Request $request){
        $profile_id = $request->get('profile_id');
        $action = $request->get('action');
        $method = $request->get('method');
        $pk_type = $request->get('pk_type');
        $pk = $request->get('pk');
        $name = $request->get('name');
        $value = $request->get('value');
        $ad_type = $request->get('ad_type');
        if(!$ad_type){
            $tmp = explode('-',$name);
            $ad_type = array_get($tmp,'0');
            $name = array_get($tmp,'1');
        }
        $datas[]=[
            $pk_type=>$name,
            $pk=>$value,
        ];
        $client = new PpcRequest($profile_id);
        $app = $client->request($ad_type);
        $result = $app->$action->$method($datas);
        echo json_encode($result);   
    }

    public function batchUpdate(Request $request){
        $status = $request->get('confirmStatus');
        $ids = $request->get('id');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $id_type = $request->get('id_type');
        $action = $request->get('action');
        $method = $request->get('method');
        try{
            $customActionMessage="";
            $datas=[];
            foreach($ids as $id){
                $data = [
                    $id_type=>$id,
                    'state'=>$status,
                ];
                if($request->get('campaign_id')) $data['campaignId']=$request->get('campaign_id');
                if($request->get('adgroup_id')) $data['adGroupId']=$request->get('adgroup_id');
                $datas[]=$data;
            }
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=$id_type.'-'.$result[$id_type].' '.$result['code'].'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }

            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);   
    }

    public function listNegkeywords(Request $request)
    {
        $datas = [];
        $profile_id = $request->get('profile_id');
        $name = $request->get('name');
        $action = $request->get('action');
        $method = $request->get('method');        
        $client = new PpcRequest($profile_id);
        $params['stateFilter'] = 'enabled';
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->$action->$method($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }

        if($name){
            $tmp=[];
            foreach($datas as $data){
                if(strpos($data['keywordText'],$name) !== false){ 
                    $tmp[] = $data;
                }
            }
            $datas = $tmp;
        }
        $iTotalRecords = count($datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.keywordId').'"  />',
                array_get($datas,$i.'.keywordText'),
                array_get($datas,$i.'.matchType'),
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function storeNegkeywords(Request $request){
        $keyword_text = $request->get('keyword_text');
        $match_type = $request->get('match_type');
        $campaignId = $request->get('campaignId');
        $adGroupId = $request->get('adGroupId');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $action = $request->get('action');
        $method = $request->get('method'); 
        try{
            $customActionMessage="";
            $datas=[];
            $keywords = explode(PHP_EOL, $keyword_text);
            if($campaignId) $data['campaignId'] = $campaignId;
            if($adGroupId) $data['adGroupId'] = $adGroupId;
            if($ad_type!='SBrands') $data['state'] = 'enabled';
            foreach($keywords as $keyword){
                $data['keywordText'] = $keyword;
                $data['matchType'] = $match_type;
                $datas[]=$data;
            }
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=$result['keywordId'].' - '.$result['code'].'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }

            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }


    public function listNegproducts(Request $request)
    {
        $datas = [];
        $profile_id = $request->get('profile_id');
        $name = $request->get('name');       
        $action = $request->get('action');
        $method = $request->get('method');      
        $client = new PpcRequest($profile_id);
        $params['stateFilter'] = 'enabled';
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        
        $app = $client->request($request->get('ad_type'));

        if($request->get('ad_type') == 'SBrands'){
            $params = [];
            $params = [
                'nextToken'=>'',
                'maxResults'=>5000,
                'filters'=>[
                    [
                        'filterType'=>'TARGETING_STATE',
                        'values'=>['enabled'],
                    ],
                    [
                        'filterType'=>'CAMPAIGN_ID',
                        'values'=>[$request->get('campaign_id')],
                    ],
                    [
                        'filterType'=>'AD_GROUP_ID',
                        'values'=>[$request->get('adgroup_id')],
                    ]
                ]
             ];
        }

        $result = $app->$action->$method($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
            if($request->get('ad_type') == 'SBrands') $datas = array_get($result,'response.negativeTargets');
        }
        $tmp=[];
        if($name){
            foreach($datas as $data){
                if(strpos($data['expression'][0]['value'],$name) !== false){ 
                    $tmp[]=[
                        'targetId'=>$data['targetId'],
                        'type'=>array_get($data,'expression.0.type'),
                        'value'=>array_get($data,'expression.0.value'),
                    ];
                }
            }
            $datas = $tmp;
        }else{
            foreach($datas as $data){
                $tmp[]=[
                    'targetId'=>$data['targetId'],
                    'type'=>array_get($data,'expression.0.type'),
                    'value'=>array_get($data,'expression.0.value'),
                ];
            }
            $datas = $tmp;
        }
        $iTotalRecords = count($datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.targetId').'"  />',
                array_get($datas,$i.'.value'),
                array_get($datas,$i.'.type'),
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }


    public function storeNegproducts(Request $request){
        $keyword_text = $request->get('asins');
        $campaignId = $request->get('campaignId');
        $adGroupId = $request->get('adGroupId');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $action = $request->get('action');
        $method = $request->get('method'); 
        try{
            $customActionMessage="";
            $datas=[];
            $keywords = explode(PHP_EOL, $keyword_text);
            foreach($keywords as $keyword){
                $data = [
                    'state'=>'enabled',
                    'expression'=>[
                        [
                            'type'=>'asinSameAs',
                            'value'=>$keyword,
                        ]
                    ],
                    'expressionType'=>'manual',
                ];
                if($campaignId) $data['campaignId'] = $campaignId;
                if($adGroupId) $data['adGroupId'] = $adGroupId;

                if($request->get('ad_type') == 'SBrands'){
                    unset($data['state']);
                    unset($data['expressionType']);
                    $datas['negativeTargets'][]=$data;
                }else{
                    $datas[]=$data;
                }   
            }
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);


            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=array_get($result,'code').' '.array_get($result,'description').'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }
        
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }



    public function createAds(Request $request){
        $keywords = $request->get('ads');
        $campaignId = $request->get('campaignId');
        $adGroupId = $request->get('adGroupId');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $action = $request->get('action');
        $method = $request->get('method'); 
        try{
            $customActionMessage="";
            $datas=[];
            foreach($keywords as $keyword){
                $data = [
                    'state'=>'enabled',
                    'sku'=>$keyword,
                ];
                if($campaignId) $data['campaignId'] = $campaignId;
                if($adGroupId) $data['adGroupId'] = $adGroupId;

                $datas[]=$data; 
            }
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=array_get($result,'code').' '.array_get($result,'description').'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }
        
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }


    public function createKeyword(Request $request){
        $campaignId = $request->get('campaignId');
        $adGroupId = $request->get('adGroupId');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $action = $request->get('action');
        $method = $request->get('method'); 
        try{
            $customActionMessage="";
            $datas=[];
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $keyword['state'] = 'enabled';
            if($campaignId) $keyword['campaignId'] = $campaignId;
            if($adGroupId) $keyword['adGroupId'] = $adGroupId;
            $keywords = $request->get('keywords');
            if(!empty($keywords)){
                foreach($keywords as $data){
                    $datas[]=array_merge($keyword,$data); 
                }
            }else{
                $keywords = explode(PHP_EOL, $request->get('keyword_text'));
                if(!empty($keywords)){
                    $keyword['matchType'] = $request->get('match_type');
                    if($request->get('bidOption')=='customize'){
                        $keyword['bid'] = round($request->get('bid'),2);
                    }
                    if(array_get($keyword,'bid')<=0)  $keyword['bid'] = round($request->get('defaultBid'),2);
                    $suggestedBid = [];
                    if($request->get('bidOption')=='suggested'){
                        foreach($keywords as $data){
                            $re['keywords'][] = [
                                'keyword'=>$data,
                                'matchType'=>$request->get('match_type'),
                            ];
                        }
                        $re['adGroupId'] =  $adGroupId;
                        $results = $app->bidding->getKeywordsBidRecommendations($re);
                        if(!empty(array_get($results,'response.recommendations'))){
                            foreach(array_get($results,'response.recommendations') as $k=>$v){
                                $suggestedBid[array_get($v,'keyword')] = array_get($v,'suggestedBid.suggested');
                            }
                        }
                    }
                    foreach($keywords as $data){
                        $keyword['keywordText']=$data;
                        if(array_get($suggestedBid,$data)>0 && $request->get('bidOption')=='suggested') $keyword['bid'] = array_get($suggestedBid,$data);
                        $datas[]=$keyword; 
                    }
                }
            }
            $results = $app->$action->$method($datas);
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=array_get($result,'code').' '.array_get($result,'description').'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }


    public function createTarget(Request $request){
        $campaignId = $request->get('campaignId');
        $adGroupId = $request->get('adGroupId');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $action = $request->get('action');
        $method = $request->get('method'); 
        try{
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $customActionMessage="";
            $datas=[];
            $expressions = $request->get('expressions');
            if(!empty($expressions)){
                foreach($expressions as $expression){
                    $target['bid'] = round(array_get($expression,'bid'),2);
                    unset($expression['bid']);
                    if($ad_type=='SBrands'){
                        $target['expressions'] =  [$expression];
                        $target['campaignId'] = $campaignId;
                    }else{
                        $target['state'] = 'enabled';
                        $target['expressionType'] = 'manual';
                        $target['expression'] =  [$expression];
                    }
                    if($campaignId && $ad_type =='SProducts'){
                        $target['campaignId'] = $campaignId;
                        $target['resolvedExpression'] = $target['expression'];
                    }
                    if($adGroupId) $target['adGroupId'] = $adGroupId;
                    if($ad_type=='SBrands'){
                        $datas['targets'][]=$target; 
                    }else{
                        $datas[]=$target; 
                    }
                }
            }else{
                $keywords = explode(PHP_EOL, $request->get('keyword_text'));
                if(!empty($keywords)){
                    $target=[];
                    $matchType = $request->get('match_type');
                    if($request->get('bidOption')=='customize'){
                        $target['bid'] = round($request->get('bid'),2);
                    }
                    if(array_get($target,'bid')<=0)  $target['bid'] = round($request->get('defaultBid'),2);
                    foreach($keywords as $expression){
                        if($ad_type=='SBrands'){
                            $target['expressions'] =  [['value'=>$expression,'type'=>$matchType]];
                            $target['campaignId'] = $campaignId;
                        }else{
                            $target['state'] = 'enabled';
                            $target['expressionType'] = 'manual';
                            $target['expression'] =  [['value'=>$expression,'type'=>$matchType]];
                        }
                        if($campaignId && $ad_type =='SProducts'){
                            $target['campaignId'] = $campaignId;
                            $target['resolvedExpression'] = $target['expression'];
                        }
                        if($adGroupId) $target['adGroupId'] = $adGroupId;
                        $datas[$expression.$matchType]=$target;
                    }
                    if($request->get('bidOption')=='suggested'){
                        foreach($keywords as $data){
                            $tmpExpression[] = [[
                                'value'=>$data,
                                'type'=>$matchType,
                            ]];
                        }
                        
                        $chunk_result = array_chunk($tmpExpression, 10);
                        foreach($chunk_result as $chunk_value){
                            $re['adGroupId'] =  $adGroupId;
                            $re['expressions'] =  $chunk_value;
                            $results = $app->bidding->getBidRecommendations($re);
                            if(!empty(array_get($results,'response.recommendations'))){
                                foreach(array_get($results,'response.recommendations') as $k=>$v){
                                    if(array_get($v,'suggestedBid.suggested')>0) $datas[array_get($v,'expression.0.value').array_get($v,'expression.0.type')]['bid'] = array_get($v,'suggestedBid.suggested');
                                }
                            }
                        }
                        
                    }
                    $datas = array_values($datas);
                }
            }
            
            $results = $app->$action->$method($datas);
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=array_get($result,'code').' '.array_get($result,'description').'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }


    public function createAdGroup(Request $request){
        $campaignId = $request->get('campaignId');
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $action = $request->get('action');
        $method = $request->get('method'); 
        try{
            $customActionMessage="";
            $data = [
                'state'=>$request->get('state'),
                'name'=>$request->get('name'),
            ];
            if($campaignId) $data['campaignId'] = $campaignId;
            if($request->get('defaultBid')) $data['defaultBid'] = round($request->get('defaultBid'),2);
            if($request->get('bidOptimization')) $data['bidOptimization'] = $request->get('bidOptimization');
            if($request->get('tactic')) $data['tactic'] = $request->get('tactic');
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $results = $app->$action->$method([$data]);
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=array_get($result,'code').' '.array_get($result,'description').'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }
        
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }

    public function listAdGroups(Request $request)
    {
        $datas = $reportData = $adgroupIds = $chartData = [];
        $profile_id = $request->get('profile_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $name = $request->get('name');            
        $client = new PpcRequest($profile_id);
        $ad_type=$request->get('ad_type');
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->groups->listAdGroupsEx($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }

        $tmp=[];
        foreach($datas as $data){
            if($name){
                if(strpos($data['name'],$name) !== false){ 
                    $tmp[$data['adGroupId']] = $data;
                    $adgroupIds[] = $data['adGroupId'];
                }
            }else{
                $tmp[$data['adGroupId']] = $data;
                $adgroupIds[] = $data['adGroupId'];
            }
        }
        $datas = $tmp;

        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
            $reportData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'adGroup',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'record_type_id',
                    'where'=>$adgroupIds?['record_type_id'=>$adgroupIds]:[],
                ]
            );

            $chartData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'adGroup',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'date',
                    'where'=>$adgroupIds?['record_type_id'=>$adgroupIds]:[],
                ]
            );
        }
        foreach($datas as $key=>$val){
            $datas[$key]= array_merge($datas[$key],array_get($reportData,$key,[
                'impressions'=>0,
                'clicks'=>0,
                'ctr'=>0,
                'cost'=>0,
                'cpc'=>0,
                'attributed_units_ordered1d'=>0,
                'attributed_sales1d'=>0,
                'acos'=>0,
                'raos'=>0,
            ]));
        }
        $sortFields = [
            '7'=>'impressions',
            '8'=>'clicks',
            '9'=>'ctr',
            '10'=>'cost',
            '11'=>'cpc',
            '12'=>'attributed_units_ordered1d',
            '13'=>'attributed_sales1d',
            '14'=>'acos',
            '15'=>'raos',
        ];
        $sortTypes = [
            'asc'=>SORT_ASC,
            'desc'=>SORT_DESC,
        ];
        $field = array_column($datas,array_get($sortFields,array_get($request->get('order'),'0.column')));
        array_multisort($field,array_get($sortTypes,array_get($request->get('order'),'0.dir')),$datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $suggestedBid = ($request->get('ad_type')=='SProducts')?$app->bidding->getAdGroupBidRecommendations(array_get($datas,$i.'.adGroupId')):[];
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.adGroupId').'"  />',
                array_get($datas,$i.'.state'),
                '<a href="/adv/adgroup/'.$profile_id.'/'.$request->get('ad_type').'/'.array_get($datas,$i.'.adGroupId').'/setting">'.array_get($datas,$i.'.name').'</a>'
                .' <a data-target="#ajax" data-toggle="modal" href="/adv/scheduleEdit?profile_id='.$profile_id.'&ad_type='.$request->get('ad_type').'&campaign_id='.array_get($datas,$i.'.campaignId').'&record_type=adGroup&record_type_id='.array_get($datas,$i.'.adGroupId').'&record_name='.urlencode(array_get($datas,$i.'.name')).'&bid='.array_get($datas,$i.'.defaultBid').'" class="badge badge-success"> Scheduled </a>',
                array_get($datas,$i.'.servingStatus'),
                array_get($suggestedBid,'response.suggestedBid.suggested')?array_get($suggestedBid,'response.suggestedBid.suggested').'<BR>'.array_get($suggestedBid,'response.suggestedBid.rangeStart').' - '.array_get($suggestedBid,'response.suggestedBid.rangeEnd'):'-',
                '<button type="button" class="ajax_bid btn default" data-pk="defaultBid" id="'.array_get($datas,$i.'.adGroupId').'">'.array_get($datas,$i.'.defaultBid').'</button>',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.impressions')>0)?array_get($reportData,array_get($datas,$i.'.adGroupId').'.impressions'):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.clicks')>0)?array_get($reportData,array_get($datas,$i.'.adGroupId').'.clicks'):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.ctr')>0)?((array_get($reportData,array_get($datas,$i.'.adGroupId').'.ctr')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.cost')>0)?array_get($reportData,array_get($datas,$i.'.adGroupId').'.cost'):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.cpc')>0)?round(array_get($reportData,array_get($datas,$i.'.adGroupId').'.cpc'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.attributed_units_ordered1d')>0)?array_get($reportData,array_get($datas,$i.'.adGroupId').'.attributed_units_ordered1d'):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.attributed_sales1d')>0)?round(array_get($reportData,array_get($datas,$i.'.adGroupId').'.attributed_sales1d'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.acos')>0)?((array_get($reportData,array_get($datas,$i.'.adGroupId').'.acos')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.adGroupId').'.raos')>0)?array_get($reportData,array_get($datas,$i.'.adGroupId').'.raos'):'-',
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        $records["recordsForChart"] = $chartData;
        echo json_encode($records);
    }


    public function editAdGroup(Request $request,$profile_id,$ad_type,$adgroup_id,$tab)
    {
        $client = new PpcRequest($profile_id);
        $app = $client->request($ad_type);
        $result = $app->groups->getAdGroupEx($adgroup_id);
        $adgroup = $suggestedKeywords = $suggestedProducts = $suggestedCategories = $products =  [];
        if(array_get($result,'success')==1){
            $adgroup =array_get($result,'response');
            $result = $app->campaigns->getCampaignEx(array_get($adgroup,'campaignId'));
            if(array_get($result,'success')==1){
                $adgroup['campaignName'] = array_get($result,'response.name');
            }
            if($ad_type=='SDisplay'){
                if($tab=="creatives"){
                    $result = $app->groups->listCreatives(['adGroupIdFilter'=>$adgroup_id]);
                    if(array_get($result,'success')==1){
                        $creatives = array_get($result,'response');
                    }
                }
                if($tab=="targetproduct"){
                    /*
                    $result = $app->product_ads->listProductAds([
                        'stateFilter'=>'enabled',
                        'campaignIdFilter'=>array_get($adgroup,'campaignId'),
                        'adGroupIdFilter'=>array_get($adgroup,'adGroupId'),
                    ]);
                    $asins = [];
                    if(array_get($result,'success')==1){
                        $ads = array_get($result,'response');
                        if(is_array($ads)){
                            foreach($ads as $ad){
                                $asins[] = array_get($ad,'asin');
                            }
                        }
                    }
                    $result = $app->targeting->getTargetingRecommendations([
                        'tactic'=>array_get($adgroup,'tactic'),
                        'products'=>$asins,
                        'typeFilter'=>'PRODUCT',
                    ]);
                    if(array_get($result,'success')==1){
                        $suggestedProducts = array_get($result,'response.recommendedProducts');
                    }

                    $result = $app->targeting->getTargetingRecommendations([
                        'tactic'=>array_get($adgroup,'tactic'),
                        'products'=>$asins,
                        'typeFilter'=>'CATEGORY',
                    ]);
                    if(array_get($result,'success')==1){
                        $suggestedCategoryies = array_get($result,'response.recommendedProducts');
                    }
                    */
                }
            }
            if($ad_type=='SProducts'){
                $adgroup['suggestedBid'] = $app->bidding->getAdGroupBidRecommendations($adgroup_id);
                if($tab=="targetkeyword"){
                    $result = $app->keywords->getAdGroupSuggestedKeywordsEx($adgroup_id,['suggestBids'=>'yes','maxNumSuggestions'=>100]);
                    if(array_get($result,'success')==1){
                        $suggestedKeywords = array_get($result,'response');
                    }
                }
                if($tab=="targetproduct"){
                    $result = $app->product_ads->listProductAds([
                        'stateFilter'=>'enabled',
                        'campaignIdFilter'=>array_get($adgroup,'campaignId'),
                        'adGroupIdFilter'=>array_get($adgroup,'adGroupId'),
                    ]);
                    $asins = [];
                    if(array_get($result,'success')==1){
                        $ads = array_get($result,'response');
                        if(is_array($ads)){
                            foreach($ads as $ad){
                                $asins[] = array_get($ad,'asin');
                            }
                        }
                    }
                    $result = $app->product_targeting->createTargetRecommendations($asins,1,50);
                    if(array_get($result,'success')==1){
                        $suggestedProducts = array_get($result,'response.recommendedProducts');
                    }

                    $result = $app->product_targeting->getTargetingCategories($asins);
                    if(array_get($result,'success')==1){
                        $suggestedCategories = array_get($result,'response');
                    }
                }
            }
            if($tab=='ad'){
                $seller_id = PpcProfile::where('profile_id',$profile_id)->value('seller_id');
                $products = DB::connection('amazon')->select("select asin,seller_sku from seller_skus 
                left join seller_accounts on seller_skus.seller_account_id=seller_accounts.id where mws_seller_id ='".$seller_id."'
                group by asin,seller_sku");
            }
        }
        return view('adv/adgroup_'.strtolower($ad_type).'_'.$tab,['profile_id'=>$profile_id,'ad_type'=>$ad_type,'adgroup'=>$adgroup,'suggestedKeywords'=>$suggestedKeywords,'suggestedProducts'=>$suggestedProducts,'suggestedCategories'=>$suggestedCategories,'products'=>$products]); 
    }

    public function updateAdGroup(Request $request)
    {
        
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $adgroup_id = $request->get('adgroup_id');
        $client = new PpcRequest($profile_id);
        $app = $client->request($ad_type);
        $data = [
            'adGroupId' => $adgroup_id,
            'name' => $request->get('name'),
            'state' => $request->get('state'),
            'defaultBid' => $request->get('defaultBid'),
        ];
        $result = $app->groups->updateAdGroup([$data]);
        if(array_get($result,'success')==1){
            echo json_encode(array_get($result,'response.0'));
        }else{
            echo json_encode([
                'code'=>array_get($result,'code'),
                'description'=>array_get($result,'response')
            ]);
        }
        
    }


    public function listAds(Request $request)
    {
        $datas = $reportData = $adIds = $chartData = [];
        $profile_id = $request->get('profile_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $name = $request->get('name');            
        $client = new PpcRequest($profile_id);
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->product_ads->listProductAds($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }

        if($name){
            $tmp=[];
            foreach($datas as $data){
                if(strpos($data['asin'],$name) !== false || strpos($data['sku'],$name) !== false){ 
                    $tmp[] = $data;
                    $adIds[] = $data['adId'];
                }
            }
            $datas = $tmp;
        }else{
            foreach($datas as $data){
                $adIds[] = $data['adId']; 
            }
        }
        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
            $reportData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'ad',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'record_type_id',
                    'where'=>$adIds?['record_type_id'=>$adIds]:[],
                ]
            );

            $chartData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'ad',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'date',
                    'where'=>$adIds?['record_type_id'=>$adIds]:[],
                ]
            );
        }
        foreach($datas as $key=>$val){
            $datas[$key]= array_merge($datas[$key],array_get($reportData,$key,[
                'impressions'=>0,
                'clicks'=>0,
                'ctr'=>0,
                'cost'=>0,
                'cpc'=>0,
                'attributed_units_ordered1d'=>0,
                'attributed_sales1d'=>0,
                'acos'=>0,
                'raos'=>0,
            ]));
        }
        $sortFields = [
            '4'=>'impressions',
            '5'=>'clicks',
            '6'=>'ctr',
            '7'=>'cost',
            '8'=>'cpc',
            '9'=>'attributed_units_ordered1d',
            '10'=>'attributed_sales1d',
            '11'=>'acos',
            '12'=>'raos',
        ];
        $sortTypes = [
            'asc'=>SORT_ASC,
            'desc'=>SORT_DESC,
        ];
        $field = array_column($datas,array_get($sortFields,array_get($request->get('order'),'0.column')));
        array_multisort($field,array_get($sortTypes,array_get($request->get('order'),'0.dir')),$datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.adId').'"  />',
                '<a href="https://www.amazon.com/dp/'.array_get($datas,$i.'.asin').'">'.array_get($datas,$i.'.asin').'</a>',
                array_get($datas,$i.'.sku'),
                array_get($datas,$i.'.state'),
                (array_get($reportData,array_get($datas,$i.'.adId').'.impressions')>0)?array_get($reportData,array_get($datas,$i.'.adId').'.impressions'):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.clicks')>0)?array_get($reportData,array_get($datas,$i.'.adId').'.clicks'):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.ctr')>0)?((array_get($reportData,array_get($datas,$i.'.adId').'.ctr')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.cost')>0)?array_get($reportData,array_get($datas,$i.'.adId').'.cost'):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.cpc')>0)?round(array_get($reportData,array_get($datas,$i.'.adId').'.cpc'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.attributed_units_ordered1d')>0)?array_get($reportData,array_get($datas,$i.'.adId').'.attributed_units_ordered1d'):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.attributed_sales1d')>0)?round(array_get($reportData,array_get($datas,$i.'.adId').'.attributed_sales1d'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.acos')>0)?((array_get($reportData,array_get($datas,$i.'.adId').'.acos')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.adId').'.raos')>0)?array_get($reportData,array_get($datas,$i.'.adId').'.raos'):'-',
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        $records["recordsForChart"] = $chartData;
        echo json_encode($records);
    }


    public function listKeywords(Request $request)
    {
        $datas = $reportData = $ids = $chartData = [];
        $profile_id = $request->get('profile_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $name = $request->get('name');            
        $client = new PpcRequest($profile_id);
        $params = $tmpExpression = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->keywords->listBiddableKeywordsEx($params);
        
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }


        $field = ($request->get('ad_type')=='SProducts')?'keyword':'keywordText';
        $tmp=[];
        foreach($datas as $data){
            if($name && (strpos($data['keywordText'],$name) === false)) continue; 
            $tmp[$data['keywordId']] = $data;
            $ids[] = $data['keywordId'];
            $tmpExpression[]=[
                $field=>array_get($data,'keywordText'),
                'matchType'=>array_get($data,'matchType'),
            ];
        }
        $datas = $tmp;
        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
            $suggestedBid = [];

            if($request->get('ad_type')=='SProducts'){
                
                $re['adGroupId'] =  $request->get('adgroup_id');
                $re['keywords'] =  $tmpExpression;
                $results = $app->bidding->getKeywordsBidRecommendations($re);
                if(!empty(array_get($results,'response.recommendations'))){
                    foreach(array_get($results,'response.recommendations') as $k=>$v){
                        $suggestedBid[array_get($v,'keyword').array_get($v,'matchType')] = array_get($v,'suggestedBid');
                    }
                }
                
            }

            if($request->get('ad_type')=='SBrands'){
                $re['campaignId'] =  $request->get('campaign_id');
                $re['adFormat'] =  $request->get('adFormat');
                $re['targets'] =  [];
                $re['keywords'] =  $tmpExpression;
                $results = [];
                //$results = $app->bid->bidRecommendations($re);
                if(!empty(array_get($results,'response.keywordsBidsRecommendationSuccessResults'))){
                    foreach(array_get($results,'response.keywordsBidsRecommendationSuccessResults') as $k=>$v){
                        $suggestedBid[array_get($v,'keyword.keywordText').array_get($v,'keyword.matchType')] = array_get($v,'recommendedBid');
                        $suggestedBid[array_get($v,'keyword.keywordText').array_get($v,'keyword.matchType')]['suggested'] = array_get($v,'recommendedBid.recommended');
                    }
                }
            }
            $reportData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'keyword',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'record_type_id',
                    'where'=>$ids?['record_type_id'=>$ids]:[],
                ]
            );

            $chartData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'keyword',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'date',
                    'where'=>$ids?['record_type_id'=>$ids]:[],
                ]
            );
        }
        foreach($datas as $key=>$val){
            $datas[$key]= array_merge($datas[$key],array_get($reportData,$key,[
                'impressions'=>0,
                'clicks'=>0,
                'ctr'=>0,
                'cost'=>0,
                'cpc'=>0,
                'attributed_units_ordered1d'=>0,
                'attributed_sales1d'=>0,
                'acos'=>0,
                'raos'=>0,
            ]));
        }
        $sortFields = [
            '8'=>'impressions',
            '9'=>'clicks',
            '10'=>'ctr',
            '11'=>'cost',
            '12'=>'cpc',
            '13'=>'attributed_units_ordered1d',
            '14'=>'attributed_sales1d',
            '15'=>'acos',
            '16'=>'raos',
        ];
        $sortTypes = [
            'asc'=>SORT_ASC,
            'desc'=>SORT_DESC,
        ];
        $field = array_column($datas,array_get($sortFields,array_get($request->get('order'),'0.column')));
        array_multisort($field,array_get($sortTypes,array_get($request->get('order'),'0.dir')),$datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $suggested = array_get($suggestedBid,array_get($datas,$i.'.keywordText').array_get($datas,$i.'.matchType'));
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.keywordId').'"  />',
                array_get($datas,$i.'.state'),
                array_get($datas,$i.'.keywordText')
                .' <a data-target="#ajax" data-toggle="modal" href="/adv/scheduleEdit?profile_id='.$profile_id.'&ad_type='.$request->get('ad_type').'&campaign_id='.array_get($datas,$i.'.campaignId').'&record_type=keyword&record_type_id='.array_get($datas,$i.'.keywordId').'&record_name='.urlencode(array_get($datas,$i.'.keywordText').' - '.array_get($datas,$i.'.matchType')).'&bid='.array_get($datas,$i.'.bid').'" class="badge badge-success"> Scheduled </a>',
                array_get($datas,$i.'.matchType'),
                array_get($datas,$i.'.servingStatus'),
                array_get($suggested,'suggested')?array_get($suggested,'suggested').'<BR>'.array_get($suggested,'rangeStart').' - '.array_get($suggested,'rangeEnd'):'-',
                '<button type="button" class="ajax_bid btn default" data-pk="bid" id="'.array_get($datas,$i.'.keywordId').'">'.round(array_get($datas,$i.'.bid'),2).'</button>',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.impressions')>0)?array_get($reportData,array_get($datas,$i.'.keywordId').'.impressions'):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.clicks')>0)?array_get($reportData,array_get($datas,$i.'.keywordId').'.clicks'):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.ctr')>0)?((array_get($reportData,array_get($datas,$i.'.keywordId').'.ctr')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.cost')>0)?array_get($reportData,array_get($datas,$i.'.keywordId').'.cost'):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.cpc')>0)?round(array_get($reportData,array_get($datas,$i.'.keywordId').'.cpc'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.attributed_units_ordered1d')>0)?array_get($reportData,array_get($datas,$i.'.keywordId').'.attributed_units_ordered1d'):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.attributed_sales1d')>0)?round(array_get($reportData,array_get($datas,$i.'.keywordId').'.attributed_sales1d'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.acos')>0)?((array_get($reportData,array_get($datas,$i.'.keywordId').'.acos')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.keywordId').'.raos')>0)?array_get($reportData,array_get($datas,$i.'.keywordId').'.raos'):'-',
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        $records["recordsForChart"] = $chartData;
        echo json_encode($records);
    }


    public function listProducts(Request $request)
    {
        $datas = $reportData = $ids = $chartData = [];
        $profile_id = $request->get('profile_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $name = $request->get('name');            
        $client = new PpcRequest($profile_id);
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        if($request->get('ad_type')=='SProducts') $result = $app->product_targeting->listTargetingClausesEx($params);
        if($request->get('ad_type')=='SDisplay') $result = $app->targeting->listTargetingClausesEx($params);
        if($request->get('ad_type')=='SBrands'){
            $params = [];
            $params['nextToken'] ='';
            $params['maxResults'] = 5000;
            if($request->get('stateFilter')) $params['filters'][] = ['filterType'=>'TARGETING_STATE','values'=>[$request->get('stateFilter')]];
            if($request->get('campaign_id')) $params['filters'][] = ['filterType'=>'CAMPAIGN_ID','values'=>[$request->get('campaign_id')]];
            if($request->get('adgroup_id')) $params['filters'][] = ['filterType'=>'AD_GROUP_ID','values'=>[$request->get('adgroup_id')]];
            $result = $app->product_targeting->listTargetingClauses($params);
        }
        $defaultBid = 0;
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
            if($request->get('ad_type')=='SBrands') $datas = array_get($datas,'targets');
        }
        $tmp = $tmpExpression = [];
        if($request->get('ad_type')!='SBrands'){
            $adgroup = $app->groups->getAdGroupEx($request->get('adgroup_id'));
            $defaultBid = round(array_get($adgroup,'response.defaultBid'),2);
        }
        
        foreach($datas as $data){
            if($name && (strpos($data['expression'][0]['value'],$name) === false)) continue;
            $tmp[$data['targetId']]=[
                'campaignId'=>array_get($data,'campaignId'),
                'targetId'=>$data['targetId'],
                'state'=>$data['state'],
                'bid'=>round(array_get($data,'bid'),2),
                'type'=>array_get($data,'expression.0.type'),
                'value'=>array_get($data,'expression.0.value'),
                'expressionType'=>array_get($data,'expressionType'),
                'servingStatus'=>array_get($data,'servingStatus'),
            ];
            if($request->get('ad_type')=='SProducts') $tmpExpression[]=array_get($data,'expression');
            if($request->get('ad_type')=='SDisplay'){
                $tmpExpression['targetingClauses'][]['targetingClause']=[
                    'expressionType'=>array_get($data,'expressionType'),
                    'expression'=>array_get($data,'expression')
                ];
            }
            if($request->get('ad_type')=='SBrands'){
                $tmpExpression['targets'][][]=[
                    'type'=>array_get($data,'expression.0.type'),
                    'value'=>array_get($data,'expression.0.value')
                ];
            }
            $ids[] = $data['targetId']; 
        }
        
        $datas = $tmp;
        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
            $suggestedBid = [];
            if($request->get('ad_type')=='SProducts'){
                $chunk_result = array_chunk($tmpExpression, 10);
                foreach($chunk_result as $chunk_value){
                    $re['adGroupId'] =  $request->get('adgroup_id');
                    $re['expressions'] =  $chunk_value;
                    $results = $app->bidding->getBidRecommendations($re);
                    if(!empty(array_get($results,'response.recommendations'))){
                        foreach(array_get($results,'response.recommendations') as $k=>$v){
                            $suggestedBid[array_get($v,'expression.0.type').array_get($v,'expression.0.value')] = array_get($v,'suggestedBid');
                        }
                    }
                }   
            }
            if($request->get('ad_type')=='SDisplay'){
                $result = $app->product_ads->listProductAds([
                    'campaignIdFilter'=>$request->get('campaign_id'),
                    'adGroupIdFilter'=>$request->get('adgroup_id'),
                ]);
                if(array_get($result,'success')==1){
                    foreach(array_get($result,'response') as $product){
                        $tmpExpression['products'][]=['asin'=>$product['asin']];
                    }
                }
                //$results = $app->bid->getBidRecommendations($tmpExpression);
            }

            if($request->get('ad_type')=='SBrands'){
                $tmpExpression['adFormat'] =  $request->get('adFormat');
                $tmpExpression['campaignId'] =  $request->get('campaign_id');
                $results = $app->bid->bidRecommendations($tmpExpression);
                if(!empty(array_get($results,'response.targetsBidsRecommendationSuccessResults'))){
                    foreach(array_get($results,'response.targetsBidsRecommendationSuccessResults') as $k=>$v){
                        $suggestedBid[array_get($v,'targets.0.type').array_get($v,'targets.0.value')] = array_get($v,'recommendedBid');
                        $suggestedBid[array_get($v,'targets.0.type').array_get($v,'targets.0.value')]['suggested'] = array_get($v,'recommendedBid.recommended');
                    }
                }
            }
            
            $reportData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'target',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'record_type_id',
                    'where'=>$ids?['record_type_id'=>$ids]:[],
                ]
            );

            $chartData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'target',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'date',
                    'where'=>$ids?['record_type_id'=>$ids]:[],
                ]
            );
        }
        foreach($datas as $key=>$val){
            $datas[$key]= array_merge($datas[$key],array_get($reportData,$key,[
                'impressions'=>0,
                'clicks'=>0,
                'ctr'=>0,
                'cost'=>0,
                'cpc'=>0,
                'attributed_units_ordered1d'=>0,
                'attributed_sales1d'=>0,
                'acos'=>0,
                'raos'=>0,
            ]));
        }
        $sortFields = [
            '8'=>'impressions',
            '9'=>'clicks',
            '10'=>'ctr',
            '11'=>'cost',
            '12'=>'cpc',
            '13'=>'attributed_units_ordered1d',
            '14'=>'attributed_sales1d',
            '15'=>'acos',
            '16'=>'raos',
        ];
        $sortTypes = [
            'asc'=>SORT_ASC,
            'desc'=>SORT_DESC,
        ];
        $field = array_column($datas,array_get($sortFields,array_get($request->get('order'),'0.column')));
        array_multisort($field,array_get($sortTypes,array_get($request->get('order'),'0.dir')),$datas);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $suggested = array_get($suggestedBid,array_get($datas,$i.'.type').array_get($datas,$i.'.value'));
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.targetId').'"  />',
                array_get($datas,$i.'.state'),
                array_get($datas,$i.'.value')
                .' <a data-target="#ajax" data-toggle="modal" href="/adv/scheduleEdit?profile_id='.$profile_id.'&ad_type='.$request->get('ad_type').'&campaign_id='.array_get($datas,$i.'.campaignId').'&record_type=target&record_type_id='.array_get($datas,$i.'.targetId').'&record_name='.urlencode(array_get($datas,$i.'.value').' - '.array_get($datas,$i.'.type')).'&bid='.array_get($datas,$i.'.bid').'" class="badge badge-success"> Scheduled </a>',
                array_get($datas,$i.'.type'),
                array_get($datas,$i.'.expressionType'),
                array_get($datas,$i.'.servingStatus'),
                array_get($suggested,'suggested')?array_get($suggested,'suggested').'<BR>'.array_get($suggested,'rangeStart').' - '.array_get($suggested,'rangeEnd'):'-',
                '<button type="button" class="ajax_bid btn default" data-pk="bid" id="'.array_get($datas,$i.'.targetId').'">'.(array_get($datas,$i.'.bid')>0?array_get($datas,$i.'.bid'):$defaultBid).'</button>',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.impressions')>0)?array_get($reportData,array_get($datas,$i.'.targetId').'.impressions'):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.clicks')>0)?array_get($reportData,array_get($datas,$i.'.targetId').'.clicks'):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.ctr')>0)?((array_get($reportData,array_get($datas,$i.'.targetId').'.ctr')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.cost')>0)?array_get($reportData,array_get($datas,$i.'.targetId').'.cost'):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.cpc')>0)?round(array_get($reportData,array_get($datas,$i.'.targetId').'.cpc'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.attributed_units_ordered1d')>0)?array_get($reportData,array_get($datas,$i.'.targetId').'.attributed_units_ordered1d'):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.attributed_sales1d')>0)?round(array_get($reportData,array_get($datas,$i.'.targetId').'.attributed_sales1d'),2):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.acos')>0)?((array_get($reportData,array_get($datas,$i.'.targetId').'.acos')*100).'%'):'-',
                (array_get($reportData,array_get($datas,$i.'.targetId').'.raos')>0)?array_get($reportData,array_get($datas,$i.'.targetId').'.raos'):'-',
            );
            if($i>=($iDisplayStart+$iDisplayLength)) break;
		}
        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        $records["recordsForChart"] = $chartData;
        echo json_encode($records);
    }

    public function createCampaign(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        try{
            $client = new PpcRequest($profile_id);
            $app = $client->request($ad_type);
            $data = [
                'name' => $request->get('name'),
                'state' => $request->get('state'),
                'startDate' => date('Ymd',strtotime($request->get('startDate'))),
                'endDate' => ($request->get('endDate')?date('Ymd',strtotime($request->get('endDate'))):NULL),
            ];
            if($ad_type=="SDisplay"){
                $data['budget'] = round($request->get('budget'),2);
                $data['budgetType'] = $request->get('budgetType');
                $data['costType'] = $request->get('costType');
                $data['tactic'] = $request->get('tactic');
            }
            
            if($ad_type=="SBrands"){
                $data['budget'] = round($request->get('budget'),2);
            }

            if($ad_type=="SProducts"){
                $data['campaignType'] = 'sponsoredProducts';
                $data['targetingType'] = $request->get('targetingType');
                $data['dailyBudget'] = round($request->get('dailyBudget'),2);
                //$data['premiumBidAdjustment'] = $request->get('premiumBidAdjustment');
                $data['bidding'] = [
                    'strategy'=>$request->get('strategy'),
                    'adjustments'=>[
                        [
                            'predicate'=>'placementTop',
                            'percentage'=>round($request->get('placementTop'),2),
                        ],  
                        [
                            'predicate'=>'placementProductPage',
                            'percentage'=>round($request->get('placementProductPage'),2),
                        ]
                    ],
                ];
            }
            $results = $app->campaigns->CreateCampaigns([$data]);
            $customActionMessage="";
            if(array_get($results,'success') == 1){
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=array_get($result,'code').' '.array_get($result,'description').'<BR>';
                }
            }else{
                throw new \Exception(array_get($results,'response'));
            }
        
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = $customActionMessage;     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }    
        echo json_encode($records);
    }

    public function listPortfolios(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $client = new PpcRequest($profile_id);
        $app = $client->request('BaseService'); 
        $result = $app->portfolios->listPortfoliosEx();
        $records = [];
        if(array_get($result,'success')==1){
            $records = array_get($result,'response');
        }
        echo json_encode($records);
    }


    public function batchScheduled(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $record_type = $request->get('record_type');
        $ids = $request->get('ids');
        return view('adv/schedule_batch',['profile_id'=>$profile_id,'ad_type'=>$ad_type,'campaign_id'=>$campaign_id,'record_type'=>$record_type,'ids'=>$ids]);
    }
	
    public function batchSaveScheduled(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $ids = $request->get('ids');
            if($ids){
                $ids = explode(',',$ids);
                foreach($ids as $id){
                    $data = new PpcSchedule;
                    $_id = explode('-',$id);
                    if(count($_id)>1){
                        $data->ad_type = $_id[0];
                        $data->campaign_id = $id = $_id[1];
                    }else{
                        $data->ad_type = $request->get('ad_type');
                        $data->campaign_id = $request->get('campaign_id');
                    }
                    if($data->ad_type!='SProducts') continue;
                    $fileds = array(
                        'profile_id',
                        'record_type',
                        'date_from',
                        'date_to',
                        'time',
                        'state',
                        'bid'
                    );
                    foreach($fileds as $filed){
                        $data->{$filed} = $request->get($filed);
                    }
                    $data->record_type_id = $id;
                    $data->status = 1;
                    $data->user_id = Auth::user()->id;
                    if($data->bid<=0) continue;
                    $data->save();
                }
            }
            DB::commit();
            $records["code"] = 'SUCCESS';
            $records["description"] = "更新成功!";
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["code"] = 'FAILED';
            $records["description"] = $e->getMessage();
        }
        echo json_encode($records);

    }
}