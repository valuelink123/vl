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
        $profiles = PpcProfile::whereIn('account_id',[15373,14765])->get();
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
        $client->refreshToken();
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
                '-',
                array_get(PpcProfile::BIDDING,array_get($datas,$i.'.bidding.strategy','legacyForSales')),
                date('Y-m-d',strtotime(array_get($datas,$i.'.startDate'))),
                array_get($datas,$i.'.endDate')?date('Y-m-d',strtotime(array_get($datas,$i.'.endDate'))):'',
                (array_get($datas,$i.'.dailyBudget')??array_get($datas,$i.'.budget')).' - '.(array_get($datas,$i.'.budgetType')??'daily'),
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
            $client->refreshToken();
            foreach($datas as $k=>$data){
                $app = $client->request($k);
                $results = $app->campaigns->updateCampaigns($data);
                foreach(array_get($results,'response') as $result){
                    $customActionMessage.=$result['campaignId'].' - '.$result['code'].'<BR>';
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
        $client->refreshToken();
        $app = $client->request($ad_type);
        $result = $app->campaigns->getCampaignEx($campaign_id);
        if(array_get($result,'success')==1){
            $campaign =array_get($result,'response');
        }
        return view('adv/campaign_'.strtolower($ad_type).'_'.$tab,['profile_id'=>$profile_id,'ad_type'=>$ad_type,'campaign'=>$campaign]); 
    }

    public function updateCampaign(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $client = new PpcRequest($profile_id);
        $client->refreshToken();
        $app = $client->request($ad_type);
        
        $data = [
            'campaignId' => $request->get('campaign_id'),
            'name' => $request->get('name'),
            'state' => $request->get('state'),
            'startDate' => date('Ymd',strtotime($request->get('startDate'))),
            'endDate' => ($request->get('endDate')?date('Ymd',strtotime($request->get('endDate'))):NULL),
        ];
        if($request->has('dailyBudget')) $data['dailyBudget'] = $request->get('dailyBudget');
        if($request->has('budget')) $data['budget'] = $request->get('budget');
        if($request->has('bidOptimization')) $data['bidOptimization'] = $request->get('bidOptimization');
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
                $datas[]=[
                    $id_type=>$id,
                    'state'=>$status,
                ];
            }
            $client = new PpcRequest($profile_id);
            $client->refreshToken();
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);
            foreach(array_get($results,'response') as $result){
                $customActionMessage.=$id_type.'-'.$result[$id_type].' '.$result['code'].'<BR>';
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
        $client->refreshToken();
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
            foreach($keywords as $keyword){
                $data = [
                    'campaignId'=>$campaignId,
                    'state'=>'enabled',
                    'keywordText'=>$keyword,
                    'matchType'=>$match_type
                ];
                if($adGroupId) $data['adGroupId'] = $adGroupId;
                $datas[]=$data;
            }
            $client = new PpcRequest($profile_id);
            $client->refreshToken();
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);
            foreach(array_get($results,'response') as $result){
                $customActionMessage.=$result['keywordId'].' - '.$result['code'].'<BR>';
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
        $client->refreshToken();
        $params['stateFilter'] = 'enabled';
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->$action->$method($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
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
                    'campaignId'=>$campaignId,
                    'state'=>'enabled',
                    'expression'=>[
                        [
                            'type'=>'asinSameAs',
                            'value'=>$keyword,
                        ]
                    ],
                    'expressionType'=>'manual',
                ];
                if($adGroupId) $data['adGroupId'] = $adGroupId;
                $datas[]=$data;
            }
            $client = new PpcRequest($profile_id);
            $client->refreshToken();
            $app = $client->request($ad_type);
            $results = $app->$action->$method($datas);
            foreach(array_get($results,'response') as $result){
                $customActionMessage.=$result['targetId'].' - '.$result['code'].'<BR>';
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
        $client->refreshToken();
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
                (array_get($datas,$i.'.defaultBid')??array_get($datas,$i.'.defaultBid')),
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
        $client->refreshToken();
        $app = $client->request($ad_type);
        $result = $app->groups->getAdGroupEx($adgroup_id);
        if(array_get($result,'success')==1){
            $adgroup =array_get($result,'response');
            if($ad_type=='SProducts') $adgroup['suggestedBid'] = $app->bidding->getAdGroupBidRecommendations($adgroup_id);
        }
        return view('adv/adgroup_'.strtolower($ad_type).'_'.$tab,['profile_id'=>$profile_id,'ad_type'=>$ad_type,'adgroup'=>$adgroup]); 
    }

    public function updateAdGroup(Request $request)
    {
        
        $profile_id = $request->get('profile_id');
        $ad_type = $request->get('ad_type');
        $campaign_id = $request->get('campaign_id');
        $adgroup_id = $request->get('adgroup_id');
        $client = new PpcRequest($profile_id);
        $client->refreshToken();
        $app = $client->request($ad_type);
        $data = [
            'adGroupId' => $adgroup_id,
            'name' => $request->get('name'),
            'state' => $request->get('state'),
            'defaultBid' => $request->get('defaultBid'),
        ];

        $result = $app->groups->updateAdGroup([$data]);
        echo json_encode($result);

    }


    public function listAds(Request $request)
    {
        $datas = $reportData = $adIds = $chartData = [];
        $profile_id = $request->get('profile_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $name = $request->get('name');            
        $client = new PpcRequest($profile_id);
        $client->refreshToken();
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
                    'record_type'=>'productAd',
                    'start_date'=>$start_date,
                    'end_date'=>$end_date,
                    'group_by'=>'record_type_id',
                    'where'=>$adIds?['record_type_id'=>$adIds]:[],
                ]
            );

            $chartData = $this->getReportData(
                [
                    'profile_id'=>$profile_id,
                    'record_type'=>'productAd',
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
        $client->refreshToken();
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->keywords->listBiddableKeywords($params);
        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }

        if($name){
            $tmp=[];
            foreach($datas as $data){
                if(strpos($data['keywordText'],$name) !== false){ 
                    $tmp[] = $data;
                    $ids[] = $data['keywordId'];
                }
            }
            $datas = $tmp;
        }else{
            foreach($datas as $data){
                $ids[] = $data['keywordId']; 
            }
        }
        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
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
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.keywordId').'"  />',
                '<a href="https://www.amazon.com/dp/'.array_get($datas,$i.'.asin').'">'.array_get($datas,$i.'.asin').'</a>',
                array_get($datas,$i.'.keywordText'),
                array_get($datas,$i.'.matchType'),
                array_get($datas,$i.'.state'),
                array_get($datas,$i.'.bid'),
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
        $client->refreshToken();
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('campaign_id')) $params['campaignIdFilter'] = $request->get('campaign_id');
        if($request->get('adgroup_id')) $params['adGroupIdFilter'] = $request->get('adgroup_id');
        //if($name) $params['name'] = $name;
        $app = $client->request($request->get('ad_type'));
        $result = $app->product_targeting->listTargetingClauses($params);

        if(array_get($result,'success')==1){
            $datas = array_get($result,'response');
        }
        $tmp=[];
        if($name){
            foreach($datas as $data){
                if(strpos($data['expression'][0]['value'],$name) !== false){ 
                    $tmp[]=[
                        'targetId'=>$data['targetId'],
                        'state'=>$data['state'],
                        'bid'=>$data['bid'],
                        'type'=>array_get($data,'expression.0.type'),
                        'value'=>array_get($data,'expression.0.value'),
                        'expressionType'=>array_get($data,'expressionType'),
                    ];
                    $ids[] = $data['targetId']; 
                }
            }
            $datas = $tmp;
        }else{
            foreach($datas as $data){
                $tmp[]=[
                    'targetId'=>$data['targetId'],
                    'state'=>$data['state'],
                    'bid'=>$data['bid'],
                    'type'=>array_get($data,'expression.0.type'),
                    'value'=>array_get($data,'expression.0.value'),
                    'expressionType'=>array_get($data,'expressionType'),
                    
                ];
                $ids[] = $data['targetId']; 
            }
            $datas = $tmp;
        }
        $iTotalRecords = count($datas);
        if($iTotalRecords>0) {
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
            $records["data"][] = array(
                '<input name="id[]" type="checkbox" class="checkboxes" value="'.array_get($datas,$i.'.targetId').'"  />',
                array_get($datas,$i.'.value'),
                array_get($datas,$i.'.type'),
                array_get($datas,$i.'.expressionType'),
                array_get($datas,$i.'.state'),
                array_get($datas,$i.'.bid'),
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
 
        $client = new PpcRequest($profile_id);
        $client->refreshToken();
        $app = $client->request($request->get('adType'));
        $params = [];
        if($request->get('stateFilter')) $params['stateFilter'] = $request->get('stateFilter');
        if($request->get('name')) $params['name'] = $request->get('name');
        $result = $app->campaigns->listCampaigns($params);
        $records = [];
        if(array_get($result,'success')==1){
            $records = array_get($result,'response');
        }
        echo json_encode($records);
    }

    public function listPortfolios(Request $request)
    {
        $profile_id = $request->get('profile_id');
        $client = new PpcRequest($profile_id);
        $client->refreshToken();
        $app = $client->request('BaseService'); 
        $result = $app->portfolios->listPortfoliosEx();
        $records = [];
        if(array_get($result,'success')==1){
            $records = array_get($result,'response');
        }
        echo json_encode($records);
    }


    public function edit(Request $request,$id)
    {
        $form =  GiftCard::with('exception')->where('id',$id)->first()->toArray();
        if(empty($form)) die('不存在!');    
        return view('giftcard/edit',['form'=>$form]);
    }

    public function create()
    {  
        return view('giftcard/edit',['form'=>[]]);
    }
	
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{ 
            $id = intval($request->get('id'));
            $data = $id?(GiftCard::where('status',0)->findOrFail($id)):(new GiftCard);
            $fileds = array(
                'bg','bu','code','amount','currency'
            );
            foreach($fileds as $filed){
                $data->{$filed} = $request->get($filed);
            }
            $data->user_id = Auth::user()->id;
            $data->save();
            DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = "更新成功!";
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }
        echo json_encode($records);
    }

    public function export(Request $request){
		$arrayData = array();
		foreach($datas as $data){
            $arrayData[] = array(
            );
		}
		if($arrayData){
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->fromArray($arrayData,NULL, 'A1' );
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="campaign.xlsx"');
			$writer = new Xlsx($spreadsheet);
			$writer->save('php://output');
		}
		die();
	}
}