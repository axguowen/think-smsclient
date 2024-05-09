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

namespace think;

use think\helper\Arr;
use think\exception\InvalidArgumentException;

/**
 * SMS客户端
 */
class SmsClient extends Manager
{
	/**
     * 驱动的命名空间
     * @var string
     */
	protected $namespace = '\\think\\smsclient\\driver\\';

	/**
     * 默认驱动
     * @access public
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

	/**
     * 获取短信配置
     * @access public
     * @param null|string $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return $this->app->config->get('smsclient.' . $name, $default);
        }

        return $this->app->config->get('smsclient');
    }

	/**
     * 获取平台配置
     * @param string $platform 平台名称
     * @param null|string $name 配置名称
     * @param null|string $default 默认值
     * @return array
     */
    public function getPlatformConfig(string $platform, string $name = null, $default = null)
    {
		// 读取驱动配置文件
        if ($config = $this->getConfig('platforms.' . $platform)) {
            return Arr::get($config, $name, $default);
        }
		// 驱动不存在
        throw new \InvalidArgumentException('平台 [' . $platform . '] 配置不存在.');
    }

    /**
     * 当前平台的驱动配置
     * @param string $name 驱动名称
     * @return mixed
     */
    protected function resolveType(string $name)
    {
        return $this->getPlatformConfig($name, 'type', 'tencent');
    }

	/**
     * 获取驱动配置
     * @param string $name 驱动名称
     * @return mixed
     */
    protected function resolveConfig(string $name)
    {
        return $this->getPlatformConfig($name);
    }

	/**
     * 选择或者切换平台
     * @access public
     * @param string $name 平台的配置名
     * @return \think\smsclient\Platform
     */
    public function platform(string $name = null, array $options = [])
    {
        // 如果指定了自定义配置
        if(!empty($options)){
            // 创建驱动实例并设置参数
            return $this->createDriver($name)->setConfig($options);
        }
        // 返回已有驱动实例
        return $this->driver($name);
    }
}