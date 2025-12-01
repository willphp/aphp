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
 * 清理命令
 */
class Clear extends Command
{
    public function cli(): bool
    {
        if (!$this->isCall) {
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 1. clear:runtime [app_name(or *)]                                          |\n";
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
        }
        return true;
    }

    // 清除运行缓存
    public function runtime(array $req = []): ?bool
    {
        if (empty($req)) {
            dir_delete(ROOT_PATH . '/runtime/');
        } else {
            foreach ($req as $app) {
                dir_delete(ROOT_PATH . '/runtime/' . $app, true);
            }
        }
        return $this->success();
    }
}