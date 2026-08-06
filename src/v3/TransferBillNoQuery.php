<?php

namespace tingyu\WeixinTransfer\v3;

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
        $data = $this->execRequest(self::METHOD_GET, $uri, '');
        if (array_key_exists('code', $data)) {
            $message = array_key_exists('message', $data) ? $data['message'] : '请求报错了，微信未返回报错信息';
            throw new \Exception($message);
        }

        if (!array_key_exists('state', $data)) {
            throw new \Exception('微信返回值缺失"单据状态 [state]"');
        }
        $data['state_info'] = array_key_exists($data['state'], $this->state) ? $this->state[$data['state']] : $this->state['other'];

        $data['reason_info'] = '';
        if ($data['state'] == 'FAIL') {
            $reasonKey = array_key_exists('fail_reason', $data) ? $data['fail_reason'] : "other";
            $data['reason_info'] = $this->reason[$reasonKey];
        }

        return $data;
    }
}