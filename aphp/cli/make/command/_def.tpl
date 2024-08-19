<?php
declare(strict_types=1);
namespace {{$namespace}}\command;
use aphp\cli\Command;
class {{$class}} extends Command
{
	public function cli(): bool
	{
        if (!$this->isCall) {
            echo "Run method：".__METHOD__."\n";
        }
        return true;
	}
}