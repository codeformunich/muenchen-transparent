[München Transparent](https://www.muenchen-transparent.de)
=========================================


Entwicklungs-Setup
------------------

Vorausgesetzt wird ein funktionierender Webserver (nginx oder apache) mit PHP und MySQL/MariaDB

Berechtigungen setzen und Abhängigkeiten installieren: (www-data muss durch den passenden Nutzer ersetzt werden, bei MacOSX z.B. "_www" benutzen)
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install

mkdir protected/runtime
chown -R www-data:www-data protected/runtime
mkdir html/assets
chown -R www-data:www-data html/assets

cp protected/config/main.template.php protected/config/main.php
```

JS-Bibliotheken installieren:
```bash
npm install -g bower
bower install
```

JS- & CSS-Dateien zusammenfügen und minimieren:
```bash
npm install gulp gulp-concat gulp-uglifyjs gulp-concat-css gulp-minify-css gulp-sourcemaps gulp-sass
gulp
```

Webserver-Konfiguration:
* DocumentRoot muss auf das html/-Verzeichnis gesetzt werden.
* Bei Apache regelt die html/.htaccess alles weitere. Für nginx gibt es eine [Beispiel-Konfiguration](docs/nginx.conf)
* Der Hostname des Webservers muss auch als SITE_BASE_URL bei protected/config/main.php gesetzt werden.

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

PDF.JS Updaten:
* Ggf. uglify-js installieren (`npm install -g uglify-js`)
* Entweder mit `diff` `docs/pdfjs_v[version].diff` oder mit `git patch apply` `pdfjs_v[version].patch` anwenden
* Im Ordner `html/pdfjs/`: `uglifyjs compatibility.js l10n.js pdf.js debugger.js viewer.js > viewer.min.js`

Code-Organisation
-----------------

* __docs/___: Das Datenbankschema, die Konfiguration für nginx, solr, Fontello
* __html/__: Statische Daten - vor allem die JS-Bibliotheken und (S)CSS-Dateien
* __protected/yiic.php__: Aufruf der Kommandozeilentools (entweder von der Shell wie z.B. "reindex_ba" oder als Cron-Job wie z.B. "update_ris_daily")
* __protected/commands/__: Definitionen der Kommantozeilentool
* __protected/components/__: Diverse (meist statische) Hilf-Funktionen
* __protected/config/__: Die Konfiguration. Insb. das Mapping der URLs auf die Controller-Funktionen und die Pfade der Kommandozeilenanwendungen.
* __protected/controllers/__: Die Controller-Klassen
* __protected/models/__: Das Objekt-relationale Datenmodell
* __protected/RISParser/__: Die Parser für das Scraping. Werden von den Kommandozeilentools aufgerufen und beschreiben das Modell.
* __protected/views/__: Die Views

Weitere Dokumentation
---------------------

* [nginx.conf](docs/nginx.conf) und [lighttpd.conf](docs/lighttpd.conf) zeigen Beispiel-Konfigurationen für nginx und Lighttpd. Von der Verwendung von lighttpd ist aber abzuraten, da einige Funktionen, wie z.B. Kalender oder der "Ältere Dokumente"-Knopf damit nicht funktionieren.
* [Icon-Font bearbeiten](docs/fontello/updating.txt)
* Eine Sammlung zu Dokumenten rund um München Transparent gibt es im [video-branch](https://github.com/codeformunich/Muenchen-Transparent/tree/video)

Eingesetzte Shell-Programme
---------------------------
* [Tesseract](https://code.google.com/p/tesseract-ocr/) für das automatische OCR. Wegen der besseren Erkennungsqualität kommt noch etwa 1-2mal montatlich eine zweite, manuelle OCR-Phase hinzu, basierend auf Nuance Omnipage.
* [Imagemagick](http://www.imagemagick.org/) zur Vorbereitung des OCRs.
* [Solr](http://lucene.apache.org/solr/) für die Volltextsuche.
* [PDFbox](pdfbox.apache.org) zur Text-Extraktion aus den PDFs.
* [sass](http://sass-lang.com/)

Eingesetzte PHP-Bibliotheken
----------------------------
* [Yii Framework](http://www.yiiframework.com/)
* [Zend Framework 2](http://framework.zend.com/)
* [Solarium](http://www.solarium-project.org/) Zur Anbindung von SolR.
* [CSS2InlineStyles](https://github.com/tijsverkoyen/CssToInlineStyles) für die HTML-formatierten E-Mails.
* [sabre/dav](http://sabre.io/) für die Kalender-Synchronisation
* [Composer](https://getcomposer.org/)

Eingesetzte JS/CSS-Bibliotheken
-------------------------------
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
