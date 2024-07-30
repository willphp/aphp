<?php
declare(strict_types=1);
namespace {{$namespace}}\controller;
use aphp\core\Jump;
class {{$class}}
{
	use Jump;
    public function __call(string $name, array $arguments)
    {
        $msg = $arguments[0] ?? '';
        $code = str_starts_with($name, '_') ? substr($name, 1) : 400;
        $this->error($msg, (int)$code);
    }
}