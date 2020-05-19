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

if(@$_GET['id']>0){
    $id = $_GET['id'];
}else{
    $id =1;
}
this.leadList($id);
function leadList($id=0){
    if($id<1000){
        $result = CRest::call(
            'crm.lead.list',
            [
                order=> ["ID"=> "ASC" ],
                filter=>['>ID'=>$id,'!SOURCE_ID'=>'1|FACEBOOK'],
                select=> [ "ID", "TITLE", "COMMENTS" ,'SOURCE_ID']
            ]
        );
        echo '<pre>';
        var_dump($result);
        echo '++++++++';
        if(!empty($result)){
            $total = $result['total'];
            $next =  $result['next'];
            if($next>1){
                $lastID = $result['result'][49]['id'];
                //this.leadList($lastID);
            }

        }
    }
}
this.leadList(1);

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