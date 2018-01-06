<?php 

class auto
{
	public static $debug = true;
	public static $press_x1 = 0;
	public static $press_y1 = 0;
	public static $press_x2 = 0;
	public static $press_y2 = 0;
	public static $png = null;
	public static $height = 0;
	public static $width = 0;
	public static $version = '1.0';
	public static $black = '';
	public static $red = '';
	public static $time_ratio = 0;
	public static $base_h_half = 0;
	public static $body_w = 0;

	public static function start()
	{
		header("content-type:text/html;charset=GB2312");
		print("请确保打开跳一跳并【开始游戏】后再用本程序，确定开始？ \n【y/n】 ");
		strcasecmp(trim(fgets(STDIN)), 'y') && exit;
		print("程序版本号:" . self::$version."\n");
		self::init();
		$sum = 0;
		$next_reset = mt_rand(3, 10);
		$next_reset_time = mt_rand(5, 10);
		while (true) {
			self::screenshot();
			self::reset_press();
			self::jump();
			$sum ++;
			if ($sum == $next_reset) {
				print("已经连续跳了" . $sum."下,休息".$next_reset_time."s\n");
				sleep($next_reset_time);
				$sum = 0;
				$next_reset = mt_rand(30, 100);
				$next_reset_time = mt_rand(10, 60);
			}
		}
	}

	public static function reset_press()
	{
		self::$press_x1 = intval(self::$width / 2);
		self::$press_y1 = intval(1584 * (self::$height / 1920.0));
		self::$press_x2 = intval(mt_rand(self::$press_x1 - 50, self::$press_x1 + 50));
		self::$press_y2 = intval(mt_rand(self::$press_y1 - 10, self::$press_y1 + 10));
	}

	public static function jump()
	{
		
		self::$png = ImageCreateFromPng('auto.png');
		$position = self::find_start_and_end();
		$length = pow($position['start_x'] - $position['end_x'], 2) + pow($position['start_y'] - $position['end_y'], 2);
		$press_time = sqrt($length) * self::$time_ratio;
		$press_time = max($press_time, 200);
		$press_time = min($press_time, 3000);
		$press_time = intval($press_time);
		$cmd = sprintf('adb shell input swipe %d %d %d %d %d',
		 self::$press_x1,self::$press_y1,self::$press_x2,self::$press_y2, $press_time);
		print('x1('.$position['start_x'].') y1('.$position['start_y'].') x2('.$position['end_x'].') y2('.$position['end_y'].') time('.$press_time.")ms\n\n");
		exec($cmd);
		sleep(mt_rand(1.0, 1.8));
	}

	public static function find_start_and_end()
	{
		$h = self::$height;
		$w = self::$width;

		$scan_start_h = 0;
		$start_x_sum = 0;
		$start_x_c = 0;
		$start_y_max = 0;
		$start_x = 0;
		$end_x = 0;
		$end_y = 0;
		$scan_start_w = intval($w / 8);
		// 从屏幕的1/3处开始往下扫码,遇到不是纯色的线则作为扫码起始线
		for ($y = intval($h / 3); $y < intval($h*2 / 3); $y += 50) { 
			$prev_rgb = self::rgb(0, $y);
			for ($x = 0; $x < $w; $x++) { 
				$cur_rgb = self::rgb($x, $y);
				if ($cur_rgb['r'] != $prev_rgb['r'] 
					or $cur_rgb['g'] != $prev_rgb['g'] 
					or $cur_rgb['b'] != $prev_rgb['b']) {
					$scan_start_h = $y - 50;
					break;
				}
			}
			if ($scan_start_h) break; 
		}

		// 从记录点开始扫描
		for ($y = $scan_start_h; $y < intval($h*2 / 3); $y++) { 
			for ($x = $scan_start_w; $x < $w - $scan_start_w; $x++) { 
				$rgb = self::rgb($x, $y);
				if ($rgb['r'] > 50 && $rgb['r'] < 60
					&& $rgb['g'] > 53 && $rgb['g'] < 63
					&& $rgb['b'] > 95 && $rgb['b'] < 110 ) {
					$start_x_sum += $x;
					$start_x_c += 1;
					$start_y_max = max($y, $start_y_max);
				}
			}
		}
		@$start_x = intval($start_x_sum / $start_x_c);
		$start_y = $start_y_max - self::$base_h_half;
		

		if ($start_x < $w / 2) {
			$end_x_start = $start_x;
			$end_x_end = $w;
		} else {
			$end_x_start = 0;
			$end_x_end = $start_x;
		}

		for ($y = intval($h / 3); $y < intval($h*2 / 3); $y++) { 
			$prev_rgb = self::rgb(0, $y);
			if ($end_x or $end_y) break;
			$end_x_sum = 0;
			$end_x_c = 0;
			for ($x = intval($end_x_start); $x < intval($end_x_end); $x++) { 

				if (abs($x - $start_x) < self::$body_w ) continue;
				$cur_rgb = self::rgb($x, $y);
				if (abs($prev_rgb['r'] - $cur_rgb['r']) + abs($prev_rgb['g'] - $cur_rgb['g']) + abs($prev_rgb['b'] - $cur_rgb['b'])  > 10) {
					$end_x_sum += $x;
					$end_x_c += 1;
				}
			}
			if ($end_x_sum) $end_x = intval($end_x_sum / $end_x_c);
		}
		$prev_rgb = self::rgb($end_x, $y);

		for ($k = $y + 274; $k > $y; $k--) { 
			$cur_rgb = self::rgb($end_x, $k);
			$cha = abs($prev_rgb['r'] - $cur_rgb['r']) + abs($prev_rgb['g'] - $cur_rgb['g']) + abs($prev_rgb['b'] - $cur_rgb['b']);
			if ( $cha < 10) break;
		}

		$end_y = intval(($y+$k) / 2);

		for ($j = $y; $j < $y + 200; $j++) { 
			$rgb = self::rgb($end_x, $j);
			if (abs($rgb['r'] - 245) + abs($rgb['g'] - 245) + abs($rgb['b'] - 245) == 0) {
				$end_y = $j+10;
				break;
			}
		}
		self::$debug != 0 && self::debug($start_x, $start_y, $end_x, $end_y);
		imagedestroy(self::$png);
		return ['start_x' => $start_x, 'start_y' => $start_y, 'end_x' => $end_x, 'end_y' => $end_y];
	}

	public static function debug($start_x, $start_y, $end_x, $end_y)
	{
		imageline(self::$png, $start_x, 0, $start_x, self::$height, self::$black);
		imageline(self::$png, 0, $start_y, self::$width, $start_y, self::$red);
		imageline(self::$png, $end_x, 0, $end_x, self::$height, self::$black);
		imageline(self::$png, 0, $end_y, self::$width, $end_y, self::$red);
		if (self::$debug === 2) {
			$filename = './debug/'.date('Ymd');
			is_dir($filename) or mkdir($filename, 0777, true);
			$filename .= '/'.date('H_i_s').'.png';
		} else {
			$filename = 'Final_screenshot.png';
		}
		imagepng(self::$png, $filename);
	}

	public static function rgb($end_x, $j)
	{
		$value = ImageColorAt(self::$png, $end_x, $j);
		$color['r'] = ($value >> 16) & 0xFF;
		$color['g'] = ($value >> 8) & 0xFF; 
		$color['b'] = $value & 0xFF;
		return $color;
	}

	public static function init()
	{
		try {
			print("正在初始化环境..." . "\n");
			date_default_timezone_set('PRC');
			if ('cli' !== PHP_SAPI) throw new Exception('请在CLI模式上运行') ;
			@unlink('auto.png');
			self::screenshot();
			if (file_exists('auto.png') !== true) throw new Exception('设备连接失败,请安照文档排查');
			self::$png = ImageCreateFromPng('auto.png');
			self::$height = imagesy(self::$png);
			self::$width = imagesx(self::$png);
			self::$black = imageColorAllocate(self::$png, 255, 255, 255);
			self::$red = imageColorAllocate(self::$png, 255, 0, 0);
			$base = 'config/';
			$filename = self::$height.'x'.self::$width.'/config.php';
			if (file_exists('config.php')) {
				$file = 'config.php';
			} elseif (file_exists($base.$filename)) {
				$file = $base.$filename;
			} elseif (file_exists($base.'config.php')) {
				$file = $base.'config.php';
			} else {
				throw new Exception('配置文件丢失!');
			}
			require_once $file;
			print("加载配置文件:" . $file."\n");
			self::$time_ratio = config::TIME_RATIO;
			self::$base_h_half = config::BASE_H_HALF;
			self::$body_w = config::BODY_W;
		} catch (Exception $e) {
			exit($e->getMessage()."\n");
		}

	}

	public static function screenshot()
	{
		exec('adb shell screencap -p /sdcard/auto.png');
		exec('adb pull /sdcard/auto.png .');
	}
}
auto::start();