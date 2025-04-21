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