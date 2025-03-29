<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';

$query = new \tingyu\WeixinTransfer\v1\Query($conf['appid'], $conf['appsecret'], $conf['mchid'], "./apiclient_cert.pem", "./apiclient_key.pem");

$data = $query->exec('test10001');

var_dump($data);