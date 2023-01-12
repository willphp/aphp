<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core;
class Build {	
	public static function app() {
		$lock = ROOT_PATH.'/app/build.lock';
		if (!is_writable($lock)) {
			if (!touch($lock)) {
				exit('应用目录不可写，请确认app目录存在，并设置权限可写！');
			}
			//目录生成
			if (!is_dir(RUNTIME_PATH)) mkdir(RUNTIME_PATH, 0777, true);
			if (!is_dir(APP_PATH)) mkdir(APP_PATH, 0755, true);			
			$viewPath = (THEME_ON)? VIEW_PATH.'/'.__THEME__ : VIEW_PATH;			
			$dirs = [];			
			$dirs[] = APP_PATH.'/config';
			$dirs[] = APP_PATH.'/controller';
			$dirs[] = APP_PATH.'/model';
			$dirs[] = APP_PATH.'/widget';			
			$dirs[] = $viewPath.'/public';
			$dirs[] = $viewPath.'/index';			
			foreach ($dirs as $dir) {
				if(!is_dir($dir)) mkdir($dir, 0755, true);
			}			
			//自定义函数文件
			if (!file_exists(ROOT_PATH.'/app/common.php')) {
				file_put_contents(ROOT_PATH.'/app/common.php', "<?php\n//自定义函数文件");
			}			
			$module = APP_NAME;			
			//生成路由
			if (!file_exists(ROOT_PATH.'/route/'.$module.'.php')) {
				file_put_contents(ROOT_PATH.'/route/'.$module.'.php', "<?php\nreturn [\n\t'index' => 'index/index',\n];");
			}
			//首页模板
			$t_index = file_get_contents(ROOT_PATH.'/willphp/tpl/index.tpl'); 
			file_put_contents($viewPath.'/index/index.html', $t_index);
			//转跳模板
			$t_jump = file_get_contents(ROOT_PATH.'/willphp/tpl/jump.tpl'); 			
			file_put_contents($viewPath.'/public/jump.html', $t_jump);
			//默认控制器
			$c_index = "<?php\nnamespace app\\$module\\controller;\nclass Index{\n\tpublic function index(){\n\t\treturn view();\n\t}\n}";
			file_put_contents(APP_PATH.'/controller/Index.php', $c_index);
			//API控制器
			$c_api = "<?php\nnamespace app\\$module\\controller;\nclass Api{\n\tuse \\willphp\\core\\Jump;\n\tpublic function clear(){\n\t\tcache(null);\n\t\t\$this->success('清除缓存成功', 'index/index');\n\t}";
			$c_api .= "\n\tpublic function captcha() {\n\t\treturn (new \\extend\\captcha\\Captcha())->make();\n\t}\n}";
			file_put_contents(APP_PATH.'/controller/Api.php', $c_api);
			//错误处理控制器
			$c_err = "<?php\nnamespace app\\$module\\controller;\nclass Error{\n\tuse \\willphp\\core\\Jump;\n\tpublic function empty(\$path = ''){\n\t\t\$this->error(\$path.' 不存在', null, 404);\n\t}";
			$c_err .= "\n\tpublic function fail(\$msg = '出错了'){\n\t\t\$this->error(\$msg);\n\t}";
			$c_err .= "\n\tpublic function validate(\$msg = '验证失败'){\n\t\t\$this->error(\$msg, null, 403);\n\t}\n}";			
			file_put_contents(APP_PATH.'/controller/Error.php', $c_err);			
			//删除锁定文件
			unlink($lock);
		}
	}
}