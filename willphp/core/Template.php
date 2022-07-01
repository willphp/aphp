<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\core;
/**
 * 模板引擎
 */
class Template {
	/**
	 * 编译模板
	 * @return $this
	 */
	public static function compile($content, $vars = []) {
		$left = Config::get('view.left_delimiter', '\{');
		$right = Config::get('view.right_delimiter', '\}');		
		$content = self::parseInclude($content);
		$var_name = '([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'; //匹配变量名
		$key_name = '([a-zA-Z0-9_\x7f-\xff]*)'; //匹配键名
		$pattern = [
				'/__(\w+)__/i', //__ROOT__常量
				'/'.$left.'\s*\$'.$var_name.'\s*'.$right.'/i', //$变量名
				'/'.$left.'\s*\$'.$var_name.'\.'.$key_name.'\s*'.$right.'/i', //$变量名.键名
				'/'.$left.'\s*\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\s*'.$right.'/i', //$变量名[键名]
				'/'.$left.'\s*\$'.$var_name.'\[\$'.$var_name.'\]\s*'.$right.'/i', //$变量名[$变量名]  
				'/'.$left.'\s*\$'.$var_name.'\[\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\]\s*'.$right.'/i', //$变量名[$变量名[键名]]
				'/'.$left.'\s*\$'.$var_name.'\.'.$key_name.'\.'.$key_name.'\s*'.$right.'/i', //$变量名.键名.键名
				'/'.$left.'\s*\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\[[\'"]?'.$key_name.'[\'"]?\]\s*'.$right.'/i', //$变量名[键名][键名]
				'/'.$left.'\s*\$'.$var_name.'\.'.$key_name.'\.'.$key_name.'\.'.$key_name.'\s*'.$right.'/i', //$变量名.键名.键名.键名
				'/'.$left.'\s*\$'.$var_name.'\[[\'"]?'.$key_name.'[\'"]?\]\[[\'"]?'.$key_name.'[\'"]?\]\[[\'"]?'.$key_name.'[\'"]?\]\s*'.$right.'/i', //$变量名[键名][键名][键名]
				'/'.$left.'\s*var\s+\$'.$var_name.'\s*=\s*(.+?)\s*'.$right.'/i', //var $变量名=*
				'/'.$left.'\s*:'.$var_name.'\((.*?)\)\s*'.$right.'/i', //: 函数名称(*)
				'/'.$left.'if \s*(.+?)\s*'.$right.'/i', // if (*)
				'/'.$left.'\s*\/(if|foreach)\s*'.$right.'/i', // end if | end foreach
				'/'.$left.'\s*else\s*'.$right.'/i', //else
				'/'.$left.'\s*(else if|elseif)\s*(.+?)\s*'.$right.'/i', //else if (*)
				'/'.$left.'\s*foreach\s+\$'.$var_name.'\s+as\s+\$'.$var_name.'\s*'.$right.'/i', //foreach $数组 as $变量
				'/'.$left.'\s*foreach\s+\$'.$var_name.'\s+as\s+\$'.$var_name.'\s*=>\s*\$'.$var_name.'\s*'.$right.'/i', //foreach $数组 as $键名=>$键值
				'/'.$left.'\s*empty\s*(.+?)\s*'.$right.'/i', // foreach 中 empty $数组
				'/'.$left.'\s*\$'.$var_name.'\|'.$var_name.'\s*'.$right.'/i', //$变量名|函数
				'/'.$left.'\s*\$'.$var_name.'\.'.$key_name.'\|'.$var_name.'\s*'.$right.'/i', //$变量名.键名|函数
				'/'.$left.'\s*\$'.$var_name.'\|'.$var_name.'=(.+?)\s*'.$right.'/i', //$变量名|函数=参数
				'/'.$left.'\s*\$'.$var_name.'\.'.$key_name.'\|'.$var_name.'=(.+?)\s*'.$right.'/i', //$变量名.变量名|函数=参数				
				'/'.$left.'\s*:'.$var_name.'\((.*?)\)\->'.$var_name.'\((.*?)\)\s*'.$right.'/i', //:函数()->方法()
				'/'.$left.'\s*\$'.$var_name.'\->'.$var_name.'\((.*?)\)\s*'.$right.'/i', //$对象名->方法()
		]; //正则
		$replace = [
				'<?php echo __\\1__; ?>',
				'<?php echo $\\1; ?>',
				'<?php echo $\\1[\'\\2\']; ?>',
				'<?php echo $\\1[\'\\2\']; ?>',
				'<?php echo $\\1[$\\2]; ?>',
				'<?php echo $\\1[$\\2[\'\\3\']]; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\']; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\']; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\']; ?>',
				'<?php echo $\\1[\'\\2\'][\'\\3\'][\'\\4\']; ?>',
				'<?php $\\1 = \\2; ?>',
				'<?php echo \\1(\\2); ?>',
				'<?php if (\\1) { ?>',
				'<?php } ?>',
				'<?php } else { ?>',
				'<?php } elseif (\\2) { ?>',
				'<?php foreach($\\1 as $\\2) { ?>',
				'<?php foreach($\\1 as $\\2 => $\\3) { ?>',
				'<?php } if (empty(\\1)) { ?>',
				'<?php echo \\2($\\1); ?>',
				'<?php echo \\3($\\1[\'\\2\']); ?>',
				'<?php echo \\2($\\1,\\3); ?>',
				'<?php echo \\3($\\1[\'\\2\'],\\4); ?>',
				'<?php echo \\1(\\2)->\\3(\\4); ?>',
				'<?php echo $\\1->\\2(\\3); ?>',
				
		]; //替换
		$content = preg_replace($pattern, $replace, $content);
		return $content;
	}
	/**
	 * 文件包含处理
	 * @return $this
	 */
	protected static function parseInclude($content) {
		$left = Config::get('view.left_delimiter', '\{\{');
		$right = Config::get('view.right_delimiter', '\}\}');			
		$content = preg_replace_callback('/'.$left.'\s*include\s+[\"\']?(.+?)[\"\']?\s*'.$right.'/i', function ($match) {
			return is_file(THEME_PATH.'/'.$match[1])? file_get_contents(THEME_PATH.'/'.$match[1]) : '['.$match[1].']';
		}, $content);
		if (preg_match('/'.$left.'\s*include\s+[\"\']?(.+?)[\"\']?\s*'.$right.'/i', $content)) {
			return self::parseInclude($content);
		}
		return $content;
	}
}