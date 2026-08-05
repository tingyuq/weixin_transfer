<?php

namespace tingyu\WeixinTransfer\tool;

trait NonceStr
{
    public function GenNonceStr($len) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLen = strlen($characters);
        $result = '';
        $bytes = random_bytes($len);
        for ($i = 0; $i < $len; $i++) {
            $result .= $characters[ord($bytes[$i]) % $charLen];
        }
        return $result;
    }
}