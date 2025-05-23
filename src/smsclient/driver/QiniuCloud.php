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
use Qiniu\Auth;
use Qiniu\Sms\Sms;

class QiniuCloud extends Platform
{
    /**
     * 平台句柄
     * @var Sms
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
    ];

    /**
     * 创建句柄
     * @access protected
     * @return $this
     */
    protected function makeHandler()
    {
        // 实例化认证对象
        $auth = new Auth($this->options['access_key'], $this->options['secret_key']);
        // 实例化要请求产品的 client 对象
        $this->handler = new Sms($auth);
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
            $response = $this->handler->sendMessage($this->options['template_id'], $mobiles, $data);
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }

        // 如果发送失败
        if(is_null($response[0])){
            // 返回
            return $response;
        }
        // 要返回的数据
        $resultData = [];
        // 遍历
        foreach($mobiles as $item){
            $resultData[] = [
                // 驱动类型
                'driver' => static::class,
                // 手机号
                'tel' => $item,
                // 流水号
                'serial_id' => $response[0]['job_id'],
                // 发送状态
                'send_status' => 1,
            ];
        }

        // 返回成功
        return [$resultData, null];
	}
}