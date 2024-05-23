<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\Models\ReplaceSku;
use Illuminate\Support\Facades\Auth;
use PDO;
use DB;
use Illuminate\Http\Response;
class ReplaceSkuController extends Controller
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
        return view('exception/replaceSkuList');
    }

    public function get(Request $request)
    {

        $datas = new ReplaceSku();
        if(array_get($_REQUEST,'keyword')){
            $datas = $datas->where('skua','like','%'.array_get($_REQUEST,'keyword').'%')->orWhere('skub','like','%'.array_get($_REQUEST,'keyword').'%');
        }
        
        $iTotalRecords = $datas->count();
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);
        $Lists =  $datas->offset($iDisplayStart)->limit($iDisplayLength)->orderBy('id','desc')->get()->toArray();
        $records["data"] = [];
		$users = getUsers();


        foreach ( $Lists as $list){
            $records["data"][] = array(
                '<input type="hidden" class="checkboxes" value="'.$list['id'].'">'.$list['skua'],
                $list['skub'],
				$list['updated_at'],
				array_get($users,$list['user_id'],$list['user_id'])
            );
		}

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function edit(Request $request,$id)
    {
        $form = [];
        if($id){
            $form = ReplaceSku::find($id)->toArray();
        }
        return view('exception/replaceSkuEdit',['form'=>$form]);
    }


    public function update(Request $request)
    {
		DB::beginTransaction();
        try{
			$id = $request->get('id');
			$db = $id?(ReplaceSku::findOrFail($id)):(new ReplaceSku);
			$db->skua = $request->get('skua');
			$db->skub = $request->get('skub');
			$db->user_id = Auth::user()->id;
            $db->save();
	        DB::commit();
            $records["customActionStatus"] = 'OK';
            $records["customActionMessage"] = "Success!";     
        }catch (\Exception $e) { 
            DB::rollBack();
            $records["customActionStatus"] = '';
            $records["customActionMessage"] = $e->getMessage();
        }
        echo json_encode($records);
    }

	public function destroy(Request $request,$id)
    {
        $db = ReplaceSku::findOrFail($id);
        $db->delete();
        $request->session()->flash('success_message','Delete Success');
        return redirect('replaceSku');
    }
}
