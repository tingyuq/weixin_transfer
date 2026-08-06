<?php

namespace tingyu\WeixinTransfer\v1;

use Hyperf\Guzzle\ClientFactory;
use tingyu\WeixinTransfer\tool\NonceStr;

abstract class Base
{
    use NonceStr;

    const METHOD_POST = 'POST';

    protected $appid;

    protected $appsecret;

    protected $mchId;

    protected $apiClientKeyCertFile;

    protected $apiClientCertFile;

    /**
     * @var string 接口请求的原始数据，json、xml
     */
    protected $rawResponse;

    /**
     * @var array 接口请求的原始数据，解析成数组的
     */
    protected $response;

    protected $domain = 'https://api.mch.weixin.qq.com';

    private ClientFactory $clientFactory;

    /**
     * @param string $appid 小程序标识(APPID)
     * @param string $appsecret 小程序APPSECRE
     * @param string $mchId 微信支付商户号(MCHID)
     * @param string $apiClientCertFile 支付证书(apiclient_cert) 文件路径
     * @param string $apiClientKeyCertFile 支付证书密钥(apiclient_key) 文件路径
     */
    public function __construct(ClientFactory $clientFactory, $appid, $appsecret, $mchId, $apiClientCertFile, $apiClientKeyCertFile)
    {
        $this->clientFactory = $clientFactory;
        $this->appid = $appid;
        $this->appsecret = $appsecret;
        $this->mchId = $mchId;
        $this->apiClientCertFile = $apiClientCertFile;
        $this->apiClientKeyCertFile = $apiClientKeyCertFile;
    }


    /**
     * 获取签名
     * @param string $method
     * @param string $uri
     * @param string $data
     */
    private function sign(&$data)
    {
        $data['nonce_str'] = $this->GenNonceStr(32);

        ksort($data);

        $str = '';
        foreach ($data as $key => $value) {
            $str .= "{$key}={$value}&";
        }
        $str .= "key={$this->appsecret}";
        $data['sign'] = md5($str);
    }

    private function arrayToXml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }

    private function xmlToArray($xml) {
        $xmlObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $arr = json_decode(json_encode($xmlObj),true);
        return $arr;
    }


    protected function execRequest($method, $uri, $data) {

        $url = $this->domain. $uri;

        $this->sign($data);
        $xml = $this->arrayToXml($data);

        $client = $this->clientFactory->create([
            'timeout' => 15.0,
            'connect_timeout' => 8.0,
            'http_errors' => false,
        ]);
        $options = [
            'cert' => $this->apiClientCertFile,
            'ssl_key' => $this->apiClientKeyCertFile,
        ];

        $response = null;
        switch ($method) {
            case self::METHOD_POST:
                $options['body'] = $xml;
                $response = $client->post($url, $options);
                break;
            default:
                throw new \Exception('未支持的http请求方法');
        }

        if (empty($response)) {
            throw new \Exception("请求未响应");
        }


        $this->rawResponse = (string)$response->getBody();
        $json = $this->xmlToArray($this->rawResponse);

        $this->response = $json;
        return $json;
    }

    /**
     * 获取原始的响应数据, xml、json
     * @return mixed
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * 获取原始解析后的响应数据
     * @return array
     */
    public function getResponse() {
        return $this->response;
    }
}