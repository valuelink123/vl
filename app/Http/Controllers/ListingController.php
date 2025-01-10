<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\SapAsinMatchSku;
use App\User;
use App\Models\SellerSku;
use App\Models\ListingPatchLog;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Exception;
use Illuminate\Http\Response;
class ListingController extends Controller
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
        return view('listing/list');
    }

    public function get(Request $request)
    {

        $datas = SellerSku::leftJoin('seller_accounts',function($q){
            $q->on('seller_skus.seller_account_id', '=', 'seller_accounts.id');
        })->leftJoin('sap_asin_match_sku',function($q){
            $q->on('sap_asin_match_sku.asin', '=', 'seller_skus.asin')
            ->on('sap_asin_match_sku.seller_sku', '=', 'seller_skus.seller_sku')
            ->on('sap_asin_match_sku.marketplace_id', '=', 'seller_skus.marketplaceid');
        })->whereNotNull('attributes');
        if (Auth::user()->seller_rules) {
			$where = '1=1 '.getSellerRules(Auth::user()->seller_rules,'sap_seller_bg','sap_seller_bu');
			$datas = $datas->whereRaw($where);
		} elseif (Auth::user()->sap_seller_id) {
            $datas = $datas->where('sap_asin_match_sku.sap_seller_id',Auth::user()->sap_seller_id);
		} 
        if(array_get($_REQUEST,'marketplace_id')){
            $datas = $datas->whereIn('seller_skus.marketplaceid',array_get($_REQUEST,'marketplace_id'));
        }
        if(array_get($_REQUEST,'bg')){
            $datas = $datas->whereIn('sap_seller_bg',array_get($_REQUEST,'bg'));
        }
        if(array_get($_REQUEST,'bu')){
            $datas = $datas->whereIn('sap_seller_bu',array_get($_REQUEST,'bu'));
        }
        if(array_get($_REQUEST,'sap_seller_id')){
            $datas = $datas->whereIn('sap_seller_id',array_get($_REQUEST,'sap_seller_id'));
        }
        if(array_get($_REQUEST,'seller_id')){
            $datas = $datas->whereIn('mws_seller_id',array_get($_REQUEST,'seller_id'));
        }

        if(array_get($_REQUEST,'keyword')){
            $keyword = array_get($_REQUEST,'keyword');
            $datas = $datas->where(function ($query) use ($keyword) {
                $query->where('seller_skus.asin', 'like', '%'.$keyword.'%')
                    ->orwhere('seller_skus.seller_sku', 'like', '%'.$keyword.'%');
            });
        }
        $sellers = getUsers('sap_seller');
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('afn_sellable','desc')->get(['seller_skus.id','seller_skus.summaries','seller_skus.attributes','seller_skus.asin','seller_skus.seller_sku','seller_skus.marketplaceid','seller_skus.afn_sellable','seller_skus.listing_updated_at','seller_accounts.label','seller_accounts.mws_seller_id','sap_asin_match_sku.sap_seller_id','sap_asin_match_sku.sap_seller_bg','sap_asin_match_sku.sap_seller_bu'])->toArray();
        $records["data"] = [];

        foreach ( $lists as $list){
            $summaries = current(json_decode($list['summaries'],true));
            $attributes = json_decode($list['attributes'],true);
            $image = array_get($summaries,'mainImage.link');
            $item_name = array_get($summaries,'itemName');
            $purchasable_offers = array_get($attributes,'purchasable_offer');
            $list_price = array_get($attributes,'list_price');
            $priceStr= '';
            if(!empty($list_price)){
                $priceStr='<span class="label label-sm label-primary">List Price</span><span class="label label-sm label-default">'.array_get($list_price,'0.value','NA').'</span><span class="label label-sm label-success">'.array_get($list_price,'0.currency').'</span><BR><BR>';
            }
            if(!empty($purchasable_offers)){
                foreach($purchasable_offers as $offer){
                    $priceStr.= '<span class="label label-sm label-primary">'.array_get($offer,'audience').'</span>';

                    if(empty(array_get($offer,'discounted_price'))){
                        $priceStr.= '<span class="label label-sm label-danger">'.array_get($offer,'our_price.0.schedule.0.value_with_tax').'</span>';
                    }else{
                        $priceStr.= '<span class="label label-sm label-default" style="text-decoration: line-through;">'.array_get($offer,'our_price.0.schedule.0.value_with_tax').'</span><span class="label label-sm label-danger">'.array_get($offer,'discounted_price.0.schedule.0.value_with_tax').'</span>';
                    }
                    $priceStr.= '<span class="label label-sm label-success">'.array_get($offer,'currency').'</span><BR><BR>';
                }
            }


            $records["data"][] = array(
                '<a href="https://'.array_get(getSiteUrl(),$list['marketplaceid']).'/dp/'.$list['asin'].'?m='.$list['mws_seller_id'].'" target="_blank"><img src="'.$image.'" id="'.$list['id'].'" width=80px height=80px></a>',
                $item_name.'<BR>
                <span class="label label-sm label-primary">'.$list['label'].'</span>
                <span class="label label-sm label-success">'.array_get(array_flip(getSiteCode()),$list['marketplaceid']).'</span>
                <span class="label label-sm label-warning"><a href="https://'.array_get(getSiteUrl(),$list['marketplaceid']).'/dp/'.$list['asin'].'?m='.$list['mws_seller_id'].'" target="_blank">'.$list['asin'].'</a></span>
                <span class="label label-sm label-danger">'.$list['seller_sku'].'</span>
                </div>',
                $list['afn_sellable'],
                $priceStr,
                $list['sap_seller_bg'].$list['sap_seller_bu'].'-'.array_get($sellers,intval($list['sap_seller_id'])),
            );
            
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $sellerAccounts = DB::connection('amazon')->table('seller_accounts')->where("primary",1)->pluck('label','id')->toArray();
	    $sellers = getUsers();
		$form=$logs=[];
        if($id){
            $form = SellerSku::find($id)->toArray();
            if(!empty($form)){
                $logs = DB::connection('amazon')->table('listing_patch_logs')
                ->where("seller_account_id",$form['seller_account_id'])
                ->where("seller_sku",$form['seller_sku'])
                ->where("marketplace_id",$form['marketplaceid'])
                ->orderBy('created_at','desc')->take(10)->get();
            }
            
        }
        return view('listing/edit',['form'=>$form ,'logs'=>$logs,'sellerAccounts'=>$sellerAccounts,'sellers'=>$sellers]);
    }


    public function update(Request $request)
    {

        try{
            $id = $request->get('id');
            $form = SellerSku::findOrFail($id);
            $summaries = current(json_decode($form['summaries'],true));
            $attributes = json_decode($form['attributes'],true);
            $our_price = $discounted_price =[];
            if($request->get('start_at')) $our_price["start_at"] = ["value"=>$request->get('start_at')];
            if($request->get('end_at')) $our_price["end_at"] = ["value"=>$request->get('end_at')];

            if($request->get('discounted_price')){
                if($request->get('discounted_start_at')) $discounted_price["start_at"] = $request->get('discounted_start_at');
                if($request->get('discounted_end_at')) $discounted_price["end_at"] = $request->get('discounted_end_at');
                $discounted_price["value_with_tax"] = round($request->get('discounted_price'),2);
                $our_price['discounted_price']=[
                    [
                        "schedule"=>[
                            $discounted_price
                        ]
                    ]
                ];
            }
            

            $bodyArr = [
                'productType'=>array_get($summaries,'productType'),
                'patches'=>[
                    [
                        'op'=>'replace',
                        'path'=>'/attributes/purchasable_offer',
                        'value'=>[
                            array_merge(
                            [
                                "currency"=>array_get($attributes,'purchasable_offer.0.currency',array_get(getMarketplaceIdCur(),$form['marketplaceid'])),
                                "audience"=>"ALL",
                                "marketplace_id"=>$form['marketplaceid'],
                                "our_price"=>[
                                    [
                                        "schedule"=>[
                                            [
                                                "value_with_tax"=>round($request->get('our_price'),2) 
                                            ]
                                        ]
                                    ]
                                ]
                            ],$our_price)
                        ],
                    ]
                ]
            ];
            ListingPatchLog::create(
                [
                    'seller_account_id'=>$form['seller_account_id'],
                    'seller_sku'=>$form['seller_sku'],
                    'marketplace_id'=>$form['marketplaceid'],
                    'user_id'=>Auth::user()->id,
                    'input'=>json_encode($bodyArr),
                ]
            );
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = "添加成功, 处理价格更新任务中, 稍后查看结果！";     
        }catch (\Exception $e) { 
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }
        echo json_encode($records);
    }

}
