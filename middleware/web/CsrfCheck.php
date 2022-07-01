<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: no-mind <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace middleware\web;
use willphp\core\Request;
use willphp\core\App;
//验证表单令牌
class CsrfCheck {
	protected $token; //表单令牌	
	public function run($next){
		if (config('view.csrf_check')) {
			$this->setServerToken();
			$token = $this->getClientToken();
			if ($token != $this->token && IS_POST && ($_SERVER['HTTP_HOST'] == Request::getHost(__HISTORY__))) {
				App::halt('表单令牌验证失败');
			}	
		}	
        $next();        
	}
	//设置服务端令牌
	protected function setServerToken() {
		$token = session('csrf_token');
		if (!$token) {
			$token = md5(get_ip().microtime(true));
			session('csrf_token', $token);	
		}
		$this->token = $token;
	}
	//获取客户端令牌
	protected function getClientToken() {
		$token = Request::getHeader('X-CSRF-TOKEN');
		return $token? $token : Request::getRequest('csrf_token');
	}
}