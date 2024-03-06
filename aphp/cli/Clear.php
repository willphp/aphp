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
            echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
            echo "1. clear:runtime      -[app|*]                         \n";
            echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
        }
        return true;
    }

    public function runtime(array $req = []): ?bool
    {
        $app = $req[0] ?? '';
        $result = Tool::dir_delete(APHP_TOP . '/runtime/' . $app, !empty($app));
        return $result ? $this->success() : $this->error();
    }
}