<?php

namespace Varimax\Aliyun;

class Sms {

    /**
     * @var string $host aliyun sms api host
     */
    protected $host = 'https://dysmsapi.aliyuncs.com';
    
    /**
     * @var array $config
     */
    protected $config = array(
        'AccessKeyId' => null,
        'AccessKeySecret'=>null,
        'TemplateCode'=>'',
        'RegionId'=> 'cn-hangzhou',
        'SignName' => null,
        'Format' => 'JSON',
    );

    protected $response = null;

    /**
     * request parameters
     */
    protected $parameters = array();

    /**
     * query string
     */
    protected $queryString;


    public function __construct(array $config = array())
    {
        $this->config = array_merge($this->config, $config);

        $this->parameters = array(
            'SignatureMethod' => "HMAC-SHA1", //固定参数
            'SignatureVersion' => "1.0",  //固定参数
            'SignatureNonce' => uniqid(), //用于请求的防重放攻击，每次请求唯一
            'Timestamp' => str_replace('GM', '', gmdate('Y-m-dTH:i:s') . 'Z'), //date('Y-m-d\TH:i:s\Z'); //格式为：yyyy-MM-dd’T’HH:mm:ss’Z’；时区为：GMT
            'PhoneNumbers' => null,
            'TemplateCode' => null,
            'TemplateParam'=>array(),
            'Action' => 'SendSms', //api命名 固定子
            'Version' => '2017-05-25' //api版本 固定值
        );
    }


    /**
     * setting host
     */
    public function host($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * config setting
     */
    public function config($key, $value = null)
    {
        if(is_array($key)){
            $this->config = array_merge($this->config, $key);
        }else if($value){
            $this->config[$key] = $value;
        }else{
            return $this->config;
        }

        return $this;
    }

    /**
     * phone alias name
     */
    public function to($number)
    {
        return $this->phone($number);
    }

    /**
     * template param alias name
     */
    public function with($key, $value = null)
    {
       return $this->param($key, $value);
    }

    /**
     * set parameters 
     */
    public function param($key, $value = null)
    {
        $this->parameters['TemplateParam'] = is_array($key) ? $key : [$key => $value];
        return $this;
    }

    /**
     * set phone number
     */
    public function phone($number)
    {
        $this->parameters['PhoneNumbers'] = $number;
        return $this;
    }

    /**
     * set template code
     */
    public function template($template, $parameters = null)
    {
        $this->config('TemplateCode', $template);
        $parameters && $this->config('TemplateParam', $parameters);
        return $this;
    }

    /**
     * get parameters 
     */
    public function parameters()
    {
        return $this->parameters;
    }


    /**
     * send sms
     * @return bool
     */
    public function send($phone = null, $param = array())
    {
        $send = $this->prepare($phone, $param)->request();

        return is_array($send) && isset($send['Code']) && $send['Code'] == 'OK';
    }

    /**
     * debug send result
     * @return array
     */
    public function debug($phone = null, $param = array())
    {
        return $this->prepare($phone, $param)->request();
    }

    /**
     * get response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * prepare prepare
     */
    protected function prepare($phone = null, $param = array())
    {
        $phone && $this->parameters["PhoneNumbers"] = $phone;
        $param && $this->parameters["TemplateParam"] = $param;

        $this->parameters["AccessKeyId"] = $this->config['AccessKeyId']; //key
        $this->parameters["RegionId"] = $this->config['RegionId']; //固定参数
        $this->parameters["SignName"] = $this->config['SignName']; //签名
        $this->parameters["TemplateCode"] = $this->config['TemplateCode']; //短信模版id
        $this->parameters["Format"] = $this->config['Format'];  //返回数据类型,支持xml,json
        $this->parameters['TemplateParam'] = json_encode($this->parameters['TemplateParam'], JSON_UNESCAPED_UNICODE);  //模版内容

        ksort($this->parameters);
        $canonicalizedQueryString = '';
        foreach ($this->parameters as $key => $value) {
            $canonicalizedQueryString .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }
        $signature = $this->signature($canonicalizedQueryString, $this->config['AccessKeySecret']);  //最终生成的签名结果值
        $this->queryString = $this->host."?Signature={$signature}{$canonicalizedQueryString}";

        return $this;
    }

    /**
     * signature
     */
    protected function signature($canonicalizedQueryString, $accessKeySecret)
    {
        $stringToSign = "GET&%2F&" . $this->encode(substr($canonicalizedQueryString, 1));
        return $this->encode(base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . "&", true)));
    }

    /**
     * encoding the url
     */
    protected function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    /**
     * send request
     */
    protected function request()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_URL, $this->queryString);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->response = curl_exec($ch);
        if ($this->response) {
            return json_decode($this->response, true);
        } else {
            return json_decode(curl_error($ch));
        }
        curl_close($ch);
    }
}