# ThinkPHP 短信 客户端

一个简单的 ThinkPHP 短信 客户端

主要功能：

支持多平台短信配置：目前支持阿里云、百度云、七牛云、腾讯云平台；

可扩展自定义平台驱动；

支持facade门面方式调用；

支持动态指定短信模板；

支持指定多个手机号接收；

支持动态切换平台；

## 安装

~~~
composer require axguowen/think-smsclient
~~~

## 用法示例

本扩展不能单独使用，依赖ThinkPHP6.0+

首先配置config目录下的smsclient.php配置文件，然后可以按照下面的用法使用。

简单使用

~~~php

use think\facade\SmsClient;

// 发送固定内容的模板短信
SmsClient::send('188****8888');
// 发送带参数的模板短信
SmsClient::send('188****8888', ['code' => '486936']);

// 同时发送多个手机号
SmsClient::send('188****8888,199****9999');
// 支持数组
SmsClient::send(['188****8888', '199****9999'], ['code' => '486936']);

~~~

动态切换平台

~~~php

use think\facade\SmsClient;

// 使用腾讯云短信平台
SmsClient::platform('tencent')->send('188****8888', ['code' => '486936']);

// 动态指定模板
SmsClient::platform('tencent', ['template_id' => '新的模板ID'])->send('188****8888', ['code' => '486936']);

~~~

## 配置说明

~~~php

// 短信配置
return [
    // 默认短信平台
    'default' => 'qiniu',
    // 短信平台配置
    'platforms' => [
        // 七牛云
        'qiniu' => [
            // 驱动类型
            'type' => 'QiniuCloud',
            // 公钥
            'access_key' => '',
            // 私钥
            'secret_key' => '',
            // 模板ID
            'template_id' => '',
        ],
        // 腾讯云
        'tencent' => [
            // 驱动类型
            'type' => 'TencentCloud',
            // 公钥
            'secret_id' => '',
            // 私钥
            'secret_key' => '',
            // 短信应用ID
            'sdk_app_id' => '',
            // 模板ID
            'template_id' => '',
            // 已审核的签名
            'sign_name' => '',
            // 服务接入点, 支持的地域列表参考 https://cloud.tencent.com/document/api/382/52071#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8
            'endpoint' => '',
        ],
        // 阿里云
        'aliyun' => [
            // 驱动类型
            'type' => 'Aliyun',
            // 公钥
            'access_id' => '',
            // 私钥
            'access_secret' => '',
            // 模板ID
            'template_id' => '',
            // 已审核的签名
            'sign_name' => '',
            // 服务接入点, 默认dysmsapi.aliyuncs.com
            'endpoint' => '',
        ],
        // 百度云
        'baidu' => [
            // 驱动类型
            'type' => 'BaiduBce',
            // 公钥
            'access_key' => '',
            // 私钥
            'secret_key' => '',
            // 模板ID
            'template_id' => '',
            // 签名ID
            'signature_id' => '',
            // 服务接入点, 默认smsv3.bj.baidubce.com
            'endpoint' => '',
        ]
    ],
];

~~~

## 自定义平台驱动

如果需要扩展自定义短信平台驱动，需要实现think\smsclient\PlatformInterface接口

具体代码可以参考现有的平台驱动

扩展自定义驱动后，只需要在短信客户端配置文件smsclient.php中设置default的值为该驱动类名（包含命名空间）即可。