<?php
declare(strict_types=1);
namespace {{NAMESPACE}}\controller;
use aphp\core\Jump;
class {{CLASS}}
{
	use Jump;
    public function clear()
    {
        cache_clear();
        cli('clear:runtime {{APP}}');
        $this->success('清除缓存成功', 'index/index');
    }
}