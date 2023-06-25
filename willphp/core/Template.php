<?php
/*----------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: 113344.com
 |----------------------------------------------------------------
 | Author: 无念 <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2023, 113344.com. All Rights Reserved.
 |---------------------------------------------------------------*/
declare(strict_types=1);

namespace willphp\core;

class Template
{
    public static function compile(string $content): string
    {
        $config = Config::init()->get('template', []);
        if (empty($config)) {
            die('模板配置读取失败!');
        }
        $b_limit = $config['{'] ?? '{';
        $e_limit = $config['}'] ?? '}';
        $regex_literal = str_replace(['{', '}'], [$b_limit, $e_limit], $config['regex_literal']);
        $content = preg_replace_callback($regex_literal, fn($match) => str_replace([$b_limit, $e_limit], [$b_limit . '#', '#' . $e_limit], $match[1]), $content);
        $regex_include = str_replace(['{', '}'], [$b_limit, $e_limit], $config['regex_include']);
        $content = self::parseInclude($content, $regex_include);
        $var = $config['var'] ?? '([a-zA-Z_][a-zA-Z0-9_]*)';
        $key = $config['key'] ?? '([a-zA-Z0-9_]*)';
        $config['regex_replace'] ??= [];
        if (!empty($config['regex_replace'])) {
            $regex = str_replace(['{', '}', 'var', 'key'], [$b_limit, $e_limit, $var, $key], array_keys($config['regex_replace']));
            $match = array_values($config['regex_replace']);
            $content = preg_replace($regex, $match, $content);
        }
        $config['str_replace'] ??= [];
        if (!empty($config['str_replace'])) {
            $content = str_replace(array_keys($config['str_replace']), array_values($config['str_replace']), $content);
        }
        return $content;
    }

    protected static function parseInclude(string $content, string $regex_include): string
    {
        $content = preg_replace_callback(
            $regex_include,
            fn($match) => is_file(THEME_PATH . '/' . $match[1]) ? file_get_contents(THEME_PATH . '/' . $match[1]) : $match[1],
            $content
        );
        if (preg_match($regex_include, $content)) {
            return self::parseInclude($content, $regex_include);
        }
        return $content;
    }
}