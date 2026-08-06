<?php

namespace tingyu\WeixinTransfer\v2;

/**
 * 商家批次单号查询批次单
 */
class OutBatchNoQuery extends Base
{
    private $uri = '/v3/transfer/batches/out-batch-no/%s?%s';



    /**
     * @param string $outBatchNo  商家批次单号
     * @param bool $needDetail 是否查询转账明细单,true-是；false-否
     * @param string $detailStatus 明细状态,$needDetail = true 时必填，取：WAIT_PAY，ALL，SUCCESS，FAIL
     * @param int $offset 请求资源起始位置
     * @param int $limit 最大资源条数
     * @return mixed
     * @throws \Exception
     */
    public function Exec($outBatchNo, $needDetail, $detailStatus = '', $offset = 0, $limit = 20) {
        if (empty($outBatchNo)) {
            throw new \Exception('商家批次单号不能为空');
        }

        if (!empty($detailStatus) && !in_array($detailStatus, ['WAIT_PAY', 'ALL', 'SUCCESS', 'FAIL'])) {
            throw new \Exception('明细状态不合法，仅支持 WAIT_PAY 待确认, ALL 所有, SUCCESS 转账成功, FAIL 转账失败');
        }

        if ($needDetail && $detailStatus == '') {
            throw new \Exception('当需要查询转账明细单是，明细状态是必填的');
        }

        $param = [
            'need_query_detail' => $needDetail,
            'offset' => $offset,
            'limit' => $limit,
        ];

        if (!empty($detailStatus)) {
            $param['detail_status'] = $detailStatus;
        }

        $queryStr = http_build_query($param);
        $uri = sprintf($this->uri, $outBatchNo, $queryStr);
        $data = $this->execRequest(self::METHOD_GET, $uri, '');
        if (array_key_exists('code', $data)) {
            $message = array_key_exists('message', $data) ? $data['message'] : '请求报错了，微信未返回报错信息';
            throw new \Exception($message);
        }

        $data['transfer_batch']['batch_status_info'] = null;
        if ($data['transfer_batch']['batch_status'] == 'CLOSED') {
            $closeReason = array_key_exists('close_reason', $data['transfer_batch']) ? $data['transfer_batch']['close_reason'] : 'other';
            $data['transfer_batch']['batch_status_info'] = self::FAIL_REASONS[$closeReason];
        }



        return $data;
    }
}