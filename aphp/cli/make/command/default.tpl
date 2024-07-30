<?php
declare(strict_types=1);
namespace {{$namespace}}\command;
use aphp\cli\Command;
class {{$class}} extends Command
{
	public function cli()
	{
		echo __METHOD__;
	}
}