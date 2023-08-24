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
    protected int $expire = -1; //模板缓存时间 <0不缓存，0永久，>0缓存n秒

    private function __construct()
    {
        if (Config::init()->get('view.cache', false)) {
            $this->expire = Config::init()->get('view.expire', 0);
        }
        $this->hash = $this->getHash();
        define('__THEME__', $this->getTheme());
        define('THEME_PATH', THEME_ON ? VIEW_PATH . '/' . __THEME__ : VIEW_PATH);
    }

    protected function getTheme(): string
    {
        $default = Config::init()->get('site.theme', 'default');
        $themeGet = Config::init()->get('app.theme_get');
        if (empty($themeGet)) {
            return $default;
        }
        $theme = Cookie::init()->get('theme', $default);
        $getTheme = input('get.'.$themeGet, '', 'clear_html');
        if (!empty($getTheme) && $getTheme != $theme) {
            Cookie::init()->set('theme', $getTheme);
            $theme = $getTheme;
        }
        return $theme;
    }

    public function cache(int $expire = 0): object
    {
        $this->expire = $expire;
        return $this;
    }

    public function with($vars = [], $value = ''): object
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

    public function set(string $name, $value): void
    {
        Arr::set(self::$vars, $name, $value);
    }

    public function getHash(string $route = ''): string
    {
        return 'view/' . md5(Route::init()->getPath($route));
    }

    public function make(string $tpl = '', array $vars = []): object
    {
        return $this->setTpl($tpl)->with($vars);
    }

    public function fetch(string $tpl = '', array $vars = []): string
    {
        return $this->make($tpl, $vars)->parse();
    }

    public function setTpl(string $tpl = ''): object
    {
        $tpl = $this->getTpl($tpl);
        $viewFile = $this->getFile($tpl);
        $theme = '';
        if (!$viewFile) {
            if (THEME_ON) {
                $theme = '[' . __THEME__ . ']';
            }
            throw new Exception($theme . $tpl . ' 模板文件不存在');
        }
        $this->viewFile = $viewFile;
        if (THEME_ON) {
            $theme = __THEME__ . '/';
        }
        $this->compileFile = RUNTIME_PATH . '/view/' . $theme . preg_replace('/\W/', '_', $tpl) . '.php';
        return $this;
    }

    public function getTpl(string $tpl = ''): string
    {
        $dir = Route::init()->getController();
        if (empty($tpl)) {
            $tpl = Route::init()->getAction();
        } elseif (strpos($tpl, ':')) {
            [$dir, $tpl] = explode(':', $tpl);
        } elseif (strpos($tpl, '/')) {
            $dir = '';
        }
        $tpl = trim($dir . '/' . $tpl, '/');
        if (!preg_match('/\.[a-z]+$/i', $tpl)) {
            $tpl .= Config::init()->get('view.prefix', '.html');
        }
        return $tpl;
    }

    public function getFile(string $tpl = ''): string
    {
        if (empty($tpl)) {
            $tpl = $this->getTpl();
        }
        $file = (THEME_ON && !file_exists(THEME_PATH . '/' . $tpl)) ? VIEW_PATH . '/default/' . $tpl : THEME_PATH . '/' . $tpl;
        return file_exists($file) ? $file : '';
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if ($this->expire >= 0 && ($cache = Cache::init()->get($this->hash))) {
            return $cache;
        }
        $html = $this->parse();
        if ($this->expire >= 0) {
            Cache::init()->set($this->hash, $html, $this->expire);
        }
        return $html;
    }

    protected function parse(): string
    {
        $this->compile();
        ob_start();
        extract(self::$vars);
        include $this->compileFile;
        return ob_get_clean();
    }

    protected function compile(): object
    {
        //APP_DEBUG ||
        if (!is_file($this->compileFile) || (filemtime($this->viewFile) > filemtime($this->compileFile))) {
            Dir::make(dirname($this->compileFile), 0777);
            $content = file_get_contents($this->viewFile);
            $content = Template::compile($content);
            if (Config::init()->get('view.csrf_check', false)) {
                $content = preg_replace('#(<form.*>)#', '$1' . PHP_EOL . '<?php echo csrf_field();?>', $content);
            }
            file_put_contents($this->compileFile, $content);
        }
        return $this;
    }
}