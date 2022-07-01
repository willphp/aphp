<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace middleware\web;
use willphp\core\Log;
//记录sql到日志
class SqlLog {
	public function run($next, $sql = ''){
		if (config('debug.sql_log', false)) {				
			Log::record($sql, 'sql');
		}
        $next();
	}
}