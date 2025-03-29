<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';


$apiClientKeyFile = file_get_contents('apiclient_test_key.pem');
$wechatplatfrom = file_get_contents('wechat_platfrom.pem');


$transfer = new \tingyu\WeixinTransfer\v2\Apply($conf['appid'], $conf['mchid'], $conf['api_key_v3'],  $conf['paySerial'], $apiClientKeyFile, $conf['wechat_serial_no'], $wechatplatfrom);

$transfer->addTransfer('detail1006','0.3', 'o5se15bHFYji9jDue8rtwGhG59NM', '丘益雄','测试');
$data = $transfer->Exec('batch1006','批次1001', '测试', 'https://api3.tianpanshiye.com/api/log/notify');
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);