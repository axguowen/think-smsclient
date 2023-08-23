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
use BaiduBce\BceClientConfigOptions;
use BaiduBce\Services\Sms\SmsClient;

class BaiduBce extends Platform
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
        'secret_key' => '',
        // 模板ID
        'template_id' => '',
        // 签名ID
        'signature_id' => '',
        // 服务接入点
        'endpoint' => '',
    ];

    /**
     * 创建句柄
     * @access protected
     * @return $this
     */
    protected function makeHandler()
    {
        // 实例化要请求产品的 client 对象
        $this->handler = new SmsClient([
            BceClientConfigOptions::PROTOCOL => 'https',
            BceClientConfigOptions::REGION => 'bj',
            BceClientConfigOptions::CREDENTIALS => [
                'ak' => $this->options['access_key'],
                'sk' => $this->options['secret_key']
            ],
            BceClientConfigOptions::ENDPOINT => $this->options['endpoint'] ?: 'smsv3.bj.baidubce.com',
        ]);
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
        // 如果指定的手机号不是数组
        if(!is_array($mobiles)){
            $mobiles = explode(',', $mobiles);
        }

        try{
            // 发送短信
            $response = $this->handler->sendMessage(implode(',', $mobiles), $this->options['signature_id'], $this->options['template_id'], $data);
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }

        // 如果发送失败
        if($response->code != 1000){
            // 返回错误信息
            return [null, new \Exception($response->message)];
        }

        // 要返回的数据
        $resultData = [];
        // 获取发送结果集合
        $sendStatusSet = $response->data;
        // 遍历
        foreach($sendStatusSet as $item){
            $resultData[] = [
                // 接口类型
                'type' => 'baidu',
                // 手机号
                'tel' => $item->mobile,
                // 流水号
                'serial_id' => $item->messageId,
                // 发送状态
                'send_status' => $item->code == 1000 ? 1 : 0,
            ];
        }

        // 返回成功
        return [$resultData, null];
	}
}