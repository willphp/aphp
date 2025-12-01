<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | (C)2020-2025 无念<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);

namespace aphp\cli;
/**
 * 删除命令
 */
class Remove extends Command
{
    public function cli(): bool
    {
        if (!$this->isCall) {
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 1. remove:ctrl [app_name@ctrl_name]                                        |\n";
            echo "| 2. remove:model [app_name@model_name]                                      |\n";
            echo "| 3. remove:view [app_name@ctrl_name] [method(or *)]                         |\n";
            echo "| 4. remove:widget [app_name@widget_name]                                    |\n";
            echo "| 5. remove:command [app_name@command_name]                                  |\n";
            echo "| 6. remove:app [app_name]                                                   |\n";
            echo "| 7. remove:table [table_name]                                               |\n";
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
        }
        return true;
    }

    // 删除控制器
    public function ctrl(array $req = []): ?bool
    {
        if (!isset($req[0])) {
            return $this->error('参数不足');
        }
        $r = $this->_remove($req[0], 'controller');
        return $r ? $this->success() : $this->error();
    }

    // 删除模型
    public function model(array $req = []): ?bool
    {
        if (!isset($req[0])) {
            return $this->error('参数不足');
        }
        $r = $this->_remove($req[0], 'model');
        return $r ? $this->success() : $this->error();
    }

    // 删除视图
    public function view(array $req = []): ?bool
    {
        $name = $req[0] ?? ''; // 应用@控制器名
        if (empty($name)) {
            return $this->error('参数不足');
        }
        [$app, $name] = name_parse($name, APP_NAME);
        $method = $req[1] ?? '*'; // 方法名
        $dir = $this->getViewPath($app) . '/' . $name;
        if ($method == '*') {
            $r = dir_delete($dir, true);
        } else {
            $file = $dir . '/' . $method . '.html';
            $r = !file_exists($file) || unlink($file);
        }
        return $r ? $this->success() : $this->error();
    }

    // 根据应用获取视图路径
    protected function getViewPath(string $app = ''): string
    {
        if (empty($app)) {
            $app = APP_NAME;
        }
        $view_path = config_get('app.app_view_path', [], true); // 路径设置
        return !empty($view_path[$app]) ? ROOT_PATH . '/' . $view_path[$app] : ROOT_PATH . '/app/' . $app . '/view';
    }

    // 删除部件
    public function widget(array $req = []): ?bool
    {
        if (!isset($req[0])) {
            return $this->error('参数不足');
        }
        $r = $this->_remove($req[0], 'widget');
        return $r ? $this->success() : $this->error();
    }

    // 删除命令
    public function command(array $req = []): ?bool
    {
        if (!isset($req[0])) {
            return $this->error('参数不足');
        }
        $r = $this->_remove($req[0], 'command');
        return $r ? $this->success() : $this->error();
    }

    // 删除应用
    public function app(array $req = []): ?bool
    {
        $app = $req[0] ?? ''; // 应用名
        if (empty($app)) {
            return $this->error('参数不足');
        }
        $route = ROOT_PATH . '/route/' . $app . '.php';
        if (file_exists($route)) {
            unlink($route);
        }
        $viewPath = $this->getViewPath($app);
        dir_delete($viewPath, true); // 删除视图
        $dir = ROOT_PATH . '/app/' . $app;
        $r = dir_delete($dir, true);
        return $r ? $this->success() : $this->error();
    }

    // 删除表
    public function table(array $req = []): ?bool
    {
        $table = $req[0] ?? ''; // 表名，不包含表前缀
        if (empty($table)) {
            return $this->error('参数不足');
        }
        $db = db();
        $table_prefix = $db->getPrefix();
        $db->execute('DROP TABLE IF EXISTS `' . $table_prefix . $table . '`;');
        cache('field/' . $table . '_field', null); // 清除表字段缓存
        return $this->success();
    }

    // 删除指定类型文件
    protected function _remove(string $name, string $type): bool
    {
        if (!in_array($type, ['controller', 'model', 'view', 'widget', 'command'])) {
            return false;
        }
        [$app, $name] = name_parse($name, APP_NAME);
        $class = name_to_camel($name); // 类名
        $file = ROOT_PATH . '/app/' . $app . '/' . $type . '/' . $class . '.php';
        return !file_exists($file) || unlink($file);
    }
}