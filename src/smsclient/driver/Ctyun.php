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
use axguowen\ctyun\common\Auth;
use axguowen\ctyun\services\sms\SmsClient;

class Ctyun extends Platform
{
    /**
     * 平台句柄
     * @var SmsClient
     */
    protected $handler;

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 公钥
        'access_key' => '',
        // 私钥
        'security_key' => '',
        // 模板ID
        'template_code' => '',
        // 签名
        'sign_name' => '',
    ];

    /**
     * 创建句柄
     * @access protected
     * @return $this
     */
    protected function makeHandler()
    {
        // 实例化认证对象
        $auth = new Auth($this->options['access_key'], $this->options['security_key']);
        // 实例化要请求产品的 client 对象
        $this->handler = new SmsClient($auth);
        // 返回
        return $this;
    }

	/**
     * 发送短信
     * @access public
     * @param string|array $mobiles
     * @param array $data
     * @return array
     */
    public function send($mobiles, array $data = [])
	{
        // 如果指定的手机号是数组
        if(is_array($mobiles)){
            $mobiles = implode(',', $mobiles);
        }
        // 数据转换为JSON字符串
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        try{
            // 发送短信
            $response = $this->handler->sendSms($mobiles, $this->options['sign_name'], $this->options['template_code'], $data);
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }

        // 如果发送失败
        if(strtolower($response['code']) != 'ok'){
            // 返回错误信息
            return [null, new \Exception($response['message'])];
        }

        // 要返回的数据
        $resultData = [];
        // 获取请求ID集合
        $requestIds = explode(',', $response['requestId']);
        // 遍历
        foreach($requestIds as $requestId){
            $resultData[] = [
                // 驱动类型
                'driver' => static::class,
                // 手机号
                'tel' => '',
                // 流水号
                'serial_id' => $requestId,
                // 发送状态
                'send_status' => 1,
            ];
        }

        // 返回成功
        return [$resultData, null];
	}
}