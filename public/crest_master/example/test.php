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
$result1 = CRest::call(
    'crm.lead.list',
    [

    ]
);
echo '<pre>';
var_dump($result1);
?>