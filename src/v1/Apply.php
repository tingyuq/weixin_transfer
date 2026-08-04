<?php

namespace tingyu\WeixinTransfer\v1;

use tingyu\HttpRequest\Method\Post;

class Apply extends Base
{

    private $uri = '/mmpaymkttransfers/promotion/transfers';

    private $errCodes = [
        'NO_AUTH' => '没有该接口权限',
        'AMOUNT_LIMIT' => '金额超限',
        'PARAM_ERROR' => '参数错误',
        'OPENID_ERROR' => 'Openid错误',
        'SEND_FAILED' => '付款错误',
        'NOTENOUGH' => '余额不足,如果要继续付款必须使用原商户订单号重试',
        'SYSTEMERROR' => '系统繁忙，请稍后再试。请先调用查询接口，查看此次付款结果，如结果为不明确状态，务必使用原商户订单号重试',
        'NAME_MISMATCH' => '姓名校验出错',
        'SIGN_ERROR' => '签名错误',
        'XML_ERROR' => 'Post请求数据不是合法的xml格式内容',
        'FATAL_ERROR' => '两次请求参数不一致',
        'FREQ_LIMIT' => '超过频率限制，请稍后再试。',
        'MONEY_LIMIT' => '已经达到今日付款总额上限/达到付款给此用户额度上限',
        'CA_ERROR' => '商户API证书校验出错',
        'V2_ACCOUNT_SIMPLE_BAN' => '无法给未实名用户付款',
        'PARAM_IS_NOT_UTF8' => '请求参数中包含非utf8编码字符',
        'SENDNUM_LIMIT' => '用户今日付款次数超过限制',
        'RECV_ACCOUNT_NOT_ALLOWED' => '收款账户不在收款账户列表',
        'PAY_CHANNEL_NOT_ALLOWED' => '本商户号未配置API发起能力',
        'SEND_MONEY_LIMIT' => '已达到今日商户付款额度上限如果要继续付款必须使用原商户订单号重试',
        'RECEIVED_MONEY_LIMIT' => '已达到今日付款给此用户额度上限，如果要继续付款必须使用原商户订单号重试',
        'EXCEED_PAYEE_ACCOUNT_LIMIT' => '用户账户收款受限',
        'PAYER_ACCOUNT_ABNORMAL' => '商户账户付款受限',
        'PAYEE_ACCOUNT_ABNORMAL' => '用户账户收款异常',
        'PAYMENT_IN_PROGRESS' => '该笔付款正在处理中',
    ];

    /**
     * @param string $tradeNo 商家转账单号
     * @param float $amount 转账金额（元）
     * @param string $openid 用户在商户appid下的唯一标识
     * @param string $username 收款方真实姓名，
     * @param string $desc 转账备注
     * @return mixed
     * @throws \Exception
     */
    public function Exec($tradeNo, $amount, $openid, $username, $desc)
    {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $tradeNo)) {
            throw new \Exception('商户订单号由长度为8到32位的大小写字母数字组成');
        }

        if (!preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/', $amount)) {
            throw new \Exception('转账金额不合法');
        }

        $amount = bcmul($amount, '100', 2);
        $amount = intval($amount);
        if ($amount < 30) {
            throw new \Exception('转账金额最小为 0.3 元');
        }

        if (empty($desc) || mb_strlen($desc) > 100) {
            throw new \Exception('付款备注不能为空，或者大于100字符');
        }

        $data = [
            'amount' => $amount,
            'desc' => $desc,
            'check_name' => 'NO_CHECK',
            'openid' => $openid,
            'partner_trade_no' => $tradeNo,
            'mchid' => $this->mchId,
            'mch_appid' => $this->appid
        ];

        if (!empty($username)) {
            $data['check_name'] = 'FORCE_CHECK';
            $data['re_user_name'] = $username;
        }

        $resp = $this->execRequest(new Post(), $this->uri, $data);
        if ($resp['return_code'] == 'FAIL') {
            $msg = array_key_exists('return_msg', $resp) ? $resp['return_msg'] : 'return_code = FAIL, 未返回实际原因，联系技术支持';
            throw new \Exception($msg);
        }

        if ($resp['result_code'] == 'FAIL') {
            $errCode = array_key_exists('err_code', $resp) ? $resp['err_code'] : 'SYSTEMERROR';
            $errCodeDes = array_key_exists('err_code_des', $resp) ? $resp['err_code_des'] : $this->errCodes[$errCode];

            throw new \Exception("转账发生错误，错误状态码：{$errCode}, 错误信息：{$errCodeDes}");
        }

        return $resp;
    }
}