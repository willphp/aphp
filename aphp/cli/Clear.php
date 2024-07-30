<?php
/*------------------------------------------------------------------
 | Software: APHP - A PHP TOP Framework
 | Site: https://aphp.top
 |------------------------------------------------------------------
 | CopyRight(C)2020-2024 大松栩<24203741@qq.com>,All Rights Reserved.
 |-----------------------------------------------------------------*/
declare(strict_types=1);
namespace aphp\cli;
use aphp\core\Tool;

class Clear extends Command
{
    public function cli(): bool
    {
        if (!$this->isCall) {
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 1. clear:runtime [app_name(or *)]                                          |\n";
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|";
        }
        return true;
    }

    // 清除运行缓存
    public function runtime(array $req = []): ?bool
    {
        if (empty($req)) {
            Tool::dir_delete(ROOT_PATH . '/runtime/');
        } else {
            foreach ($req as $app) {
                Tool::dir_delete(ROOT_PATH . '/runtime/' . $app, true);
            }
        }
        return $this->success();
    }
}