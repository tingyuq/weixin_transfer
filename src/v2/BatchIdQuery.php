<?php

namespace tingyu\WeixinTransfer\v2;

use tingyu\HttpRequest\Method\Get;

/**
 * 微信批次单号查询批次单
 */
class BatchIdQuery extends Base
{
    private $uri = '/v3/transfer/batches/batch-id/%s?%s';

    private $closeReason = [
        'OVERDUE_CLOSE' => '系统超时关闭，可能原因账户余额不足或其他错误',
        'TRANSFER_SCENE_INVALID' => '付款确认时，转账场景已不可用，系统做关单处理',
        'other' => '微信转账批次关闭，原因未知，详情登录商户号查询'
    ];

    /**
     * @param string $batchId 微信批次单号
     * @param bool $needDetail 是否查询转账明细单,true-是；false-否
     * @param string $detailStatus 明细状态,$needDetail = true 时必填，取：WAIT_PAY，ALL，SUCCESS，FAIL
     * @param int $offset 请求资源起始位置
     * @param int $limit 最大资源条数
     * @return mixed
     * @throws \Exception
     */
    public function Exec($batchId, $needDetail,$detailStatus = '', $offset = 0, $limit = 20 ) {
        if (empty($batchId)) {
            throw new \Exception('微信批次单号不能为空');
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
        $uri = sprintf($this->uri, $batchId, $queryStr);
        $data = $this->execRequest(new Get(), $uri, '');
        if ($data['transfer_batch']['batch_status'] == 'CLOSED') {
            $closeReason = array_key_exists('close_reason', $data['transfer_batch']) ? $data['transfer_batch']['close_reason'] : 'other';
            throw new \Exception($this->closeReason[$closeReason]);
        }

        return $data;
    }
}