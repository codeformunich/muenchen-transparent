<?php

declare(strict_types=1);

class CurlBasedDownloader
{
    private static CurlBasedDownloader $instance;

    public static function getInstance(): CurlBasedDownloader
    {
        if (!isset(static::$instance)) {
            static::$instance = new CurlBasedDownloader();
        }
        return static::$instance;
    }

    /**
     * Lets test cases set overridden sub-classes
     */
    public static function setInstance(CurlBasedDownloader $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * @throws DocumentVanishedException
     */
    public function loadUrl(string $url, bool $detectVanished = false, int $retries = 3, int $courtesyWait = 200000): string
    {
        if (defined("IN_TEST_MODE")) {
            return '';
        }

        if ($courtesyWait > 0) {
            usleep($courtesyWait);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $text = curl_exec($ch);
        curl_close($ch);

        if ($detectVanished) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] === 302 && str_contains($info['redirect_url'], 'extranet/login')) {
                throw new DocumentVanishedException('Redirected to: ' . $info['redirect_url']);
            }
            if ($info['http_code'] === 404) {
                throw new DocumentVanishedException('Document not found');
            }
        }

        if ($text === false) {
            if ($retries > 0) {
                echo "Dowload failed. Retrying in 30 seconds\n";
                sleep(30);
                return $this->loadUrl($url, $retries - 1, $courtesyWait);
            } else {
                throw new \Exception('Could not download URL: ' . $url);
            }
        }

        return $text;
    }

    public function downloadFile(string $url_to_read, string $filename, int $timeout = 30): void
    {
        if (defined("IN_TEST_MODE")) {
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_to_read);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // here HTTP request is 'HEAD'

        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info["http_code"] != 200 && $info["http_code"] != 403) {
            echo "Not found: $url_to_read\n";
            return;
        }

        $fp  = fopen($filename, "w");
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $url_to_read);
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch2, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
        //curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_FILE, $fp);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
        curl_exec($ch2);
        $info = curl_getinfo($ch2);
        curl_close($ch2);
        //file_put_contents($filename, $text);
        fclose($fp);

        if (!defined("VERYFAST")) {
            sleep(1);
        }
    }
}
