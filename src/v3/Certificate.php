<?php

namespace tingyu\WeixinTransfer\v3;

/**
 * 获取微信平台证书
 */
class Certificate extends Base
{

    /**
     * @return array
     * @throws \Exception
     */
    public function Exec() {

        return $this->getWechatSerialInfo();
    }
}