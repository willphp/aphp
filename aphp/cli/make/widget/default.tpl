<?php
declare(strict_types=1);
namespace {{$namespace}}\widget;
use aphp\core\Widget;
class {{$class}} extends Widget
{
    protected string $tag = '{{$tag}}';
    protected int $expire = 0;
    public function set($id = '', array $options = [])
    {
        return date('Y-m-d H:i:s');
    }
}