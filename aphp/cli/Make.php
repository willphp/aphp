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
    public function cli(): bool
    {
        if (!$this->isCall) {
            echo "\n++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
            echo "0. make:app     -[app]                                      \n";
            echo "1. make:ctrl    -[app@ctrl] -[tpl]                          \n";
            echo "2. make:model   -[app@table] -[pk] -[tpl]                   \n";
            echo "3. make:widget  -[app@name] -[tag] -[tpl]                   \n";
            echo "4. make:command -[app@name] -[tpl]                          \n";
            echo "5. make:view    -[app@ctrl] -[method] -[tpl]                \n";
            echo "++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
        }
        return true;
    }

    public function app(array $req = []): ?bool
    {
        $app = $req[0] ?? 'index';
        $namespace = 'app\\' . $app;
        $path = APHP_TOP . '/' . strtr($namespace, '\\', '/');
        if (is_dir($path)) {
            return $this->error($app . ' already exists');
        }
        Tool::dir_init($path);
        $build = ['command', 'config', 'controller', 'model', 'widget'];
        foreach ($build as $dir) {
            if (!is_dir($path . '/' . $dir)) mkdir($path . '/' . $dir, 0755, true);
        }
        if (!file_exists(APHP_TOP . '/app/common.php')) {
            file_put_contents(APHP_TOP . '/app/common.php', "<?php\ndeclare(strict_types=1);\n//User-Defined Functions");
        }
        if (!file_exists(APHP_TOP . '/route/' . $app . '.php')) {
            file_put_contents(APHP_TOP . '/route/' . $app . '.php', "<?php\nreturn [\n\t'index' => 'index/index',\n];");
        }
        cli('make:ctrl ' . $app . '@index index');
        cli('make:ctrl ' . $app . '@error error');
        cli('make:ctrl ' . $app . '@api api');
        cli('make:view ' . $app . '@index index');
        cli('make:view ' . $app . '@public jump');
        return $this->success($app . ' App Build Success');
    }

    public function ctrl(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test';
        [$app, $name] = parse_app_name($name);
        $tpl = $req[1] ?? 'default';
        $namespace = 'app\\' . $app;
        $class = name_camel($name);
        $tpl_file = $this->_parse_tpl_file($tpl, 'controller', $app);
        $make_file = APHP_TOP . '/' . strtr($namespace, '\\', '/') . '/controller/' . $class . '.php';
        $replace = [
            '{{NAMESPACE}}' => $namespace,
            '{{CLASS}}' => $class,
        ];
        return $this->_make_file($tpl_file, $make_file, $replace);
    }

    public function model(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test';
        $pk = $req[1] ?? 'id';
        $tpl = $req[2] ?? 'default';
        [$app, $name] = parse_app_name($name);
        $namespace = 'app\\' . $app;
        $class = name_camel($name);
        $tpl_file = $this->_parse_tpl_file($tpl, 'model', $app);
        $make_file = APHP_TOP . '/' . strtr($namespace, '\\', '/') . '/model/' . $class . '.php';
        $replace = [
            '{{NAMESPACE}}' => $namespace,
            '{{CLASS}}' => $class,
            '{{TABLE}}' => $name,
            '{{PK}}' => $pk,
        ];
        return $this->_make_file($tpl_file, $make_file, $replace);
    }

    public function widget(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test';
        [$app, $name] = parse_app_name($name);
        $tag = $req[1] ?? $name;
        $tpl = $req[2] ?? 'default';
        $namespace = 'app\\' . $app;
        $class = name_camel($name);
        $tpl_file = $this->_parse_tpl_file($tpl, 'widget', $app);
        $make_file = APHP_TOP . '/' . strtr($namespace, '\\', '/') . '/widget/' . $class . '.php';
        $replace = [
            '{{NAMESPACE}}' => $namespace,
            '{{CLASS}}' => $class,
            '{{TAG}}' => $tag,
        ];
        return $this->_make_file($tpl_file, $make_file, $replace);
    }

    public function command(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@test';
        [$app, $name] = parse_app_name($name);
        $tpl = $req[1] ?? $name;
        $namespace = 'app\\' . $app;
        $class = name_camel($name);
        $tpl_file = $this->_parse_tpl_file($tpl, 'command', $app);
        $make_file = APHP_TOP . '/' . strtr($namespace, '\\', '/') . '/command/' . $class . '.php';
        $replace = [
            '{{NAMESPACE}}' => $namespace,
            '{{CLASS}}' => $class,
        ];
        return $this->_make_file($tpl_file, $make_file, $replace);
    }

    public function view(array $req = []): ?bool
    {
        $name = $req[0] ?? 'index@index';
        [$app, $name] = parse_app_name($name);
        $method = $req[1] ?? $name;
        $tpl = $req[2] ?? $method;
        $tpl_file = $this->_parse_tpl_file($tpl, 'view', $app);
        if ($app == APP_NAME) {
            $view_dir = THEME_ON ? VIEW_PATH . '/default' : VIEW_PATH;
        } else {
            $config = Config::init()->get('app');
            $view_dir = !empty($config['view_path'][$app]) ? APHP_TOP . '/' . $config['view_path'][$app] : APHP_TOP . '/app/' . $app . '/view';
            if (!empty($config['theme_on']) && in_array($app, $config['theme_on'])) {
                $view_dir .= '/default';
            }
        }
        $make_dir = Tool::dir_init($view_dir . '/' . $name);
        $make_file = $make_dir . '/' . $method . '.html';
        return $this->_make_file($tpl_file, $make_file);
    }

    protected function _parse_tpl_file(string $tpl, string $type, string $app = ''): string
    {
        if (empty($app)) {
            $app = APP_NAME;
        }
        $file = APHP_TOP . '/app/' . $app . '/command/make/' . $type . '/' . $tpl . '.tpl';
        if (!is_file($file)) {
            $file = APHP_TOP . '/aphp/cli/make/' . $type . '/' . $tpl . '.tpl';
            if (!is_file($file)) {
                $file = APHP_TOP . '/aphp/cli/make/' . $type . '/default.tpl';
            }
        }
        return $file;
    }

    protected function _make_file(string $tpl_file, string $make_file, array $replace = []): ?bool
    {
        $make = substr($make_file, strlen(APHP_TOP . '/'));
        if (!is_file($tpl_file)) {
            return $this->error(basename($tpl_file) . ' Template Not Exist');
        }
        if (is_file($make_file)) {
            return $this->error($make . ' File Already Exist');
        }
        $content = file_get_contents($tpl_file);
        if (!empty($content)) {
            $search = array_keys($replace);
            $to = array_values($replace);
            $content = str_replace($search, $to, $content);
        }
        $result = (bool)file_put_contents($make_file, $content);
        return $result ? $this->success($make . ' Build Success') : $this->error($make . ' Build Fail');
    }
}