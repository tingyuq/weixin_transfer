<?php

namespace tingyu\WeixinTransfer\v2;

use tingyu\HttpRequest\Method\Post;

/**
 * 发起商家转账
 */
class Apply extends Base
{
    private $uri = '/v3/transfer/batches';

    private $totalAmount = 0;
    private $totalNum = 0;

    private $transferDetailList = [];

    /**
     * 添加转账明细
     * @param string $outDetailNo 商家明细单号
     * @param float $amount 转账金额(元)
     * @param string $openId 收款用户openid
     * @param string $username 收款用户姓名
     * @param string $remark 转账备注
     * @return void
     * @throws \Exception
     */
    public function addTransfer($outDetailNo, $amount, $openId, $username, $remark) {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $outDetailNo)) {
            throw new \Exception('商家明细号由长度为8到32位的大小写字母数字组成');
        }

        if (!preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/', $amount)) {
            throw new \Exception('转账金额不合法');
        }

        $amount = bcmul($amount, '100', 2);
        $amount = intval($amount);
        if ($amount < 30) {
            throw new \Exception('转账金额最小为 0.3 元');
        }

        if ($amount >= 200000 && empty($username)) {
            throw new \Exception('单笔转账超过2000元，必须传入用户姓名');
        }

        $remarkLen = strlen($remark);
        if ($remarkLen == 0 || $remarkLen > 32) {
            throw new \Exception('转账说明必填且最长32个字符');
        }

        $data = [
            'out_detail_no' => $outDetailNo,
            'transfer_amount' => $amount,
            'transfer_remark' => $remark,
            'openid' => $openId,
        ];


        if (!empty($username)) {
            $encrypt = openssl_public_encrypt($username, $encryptedUsername, $this->wechatCertContent, OPENSSL_PKCS1_OAEP_PADDING);
            if (!$encrypt) {
                throw new \Exception('敏感信息加密失败,重试或联系技术');
            }
            $data['user_name'] = base64_encode($encryptedUsername);
        }

        $this->totalNum +=1;
        $this->totalAmount += $amount;
        $this->transferDetailList[] = $data;
    }


    /**
     * @param string $outBatchNo 商家批次单号
     * @param string $batchName 批次名称
     * @param string $batchRemark 批次备注
     * @param string $notifyUrl 异步通知地址
     * @return mixed
     * @throws \Exception
     */
    public function Exec($outBatchNo, $batchName, $batchRemark, $notifyUrl='') {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $outBatchNo)) {
            throw new \Exception('商家批次单号由长度为8到32位的大小写字母数字组成');
        }

        if (empty($batchName)) {
            throw new \Exception('批量转账名称不能为空');
        }

        $remarkLen = strlen($batchRemark);
        if ($remarkLen > 32) {
            throw new \Exception('转账说明必填且最长32个字符');
        }

        $formData = [
            'appid' => $this->appid,
            'out_batch_no' => $outBatchNo,
            'batch_name' => $batchName,
            'batch_remark' => $batchRemark,
            'total_amount' => $this->totalAmount,
            'total_num' => $this->totalNum,
            'transfer_detail_list' => $this->transferDetailList,
        ];

        if (!empty($notifyUrl)) {
            $formData['notify_url'] = $notifyUrl;
        }

        $data = $this->execRequest(new Post(), $this->uri, json_encode($formData));
        $this->init();
        if (array_key_exists('code', $data)) {
            $message = array_key_exists('message', $data) ? $data['message'] : '请求报错了，微信未返回报错信息';
            throw new \Exception($message);
        }

        return $data;
    }

    private function init() {
        $this->totalNum =  0;
        $this->totalAmount = 0;
        $this->transferDetailList = [];
    }
}