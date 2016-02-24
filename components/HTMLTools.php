<?php

class HTMLTools {
    /**
     * @param $text
     * @return string
     */
    public static function textToHtmlWithLink($text)
    {
        $html = nl2br(Html::encode($text), false);

        $urlsearch = $urlreplace = [];
        $wwwsearch = $wwwreplace = [];

        $urlMaxlen      = 250;
        $urlMaxlenEnd   = 50;
        $urlMaxlenHost  = 150;
        $urlPatternHost = '[-a-zäöüß0-9\_\.]';
        $urlPattern     = '([-a-zäöüß0-9\_\$\.\:;\/?=\+\~@,%#!\'\[\]\|]|\&(?!amp\;|lt\;|gt\;|quot\;)|\&amp\;)';
        $urlPatternEnd  = '([-a-zäöüß0-9\_\$\:\/=\+\~@%#\|]|\&(?!amp\;|lt\;|gt\;|quot\;)|\&amp\;)';

        $endPattern     = "($urlPatternEnd|($urlPattern*\\($urlPattern{0,$urlMaxlenEnd}\\)){1,3})";
        $hostUrlPattern = "$urlPatternHost{1,$urlMaxlenHost}(\\/?($urlPattern{0,$urlMaxlen}$endPattern)?)?";

        $urlsearch[]  = "/([({\\[\\|>\\s])((https?|ftp|news):\\/\\/|mailto:)($hostUrlPattern)/siu";
        $urlreplace[] = "\\1<a rel=\"nofollow\" href=\"\\2\\4\">\\2\\4</a>";

        $urlsearch[]  = "/^((https?|ftp|news):\\/\\/|mailto:)($hostUrlPattern)/siu";
        $urlreplace[] = "<a rel=\"nofollow\" href=\"\\1\\3\">\\1\\3</a>";

        $wwwsearch[]  = "/([({\\[\\|>\\s])((?<![\\/\\/])www\\.)($hostUrlPattern)/siu";
        $wwwreplace[] = "\\1<a rel=\"nofollow\" href=\"http://\\2\\3\">\\2\\3</a>";

        $wwwsearch[]  = "/^((?<![\\/\\/])www\\.)($hostUrlPattern)/siu";
        $wwwreplace[] = "<a rel=\"nofollow\" href=\"http://\\1\\2\">\\1\\2</a>";

        $html = preg_replace($urlsearch, $urlreplace, $html);
        $html = preg_replace($wwwsearch, $wwwreplace, $html);

        return $html;
    }
}
