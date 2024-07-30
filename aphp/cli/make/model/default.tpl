<?php
declare(strict_types=1);
namespace {{$namespace}}\model;
use aphp\core\Model;
class {{$class}} extends Model
{
	protected string $table = '{{$table_name}}';
	protected string $pk = '{{$pk|default='pk'}}';
}