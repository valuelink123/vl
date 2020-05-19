<?php
include_once ('../src/crest.php');
//$result = CRest::call(
//    'crm.lead.add',
//    [
//        'fields'=>[
//            'TITLE'=>'title123',
//            'NAME'=>'mingzi',
//            'PHONE'=>'123456',
//            'EMAIL'=>'235235@qq.com',
//            'SECOND_NAME'=>'name1241'
//        ]
//
//    ]
//);
$result=[];
function leadList($id=0){
    $result = CRest::call(
        'crm.lead.list',
        [
            order=> ["ID"=> "DESC" ],
            filter=>["!SOURCE_ID"=> NULL,"!SOURCE_ID"=> "","!SOURCE_ID"=> "1|FACEBOOK","!SOURCE_ID"=> "*|FACEBOOK"],
            select=> [ "ID", "TITLE", "COMMENTS" ,'SOURCE_ID']
        ]
    );
    echo '<pre>';
    var_dump($result);
    if(!empty($result)){
        $total = $result['total'];
        $next =  $result['next'];
        if($next>1){
            $lastID = $result['result'][49]['id'];
            //this.leadList($lastID);
        }

    }
}

this.leadList();
echo '----------------------------------------------------';
$result1 = CRest::call(
    'crm.lead.get',
    [
        'id'=>42765
    ]
);
echo '<pre>';
var_dump($result1);
?>