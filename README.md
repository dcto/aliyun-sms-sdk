# aliyun-sms-sdk
阿里云短信接口SDK


## 使用方法

1、引入Sms.php
```
use Varimax\Aliyun\Sms;
$sms = new Sms;
```
2、修改获取配置信息

```
$sms->config(array(
    'AccessKeyId'=> '{{AccessKeyId}}',
    'AccessKeySecret'=> '{{AccessKeySecret}}',
    'TemplateCode'=> '{{TemplateCode}}',
    'SignName'=> '{{SignName}}'
));
```

3、修改`TemplateParam`短信模板变量
可以调用模板变量方式
```
$templateCode='SMS_2341135685';
$templateParam = array('code'=>'1234');
$sms->template($templateCode, $templateParam);
```
也可以使用`with`方法替换模板变量
```
$sms->with('code', 5555);
```

4、调用`phone`方法设置要发送的号码
```
$sms->phone(13800138000);
```

4、或直接调用`send`方法发送短信

```
$sms->send(13800138000, array('code'=>5555));
```



## 错误码列表

|           Code            |             描述             |
| :-----------------------: | :--------------------------: |
|            OK             |           请求成功           |
|  isp.RAM_PERMISSION_DENY  |         RAM权限DENY          |
|    isv.OUT_OF_SERVICE     |           业务停机           |
| isv.PRODUCT_UN_SUBSCRIPT  | 未开通云通信产品的阿里云客户 |
|  isv.PRODUCT_UNSUBSCRIBE  |          产品未开通          |
|  isv.ACCOUNT_NOT_EXISTS   |          账户不存在          |
|   isv.ACCOUNT_ABNORMAL    |           账户异常           |
| isv.SMS_TEMPLATE_ILLEGAL  |        短信模板不合法        |
| isv.SMS_SIGNATURE_ILLEGAL |        短信签名不合法        |
| isv.INVALID_PARAMETERS |	参数异常 |
| isp.SYSTEM_ERROR |	系统错误 |
| isv.MOBILE_NUMBER_ILLEGAL |	非法手机号 |
| isv.MOBILE_COUNT_OVER_LIMIT |	手机号码数量超过限制	|
| isv.TEMPLATE_MISSING_PARAMETERS |	模板缺少变量 |
| isv.BUSINESS_LIMIT_CONTROL | 业务限流 |
| isv.INVALID_JSON_PARAM | JSON参数不合法，只接受字符串值 |
| isv.BLACK_KEY_CONTROL_LIMIT |	黑名单管控 |
| isv.PARAM_LENGTH_LIMIT | 参数超出长度限制 |
| isv.PARAM_NOT_SUPPORT_URL |	不支持URL |
| isv.AMOUNT_NOT_ENOUGH |	账户余额不足 |
