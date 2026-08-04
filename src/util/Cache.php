<?php
namespace tingyu\WeixinTransfer\util;

class Cache
{

    /**
     *
     * @param $name
     * @param $value
     * @param $expire 过期时长，0 无过期时长
     * @return bool
     */
    public function set($name, $value, $expire = 0)
    {
        $pwd = dirname(__DIR__);

        $cacheDir = $pwd . DIRECTORY_SEPARATOR . 'cache' ;
        if (!is_dir($cacheDir)) {
            try {
                mkdir($cacheDir, 0755, true);
            }catch (\Exception $e) {
                return false;
            }
        }

        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . md5($name) . '.tmp';

        $data = [
            'expireAt' => time() + $expire,
            'cache' => $value,
        ];
        $result = file_put_contents($cacheFile, serialize($data), LOCK_EX);
        if ($result === false) {
            return false;
        }

        return true;
    }

    public function get($name)
    {
        $pwd = dirname(__DIR__);

        $cacheFile = $pwd . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . md5($name) . '.tmp';

        if (!file_exists($cacheFile)) {
            return false;
        }

        $result = file_get_contents($cacheFile);
        if ($result === false) {
            return false;
        }

        $data = unserialize($result);
        if (!array_key_exists('expireAt', $data) || !array_key_exists('cache', $data)) {
            return false;
        }

        if ($data['expireAt'] > 0 && time() > $data['expireAt']) {
            return false;
        }

        return $data['cache'];

    }

}