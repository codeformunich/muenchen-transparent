<?php

class ClearDBCacheCommand extends CConsoleCommand
{
    public function run($args)
    {
        Yii::app()->cache->flush();
    }
}
