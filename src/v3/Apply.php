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
     * @param float $amount 转账金额（元）
     * @param string $openId 用户在商户appid下的唯一标识
     * @param string $userName 收款方真实姓名， 标准RSA算法 内部完成加密， 转账金额 >= 2,000元时，该笔明细必须填写
     * @param string $remark  转账备注， 最长32字符
     * @param int $sceneId 转账场景，转账平台中申请
     * @param array $sceneInfo 转账场景，参考微信文档
     * @param string $notifyUrl 回调地址
     * @return mixed
     * @throws \Exception
     */
    public function Exec($outBillNo, $amount, $openId, $userName, $remark, $sceneId, $sceneInfo, $notifyUrl) {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $outBillNo)) {
            throw new \Exception('商家转账单号由长度为8到32位的大小写字母数字组成');
        }

        if (!preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/', $amount)) {
            throw new \Exception('转账金额不合法');
        }

        $amount = intval($amount * 100);
        if ($amount < 30) {
            throw new \Exception('转账金额最小为 0.3 元');
        }

        /**
         * 当单笔转账金额在0.3元（含）至2,000元（含）之间时，商户可以自主决定是否传入用户姓名。当单笔转账金额低于0.3元时，不支持传入用户姓名校验。
         * 当单笔转账金额超过2,000元时，为保障商户出资金安全，必须传入用户姓名进行校验。
         */
        if ($amount > 200000 && empty($userName)) {
            throw new \Exception('单笔转账超过2000元，必须传入用户姓名');
        }

        if (empty($openId)) {
            throw new \Exception("用户唯一标识openid不能为空");
        }

        if (empty($sceneId)) {
            throw new \Exception('转账场景不能为空');
        }

        $remarkLen = strlen($remark);
        if ($remarkLen< 0 || $remarkLen > 32) {
            throw new \Exception('转账备注必填且最长32个字符');
        }

        $formData = [
            'appid' => $this->appid,
            'out_bill_no' => $outBillNo,
            'transfer_scene_id' => $sceneId,
            'openid' => $openId,
            'transfer_amount' => $amount,
            'transfer_remark' => $remark,
            "user_recv_perception" => "现金奖励",
            'transfer_scene_report_infos' => $sceneInfo,
        ];

        if (!empty($userName)) {
            openssl_public_encrypt($userName, $encryptedUsername, $this->wechatCertContent, OPENSSL_PKCS1_OAEP_PADDING);
            $formData['user_name'] = base64_encode($encryptedUsername);
        }

        if (!empty($notifyUrl)) {
            $formData['notify_url'] = $notifyUrl;
        }

        $data = $this->execRequest(new Post(), $this->uri, json_encode($formData));

        $msg = '';
        switch ($data['state']) {
            case 'PROCESSING':
                $msg = '转账锁定资金中';
                break;
            case 'WAIT_USER_CONFIRM':
                $msg = ' 待收款用户确认';
                break;
            case 'TRANSFERING':
                $msg = '转账中';
                break;
            case 'FAIL':
                $msg = '转账失败';
                break;
            case 'CANCELING':
                $msg = '商户撤销请求受理成功，该笔转账正在撤销中';
                break;
            case 'CANCELLED':
                $msg = '转账撤销完成';
                break;
            case 'SUCCESS':
                $msg = '该单号已经转账成功，请更换的单号';
                break;
        }

        if (!isset($result['package_info']) || empty($result['package_info'])) {
            $msg = '发起提现异常，缺失用户确认参数';
        }

        if ($msg != '') {
            throw new \Exception($msg);
        }

        return $data;
    }
}