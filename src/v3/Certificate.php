<?php

namespace tingyu\WeixinTransfer\v3;

/**
 * 发起商家转账
 */
class Certificate extends Base
{

    /**
     * @param string $outBatchNo 商家批次单号
     * @param string $batchName 批次名称
     * @param string $batchRemark 批次备注
     * @param string $notifyUrl 异步通知地址
     * @return mixed
     * @throws \Exception
     */
    public function Exec() {

        return $this->getWechatSerialInfo();
    }
}