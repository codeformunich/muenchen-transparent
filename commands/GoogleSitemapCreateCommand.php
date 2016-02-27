<?php

use Yii;
use app\models\Termin;
use app\models\StadtraetIn;
use yii\helpers\Html;

class GoogleSitemapCreateCommand extends ConsoleCommand
{
    public function run($args)
    {

        $sitemap_files = [];

        $datumformat = function ($datum) {
            $x = explode(" ", $datum);
            $y = explode("-", $x[0]);
            if ($y[0] < 2000) $y[0] = "2000";
            if ($y[1] < 1 || $y[1] > 12) $y[1] = "01";
            if ($y[2] < 1 || $y[2] > 31) $y[2] = "01";
            return $y[0] . "-" . $y[1] . "-" . $y[2];
        };


        $sitemap_basepath = Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR;


        // Dokumente

        $sql       = Yii::$app->db->createCommand();
        $dokumente = $sql->select("id, datum")->from("dokumente")->where("deleted = 0")->order("id")->queryAll();

        $sm_num = Ceil(count($dokumente) / 30000);
        for ($sm_page = 0; $sm_page < $sm_num; $sm_page++) {
            echo "Dokumente - Seite $sm_page\n";

            $sitemap_file = "sitemap-dokumente-" . $sm_page . ".xml";
            $fp           = fopen($sitemap_basepath . $sitemap_file, "w");
            fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($fp, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

            for ($i = $sm_page * 30000; $i < ($sm_page + 1) * 30000 && $i < count($dokumente); $i++) {
                $dok = $dokumente[$i];
                fwrite($fp, "<url>\n<loc>" . SITE_BASE_URL . "/dokumente/" . $dok["id"] . "/</loc>\n");
                fwrite($fp, "<changefreq>monthly</changefreq>\n");
                fwrite($fp, "<lastmod>" . $datumformat($dok["datum"]) . "</lastmod>\n");
                fwrite($fp, "</url>\n");
            }

            fwrite($fp, "</urlset>\n");
            fclose($fp);

            $sitemap_files[] = SITE_BASE_URL . "/" . $sitemap_file;
        }


        // Anträge

        $sql      = Yii::$app->db->createCommand();
        $antraege = $sql->select("id, datum_letzte_aenderung")->from("antraege")->order("id")->queryAll();

        $sm_num = Ceil(count($antraege) / 30000);
        for ($sm_page = 0; $sm_page < $sm_num; $sm_page++) {
            echo "Anträge - Seite $sm_page\n";

            $sitemap_file = "sitemap-antraege-" . $sm_page . ".xml";
            $fp           = fopen($sitemap_basepath . $sitemap_file, "w");
            fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($fp, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

            for ($i = $sm_page * 30000; $i < ($sm_page + 1) * 30000 && $i < count($antraege); $i++) {
                $dok = $antraege[$i];
                fwrite($fp, "<url>\n<loc>" . SITE_BASE_URL . "/antraege/" . $dok["id"] . "/</loc>\n");
                fwrite($fp, "<changefreq>weekly</changefreq>\n");
                fwrite($fp, "<lastmod>" . $datumformat($dok["datum_letzte_aenderung"]) . "</lastmod>\n");
                fwrite($fp, "</url>\n");
            }

            fwrite($fp, "</urlset>\n");
            fclose($fp);

            $sitemap_files[] = SITE_BASE_URL . "/" . $sitemap_file;
        }


        // Termine

        $sql     = Yii::$app->db->createCommand();
        $termine = $sql->select("id, datum_letzte_aenderung")->from("termine")->where("typ = " . Termin::$TYP_AUTO)->order("id")->queryAll();

        $sm_num = Ceil(count($termine) / 30000);
        for ($sm_page = 0; $sm_page < $sm_num; $sm_page++) {
            echo "Termine - Seite $sm_page\n";

            $sitemap_file = "sitemap-termine-" . $sm_page . ".xml";
            $fp           = fopen($sitemap_basepath . $sitemap_file, "w");
            fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
            fwrite($fp, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

            for ($i = $sm_page * 30000; $i < ($sm_page + 1) * 30000 && $i < count($termine); $i++) {
                $dok = $termine[$i];
                fwrite($fp, "<url>\n<loc>" . SITE_BASE_URL . "/termine/" . $dok["id"] . "/</loc>\n");
                fwrite($fp, "<changefreq>weekly</changefreq>\n");
                fwrite($fp, "<lastmod>" . $datumformat($dok["datum_letzte_aenderung"]) . "</lastmod>\n");
                fwrite($fp, "</url>\n");
            }

            fwrite($fp, "</urlset>\n");
            fclose($fp);

            $sitemap_files[] = SITE_BASE_URL . "/" . $sitemap_file;
        }


        // StadträtInnen

        echo "StadträtInnen\n";
        /** @var StadtraetIn[] $strs */
        $strs         = StadtraetIn::findAll();
        $sitemap_file = "sitemap-stadtraetinnen.xml";
        $fp           = fopen($sitemap_basepath . $sitemap_file, "w");
        fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        fwrite($fp, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

        foreach ($strs as $str) {
            fwrite($fp, "<url>\n<loc>" . $str->getLink() . "</loc>\n");
            fwrite($fp, "<changefreq>weekly</changefreq>\n");
            fwrite($fp, "<lastmod>" . date("Y-m-d") . "</lastmod>\n");
            fwrite($fp, "</url>\n");
        }

        fwrite($fp, "</urlset>\n");
        fclose($fp);

        $sitemap_files[] = SITE_BASE_URL . "/" . $sitemap_file;


        $fp = fopen($sitemap_basepath . "sitemap-index.xml", "w");
        fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        foreach ($sitemap_files as $file) fwrite($fp, '<sitemap>
      <loc>' . Html::encode($file) . '</loc>
      <lastmod>' . date("Y-m-d") . '</lastmod>
   </sitemap>' . "\n");

        fwrite($fp, '</sitemapindex>');
        fclose($fp);
    }
}