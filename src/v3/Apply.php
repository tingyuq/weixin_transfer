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

        $amount = bcmul($amount, '100',2);
        $amount = intval($amount);
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
        if ($remarkLen == 0 || $remarkLen > 32) {
            throw new \Exception('转账备注必填且最长32个字符');
        }

        switch ($sceneId) {
            case 1000:
                $sceneTitle = '现金营销';
                break;
            case 1011:
                $sceneTitle = "企业赔付";
                break;
            case 1005:
                $sceneTitle = '劳务报酬';
                break;
            case 1009:
                $sceneTitle = '货款';
                break;
            case 1010:
                $sceneTitle = "二手回收货款";
                break;
            case 1013:
                $sceneTitle = '公益补助金';
                break;
            case 1002:
                $sceneTitle = '行政补贴';
                break;
            case 1004:
                $sceneTitle = "保险理赔款";
                break;
            default:
                throw new \Exception("未支持转账场景ID:{$sceneId}，联系技术支持");
        }

        $formData = [
            'appid' => $this->appid,
            'out_bill_no' => $outBillNo,
            'transfer_scene_id' => (string)$sceneId,
            'openid' => $openId,
            'transfer_amount' => $amount,
            'transfer_remark' => $remark,
            "user_recv_perception" => $sceneTitle,
            'transfer_scene_report_infos' => $sceneInfo,
        ];

        if (!empty($userName)) {
            $encrypt = openssl_public_encrypt($userName, $encryptedUsername, $this->wechatCertContent, OPENSSL_PKCS1_OAEP_PADDING);
            if (!$encrypt) {
                throw new \Exception('敏感信息加密失败,重试或联系技术');
            }
            $formData['user_name'] = base64_encode($encryptedUsername);
        }

        if (!empty($notifyUrl)) {
            $formData['notify_url'] = $notifyUrl;
        }

        $data = $this->execRequest(new Post(), $this->uri, json_encode($formData));

        if (array_key_exists('code', $data)) {
            $message = array_key_exists('message', $data) ? $data['message'] : '请求报错了，微信未返回报错信息';
            throw new \Exception($message);
        }

        if (!array_key_exists('state', $data)) {
            throw new \Exception('微信返回值缺失"单据状态 [state]"');
        }

        if ($data['state'] == 'ACCEPTED' && (!array_key_exists('package_info', $data) || empty($data['package_info']))) {
            throw new \Exception('微信返回值缺失"收款页的package信息 [package_info]"');
        }

        $data['reason_info'] = '';
        if ($data['state'] == 'FAIL') {
            $reasonKey = array_key_exists('fail_reason', $data) ? $data['fail_reason'] : "other";
            $data['reason_info'] = $this->reason[$reasonKey];
        }

        $data['state_info'] = array_key_exists($data['state'], $this->state) ? $this->state[$data['state']] : $this->state['other'];

        return $data;
    }
}