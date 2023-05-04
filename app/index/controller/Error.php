<?php
declare(strict_types=1);
namespace app\index\controller;
use willphp\core\Jump;
class Error
{
	use Jump;
	public function __call(string $name, array $arguments)
	{
		$msg = $arguments[0] ?? '出错了啦...';
		$code = str_starts_with($name, '_') ? substr($name, 1) : 400;
		$this->error($msg, (int)$code);
	}
}