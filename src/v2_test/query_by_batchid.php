<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';

$apiClientKeyFile = file_get_contents('apiclient_test_key.pem');


$info = [
    [
        'info_type' => '活动名称',
        'info_content' => '新会员有礼'
    ],
    [
        'info_type' => '奖励说明',
        'info_content' => '参与奖励'
    ]
];


$transfer = new \tingyu\WeixinTransfer\v2\BatchIdQuery($conf['appid'], $conf['mchid'], $conf['api_key_v3'], $conf['paySerial'], $apiClientKeyFile);

$data = $transfer->Exec('131000002079006350611632025032942638825863', true, 'SUCCESS');
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);