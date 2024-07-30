<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\cli;

use aphp\core\Config;
use aphp\core\Tool;

class Make extends Command
{
    protected array $replace = [];

    public function cli(): bool
    {
        if (!$this->isCall) {
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 1. make:ctrl    [app_name@ctrl_name] [tpl:default] [-f]                    |\n";
            echo "| 2. make:model   [app_name@table_name] -[pk] -[tpl:default] [-f]            |\n";
            echo "| 3. make:view    [app_name@ctrl_name] -[method] -[tpl:default] [-f]         |\n";
            echo "| 4. make:widget  [app_name@widget_name] -[tag] -[tpl:default] [-f]          |\n";
            echo "| 5. make:command [app_name@command_name] -[tpl:default] [-f]                |\n";
            echo "| 6. make:app     [app_name]                                                 |\n";
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 7. clear:runtime [app_name(or *)]                                          |\n";
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|";
        }
        return true;
    }

    // 生成控制器：make:ctrl 应用名@控制器名 来源模板 -f 覆盖生成
    public function ctrl(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test'; // 应用@控制器名
        [$app, $name] = parse_app_name($name);
        $tpl = $req[1] ?? 'default'; // 默认模板
        $is_cover = isset($req[2]) && $req[2] == '-f'; // 是否覆盖
        $namespace = 'app\\' . $app; // 命名空间
        $class = name_to_camel($name); // 类名
        $tpl_file = $this->_get_tpl_file($app, $tpl, 'controller'); // 获取模板文件
        $make_file = ROOT_PATH . '/' . strtr($namespace, '\\', '/') . '/controller/' . $class . '.php'; // 生成的文件名
        // 模板替换数据
        $replace = [];
        $widget_class = 'app\\' . $app . '\\widget\\MakeCtrl';
        if (class_exists($widget_class)) {
            $replace = app($widget_class)->set($name); //获取替换数据配置
        }
        $replace['namespace'] ??= $namespace;
        $replace['class'] ??= $class;
        $replace['app'] ??= $app;
        return $this->_make_file($tpl_file, $make_file, $replace, $is_cover);
    }

    // 生成模型类：make:model 应用名@表名 主键 来源模板 -f 覆盖生成
    public function model(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test'; // 应用@模型名
        $pk = $req[1] ?? 'id'; // 主键
        $tpl = $req[2] ?? 'default'; // 模板
        $is_cover = isset($req[3]) && $req[3] == '-f'; // 是否覆盖
        [$app, $name] = parse_app_name($name);
        $namespace = 'app\\' . $app; // 命名空间
        $class = name_to_camel($name); // 类名
        $tpl_file = $this->_get_tpl_file($app, $tpl, 'model'); // 获取模板文件
        $make_file = ROOT_PATH . '/' . strtr($namespace, '\\', '/') . '/model/' . $class . '.php'; // 生成的文件名
        // 模板替换数据
        $replace = [];
        $widget_class = 'app\\' . $app . '\\widget\\MakeModel';
        if (class_exists($widget_class)) {
            $replace = app($widget_class)->set($name); //获取替换数据配置
        }
        $replace['namespace'] ??= $namespace;
        $replace['class'] ??= $class;
        $replace['table_name'] ??= $name;
        $replace['pk'] ??= $pk;
        return $this->_make_file($tpl_file, $make_file, $replace, $is_cover);
    }

    // 生成视图：make:view 应用名@控制器名 方法名 来源模板 -f 覆盖生成
    public function view(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@index'; // 应用@控制器名
        [$app, $name] = parse_app_name($name);
        $method = $req[1] ?? $name; // 方法名
        $tpl = $req[2] ?? $method; // 模板
        $is_cover = isset($req[3]) && $req[3] == '-f'; // 是否覆盖
        $tpl_file = $this->_get_tpl_file($app, $tpl, 'view'); // 获取模板文件
        $make_file = $this->_get_view_file($app, $name, $method); // 生成的文件名
        // 模板替换数据
        $replace = [];
        $widget_class = 'app\\' . $app . '\\widget\\MakeView';
        if (class_exists($widget_class)) {
            $replace = app($widget_class)->set($name, ['tpl' => $tpl]); //获取替换数据配置
        }
        return $this->_make_file($tpl_file, $make_file, $replace, $is_cover);
    }

    // 生成部件：make:widget 应用名@部件名 标签名 来源模板 -f 覆盖生成
    public function widget(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test'; // 应用@部件名
        [$app, $name] = parse_app_name($name);
        $tag = $req[1] ?? $name; // 标签名
        $tpl = $req[2] ?? 'default'; // 模板
        $is_cover = isset($req[3]) && $req[3] == '-f'; // 是否覆盖
        $namespace = 'app\\' . $app; // 命名空间
        $class = name_to_camel($name); // 类名
        $tpl_file = $this->_get_tpl_file($app, $tpl, 'widget'); // 获取模板文件
        $make_file = ROOT_PATH . '/' . strtr($namespace, '\\', '/') . '/widget/' . $class . '.php'; // 生成的文件名
        // 模板替换数据
        $replace = [];
        $widget_class = 'app\\' . $app . '\\widget\\MakeWidget';
        if (class_exists($widget_class)) {
            $replace = app($widget_class)->set($name); //获取替换数据配置
        }
        $replace['namespace'] ??= $namespace;
        $replace['class'] ??= $class;
        $replace['tag'] ??= $tag;
        return $this->_make_file($tpl_file, $make_file, $replace, $is_cover);
    }

    // 生成命令：make:command 应用名@命令名 来源模板 -f 覆盖生成
    public function command(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test'; // 应用@命令名
        [$app, $name] = parse_app_name($name);
        $tpl = $req[1] ?? $name; // 模板
        $is_cover = isset($req[2]) && $req[2] == '-f'; // 是否覆盖
        $namespace = 'app\\' . $app; // 命名空间
        $class = name_to_camel($name); // 类名
        $tpl_file = $this->_get_tpl_file($app, $tpl, 'command'); // 获取模板文件
        $make_file = ROOT_PATH . '/' . strtr($namespace, '\\', '/') . '/command/' . $class . '.php'; // 生成的文件名
        // 模板替换数据
        $replace = [];
        $widget_class = 'app\\' . $app . '\\widget\\MakeCommand';
        if (class_exists($widget_class)) {
            $replace = app($widget_class)->set($name); //获取替换数据配置
        }
        $replace['namespace'] ??= $namespace;
        $replace['class'] ??= $class;
        return $this->_make_file($tpl_file, $make_file, $replace, $is_cover);
    }

    // 生成应用：make:app 应用名
    public function app(array $req = []): ?bool
    {
        $app = $req[0] ?? 'index'; // 应用名
        $namespace = 'app\\' . $app; // 命名空间
        $path = ROOT_PATH . '/' . strtr($namespace, '\\', '/'); // 应用路径
        if (is_dir($path)) {
            return $this->error($app . ' already exists');
        }
        Tool::dir_init($path);
        $build = ['command', 'config', 'controller', 'model', 'widget'];
        foreach ($build as $dir) {
            if (!is_dir($path . '/' . $dir)) mkdir($path . '/' . $dir, 0755, true);
        }
        if (!file_exists(ROOT_PATH . '/app/common.php')) {
            file_put_contents(ROOT_PATH . '/app/common.php', "<?php\ndeclare(strict_types=1);\n//User Functions");
        }
        if (!file_exists(ROOT_PATH . '/route/' . $app . '.php')) {
            Tool::dir_init(ROOT_PATH . '/route/');
            file_put_contents(ROOT_PATH . '/route/' . $app . '.php', "<?php\nreturn [\n\t'index' => 'index/index',\n];");
        }
        cli('make:ctrl ' . $app . '@index index');
        cli('make:ctrl ' . $app . '@error error');
        cli('make:ctrl ' . $app . '@api api');
        cli('make:view ' . $app . '@index index');
        cli('make:view ' . $app . '@public jump');
        return $this->success($app . ' App Build Success');
    }

    // 获取生成的模板路径
    protected function _get_view_file(string $app, string $class, string $method): string
    {
        $path = THEME_ON ? VIEW_PATH . '/default' : VIEW_PATH;
        if ($app != APP_NAME) {
            $view_path = config_get('app.view_path', [], true);
            $theme_on = config_get('app.theme_on', [], true);
            $path = $view_path[$app] ?: ROOT_PATH . '/app/' . $app . '/view';
            if (in_array($app, $theme_on)) {
                $path .= '/default';
            }
        }
        return Tool::dir_init($path . '/' . $class) . '/' . $method . '.html';
    }

    // 获取模板文件
    protected function _get_tpl_file(string $app, string $tpl, string $type): string
    {
        $tpl_list = [
            ROOT_PATH . '/app/' . $app . '/command/make/' . $type . '/' . $tpl . '.tpl',
            ROOT_PATH . '/app/' . $app . '/command/make/' . $type . '/default.tpl',
            ROOT_PATH . '/aphp/cli/make/' . $type . '/' . $tpl . '.tpl',
        ];
        foreach ($tpl_list as $file) {
            if (is_file($file)) {
                return $file;
            }
        }
        return ROOT_PATH . '/aphp/cli/make/' . $type . '/default.tpl';
    }

    // 生成文件
    protected function _make_file(string $tpl_file, string $make_file, array $replace = [], bool $is_cover = false): ?bool
    {
        $make = substr($make_file, strlen(ROOT_PATH . '/'));
        if (!is_file($tpl_file)) {
            return $this->error(basename($tpl_file) . ' Template Not Exist');
        }
        if (!$is_cover && is_file($make_file)) {
            return $this->error($make . ' File Already Exist');
        }
        $content = file_get_contents($tpl_file);
        if (!empty($content)) {
            $this->replace = $replace;
            $content = $this->_template_replace($content);
        }
        $result = (bool)file_put_contents($make_file, $content);
        return $result ? $this->success($make . ' Build Success') : $this->error($make . ' Build Fail');
    }

    // 模板替换
    protected function _template_replace(string $content): string
    {
        return preg_replace_callback_array(
            [
                '/{{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*}}/i' => function ($match) {
                    return $this->replace[$match[1]] ?? '';
                },
                '/{{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\|default=\'(.+?)\'\s*}}/i' => function ($match) {
                    return $this->replace[$match[1]] ?? $match[2];
                },
                '/{{\s*:([a-zA-Z_][a-zA-Z0-9_]*)\(\'(.*?)\'\)\s*}}/i' => function ($match) {
                    return $match[1]($match[2]);
                },
                '/{{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*==\s*\'(.+?)\'\s*\?\s*\'(.+?)\'\s*:\s*\'(.+?)\'\s*}}/i' => function ($match) {
                    return (isset($this->replace[$match[1]]) && $this->replace[$match[1]] == $match[2]) ? $match[3] : $match[4];
                }
            ],
            $content
        );
    }
}