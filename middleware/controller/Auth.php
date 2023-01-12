<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: no-mind <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace middleware\controller;
//验证Auth
class Auth {	
	public function run($next){	
		if (!session('user_id')) {
			header('Location:'.url('login/login'));			
		} 		
        $next();
	}
}