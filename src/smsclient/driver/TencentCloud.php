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
use TencentCloud\Common\Credential;
use TencentCloud\Sms\V20210111\SmsClient;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;

class TencentCloud extends Platform
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
        'secret_id' => '',
        // 私钥
        'secret_key' => '',
		// 短信应用ID
		'sdk_app_id' => '',
        // 模板ID
        'template_id' => '',
		// 已审核的签名
		'sign_name' => '',
    ];

    /**
     * 创建句柄
     * @access protected
     * @return $this
     */
    protected function makeHandler()
    {
        // 实例化授权对象
        $credential = new Credential($this->options['secret_id'], $this->options['secret_key']);
        // 实例化要请求产品的 client 对象
        $this->handler = new SmsClient($credential, $this->options['endpoint']);
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

        // 处理参数
        $params = [];
        // 如果存在参数
        if(!empty($data)){
            // 遍历
            foreach($data as $k => $v){
                $params[] = $v;
            }
        }

		// 请求对象
		$request = new SendSmsRequest();

		// 填充请求参数,这里request对象的成员变量即对应接口的入参
		// 短信应用ID: 短信SdkAppId在 [短信控制台] 添加应用后生成的实际SdkAppId，示例如1400006666
		// 应用 ID 可前往 [短信控制台](https://console.cloud.tencent.com/smsv2/app-manage) 查看
		$request->SmsSdkAppId = $this->options['sdk_app_id'];
		/* 短信签名内容: 使用 UTF-8 编码，必须填写已审核通过的签名 */
		$request->SignName = $this->options['sign_name'];
		/* 模板 ID: 必须填写已审核通过的模板 ID */
		$request->TemplateId = $this->options['template_id'];
		/* 模板参数: 模板参数的个数需要与 TemplateId 对应模板的变量个数保持一致，若无模板参数，则设置为空*/
		$request->TemplateParamSet = $params;
		/* 下发手机号码，采用 E.164 标准，+[国家或地区码][手机号]
		* 示例如：+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
		$request->PhoneNumberSet = $mobiles;
		// 通过client对象调用SendSms方法发起请求。注意请求方法名与请求对象是对应的
		// 返回的response是一个SendSmsResponse类的实例，与请求对象对应
        try{
            $response = $this->handler->SendSms($request);
        } catch (\Exception $e) {
            // 返回错误
            return [null, $e];
        }

        // 如果存在错误信息
        if(isset($response->Error)){
            // 返回错误信息
            return [null, new \Exception($response->Error->Message)];
        }

        // 要返回的数据
        $resultData = [];
        // 获取发送结果集合
        $sendStatusSet = $response->SendStatusSet;
        // 遍历
        foreach($sendStatusSet as $item){
            $resultData[] = [
                // 接口类型
                'type' => 'tencent',
                // 手机号
                'tel' => $item->PhoneNumber,
                // 流水号
                'serial_id' => $item->SerialNo,
                // 发送状态
                'send_status' => $item->Code == 'Ok' ? 1 : 0,
            ];
        }

        // 返回成功
        return [$resultData, null];
	}
}