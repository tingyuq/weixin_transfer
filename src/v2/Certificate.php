<?php

namespace tingyu\WeixinTransfer\v2;

/**
 * 获取微信平台证书
 */
class Certificate extends Base
{
    private $uri = '/v3/certificates';

    /**
     * @return array
     * @throws \Exception
     */
    public function Exec() {

        return $this->getWechatSerialInfo();
    }
}