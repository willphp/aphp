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
 * 命令帮助
 */
class Help extends Command
{
    public function cli(): bool
    {
        if (!$this->isCall) {
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 1. make:ctrl    [app_name@ctrl_name] [tpl:_def] [-f]                       |\n";
            echo "| 2. make:model   [app_name@table_name] [pk] [tpl:_def] [-f]                 |\n";
            echo "| 3. make:view    [app_name@ctrl_name] [method] [tpl:_def] [-f]              |\n";
            echo "| 4. make:widget  [app_name@widget_name] [tag] [tpl:_def] [-f]               |\n";
            echo "| 5. make:command [app_name@command_name] [tpl:_def] [-f]                    |\n";
            echo "| 6. make:app     [app_name]                                                 |\n";
            echo "| 7. make:table   [app_name@table_name] [tpl:_def] [-f]                      |\n";
            echo "|++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++|\n";
            echo "| 0. clear:runtime [app_name(or *)]                                          |\n";
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

}