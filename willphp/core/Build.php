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
class Build
{
    public function initApp(): void
    {
        $lock = ROOT_PATH . '/app/build.lock';
        if (!is_writable($lock)) {
            if (!touch($lock)) {
                exit('请确认app目录存在，并设置权限可写！');
            }
            if (!is_dir(RUNTIME_PATH)) mkdir(RUNTIME_PATH, 0777, true);
            if (!is_dir(APP_PATH)) mkdir(APP_PATH, 0755, true);
            $themePath = THEME_ON ? VIEW_PATH . '/' . get_config('site.theme', 'default') : VIEW_PATH;
            $dirs = [APP_PATH . '/config', APP_PATH . '/controller', APP_PATH . '/model', APP_PATH . '/widget', $themePath . '/public', $themePath . '/index'];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) mkdir($dir, 0755, true);
            }
            //自定义函数文件
            if (!file_exists(ROOT_PATH . '/app/common.php')) {
                file_put_contents(ROOT_PATH . '/app/common.php', "<?php\n//自定义函数");
            }
            //生成路由
            if (!file_exists(ROOT_PATH . '/route/' . APP_NAME . '.php')) {
                file_put_contents(ROOT_PATH . '/route/' . APP_NAME . '.php', "<?php\nreturn [\n\t'index' => 'index/index',\n];");
            }
            //默认控制器
            $c_index = "<?php\ndeclare(strict_types=1);\nnamespace app\\" . APP_NAME . "\\controller;\nclass Index\n{\n\tpublic function index()\n\t{\n\t\treturn view();\n\t}\n}";
            file_put_contents(APP_PATH . '/controller/Index.php', $c_index);
            //API控制器
            $c_api = "<?php\ndeclare(strict_types=1);\nnamespace app\\" . APP_NAME . "\\controller;\nclass Api\n{\n\tuse \\willphp\\core\\Jump;\n\tpublic function clear(): void\n\t{\n\t\tcache(null);\n\t\tupdate_config();\n\t\t\$this->success('清除缓存成功', 'index/index');\n\t}";
            $c_api .= "\n\tpublic function captcha()\n\t{\n\t\treturn (new \\extend\\captcha\\Captcha())->make();\n\t}\n}";
            file_put_contents(APP_PATH . '/controller/Api.php', $c_api);
            //错误处理控制器
            $c_err = "<?php\ndeclare(strict_types=1);\nnamespace app\\" . APP_NAME . "\\controller;\nclass Error\n{\n\tuse \\willphp\\core\\Jump;\n\tpublic function __call(\$method, \$args)";
            $c_err .= "\n\t{\n\t\t\$msg = \$args[0] ?? '出错了！';\n\t\t\$code = str_starts_with(\$method, '_') ? substr(\$method, 1) : 400;\n\t\t\$this->error(\$msg, (int)\$code);\n\t}\n}";
            file_put_contents(APP_PATH . '/controller/Error.php', $c_err);
            //首页模板
            $t_index = file_get_contents(ROOT_PATH . '/willphp/tpl/index.tpl');
            file_put_contents($themePath . '/index/index.html', $t_index);
            //转跳模板
            $t_jump = file_get_contents(ROOT_PATH . '/willphp/tpl/jump.tpl');
            file_put_contents($themePath . '/public/jump.html', $t_jump);
            //删除锁定文件
            unlink($lock);
        }
    }
}