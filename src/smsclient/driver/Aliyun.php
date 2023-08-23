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
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;

class Aliyun extends Platform
{
    /**
     * 平台句柄
     * @var Dysmsapi
     */
    protected $handler;

	/**
     * 平台配置参数
     * @var array
     */
    protected $options = [
        // 公钥
        'access_id' => '',
        // 私钥
        'access_secret' => '',
        // 模板ID
        'template_id' => '',
		// 已审核的签名
		'sign_name' => '',
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
        // 实例化认证对象
        $config = new \Darabonba\OpenApi\Models\Config([
            'accessKeyId' => $this->options['access_id'],
            'accessKeySecret' => $this->options['access_secret'],
            'endpoint' => $this->options['endpoint'] ?: 'dysmsapi.aliyuncs.com',
        ]);
        // 实例化要请求产品的 client 对象
        $this->handler = new Dysmsapi($config);
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
        // 实例化请求类
        $request = new SendSmsRequest([
            'phoneNumbers' => implode(',', $mobiles),
            'signName' => $this->options['sign_name'],
            'templateCode' => $this->options['template_id'],
            'templateParam' => json_encode($data),
        ]);

        try{
            // 发送短信
            $response = $this->handler->sendSms($request);
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }

        // 如果发送失败
        if(is_null($response->body->Code != 'OK')){
            // 返回错误
            return [null, new \Exception($response->body->Message)];
        }

        // 要返回的数据
        $resultData = [];
        // 遍历
        foreach($mobiles as $item){
            $resultData[] = [
                // 接口类型
                'type' => 'aliyun',
                // 手机号
                'tel' => $item,
                // 流水号
                'serial_id' => $response->body->BizId,
                // 发送状态
                'send_status' => 1,
            ];
        }

        // 返回成功
        return [$resultData, null];
	}
}