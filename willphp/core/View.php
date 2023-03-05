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

use Exception;

class View
{
    use Single;

    protected static array $vars = []; //模板变量
    protected string $viewFile; //模板文件
    protected string $compileFile; //模板编译文件
    protected string $hash; //页面标识
    protected $expire = false; //模板缓存时间

    private function __construct()
    {
        if (get_config('view.cache', false)) {
            $this->expire = get_config('view.expire', 0);
        }
        $this->hash = $this->getHash();
    }

    public function getHash(string $route = ''): string
    {
        return 'view.' . md5(Route::init()->getPath($route));
    }

    public function cache(int $expire = 0): View
    {
        $this->expire = $expire;
        return $this;
    }

    protected function getCache()
    {
        return Cache::driver()->get($this->hash);
    }

    protected function setCache($html)
    {
        return Cache::driver()->set($this->hash, $html, $this->expire);
    }

    public function make(string $file = '', array $vars = []): View
    {
        return $this->setFile($file)->with($vars);
    }

    public function fetch(string $file = '', array $vars = []): string
    {
        return $this->make($file, $vars)->parse();
    }

    public function toString(): string
    {
        if (false !== $this->expire && ($cache = $this->getCache())) {
            return $cache;
        }
        $html = $this->parse();
        if (false !== $this->expire && $this->expire >= 0) {
            $this->setCache($html);
        }
        return $html;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    protected function parse(): string
    {
        $this->compile();
        ob_start();
        extract(self::$vars);
        include $this->compileFile;
        return ob_get_clean();
    }

    protected function compile(): View
    {
        if (APP_DEBUG || !is_file($this->compileFile) || (filemtime($this->viewFile) > filemtime($this->compileFile))) {
            is_dir(dirname($this->compileFile)) or mkdir(dirname($this->compileFile), 0755, true);
            $content = file_get_contents($this->viewFile);
            $content = Template::compile($content);
            $content = $this->csrf($content);
            file_put_contents($this->compileFile, $content);
        }
        return $this;
    }

    protected function csrf($content)
    {
        if (get_config('view.csrf_check', false)) {
            $content = preg_replace('#(<form.*>)#', '$1' . PHP_EOL . '<?php echo csrf_field();?>', $content);
        }
        return $content;
    }

    protected function getCookieTheme(): string
    {
        $theme = cookie('theme');
        $get_t = input('get.t', '', 'clear_html');
        if (!empty($get_t) && $get_t != $theme) {
            cookie('theme', $get_t);
            $theme = $get_t;
        }
        return $theme;
    }

    public function view_check(string $file = '')
    {
        if (!defined('__THEME__')) {
            if (THEME_ON && $theme = $this->getCookieTheme()) {
                define('__THEME__', $theme);
            } else {
                define('__THEME__', get_config('site.theme', 'default'));
            }
            define('THEME_PATH', THEME_ON ? VIEW_PATH . '/' . __THEME__ : VIEW_PATH);
        }
        if (empty($file)) {
            $file = $this->getViewFile($file);
        }
        $viewFile = THEME_PATH . '/' . $file;
        if (!file_exists($viewFile) && THEME_ON) {
            $viewFile = VIEW_PATH . '/default/' . $file;
        }
        return file_exists($viewFile) ? $viewFile : false;
    }

    public function setFile(string $file = ''): View
    {
        $file = $this->getViewFile($file);
        $viewFile = $this->view_check($file);
        if (!$viewFile) {
            $theme = THEME_ON ? '[' . __THEME__ . ']' : '';
            throw new Exception($theme . $file . ' 模板文件不存在');
        }
        $this->viewFile = $viewFile;
        $theme = THEME_ON ? __THEME__ . '/' : '';
        $this->compileFile = RUNTIME_PATH . '/view/' . $theme . preg_replace('/\W/', '_', $file) . '.php';
        return $this;
    }

    public function getViewFile(string $file = ''): string
    {
        $dir = Route::init()->getController();
        if (empty($file)) {
            $file = Route::init()->getAction();
        } elseif (strpos($file, ':')) {
            [$dir, $file] = explode(':', $file);
        } elseif (strpos($file, '/')) {
            $dir = '';
        }
        $file = trim($dir . '/' . $file, '/');
        if (!preg_match('/\.[a-z]+$/i', $file)) {
            $file .= get_config('view.prefix', '.html');
        }
        return $file;
    }

    public function with($vars = [], $value = ''): View
    {
        if (is_array($vars)) {
            foreach ($vars as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $this->set($vars, $value);
        }
        return $this;
    }

    protected function set(string $key, $value): void
    {
        array_dot_set(self::$vars, $key, $value);
    }
}