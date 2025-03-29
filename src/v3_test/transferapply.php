<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';

use tingyu\WeixinTransfer\v3\Apply;

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


$transfer = new Apply($conf['appid'], $conf['mchid'], $conf['paySerial'], $apiClientKeyFile);

$data = $transfer->Exec('Test10001', '1000', 'oPN6d7Th2Peuy9KexAnr_gtO4ZE0','','0.1', '测试', $info);
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);