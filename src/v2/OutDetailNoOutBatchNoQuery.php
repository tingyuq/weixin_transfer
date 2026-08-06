<?php

namespace tingyu\WeixinTransfer\v2;


/**
 * 商家明细单号查询明细单
 */
class OutDetailNoOutBatchNoQuery extends Base
{
    private $uri = '/v3/transfer/batches/out-batch-no/%s/details/out-detail-no/%s';


    /**
     * @param string $outBatchNo 商家批次单号
     * @param string $outDetailNo 商家明细单号
     * @return mixed
     * @throws \Exception
     */
    public function Exec($outBatchNo, $outDetailNo) {
        if (empty($outBatchNo) || empty($outDetailNo)) {
            throw new \Exception('批次单号、明细单号均不能为空');
        }


        $uri = sprintf($this->uri, $outBatchNo, $outDetailNo);
        $data = $this->execRequest(self::METHOD_GET, $uri, '');
        if (array_key_exists('code', $data)) {
            $message = array_key_exists('message', $data) ? $data['message'] : '请求报错了，微信未返回报错信息';
            throw new \Exception($message);
        }

        $data['fail_reason_info'] = null;
        if ($data['detail_status'] == 'FAIL') {
            $failReason = array_key_exists('fail_reason',$data) ? $data['fail_reason']: 'other';
            $data['fail_reason_info'] = self::FAIL_REASONS[$failReason];
        }

        return $data;
    }
}