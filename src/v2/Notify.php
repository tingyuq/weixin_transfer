<?php

namespace tingyu\WeixinTransfer\v2;

use tingyu\WeixinTransfer\util\DecryptAes;

class Notify extends Base
{
/*
 回调过来的信息，进行处理即可
{
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
}

 */

    /**
     * 加密数据， resource
     * @param $resource
     * @return mixed
     * @throws \Exception
     */
    public function Exec($resource) {
        $decrypt = new DecryptAes($this->appKeyV3);

        $data = $decrypt->decryptToString($resource['associated_data'], $resource['nonce'], $resource['ciphertext']);

        $data = json_decode($data, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception('Decrypt data error');
        }

		return $data;
    }




}