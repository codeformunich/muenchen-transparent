<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Oparl extends \Codeception\Module
{
    // magic variable
    protected $requiredFields = ['updatejson'];

    // exposed through getters
    private $uglyResponse;
    private $prettyResponse;
    private $tree;

    // really prrivate
    /** The path to the file with the expected response */
    private $filepath;
    /** Flag to notify the user about `--env updatejson` in the right situation */
    private $notify_about_updatejson;
    /** Stores all URLs that have already gotten the basic checks to avoid redundant work */
    private $checked_urls = [];

    /**
     * Returns the response as pretty pretty-printed json including decoded umlauts
     *
     * @return string
     */
    public function getPrettyResponse() {
        return $this->prettyResponse;
    }

    /*
     *
     */
    public function getUglyResponse() {
        return $this->uglyResponse;
    }

    public function getTree() {
        return $this->tree;
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
     * Sets filepath, uglyResponse, prettyResponse and tree
     */
    public function setVariables($url) {
        $this->checked_urls[] = $url;
        
        // edge case of the oparl:system object
        if ($url == "/") {
            $url = "/system/0";
        }

        $this->filepath = codecept_data_dir() . 'oparl' . $url . '.json';
        $this->uglyResponse = stripslashes(json_encode(json_decode($this->getModule('REST')->grabResponse()), JSON_UNESCAPED_UNICODE));
        $this->prettyResponse = stripslashes(json_encode(json_decode($this->getModule('REST')->grabResponse()), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->tree = json_decode($this->getUglyResponse());
    }

    /**
     * Checks that response returned from the api matches the expected response stored in a file in the data directory
     *
     * When run with the `updatejson` environment all expected reponses that do not match the actual response overwritten
     */
    public function seeOParlFile() {
        $this->notify_about_updatejson = true;
        // Check if an expected result exists
        if (!file_exists($this->filepath)) {
            if ($this->config['updatejson'] === true) {
                $this->writeln("\nCreating missing file with expected response ...");
                if (!file_exists(dirname($this->filepath)))
                    mkdir(dirname($this->filepath), 0777, true);
                file_put_contents($this->filepath, $this->prettyResponse);
            } else {
                $this->fail('The file with expected json missing in validateResponse(): ' . $this->filepath);
            }
        }

        // Finally, check if the response matches
        $expected = file_get_contents($this->filepath);
        if ($this->config['updatejson'] !== true) {
            
            $this->assertEquals($expected, $this->prettyResponse);
        } else {
            if ($this->prettyResponse != $expected) {
                $this->writeln("\nTest failed. Updating expected JSON ...");
                file_put_contents($this->filepath, $this->prettyResponse);
            }
        }
        $this->notify_about_updatejson = false;
    }

    /**
     * @return array
     */
    public function isURLKnown($url)
    {
        if (in_array($url, $this->checked_urls))
            return true;
        $this->checked_urls[] = $url;
        return false;
    }

    /**
     * Prints some usefull debug information about a failed test
     */
    public function _failed(\Codeception\TestCase $test, $fail) {
        $this->writeln($this->prettyResponse);
        if ($this->notify_about_updatejson) {
            $this->writeln("The file with expected json is missing or differs from the real output.");
            $this->writeln("Run codeception with `--env updatejson` to fix this.");
        }
    }
}
