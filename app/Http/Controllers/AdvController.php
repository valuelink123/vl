<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\PpcProfile;
use App\Models\PpcReportData;
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
        if($name){
            $tmp=[];
            foreach($datas as $data){
                if(strpos($data['name'],$name) !== false){ 
                    $tmp[] = $data;
                    $campaignIds[] = $data['campaignId'];
                }
            }
            $datas = $tmp;
        }
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
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);        
        $records["data"] = array();
		for($i=$iDisplayStart;$i<=($iTotalRecords-1);$i++){
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'-'.array_get($datas,$i.'.campaignId').'"  />',
                array_get($datas,$i.'.state'),
                '<a href="/adv/campaign/'.$profile_id.'/'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'/'.array_get($datas,$i.'.campaignId').'/setting">'.array_get($datas,$i.'.name').'</a>',
                array_get($datas,$i.'.servingStatus'),
                (array_get($datas,$i.'.campaignType')??(array_get($datas,$i.'.adFormat')?'Sponsored Brands':'Sponsored Display')).' - '.(array_get($datas,$i.'.targetingType')??'manual'),
                array_get(PpcProfile::BIDDING,array_get($datas,$i.'.bidding.strategy','legacyForSales')),
                date('Y-m-d',strtotime(array_get($datas,$i.'.startDate'))),
                array_get($datas,$i.'.endDate')?date('Y-m-d',strtotime(array_get($datas,$i.'.endDate'))):'',
                '<button type="button" class="ajax_bid btn default" data-pk="'.(array_get($datas,$i.'.dailyBudget')?'dailyBudget':'budget').'" id="'.(array_get($datas,$i.'.campaignType')?'SProducts':(array_get($datas,$i.'.adFormat')?'SBrands':'SDisplay')).'-'.array_get($datas,$i.'.campaignId').'">'.((array_get($datas,$i.'.dailyBudget')??array_get($datas,$i.'.budget'))).'</button>'.(array_get($datas,$i.'.budgetType')??'daily'),
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

    public function editCampaign(Request $request,$profile_id,$ad_type,$campaign_id,$tab)
    {
        $client = new PpcRequest($profile_id);
        $app = $client->request($ad_type);
        $result = $app->campaigns->getCampaignEx($campaign_id);
        $campaign = $suggestedKeywords = $suggestedProducts = $suggestedCategories = [];
        if(array_get($result,'success')==1){
            $campaign =array_get($result,'response');
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
        return view('adv/campaign_'.strtolower($ad_type).'_'.$tab,['profile_id'=>$profile_id,'ad_type'=>$ad_type,'campaign'=>$campaign,'suggestedKeywords'=>$suggestedKeywords,'suggestedProducts'=>$suggestedProducts,'suggestedCategories'=>$suggestedCategories]); 
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
            $customActionMessage="";
            $datas=[];
            $expressions = $request->get('expressions');
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
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->groups->listAdGroupsEx($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }

        if($name){
            $tmp=[];
            foreach($datas as $data){
                if(strpos($data['name'],$name) !== false){ 
                    $tmp[] = $data;
                    $adgroupIds[] = $data['adGroupId'];
                }
            }
            $datas = $tmp;
        }else{
            foreach($datas as $data){
                $adgroupIds[] = $data['adGroupId']; 
            }

        }
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
                '<a href="/adv/adgroup/'.$profile_id.'/'.$request->get('ad_type').'/'.array_get($datas,$i.'.adGroupId').'/setting">'.array_get($datas,$i.'.name').'</a>',
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
        $adgroup = $suggestedKeywords = $suggestedProducts = $suggestedCategories = [];
        if(array_get($result,'success')==1){
            $adgroup =array_get($result,'response');

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
        }
        return view('adv/adgroup_'.strtolower($ad_type).'_'.$tab,['profile_id'=>$profile_id,'ad_type'=>$ad_type,'adgroup'=>$adgroup,'suggestedKeywords'=>$suggestedKeywords,'suggestedProducts'=>$suggestedProducts,'suggestedCategories'=>$suggestedCategories]); 
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
        if($name){
            $tmp=[];
            foreach($datas as $data){
                if(strpos($data['keywordText'],$name) !== false){ 
                    $tmp[] = $data;
                    $ids[] = $data['keywordId'];
                    $tmpExpression[]=[
                        $field=>array_get($data,'keywordText'),
                        'matchType'=>array_get($data,'matchType'),
                    ];
                }
            }
            $datas = $tmp;
        }else{
            foreach($datas as $data){
                $ids[] = $data['keywordId'];
                $tmpExpression[]=[
                    $field=>array_get($data,'keywordText'),
                    'matchType'=>array_get($data,'matchType'),
                ];
            }
        }
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
                array_get($datas,$i.'.keywordText'),
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
        

        if($name){
            foreach($datas as $data){
                if(strpos($data['expression'][0]['value'],$name) !== false){ 
                    $tmp[]=[
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
            }
        }else{
            foreach($datas as $data){
                $tmp[]=[
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
                array_get($datas,$i.'.value'),
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
                            'percentage'=>$request->get('placementTop'),
                        ],  
                        [
                            'predicate'=>'placementProductPage',
                            'percentage'=>$request->get('placementProductPage'),
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
}