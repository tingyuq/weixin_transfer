<?php

namespace tingyu\WeixinTransfer\v3;

use tingyu\HttpRequest\Method\Get;

/**
 * 商户单号查询转账单
 */
class OutBillNoQuery extends Base
{

    private $uri = '/v3/fund-app/mch-transfer/transfer-bills/out-bill-no/%s';

    /**
     * @param string $outBillNo 商户单号
     * @return mixed
     * @throws \Exception
     */
    public function Exec($outBillNo) {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $outBillNo)) {
            throw new \Exception('商家转账单号由长度为8到32位的大小写字母数字组成');
        }

        $uri = sprintf($this->uri, $outBillNo);
        $data = $this->execRequest(new Get(), $uri, '');
        return $data;
    }
}