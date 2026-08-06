<?php

namespace tingyu\WeixinTransfer\v2;

/**
 * 微信明细单号查询明细单
 */
class DetailIdBatchIdQuery extends Base
{
    private $uri = '/v3/transfer/batches/batch-id/%s/details/detail-id/%s';



    /**
     * @param string $batchId 微信批次单号
     * @param string $detailId 微信明细单号
     * @return mixed
     * @throws \Exception
     */
    public function Exec($batchId, $detailId) {
        if (empty($batchId)) {
            throw new \Exception('微信批次单号不能为空');
        }
        if (empty($detailId)) {
            throw new \Exception('微信明细单号不能为空');
        }


        $uri = sprintf($this->uri, $batchId, $detailId);
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