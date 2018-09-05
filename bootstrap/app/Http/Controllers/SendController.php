<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inbox;
use App\Sendbox;
use App\Accounts;
use Illuminate\Support\Facades\Session;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\MultipleQueue;
use PDO;
use DB;
use Illuminate\Http\Response;
class SendController extends Controller
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

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('send/index');
    }

    public function create()
    {
        return view('send/add',['accounts'=>$this->getAccounts()]);
    }

    public function getAccounts(){
        $accounts = Accounts::get()->toArray();
        $accounts_array = array();
        foreach($accounts as $account){
            $accounts_array[$account['id']] = $account['account_email'];
        }
        return $accounts_array;
    }

    public function deletefile($filename){
        try {
            $filename = base64_decode($filename);
            \File::delete(public_path().$filename);
            $success = new \stdClass();
            $success->{$filename} = true;
            return \Response::json(array('files'=> array($success)), 200);
        } catch(\Exception $exception){
            // Return error
            return \Response::json($exception->getMessage(), 400);
        }
    }
    public function store(Request $request)
    {

        $file = $request->file('files');
        if($file) {
            try {
                $file_name = $file[0]->getClientOriginalName();
                $file_size = round($file[0]->getSize() / 1024);
                $file_ex = $file[0]->getClientOriginalExtension();
                $newname = date('YmdHis') . rand(1000, 9999) . '.' . $file_ex;
                $newpath = '/uploads/'.date('Ym').'/';
                $file[0]->move(public_path().$newpath,$newname);
            } catch (\Exception $exception) {
                $error = array(
                    'name' => $file[0]->getClientOriginalName(),
                    'size' => $file[0]->getSize(),
                    'error' => $exception->getMessage(),
                );
                // Return error
                return \Response::json($error, 400);
            }

            // If it now has an id, it should have been successful.
            if (file_exists(public_path().$newpath.$newname)) {
                $newurl = url($newpath . $newname);
                $success = new \stdClass();
                $success->name = $newpath . $newname;
                $success->size = $file_size;
                $success->url = $newurl;
                $success->thumbnailUrl = $newurl;
                $success->deleteUrl = url('send/deletefile/' . base64_encode($newpath . $newname));
                $success->deleteType = 'get';
                $success->fileID = $newpath . $newname;
                return \Response::json(array('files' => array($success)), 200);
            } else {
                return \Response::json('Error', 400);
            }
            return \Response::json('Error', 400);
        }

        $this->validate($request, [
            'from_address' => 'required|string',
            'to_address' => 'required|string',
            'subject' => 'required|string',
            'content' => 'required|string',
            'user_id' => 'required|int',
        ]);

        $sendbox = new Sendbox;
        $sendbox->user_id = intval(Auth::user()->id);
        $sendbox->from_address = $request->get('from_address');
        $sendbox->to_address = $request->get('to_address');
        $sendbox->subject = $request->get('subject');
        $sendbox->text_html = $request->get('content');
        $sendbox->date = date('Y-m-d H:i:s');
        $sendbox->inbox_id = $request->get('inbox_id')?intval($request->get('inbox_id')):0;
        if($request->get('fileid')) $sendbox->attachs = serialize($request->get('fileid'));
        if ($sendbox->save()) {
            $request->session()->flash('success_message','Save Email Success');
            if($request->get('inbox_id')){
                Inbox::where('id',intval($request->get('inbox_id')))->update(['reply'=>2]);
                return redirect('inbox/'.$request->get('inbox_id'));
            }else{
                return redirect('send');
            }


            return redirect('inbox/'.$request->get('inbox_id'));
        } else {
            $request->session()->flash('error_message','Set Email Failed');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {

        $email = Sendbox::where('id',$id)->first();
        if(!Auth::user()->admin){
            $email->where('user_id',$this->getUserId());
        }
        $email->toArray();
        return view('send/view',['email'=>$email,'users'=>$this->getUsers()]);
    }
    public function get()
    {
        /*
   * Paging
   */

        $orderby = 'date';
        $sort = 'desc';
        if(isset($_REQUEST['order'][0])){
            if($_REQUEST['order'][0]['column']==1) $orderby = 'from_address';
            if($_REQUEST['order'][0]['column']==2) $orderby = 'to_address';
            if($_REQUEST['order'][0]['column']==3) $orderby = 'subject';
            if($_REQUEST['order'][0]['column']==4) $orderby = 'date';
            $sort = $_REQUEST['order'][0]['dir'];
        }
        /*
        if (isset($_REQUEST["customActionType"]) && $_REQUEST["customActionType"] == "group_action") {
            Inbox::where('user_id',$this->getUserId())->whereIN('id',$_REQUEST["id"])->update(['reply'=>$_REQUEST["customActionName"]]);

            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Group action successfully has been completed. Well done!"; // pass custom message(useful for getting status of group actions)
        }
        */
        if(Auth::user()->admin){
            $customers = new Sendbox;
        }else{
            $customers = Sendbox::where('user_id',$this->getUserId());
        }


        if(array_get($_REQUEST,'status')){
            if($_REQUEST['status'] == 1) $customers = $customers->whereNull('send_date');
            if($_REQUEST['status'] == 2) $customers = $customers->whereNotNull('send_date');;
        }
        if(array_get($_REQUEST,'from_address')){
            $customers = $customers->where('from_address', 'like', '%'.$_REQUEST['from_address'].'%');
        }
        if(array_get($_REQUEST,'to_address')){
            $customers = $customers->where('to_address', 'like', '%'.$_REQUEST['to_address'].'%');
        }

        if(array_get($_REQUEST,'subject')){
            $customers = $customers->where('subject', 'like', '%'.$_REQUEST['subject'].'%');
        }
        if(array_get($_REQUEST,'date_from')){
            $customers = $customers->where('date','>=',$_REQUEST['date_from'].' 00:00:00');
        }
        if(array_get($_REQUEST,'date_to')){
            $customers = $customers->where('date','<=',$_REQUEST['date_to'].' 23:59:59');
        }
        $customersList =  $customers->orderBy($orderby,$sort)->get()->toArray();

        $iTotalRecords = count($customersList);
        $iDisplayLength = intval($_REQUEST['length']);
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($_REQUEST['start']);
        $sEcho = intval($_REQUEST['draw']);

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;


        for($i = $iDisplayStart; $i < $end; $i++) {
            $records["data"][] = array(
                '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$customersList[$i]['id'].'"/><span></span></label>',
                $customersList[$i]['from_address'],
                $customersList[$i]['to_address'],
                '<a href="/send/'.$customersList[$i]['id'].'" style="color:#333;">'.$customersList[$i]['subject'].'</a>',
                $customersList[$i]['date'],
                $customersList[$i]['send_date']?'<span class="label label-sm label-success">'.$customersList[$i]['send_date'].'</span> ':'<span class="label label-sm label-danger">Wating</span> ',
                '<a href="/send/'.$customersList[$i]['id'].'" class="btn btn-sm btn-outline grey-salsa"><i class="fa fa-search"></i> View </a>',
            );
        }



        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        echo json_encode($records);
    }

    public function getUsers(){
        $users = User::get()->toArray();
        $users_array = array();
        foreach($users as $user){
            $users_array[$user['id']] = $user['name'];
        }
        return $users_array;
    }

}