<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';


$apiClientKeyFile = file_get_contents('apiclient_test_key.pem');


$transfer = new \tingyu\WeixinTransfer\v2\Apply($conf['appid'], $conf['mchid'], $conf['paySerial'], $apiClientKeyFile);

$transfer->addTransfer('detail1001','0.1', '测试','o5se15bHFYji9jDue8rtwGhG59NM');
$data = $transfer->Exec('batch1001','批次1001', '测试');
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);