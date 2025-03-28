<?php

namespace tingyu\WeixinTransfer\v3;

use tingyu\HttpRequest\Method\Get;

/**
 * 微信单号查询转账单
 */
class TransferBillNoQuery extends Base
{

    private $uri = '/v3/fund-app/mch-transfer/transfer-bills/transfer-bill-no/%s';

    /**
     * @param string $transferBillNo 微信单号
     * @return mixed
     * @throws \Exception
     */
    public function Exec($transferBillNo) {

        if (empty($transferBillNo)) {
            throw new \Exception('微信转账单号必传');
        }

        $uri = sprintf($this->uri, $transferBillNo);
        $data = $this->execRequest(new Get(), $uri, '');
        return $data;
    }
}