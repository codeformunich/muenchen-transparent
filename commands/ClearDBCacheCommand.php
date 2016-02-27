<?php

use Yii;

class ClearDBCacheCommand extends ConsoleCommand
{
    public function run($args)
    {
        Yii::$app->cache->flush();
    }
}
