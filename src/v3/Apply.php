<?php

namespace tingyu\WeixinTransfer\v3;

use tingyu\HttpRequest\Method\Post;

/**
 * 发起转账
 */
class Apply extends Base
{
    private $uri = '/v3/fund-app/mch-transfer/transfer-bills';

    /**
     * @param string $outBillNo 商家转账单号
     * @param int $transferSceneId 转账场景，转账平台中申请
     * @param string $openId 用户在商户appid下的唯一标识
     * @param string $userName 收款方真实姓名， 标准RSA算法 内部完成加密， 转账金额 >= 2,000元时，该笔明细必须填写
     * @param float $transferAmount 转账金额（元）
     * @param string $transferRemark  转账备注， 最长32字符
     * @param array $transferSceneReportInfo 转账场景，参考微信文档
     * @param string $notifyUrl 回调地址
     * @return mixed
     * @throws \Exception
     */
    public function Exec($outBillNo, $transferSceneId, $openId, $userName, $transferAmount, $transferRemark, $transferSceneReportInfo, $notifyUrl = '') {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $outBillNo)) {
            throw new \Exception('商家转账单号由长度为8到32位的大小写字母数字组成');
        }

        if (!preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/', $transferAmount)) {
            throw new \Exception('转账金额不合法');
        }
        $transferAmount *= 100;   // 元转分


        /**
         * 当单笔转账金额在0.3元（含）至2,000元（含）之间时，商户可以自主决定是否传入用户姓名。当单笔转账金额低于0.3元时，不支持传入用户姓名校验。
         * 当单笔转账金额超过2,000元时，为保障商户出资金安全，必须传入用户姓名进行校验。
         */
        if ($transferAmount < 30) {
            $userName = '';
        } else if ($transferAmount > 20000) {
            if (empty($userName)) {
                throw new \Exception('单笔转账超过2000元，必须传入用户姓名');
            }
        }

        $transferAmount = intval($transferAmount);

        if (empty($openId)) {
            throw new \Exception("用户唯一标识openid不能为空");
        }

        if (empty($transferSceneId)) {
            throw new \Exception('转账场景不能为空');
        }

        $remarkLen = strlen($transferRemark);
        if ($remarkLen< 0 || $remarkLen > 32) {
            throw new \Exception('转账备注必填且最长32个字符');
        }

        if (!empty($userName)) {
            $userName = md5($userName);  // todo 需要对姓名进行加密处理， rsa
        }

        $formData = [
            'appid' => $this->appid,
            'out_bill_no' => $outBillNo,
            'transfer_scene_id' => $transferSceneId,
            'openid' => $openId,
            'transfer_amount' => $transferAmount,
            'transfer_remark' => $transferRemark,
            "user_recv_perception" => "现金奖励",
            'transfer_scene_report_infos' => $transferSceneReportInfo,
        ];

        if (!empty($userName)) {
            $formData['user_name'] = $userName;
        }

        if (!empty($notifyUrl)) {
            $formData['notify_url'] = $notifyUrl;
        }

        $data = $this->execRequest(new Post(), $this->uri, json_encode($formData));
        return $data;
    }
}