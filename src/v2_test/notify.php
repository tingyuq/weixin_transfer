<?php

require_once dirname(__DIR__) . "/../vendor/autoload.php";
require_once 'config.php';


$apiClientKeyFile = file_get_contents('apiclient_test_key.pem');


$transfer = new \tingyu\WeixinTransfer\v2\Notify($conf['appid'], $conf['mchid'], $conf['api_key_v3'], $conf['paySerial'], $apiClientKeyFile);


$json = '{
    "id": "6e51f172-3f84-5c28-827c-38eab9991628",
	"create_time": "2025-03-29T10:54:05+08:00",
	"resource_type": "encrypt-resource",
	"event_type": "MCHTRANSFER.BATCH.FINISHED",
	"summary": "商家转账批次完成通知",
	"resource": {
        "original_type": "mch_payment",
		"algorithm": "AEAD_AES_256_GCM",
		"ciphertext": "9MAVi7J5iMt4Npft6pbSnMj+\/YVy4GXSNZ8ntJ1To3Z9mC+SZW5SAdEqaIREEzURjYR5R9\/D8zAOqBcok\/M08d8vrjHVmweJifvIb5DHhTfgdVB3eHUqY77mT2SZuINuRQhxC8ma4bx\/FE3d6WDF+4gcHTcWzB6g7WqnklD7CiULTUAm6ldeRYQMDdeyBXHzNZH\/Pd+ODndlU9kMXlKg3hzAT+1apIofppds\/cb9tkBLIRZoxN9DArK81yMkv\/HuxKxbbh+WnMJCKJR+SqZmLHcyTAk3BmhgmsfG94A9DQhRlAIrmUkeED6H28o1hqA4YdBzFnN5Ex5fH\/j2KHEoqbIj6ALfFx0b2ksuugAwoPwbKJgh5+fcnCiT7+anNoO10htO14m9\/SR+k4GiqBqQRMOvkD0=",
		"associated_data": "mch_payment",
		"nonce": "gGM4EskvNnRv"
	}
}';

$arr = json_decode($json, true);

$data = $transfer->Exec($arr['resource']);
echo json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
echo PHP_EOL;