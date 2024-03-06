<?php
declare(strict_types=1);
namespace {{NAMESPACE}}\controller;
use aphp\core\Jump;
class {{CLASS}}
{
    use Jump;
    public function index()
    {
        return view();
    }
    public function ok()
    {
        $this->success();
    }
}