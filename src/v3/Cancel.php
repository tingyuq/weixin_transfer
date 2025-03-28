<?php

namespace tingyu\WeixinTransfer\v3;

use tingyu\HttpRequest\Method\Post;

/**
 * 撤销转账
 */
class Cancel extends Base
{

    private $uri = '/v3/fund-app/mch-transfer/transfer-bills/out-bill-no/%s/cancel';

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
        $data = $this->execRequest(new Post(), $uri, '');
        return $data;
    }
}