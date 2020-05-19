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
$result = CRest::call(
    'crm.lead.list',
    [
        order=> ["ID"=> "DESC" ],
        select=> [ "ID", "TITLE", "COMMENTS" ]
    ]
);
echo '<pre>';
var_dump($result);
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