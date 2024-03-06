<?php
declare(strict_types=1);
namespace {{NAMESPACE}}\widget;
use aphp\core\Widget;
class {{CLASS}} extends Widget
{
    protected string $tag = '{{TAG}}';
    protected int $expire = 0;
    public function set($id = '', array $options = [])
    {
        return date('Y-m-d H:i:s');
    }
}