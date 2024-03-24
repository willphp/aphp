<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\core;

use Exception;

class View
{
    use Single;

    protected static array $vars = [];
    protected array $route;
    protected string $theme = ''; //theme
    protected string $viewPath;
    protected string $viewFile;
    protected string $compileFile;
    protected int $expire = -1; //cache time -1 no cache，0 cache，>0 cache n second

    private function __construct()
    {
        $this->route = Route::init()->getRoute();
        if (Config::init()->get('view.cache', false)) {
            $this->expire = Config::init()->get('view.expire', 0);
        }
        if (THEME_ON) {
            $this->theme = $this->getTheme();
        }
        define('__THEME__', $this->theme);
        $this->viewPath = rtrim(VIEW_PATH . '/'. $this->theme, '/');
    }

    protected function getTheme(): string
    {
        $default = Config::init()->get('site.theme', 'default');
        $theme_get = Config::init()->get('app.theme_get');
        if (empty($theme_get)) {
            return $default;
        }
        $theme = input('get.'.$theme_get, '', 'clear_html');
        if (!empty($theme)) {
            Cookie::init()->set('__theme__', $theme);
            return $theme;
        }
        return Cookie::init()->get('__theme__', $default);
    }

    public function with($vars = [], $value = ''): object
    {
        if (!empty($vars)) {
            if (is_array($vars)) {
                foreach ($vars as $k => $v) {
                    Tool::arr_set(self::$vars, $k, $v);
                }
            } else {
                Tool::arr_set(self::$vars, $vars, $value);
            }
        }
        return $this;
    }

    public function cache(int $expire = 0): object
    {
        $this->expire = $expire;
        return $this;
    }

    public function make(string $tpl = '', array $vars = [], bool $isCall = false): object
    {
        return $this->setTpl($tpl, $isCall)->with($vars);
    }

    public function fetch(string $tpl = '', array $vars = []): string
    {
        return $this->make($tpl, $vars)->parse();
    }

    protected function setTpl(string $tpl = '', bool $isCall = false): object
    {
        $dir = $this->route['controller'];
        if (empty($tpl)) {
            $tpl = $this->route['action'];
        } elseif (strpos($tpl, ':')) {
            [$dir, $tpl] = explode(':', $tpl);
        } elseif (strpos($tpl, '/')) {
            $dir = '';
        }
        $tpl = trim($dir . '/' . $tpl, '/');
        if (!preg_match('/\.[a-z]+$/i', $tpl)) {
            $tpl .= Config::init()->get('view.suffix', '.html');
        }
        $this->viewFile = $this->viewPath . '/' . $tpl;
        if (THEME_ON && !file_exists($this->viewFile)) {
            $this->viewFile = VIEW_PATH . '/default/' . $tpl;
        }
        if (!file_exists($this->viewFile) && !$isCall) {
            throw new Exception($tpl . ' Template Not Exist');
        }
        $this->compileFile = rtrim(RUNTIME_PATH . '/view/'. $this->theme, '/') . '/' . preg_replace('/\W/', '_', $tpl) . '.php';
        return $this;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if (IS_CLI || !file_exists($this->viewFile)) {
            return '';
        }
        if ($this->expire >= 0) {
            return Cache::init()->make('view/' . md5($this->route['path']), fn() => $this->parse(), $this->expire);
        }
        return $this->parse();
    }

    protected function parse(): string
    {
        $this->compile();
        ob_start();
        extract(self::$vars, EXTR_SKIP);
        include $this->compileFile;
        return ob_get_clean();
    }

    protected function compile(): object
    {
        if (!is_file($this->compileFile) || (filemtime($this->viewFile) > filemtime($this->compileFile))) {
            Tool::dir_init(dirname($this->compileFile), 0777);
            $content = file_get_contents($this->viewFile);
            $content = Template::compile($content, $this->viewPath);
            file_put_contents($this->compileFile, $content);
        }
        return $this;
    }
}