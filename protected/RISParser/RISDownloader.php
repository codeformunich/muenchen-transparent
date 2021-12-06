<?php

declare(strict_types=1);

class RISDownloader
{
    private static ?string $sessionCookie = null;

    private static function getSessionCookie(): string
    {
        if (static::$sessionCookie === null) {
            static::$sessionCookie = '60CA9FDEBAD3573ACF09DD816CBC1711'; // @TODO
        }

        return static::$sessionCookie;
    }

    public static function downloadSessionCookie(): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, RIS_BASE_URL . 'aktuelles');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $text = curl_exec($ch);
        curl_close($ch);

        if (!preg_match('/JSESSIONID=(?<id>[0-9a-z]+);/siu', $text, $matches)) {
            die("Could not find a Session ID");
        }

        return $matches['id'];
    }

    public static function downloadStrAntragIndex(): string
    {
        $url = RIS_BASE_URL . 'antrag/str/uebersicht?1-1.-form=';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'von=2021-01-01&bis=2021-01-10&status=');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Cookie' => 'JSESSIONID=' . static::getSessionCookie(),
        ]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $text = curl_exec($ch);
        var_dump(curl_getinfo($ch));
        curl_close($ch);

        //$text = RISTools::toutf8($text);

        if (!defined("VERYFAST")) {
            sleep(1);
        }

        return $text;
    }
}
