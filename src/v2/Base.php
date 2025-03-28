<?php

namespace tingyu\WeixinTransfer\v2;

use tingyu\HttpRequest\HttpClient;

abstract class Base
{
    protected $appid;

    protected $mchId;

    protected $apiClientKeyContent;

    protected $domain = 'https://api.mch.weixin.qq.com';

    protected $serialNo;

    protected $state = [
        'ACCEPTED'=>'转账已受理',
        'PROCESSING'=>'转账锁定资金中。如果一直停留在该状态，建议检查账户余额是否足够，如余额不足，可充值后再原单重试。',
        'WAIT_USER_CONFIRM'=>'待收款用户确认，可拉起微信收款确认页面进行收款确认',
        'TRANSFERING'=>'转账中，可拉起微信收款确认页面再次重试确认收款',
        'SUCCESS'=>'转账成功',
        'FAIL'=>'转账失败',
        'CANCELING'=>'商户撤销请求受理成功，该笔转账正在撤销中',
        'CANCELLED'=>'转账撤销完成',
        'other' => '未返回状态，请登录商户号中心查询，或联系技术支持'
    ];

    protected $reason = [
        'ACCOUNT_FROZEN' => '该用户账户被冻结',
        'ACCOUNT_NOT_EXIST' => '该用户账户不存在',
        'BANK_CARD_ACCOUNT_ABNORMAL' => '银行卡已被销户、冻结、作废、挂失等致无法入账',
        'BANK_CARD_BANK_INFO_WRONG' => '登记的银行名称或分支行信息有误',
        'BANK_CARD_CARD_INFO_WRONG' => '银行卡户名或卡号有误',
        'BANK_CARD_COLLECTIONS_ABOVE_QUOTA' => '银行卡属二/三类卡，达到收款限额无法入账',
        'BANK_CARD_PARAM_ERROR' => '用户收款卡错误，请核实信息',
        'BANK_CARD_STATUS_ABNORMAL' => '银行卡状态异常，无法入账',
        'BLOCK_B2C_USERLIMITAMOUNT_BSRULE_MONTH' => '超出用户月转账收款限额，本月不支持继续向该用户付款',
        'BLOCK_B2C_USERLIMITAMOUNT_MONTH' => '用户账户存在风险收款受限，本月不支持继续向该用户付款',
        'DAY_RECEIVED_COUNT_EXCEED' => '超过用户日收款次数，核实产品设置是否准确',
        'DAY_RECEIVED_QUOTA_EXCEED' => '超过用户日收款额度，核实产品设置是否准确',
        'EXCEEDED_ESTIMATED_AMOUNT' => '转账金额超过预约金额范围，请检查',
        'ID_CARD_NOT_CORRECT' => '收款人身份证校验不通过，请核实信息',
        'MERCHANT_REJECT' => '商户员工（转账验密人）已驳回转账',
        'MERCHANT_NOT_CONFIRM' => '商户员工（转账验密人）超时未验密',
        'NAME_NOT_CORRECT' => '收款人姓名校验不通过，请核实信息',
        'OPENID_INVALID' => 'OpenID格式错误或者不属于商家公众账号',
        'OTHER_FAIL_REASON_TYPE' => '其它失败原因',
        'OVERDUE_CLOSE' => '超过系统重试期，系统自动关闭',
        'PAYEE_ACCOUNT_ABNORMAL' => '用户账户收款异常，请联系用户完善其在微信支付的身份信息以继续收款',
        'PAYER_ACCOUNT_ABNORMAL' => '商户账户付款受限，可前往商户平台获取解除功能限制指引',
        'PRODUCT_AUTH_CHECK_FAIL' => '未开通该权限或权限被冻结，请核实产品权限状态',
        'REALNAME_ACCOUNT_RECEIVED_QUOTA_EXCEED' => '用户账户收款受限，请引导用户在微信支付查看详情',
        'REAL_NAME_CHECK_FAIL' => '收款人未实名认证，需要用户完成微信实名认证',
        'RECEIVE_ACCOUNT_NOT_CONFIGURE' => '请前往商户平台-商家转账到零钱-前往功能-转账场景中添加',
        'RESERVATION_INFO_NOT_MATCH' => '转账信息，如用户OpenID等参数，与预约时传入的信息不一致，请检查',
        'RESERVATION_SCENE_NOT_MATCH' => '该预约单的转账场景与发起转账时传入的不同，请检查',
        'RESERVATION_STATE_INVALID' => '预约转账单状态异常，请检查',
        'TRANSFER_QUOTA_EXCEED' => '超过用户单笔收款额度，核实产品设置是否准确',
        'TRANSFER_REMARK_SET_FAIL' => '转账备注设置失败， 请调整后重新再试',
        'TRANSFER_RISK' => '该笔转账可能存在风险，已被微信拦截',
        'TRANSFER_SCENE_INVALID' => '你尚未获取该转账场景，请确认转账场景ID是否正确',
        'TRANSFER_SCENE_UNAVAILABLE' => '该转账场景暂不可用，请确认转账场景ID是否正确',
        'other' => '微信未返回失败原因，请登录微信商户平台查看，或联系技术支持',
    ];

    /**
     * @param string $appid 应用appid
     * @param string $mchId 商户号id
     * @param string $paySerial  证书系列号
     * @param string $apiClientKeyFile api私钥文件路径， 或文件内容  //todo 待校验，看文本内容是否可以
    */
    public function __construct($appid, $mchId, $serialNo, $apiClientKeyContent)
    {
        $this->appid = $appid;
        $this->mchId = $mchId;
        $this->serialNo = $serialNo;
        $this->apiClientKeyContent = $apiClientKeyContent;
    }


    /**
     * 获取签名
     * @param string $method
     * @param string $uri
     * @param string $data
     * @return string
     */
    protected function getAuth($method, $uri, $data)
    {
        $time = time();
        $nonce = general_nonce_string(32);


    $str = <<<EOL
$method
$uri
$time
$nonce
$data

EOL;

        $privateKey = openssl_pkey_get_private($this->apiClientKeyContent);
        if ($privateKey === false) {
            throw new \Exception('无法从PEM字符串中获取私钥信息');
        }

        $bool = openssl_sign($str, $signature,$privateKey, OPENSSL_ALGO_SHA256);
        if (!$bool) {
            throw new \Exception('验签错误');
        }

        $base64Signature = base64_encode($signature);

        return sprintf('WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",signature="%s",timestamp="%d",serial_no="%s"', $this->mchId, $nonce, $base64Signature, $time, $this->serialNo);
    }


    protected function execRequest(HttpClient $http, $uri, $data) {

        $url = $this->domain. $uri;
        $className = explode('\\', strtoupper(get_class($http)));

        $auth = $this->getAuth($className[3], $uri, $data);

        $response = $http->setHeader('Authorization', $auth)
            ->setHeader('Accept', 'application/json')
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Wechatpay-Serial', $this->serialNo)
            ->do($url, $data);

        if ($http->getErrNo() != 0) {
            throw new \Exception($http->getErrMsg(), $http->getErrNo());
        }

        if ($response == '') {
            throw new \Exception("请求未响应");
        }

        $json = json_decode($response, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg(), json_last_error());
        }

        if (array_key_exists('code', $json)) {
            $message = array_key_exists('message', $json) ? $json['message'] : '请求报错了，微信未返回报错信息';

            throw new \Exception($message);
        }

        return $json;
    }

    abstract function exec();
}