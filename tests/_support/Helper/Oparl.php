<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Oparl extends \Codeception\Module
{
    protected $requiredFields = ['updatejson'];

    /*
     * returns the unescaped response content with unicode symbols in it
     */
    public function getResponseContent($json_flags = JSON_UNESCAPED_UNICODE) {
        return stripslashes(json_encode(json_decode($this->getModule('REST')->grabResponse()), $json_flags));
    }

    /**
     * For debugging and developing it's often very usefull to have the
     * possibility to print something without having to use the debug flag
     */
    public function writeln($text = '') {
        $output = new \Codeception\Lib\Console\Output([]);
        $output->writeln($text);
    }

    /**
     * Checks that response returned from the api matches the expected response stored in a file in the data directory
     *
     * When run with the `updatejson` environment all expected reponses that do not match the actual response overwritten
     */
    public function validateResponse($url) {
        // edge case of the oparl:system object
        if ($url == "/") {
            $url = "/system/0";
        }

        $filepath = codecept_data_dir() . 'oparl' . $url . '.json';
        $response = $this->getResponseContent(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Check if an expected result exists
        if (!file_exists($filepath)) {
            if ($this->config['updatejson'] === true) {
                $this->writeln("\nCreating missing file with expected response ...");
                if (!file_exists(dirname($filepath)))
                    mkdir(dirname($filepath), 0777, true);
                file_put_contents($filepath, $response);
            } else {
                $this->fail('File with expected json missing in validateResponse(): ' . $filepath);
            }
        }

        // Finally, check if the response matches
        $expected = file_get_contents($filepath);
        if ($this->config['updatejson'] !== true) {
            $this->assertEquals($expected, $response);
        } else {
            if ($response != $expected) {
                $this->writeln("\nTest failed. Updating expected JSON ...");
                file_put_contents($filepath, $response);
            }
        }
    }

    /**
     * Prints some usefull debug information about a failed test
     */
    public function _failed(\Codeception\TestCase $test, $fail) {
        $filename    = $test->getTestFileName($test);
        $pretty_json = $this->getResponseContent(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $url_full    = $this->getModule('PhpBrowser')->client->getHistory()->current()->getUri();

        $this->writeln($filename);
        $this->writeln($url_full);
        $this->writeln($pretty_json);
    }
}
