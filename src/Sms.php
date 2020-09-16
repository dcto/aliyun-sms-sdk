<?php

namespace Aliyun\Sms;

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

    /**
     * @var string send phone number
     */
    protected $phone;

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
        return $this->config('TemplateParam', is_array($key) ? $key : [$key => $value]);
    }

    /**
     * set phone number
     */
    public function phone($number)
    {
        $this->phone = $number;
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
     * send sms
     */
    public function send($phone = null, $param = array())
    {
        $parameters["AccessKeyId"] = $this->config['AccessKeyId']; //key
        $parameters["RegionId"] = $this->config['RegionId']; //固定参数
        $parameters["PhoneNumbers"] = $phone?:$this->phone; //手机号
        $parameters["SignName"] = $this->config['SignName']; //签名
        $parameters["TemplateCode"] = $this->config['TemplateCode']; //短信模版id
        $parameters["TemplateParam"] = json_encode($param ?: $this->config['TemplateParam'], JSON_UNESCAPED_UNICODE);  //模版内容
        $parameters["Format"] = $this->config['Format'];  //返回数据类型,支持xml,json
        $parameters["SignatureMethod"] = "HMAC-SHA1"; //固定参数
        $parameters["SignatureVersion"] = "1.0";  //固定参数
        $parameters["SignatureNonce"] = uniqid(); //用于请求的防重放攻击，每次请求唯一
        $parameters["Timestamp"] = date('Y-m-d\TH:i:s\Z'); //格式为：yyyy-MM-dd’T’HH:mm:ss’Z’；时区为：GMT
        $parameters["Action"] = 'SendSms'; //api命名 固定子
        $parameters["Version"] = '2017-05-25'; //api版本 固定值

        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $signature = $this->signature($canonicalizedQueryString, $this->config['AccessKeySecret']);  //最终生成的签名结果值

        $url = $this->host."?Signature={$signature}{$canonicalizedQueryString}";
        return $this->request($url);
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
    protected function request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $httpResponse = curl_exec($ch);
        if ($httpResponse) {
            return json_decode($httpResponse);
        } else {
            return json_decode(curl_error($ch));
        }
        curl_close($ch);
    }
}