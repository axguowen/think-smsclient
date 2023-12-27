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

class Vaptcha extends Platform
{
    /**
     * 接口地址
     */
    const BASE_URL = 'https://sms.vaptcha.com';

    /**
     * 错误码
     */
    const ERROR_CODE = [
        '200' => '成功',
        '201' => '发送失败',
        '202' => '用户验证失败/smskey 不正确',
        '203' => '剩余短信条数不足',
        '204' => '发送频率过快，换号码或稍后再试',
        '205' => '短信长度受限',
        '206' => '手机号码格式错误',
        '207' => '验证码格式错误',
        '208' => '参数错误',
        '209' => '账号受限',
        '210' => '国别码错误',
        '211' => '模板数据错误',
        '212' => '模板编号错误',
        '213' => '敏感词错误',
        '230' => '接口错误',
        '600' => '验证通过',
        '601' => '验证失败',
    ];
    
	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 短信账户id
        'smsid' => '',
        // 短信账户key
        'smskey' => '',
        // 验证码令牌
        'token' => '',
        // 国别码
        'countrycode' => '86',
        // 模板ID
        'templateid' => '',
    ];

	/**
     * 发送短信
     * @access public
     * @param string|array $mobiles
     * @param array $data
     * @param string $token
     * @return array
     */
    public function send($mobiles, array $data = [], string $token = '')
	{
        // 要发送的数据
        $requestBody = $this->options;
        // 如果指定了token
        if(!empty($token)){
            $requestBody['token'] = $token;
        }
        // 处理参数
        $params = [];
        // 如果存在参数
        if(!empty($data)){
            // 遍历
            foreach($data as $k => $v){
                $params[] = $v;
            }
        }
        // 短信模板数据
        $requestBody['data'] = $params;
        // 如果指定的手机号不是数组
        if(!is_array($mobiles)){
            $mobiles = explode(',', $mobiles);
        }

        // 请求头
        $requestHeaders = [
            'Content-type' => 'application/json;charset=utf-8',
            'Accept' => 'application/json',
        ];

        // 要返回的数据
        $resultData = [];

        try{
            // 遍历手机号
            foreach($mobiles as $mobile){
                // 手机号
                $requestBody['phone'] = $mobile;
                // 发送短信
                $response = HttpClient::post(static::BASE_URL . '/send', json_encode($requestBody), $requestHeaders);
                // 错误
                if (!$response->ok()) {
                    throw new \Exception($response->error, $response->statusCode);
                }
                // 错误
                if($response->body != 200){
                    if(!isset(static::ERROR_CODE[$response->body])){
                        throw new \Exception('短信发送失败, 未知错误');
                    }
                    throw new \Exception(static::ERROR_CODE[$response->body]);
                }
                $resultData[] = [
                    // 驱动类型
                    'driver' => static::class,
                    // 手机号
                    'tel' => $mobile,
                    // 流水号
                    'serial_id' => null,
                    // 发送状态
                    'send_status' => 1,
                ];
            }
            
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }

        // 返回成功
        return [$resultData, null];
	}
}