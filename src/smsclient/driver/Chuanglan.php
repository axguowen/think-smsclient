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

namespace think\smsclient\driver;

use think\smsclient\Platform;
use axguowen\HttpClient;

class Chuanglan extends Platform
{
    /**
     * 接口地址
     */
    const BASE_URL = 'https://smssh.253.com';
    
	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 应用ID
        'app_id' => '',
        // 应用密钥
        'app_secret' => '',
        // 产品类型notify-通知短信；market-营销短信；verify-验证码短信
        'product_type' => 'notify',
        // 模板ID
        'template_id' => '',
    ];

	/**
     * 发送短信
     * @access public
     * @param string|array $mobiles
     * @param array $data
     * @param array $options
     * @return array
     */
    public function send($mobiles, array $data = [], array $options = [])
	{
        // 处理参数
        $params = [];
        $index = 1;
        // 如果存在参数
        if(!empty($data)){
            // 遍历
            foreach($data as $k => $v){
                $key = 'param' . $index;
                $params[$key] = $v;
                $index++;
            }
        }
        // 如果指定的手机号是字符串则转换为数组
        if(!is_array($mobiles)){
            $mobiles = explode(',', $mobiles);
        }
        // 模板参数
        $templateParam = [];
        // 遍历手机号
        foreach($mobiles as $mobile){
            $templateParam[] = $params;
        }

        // 要发送的数据
        $requestBody = [
            'productType' => $this->options['product_type'],
            'phoneNumbers' => implode(',', $mobiles),
            'templateCode' => $this->options['template_id'],
            'templateParam' => json_encode($templateParam, JSON_UNESCAPED_UNICODE),
        ];

        // 构造随机串
        $nonce = md5(uniqid());
        // 当前时间戳
        $curTime = time();
        // 签名值
        $checkSum = hash('sha1', $this->options['app_secret'] . $nonce . $curTime);

        // 请求头
        $requestHeaders = [
            'Content-Type' => 'application/json',
            'AppID' => $this->options['app_id'],
            'CheckSum' => $checkSum,
            'Nonce' => $nonce,
            'CurTime' => $curTime,
        ];

        try{
            // 发送短信
            $response = HttpClient::post(static::BASE_URL . '/sms/v2/batch-send', json_encode($requestBody, JSON_UNESCAPED_UNICODE), $requestHeaders);
            // 错误
            if (!$response->ok()) {
                throw new \Exception($response->error, $response->statusCode);
            }
            // 获取响应体
            $responseBody = !is_null($response->body) ? $response->json() : [];
            // 为空
            if(empty($responseBody) || !isset($responseBody['code'])){
                return [null, new \Exception('操作失败, 错误原因: 未知')];
            }
            // 失败
            if($responseBody['code'] != '000000'){
                if(isset($responseBody['msg'])){
                    throw new \Exception($responseBody['msg']);
                }
                throw new \Exception('短信发送失败, 未知错误');
            }
            // 要返回的数据
            $resultData = [
                [
                    // 驱动类型
                    'driver' => static::class,
                    // 手机号
                    'tel' => $mobiles,
                    // 流水号
                    'serial_id' => null,
                    // 发送状态
                    'send_status' => 1,
                    // 响应数据
                    'response' => $responseBody,
                ]
            ];
            // 返回成功
            return [$resultData, null];
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }
	}
}