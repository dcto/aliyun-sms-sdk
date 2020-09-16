<?php


use Varimax\Aliyun\Sms;

$sms = new Sms;

$sms->config(array(
    'AccessKeyId'=> '{{AccessKeyId}}',
    'AccessKeySecret'=> '{{AccessKeySecret}}',
    'TemplateCode'=> '{{TemplateCode}}',
    'SignName'=> '{{SignName}}'
));

$result = $sms->with('code', 5555)->send(13800138000);

print_r($result);
