[München Transparent](https://www.muenchen-transparent.de)
=========================================

[![Build Status](https://travis-ci.org/codeformunich/Muenchen-Transparent.svg?branch=master)](https://travis-ci.org/codeformunich/Muenchen-Transparent)

## Entwicklungs-Setup

Vorausgesetzt wird nginx mit PHP und MySQL/MariaDB

Berechtigungen setzen und Abhängigkeiten installieren: (`www-data` muss durch den passenden Nutzer ersetzt werden, bei MacOSX ist das z.B. `_www` )
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install

mkdir protected/runtime
chown -R www-data:www-data protected/runtime

cp protected/config/main.template.php protected/config/main.php
```

Abhängigkeiten installieren:
```bash
npm install -g bower
bower install
npm install
```

JS- & CSS-Dateien zusammenfügen und minimieren:
```bash
gulp
```

nginx-Konfiguration:
* Der Hostname der gleiche wie SITE_BASE_URL in protected/config/main.php sein.
* `root` muss auf den `html/`-Ordner gesetzt werden;
* `$yii_bootstrap` muss auf  `index.php` gesetzt werden; 
* Die Einstellungen aus [nginx-minimal.conf](docs/nginx-minimal.conf) müssen übernommen werden, entweder mit `include` oder mit copy&paste.
* Zwei erweiterte Besipiele einer vollständigen Konfiguration finden sich in [nginx-full.conf](docs/nginx-full.conf) und [nginx-travis.conf](docs/nginx-travis.conf).

MariaDB/MySQL-Konfiguration
* Eine Datenbank und einen zugehörigen Nutzer anlegen. Hier im Beispiel: Datenbank "muenchen_transparent", Benutzer "ris", Passwort "sec"
* `cat docs/schema.sql docs/init_data/1.sql docs/init_data/2_vorgaenge.sql docs/init_data/3_antraege.sql docs/init_data/4_termine.sql docs/init_data/5_dokumente.sql  | mysql -u ris -psec muenchen_transparent`
* Der zugehörige Abschnitt in der protected/config/main.php wäre dann:
```php
'db'           => array(
            'connectionString'      => 'mysql:host=127.0.0.1;dbname=muenchen_transparent',
            'emulatePrepare'        => true,
            'username'              => 'ris',
            'password'              => 'sec',
            'charset'               => 'utf8mb4',
            'queryCacheID'          => 'apcCache',
            'schemaCachingDuration' => 3600,
        ),
```

PHP-Konfiguration:
* Die Option "short_open_tag" muss auf "On" gestellt sein.
* Das Modul für curl muss installiert sein (`php5-curl`)

[TODO: Solr]

## Code-Organisation

* __docs/___: Das Datenbankschema, die Konfiguration für nginx, solr, Fontello, travis, etc.
* __html/__: Statische Daten - vor allem die JS-Bibliotheken und (S)CSS-Dateien
* __protected/yiic.php__: Aufruf der Kommandozeilentools (entweder von der Shell wie z.B. "reindex_ba" oder als Cron-Job wie z.B. "update_ris_daily")
* __protected/commands/__: Definitionen der Kommantozeilentools
* __protected/components/__: Diverse (meist statische) Hilf-Funktionen
* __protected/config/__: Die Konfiguration. Insb. das Mapping der URLs auf die Controller-Funktionen und die Pfade der Kommandozeilenanwendungen.
* __protected/RISParser/__: Die Parser für das Scraping. Werden von den Kommandozeilentools aufgerufen und beschreiben das Modell.
* __protected/models/__: Model
* __protected/controllers/__: Controller
* __protected/views/__: View

## Tests

Zum automatisierten Testen wird [codeception](http://codeception.com/) verwendet. Die Tests werden automatisch von Travis CI ausgeführt. Es ist zu beachten, dass die Tests im Moment noch im PhpBrowser statt über selenium laufen, d.h. es können u.a. noch keine auf javascript basierenden Funktionen getestet werden.

[TODO: Test lokal ausführen und Struktur erklären]

## Weitere Dokumentation
* [Icon-Font bearbeiten](docs/fontello/updating.txt)
* Eine Sammlung zu Dokumenten rund um München Transparent gibt es im [video-branch](https://github.com/codeformunich/Muenchen-Transparent/tree/video)

### pdf.js  Updaten:
* Ggf. uglify-js installieren (`npm install -g uglify-js`)
* Entweder mit `diff` `docs/pdfjs_v[version].diff` oder mit `git patch apply` `pdfjs_v[version].patch` anwenden
* Im Ordner `html/pdfjs/`: `uglifyjs compatibility.js l10n.js pdf.js debugger.js viewer.js > viewer.min.js`

## Eingesetzte Shell-Programme
* [Tesseract](https://code.google.com/p/tesseract-ocr/) für das automatische OCR. Wegen der besseren Erkennungsqualität kommt noch etwa 1-2mal montatlich eine zweite, manuelle OCR-Phase hinzu, basierend auf Nuance Omnipage.
* [Imagemagick](http://www.imagemagick.org/) zur Vorbereitung des OCRs.
* [Solr](http://lucene.apache.org/solr/) für die Volltextsuche.
* [PDFbox](pdfbox.apache.org) zur Text-Extraktion aus den PDFs.

## Eingesetzte PHP-Bibliotheken
* [Yii Framework](http://www.yiiframework.com/)
* [Zend Framework 2](http://framework.zend.com/)
* [Solarium](http://www.solarium-project.org/) Zur Anbindung von SolR.
* [CSS2InlineStyles](https://github.com/tijsverkoyen/CssToInlineStyles) für die HTML-formatierten E-Mails.
* [sabre/dav](http://sabre.io/) für die Kalender-Synchronisation
* [Composer](https://getcomposer.org/)
* [Codeception](http://codeception.com/)

Eingesetzte JS/CSS-Bibliotheken
-------------------------------
* [Gulp](http://gulpjs.com/) Zum automatischen Erstellen von minimiertem css und javascript 
* [Sass](http://sass-lang.com/)
* [jQuery](http://www.jquery.com/)
* [Leaflet](http://leafletjs.com/) für die Karten (mit dem Kartenmaterial von [Skobbler](http://www.skobbler.com/))
* [Modernizr](http://modernizr.com/)
* [Bootstrap](http://getbootstrap.com/)
* [Material Design for Bootstrap](http://fezvrasta.github.io/bootstrap-material-design/)
* [Fontello](http://fontello.com/)
* [Moment.js](momentjs.com)
* [FullCalendar](http://fullcalendar.io/)
* [List.js](http://www.listjs.com/)
* [Bower](http://bower.io/)
* [Isotope](http://isotope.metafizzy.co/)
* [Shariff](http://www.heise.de/ct/artikel/Shariff-Social-Media-Buttons-mit-Datenschutz-2467514.html)
* [CKEditor](http://ckeditor.com/)
