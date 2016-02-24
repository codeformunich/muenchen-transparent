<?php

//define("VERYFAST", true);

class EMailTestCommand extends CConsoleCommand
{
    public function run($args)
    {
        RISTools::send_email("tobias@hoessl.eu", "Test", "Abc", "<strong>Test 123</strong>", "test");
    }
}