<?php
// +----------------------------------------------------------------------
// | ThinkPHP SmsClient [Simple SMS Client For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP 短信客户端
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

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
            // 服务接入地域, 支持的地域列表参考 https://cloud.tencent.com/document/api/382/52071#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8
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
