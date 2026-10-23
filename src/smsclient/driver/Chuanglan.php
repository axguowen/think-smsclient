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
     * 错误码
     */
    const ERROR_CODE = [
        '000000' => '成功',
        '120001' => '验签失败',
        '120301' => 'AppID 缺失 / 未绑定产品',
        '120302' => '消息头参数缺失',
        '120401' => '无效 Token',
        '120504' => '签名过期（CurTime 与服务器时间差 > 5 分钟）',
        '190004' => '参数错误',
        '190403' => 'IP 白名单校验失败',
        '190500' => '服务器未捕获异常',
        '201500' => '默认业务异常',
        '201501' => '参数类型错误',
        '201502' => '请求解析错误',
        '201503' => '账号不存在',
        '201504' => '子账号不存在',
        '201505' => '子账号无权限',
        '201506' => '参数为空',
        '201507' => '参数错误',
        '201602' => 'productType 为空',
        '201603' => 'productType 不合法',
        '201655' => '无权限操作',
        '201507' => '参数错误',
    ];
    
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
        // 要发送的数据
        $requestBody = [
            'productType' => $this->options['product_type'],
            'phoneNumbers' => $mobiles,
            'templateCode' => $this->options['template_id'],
            'templateParam' => [],
        ];
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
        // 短信模板数据
        $requestBody['templateParam'] = $params;
        // 如果指定的手机号是数组则转换为逗号分隔的字符串
        if(is_array($mobiles)){
            $mobiles = implode(',', $mobiles);
        }

        // 构造随机串
        $nonce = md5(uniqid());
        // 当前时间戳
        $curTime = time();
        // 签名值
        $checkSum = hash('sha1', $this->options['app_secret'] . $nonce . $curTime);

        // 请求头
        $requestHeaders = [
            'Content-type' => 'application/json;charset=utf-8',
            'AppID' => $this->options['app_id'],
            'CheckSum' => $checkSum,
            'Nonce' => $nonce,
            'CurTime' => $curTime,
        ];

        try{
            // 发送短信
            $response = HttpClient::post(static::BASE_URL . '/sms/v2/batch-send', json_encode($requestBody), $requestHeaders);
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
            if($responseBody['code'] != 0){
                if(!isset(static::ERROR_CODE[$responseBody['code']])){
                    throw new \Exception('短信发送失败, 未知错误');
                }
                throw new \Exception(static::ERROR_CODE[$responseBody['code']]);
            }
            // 返回成功
            return [[
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
            ], null];
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }
	}
}