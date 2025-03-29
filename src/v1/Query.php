<?php

namespace tingyu\WeixinTransfer\v1;

use tingyu\HttpRequest\Method\Post;

class Query extends Base
{
    private $uri = '/mmpaymkttransfers/gettransferinfo';

    private $errCodes = [
        'CA_ERROR' => '请求未携带证书，或请求携带的证书出错',
        'SIGN_ERROR' => '商户签名错误',
        'FREQ_LIMIT' => '受频率限制',
        'XML_ERROR' => '请求的xml格式错误，或者post的数据为空',
        'SYSTEMERROR' => '系统繁忙，请再试',
        'NOT_FOUND' => '1、指定单号数据不存在 2、指定单号数据不存在，单据查询超过有效期'
    ];

    public function Exec($tradeNo)
    {
        if (!preg_match('/^[A-Za-z0-9]{8,32}$/', $tradeNo)) {
            throw new \Exception('商户订单号由长度为8到32位的大小写字母数字组成');
        }

        $data = [
            'partner_trade_no' => $tradeNo,
            'mch_id' => $this->mchId,
            'appid' => $this->appid,
        ];

        $resp = $this->execRequest(new Post(), $this->uri, $data);
        if ($resp['return_code'] == 'FAIL') {
            $msg = array_key_exists('return_msg', $resp) ? $resp['return_msg'] : 'return_code = FAIL, 未返回实际原因，联系技术支持';
            throw new \Exception($msg);
        }

        if ($resp['result_code'] == 'FAIL') {
            $errCode = array_key_exists('err_code', $resp) ? $resp['err_code'] : 'SYSTEMERROR';
            $errCodeDes = array_key_exists('err_code_des', $resp) ? $resp['err_code_des'] : $this->errCodes[$errCode];

            throw new \Exception("发生错误：{$errCode} . {$errCodeDes}");
        }

        return $resp;
    }
}