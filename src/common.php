<?php

if (!function_exists('generate_nonce_string')) {
    function generate_nonce_string($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLen = strlen($characters);
        $result = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[ord($bytes[$i]) % $charLen];
        }
        return $result;
    }
}