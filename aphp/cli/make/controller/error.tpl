<?php
declare(strict_types=1);
namespace {{NAMESPACE}}\controller;
use aphp\core\Jump;
class {{CLASS}}
{
	use Jump;
    public function __call(string $name, array $arguments)
    {
        $msg = $arguments[0] ?? '';
        $code = str_starts_with($name, '_') ? substr($name, 1) : 400;
        $this->error($msg, (int)$code);
    }
}