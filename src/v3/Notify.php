<?php

namespace tingyu\WeixinTransfer\v3;

class Notify extends Base
{


    /**
     * @param string $outBillNo 商家转账单号
     * @param string $transferSceneId 转账场景，转账平台中申请
     * @param string $openId 用户在商户appid下的唯一标识
     * @param string $userName 收款方真实姓名， 标准RSA算法 内部完成加密， 转账金额 >= 2,000元时，该笔明细必须填写
     * @param double $transferAmount 转账金额（元），内部转换成分
     * @param string $transferRemark 转账备注， 最长32字符
     * @throw \Exception
     * @return array
     */
    public function Exec() {

        // 仅进行加解密处理

        return [];
    }
}