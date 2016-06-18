<?php
namespace Helper;

use Codeception\Lib\ModuleContainer;

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

    // really private
    /** The path to the file with the expected response */
    private $url;
    /** Flag to notify the user about `--env updatejson` in the right situation */
    private $notify_about_updatejson;
    /** Stores all URLs that have already gotten the basic checks to avoid redundant work */
    private $checked_urls = [];
    /** Stores the expected results in the format ["[url-affix]" => "expected json", ....] */
    private $expectedResults = [];

    /**
     * Returns the server response as pretty-printed json including decoded umlauts
     *
     * @return string
     */
    public function getPrettyResponse() {
        return $this->prettyResponse;
    }

    /**
     * Returns the server response as compressed json including decoded umlauts
     *
     * @return string
     */
    public function getUglyResponse() {
        return $this->uglyResponse;
    }

    /**
     * Returns the server response as array-based tree
     *
     * @return array
     */
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

    public function _beforeSuite() {
        $filename = codecept_data_dir() . "oparl_expected_results.txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            preg_match_all('/^([^ ]+) (.+)$/m', $content, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $pretty_expected = stripslashes(json_encode(json_decode($match[2]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->expectedResults[$match[1]] = $pretty_expected;
            }
        } else {
            if ($this->config['updatejson'] === true) {
                $this->writeln("\nCreating missing file with expected response ...");
                file_put_contents($filename, '\n');
            } else {
                $this->fail('The file with expected results is missing (' . $filename . ')');
            }
        }
    }

    public function _afterSuite() {
        $filename = codecept_data_dir() . "oparl_expected_results.txt";
        file_put_contents($filename, '');
        foreach ($this->expectedResults as $url => $pretty_expected) {
            $ugly_expected = stripslashes(json_encode(json_decode($pretty_expected), JSON_UNESCAPED_UNICODE));
            file_put_contents($filename, $url . ' ' . $ugly_expected . "\n", FILE_APPEND);
        }
    }

    /**
     * Sets filepath, uglyResponse, prettyResponse and tree
     */
    public function setVariables($url) {
        $this->checked_urls[] = $url;

        $this->url = $url;
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

        if ($this->config['updatejson'] === true) {
            if (!array_key_exists($this->url, $this->expectedResults)) {
                $this->writeln("\nCreating expected response ...");
            } else if ($this->expectedResults[$this->url] != $this->getPrettyResponse()) {
                $this->writeln("\nUpdating expected response ...");
            } else {
                return;
            }

            $this->expectedResults[$this->url] = $this->getPrettyResponse();
        }

        if (!array_key_exists($this->url, $this->expectedResults)) {
            $this->fail('There\'s no expected response for this url: ' . $this->url);
        }

        $this->assertEquals($this->expectedResults[$this->url], $this->getPrettyResponse());

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
        $this->writeln($this->getPrettyResponse());
        if ($this->notify_about_updatejson) {
            $this->writeln("The file with expected json is missing or differs from the real output.");
            $this->writeln("Run codeception with `--env updatejson` to fix this.");
        }
    }
}
