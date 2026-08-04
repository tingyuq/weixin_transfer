<?php

namespace tingyu\WeixinTransfer\v3;

use tingyu\WeixinTransfer\util\DecryptAes;

class Notify extends Base
{


    /*

    {
	"id": "45081f3f-291a-5906-84d8-109f4786b3b6",
	"create_time": "2025-03-29T11:54:26+08:00",
	"resource_type": "encrypt-resource",
	"event_type": "MCHTRANSFER.BILL.FINISHED",
	"summary": "\u5546\u5bb6\u8f6c\u8d26\u5355\u636e\u7ec8\u6001\u901a\u77e5",
	"resource": {
		"original_type": "mch_payment",
		"algorithm": "AEAD_AES_256_GCM",
		"ciphertext": "O7cSoaUcHh6HHAH1MB3tpxstfE04ykI8HMqbmMBwprwPQeOxgG3dDw2hbVdaSzP9rwe7FB7WIgVI+KJgSA51BhS9SNRA34g8mF4dRblDGrNGLfvkPX7S9zWEhwfeZvl8y+Y5SnPzE2g+7sRdnH\/PHmUf3YD4NF06AuH0Y3d0ZNSchigKEaKzC4yUVEHbktwUkP6KFnMVJfy3LGBYuHcRyHprWX6EOX9F+RPgC8Ua0ePDIGee\/zqxQZXZQCxGcToJfKUHjuAtGY3B11wlbGqxNqggze\/\/W3\/1d6cBjpwREbVONpGBltz0UVtiyTbNa6YXMRewJiJGS6bFdZoM4JCwv5XOn7JW+RPj+PI9koO63+EtUrJ0BRv6RjiG0rq4f64XtFuVVSmylGt6oSgYHuCiQWw=",
		"associated_data": "mch_payment",
		"nonce": "aamMyKMCoz2s"
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