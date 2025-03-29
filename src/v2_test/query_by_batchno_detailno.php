<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';

$apiClientKeyFile = file_get_contents('apiclient_test_key.pem');
$wechatplatfrom = file_get_contents('wechat_platfrom.pem');

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


$transfer = new \tingyu\WeixinTransfer\v2\OutDetailNoOutBatchNoQuery($conf['appid'], $conf['mchid'], $conf['api_key_v3'],  $conf['paySerial'], $apiClientKeyFile, $conf['wechat_serial_no'], $wechatplatfrom);

$data = $transfer->Exec('batch1006', 'detail1006');
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);