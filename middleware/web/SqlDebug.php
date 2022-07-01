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
//记录sql到调试栏
class SqlDebug {	
	public function run($next, $sql = ''){
		if (APP_TRACE) trace($sql, 'sql');
        $next();
	}
}