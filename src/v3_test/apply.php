<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';

use tingyu\WeixinTransfer\v3\Apply;

$apiClientKeyFile = file_get_contents('apiclient_test_key.pem');
$apiClientCertFile = file_get_contents('apiclient_test_cert.pem');


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


$transfer = new Apply();
$transfer->setAppid($conf['appid'])
    ->setMchId($conf['mchid'])
    ->setAppKeyV3($conf['api_key_v3'])
    ->setSerialNo($conf['paySerial'])
    ->setApiClientKeyContent($apiClientCertFile);

$data = $transfer->Exec('Test10015', '1000', 'oPN6d7Th2Peuy9KexAnr_gtO4ZE0','丘益雄','0.3', '测试', $info, 'https://api3.tianpanshiye.com/api/log/notify');
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);