# PHP玩微信『跳一跳』
>适用于Android-Win用户（买不起mac和iPhone无法提供iPhone版）

## 环境部署
- 一键启动**快速方式**（无须配置环境，**强烈推荐**）请转移至 [PHP一键开始微信『跳一跳』](https://github.com/phpxiaowei/php_wechat_jump/releases)，经典方式接往下阅读。
- [ADB](http://img.wm07.cn/UniversalAdbDriverSetup.msi) 直接下载安装即可，然后把adb.exe的目录添加至环境变量
- [PHP](http://windows.php.net/download#php-7.2)（老鸟略过，需开启gd2以及php加入环境变量） 建议7.2版本,下载解压，把php.exe所在的目录添加至环境变量
- 需要在php.ini中把extension_dir的值改为 "ext" 把extension=php_gd2.dll前面的分号 ; 去掉，保存OK。

## 使用说明
- 手机连接电脑并开启USB调试，选择MTP模式，在开发者选项中如果有**允许模拟点击**请勾选。（建议连接在主机后USB接口，线也能影响是否能正常连接电脑）。
- 在项目根目录 左手按住shift+右手鼠标右键(选择在此处打开命令窗口..) 键入命令 php main.php 按照提示开始即可。
- 默认按分辨率自动匹配配置文件,如果跳的不准或没有你手机合适的配置文件（config.php）请自行复制一份配置文件（在./config目录下）到根目录（与main.php同级） 进行参数调整，TIME_RATIO为时间系数，BASE_H_HALF为底座高度的二分之一，BODY_W为棋子的宽度。

## 关于调试
- 在main.php $debug参数可控制调试粒度
- 0或者false为不开启调试
- 1为默认模式，仅保存最后一次跳跃的截图并明确起始点和终点
- 2模式为保存每次跳跃前的截图

## ADB安装的啰嗦
- 安装完成并加入了环境变量之后
- 手机连接（同上）
- 打开cmd.exe输入命令：  ``` adb devices ```
- 如果出现 类似下面信息表示连接成功
```bash
List of devices attached
6934dc33    device
```
- 如果没有说明设备未正常连接，数据线与USB接口也可能导致该问题，如果5037端口被占用可能导致adb启动失败，请完全关闭手机助手等软件

## 版权说明
- 遵循Apache-2.0 协议
- 严禁用于商业用途

>算法借鉴[python 微信跳一跳辅助](https://github.com/wangshub/wechat_jump_game)
