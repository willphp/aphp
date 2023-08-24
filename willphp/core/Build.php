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
/**
 * 初始化生成应用类
 */
class Build
{
    public static function make(): void
    {
        $lock = ROOT_PATH . '/app/build.lock';
        if (!is_writable($lock)) {
            if (!touch($lock)) exit('请确认app目录存在，并设置权限可写！');
            if (!is_dir(RUNTIME_PATH)) mkdir(RUNTIME_PATH, 0777, true);
            if (!is_dir(APP_PATH)) mkdir(APP_PATH, 0755, true);
            $viewPath = THEME_ON ? VIEW_PATH . '/' . Config::init()->get('site.theme', 'default') : VIEW_PATH;
            $build = [APP_PATH . '/config', APP_PATH . '/controller', APP_PATH . '/model', APP_PATH . '/widget', $viewPath . '/public', $viewPath . '/index'];
            foreach ($build as $dir) {
                if (!is_dir($dir)) mkdir($dir, 0755, true);
            }
            //自定义函数
            if (!file_exists(ROOT_PATH . '/app/common.php')) {
                file_put_contents(ROOT_PATH . '/app/common.php', "<?php\ndeclare(strict_types=1);\n//自定义函数");
            }
            //生成路由
            if (!file_exists(ROOT_PATH . '/route/' . APP_NAME . '.php')) {
                file_put_contents(ROOT_PATH . '/route/' . APP_NAME . '.php', "<?php\nreturn [\n\t'index' => 'index/index',\n];");
            }
            //默认控制器
            $c_index = "<?php\ndeclare(strict_types=1);\nnamespace app\\" . APP_NAME . "\\controller;\nclass Index\n{\n\tpublic function index()\n\t{\n\t\treturn view();\n\t}\n}";
            file_put_contents(APP_PATH . '/controller/Index.php', $c_index);
            //错误处理控制器
            $c_error = "<?php\ndeclare(strict_types=1);\nnamespace app\\" . APP_NAME . "\\controller;\nuse willphp\\core\\Jump;\nclass Error\n{\n\tuse Jump;\n\tpublic function __call(string \$name, array \$arguments)";
            $c_error .= "\n\t{\n\t\t\$msg = \$arguments[0] ?? '出错了啦...';\n\t\t\$code = str_starts_with(\$name, '_') ? substr(\$name, 1) : 400;\n\t\t\$this->error(\$msg, (int)\$code);\n\t}\n}";
            file_put_contents(APP_PATH . '/controller/Error.php', $c_error);
            //API控制器
            $c_api = "<?php\ndeclare(strict_types=1);\nnamespace app\\" . APP_NAME . "\\controller;\nuse willphp\\core\\Jump;\nclass Api\n{\n\tuse Jump;\n\tpublic function clear()\n\t{\n\t\tcache_flush('[all]');";
            $c_api .= "\n\t\t\$this->success('清除缓存成功', 'index/index');\n\t}\n}";
            file_put_contents(APP_PATH . '/controller/Api.php', $c_api);
            //首页模板
            $t_index = file_get_contents(ROOT_PATH . '/willphp/tpl/build_index.tpl');
            file_put_contents($viewPath . '/index/index.html', $t_index);
            //转跳模板
            $t_jump = file_get_contents(ROOT_PATH . '/willphp/tpl/build_jump.tpl');
            file_put_contents($viewPath . '/public/jump.html', $t_jump);
            unlink($lock);
        }
    }
}