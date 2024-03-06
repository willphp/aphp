<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;
class Template
{
    public static function compile(string $content, string $viewPath): string
    {
        $config = Config::init()->get('template', []);
        if (empty($config)) {
            return $content;
        }
        $b_limit = $config['{'] ?? '{';
        $e_limit = $config['}'] ?? '}';
        $suffix = Config::init()->get('view.suffix', '.html');
        $config['regex_layout'] ??= '/{\s*layout\s+name\s*=\s*[\"\']?([a-zA-Z0-9_\/]*'.$suffix.')[\"\']?\s*}/i';
        $config['regex_include'] ??= '/{\s*include\s+file\s*=\s*[\"\']?([a-zA-Z0-9_\/]*'.$suffix.')[\"\']?\s*}/i';
        $config['regex_literal'] ??= '/{literal}(.*?){\/literal}/s';
        $config['regex_replace'] ??= [];
        $config['str_replace'] ??= [];
        //模板布局处理
        $regex_layout = str_replace(['{', '}'], [$b_limit, $e_limit], $config['regex_layout']); //布局模板正则
        preg_match($regex_layout, $content, $layout);
        if (!empty($layout)) {
            $layout_file = $viewPath . '/' . $layout[1];
            if (is_file($layout_file)) {
                $layout_content = str_replace($layout[0], '', $content);
                $layout_main = file_get_contents($layout_file);
                $content = str_replace('{__CONTENT__}', $layout_content, $layout_main);
            }
        }
        //文件包含处理
        $regex_include = str_replace(['{', '}'], [$b_limit, $e_limit], $config['regex_include']);
        $content = self::parseInclude($content, $regex_include, $viewPath);
        //原样输出处理
        $regex_literal = str_replace(['{', '}'], [$b_limit, $e_limit], $config['regex_literal']);
        $content = preg_replace_callback($regex_literal, fn($match) => str_replace([$b_limit, $e_limit], [$b_limit . '#', '#' . $e_limit], $match[1]), $content);
        //正则替换
        $var = $config['var'] ?? '([a-zA-Z_][a-zA-Z0-9_]*)';
        $key = $config['key'] ?? '([a-zA-Z0-9_]*)';
        if (!empty($config['regex_replace'])) {
            $regex = str_replace(['{', '}', 'var', 'key'], [$b_limit, $e_limit, $var, $key], array_keys($config['regex_replace']));
            $match = array_values($config['regex_replace']);
            $content = preg_replace($regex, $match, $content);
        }
        //字符串替换
        if (!empty($config['str_replace'])) {
            $content = str_replace(array_keys($config['str_replace']), array_values($config['str_replace']), $content);
        }
        return $content;
    }

    protected static function parseInclude(string $content, string $regex_include, string $viewPath): string
    {
        $content = preg_replace_callback(
            $regex_include,
            fn($match) => is_file($viewPath . '/' . $match[1]) ? file_get_contents($viewPath . '/' . $match[1]) : $match[1],
            $content
        );
        if (preg_match($regex_include, $content)) {
            return self::parseInclude($content, $regex_include, $viewPath);
        }
        return $content;
    }
}