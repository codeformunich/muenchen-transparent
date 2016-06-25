[München Transparent](https://www.muenchen-transparent.de)
=========================================

[![Build Status](https://travis-ci.org/codeformunich/Muenchen-Transparent.svg?branch=master)](https://travis-ci.org/codeformunich/Muenchen-Transparent)
[![Code Climate](https://codeclimate.com/github/codeformunich/Muenchen-Transparent/badges/gpa.svg)](https://codeclimate.com/github/codeformunich/Muenchen-Transparent)
[![Dependencies](https://gemnasium.com/codeformunich/Muenchen-Transparent.svg)](https://gemnasium.com/codeformunich/Muenchen-Transparent)


München Transparent ist ein alternatives Ratsinformationssystem (RIS) für München.

## Setup

### Docker

Für einen einfachen Einstieg in die Entwicklung an München Transparent ist eine komplette Entwicklungsumgebung mit `docker-compose` eingecheckt.
Du benötigst somit nur eine Installation von [docker](https://www.docker.com/products/docker) für dein Betriebssystem und [docker-compose](https://docs.docker.com/compose/install/).

Kopiere `docs/docker/main.template.php` nach `protected/config/main.php`.

Starte die Entwicklungsumgebung mittels `docker-compose up`.

Je nach Betriebssystem/docker-Konfiguration musst du noch die `SITE_BASE_URL` in der `protected/config/main.php` anpassen.

### Selbst aufsetzen

Vorausgesetzt werden nginx mit PHP und MySQL/MariaDB sowie npm und composer.
Für die Verarbeitung von PDFs werden noch imagemagick, poppler-utils, tesseract, eine Java-Runtime und [PDFbox](http://pdfbox.apache.org) benötigt.

Berechtigungen setzen und Abhängigkeiten installieren: (`www-data` muss durch den passenden Nutzer ersetzt werden, bei MacOSX ist das z.B. `_www` )
```bash
chown -R www-data:www-data protected/runtime
cp protected/config/main.template.php protected/config/main.php
```

Abhängigkeiten installieren und minimiertes javascript und css erzeugen:
```bash
npm install -g bower gulp-cli
npm install
composer install
bower install
gulp
```

### nginx-Konfiguration:
* Der gewählte `server_name` muss in protected/config/main.php als `SITE_BASE_URL` eingetragen werden.
* `root` muss auf den `html/`-Ordner zeigen.
* `$yii_bootstrap` muss auf `index.php` gesetzt werden.
* Die Einstellungen aus [nginx-minimal.conf](docs/nginx-minimal.conf) müssen übernommen werden, entweder durch ein `include` oder mit copy&paste.
* Zwei erweiterte Beispiele einer vollständigen Konfiguration finden sich in [nginx-full.conf](docs/nginx-full.conf) und [nginx-travis.conf](docs/nginx-travis.conf).

### MariaDB/MySQL-Konfiguration
* Eine Datenbank und einen zugehörigen Nutzer anlegen.
* Die Datenbank-Konfiguration muss dann in `protected/config/main.php` eingetragen werden. Im Beispiel werden die Datenbank "muenchen_transparent", der Benutzer "ris" und das Passwort "sec" verwendet:
```php
'db' => [
    'connectionString'      => 'mysql:host=127.0.0.1;dbname=muenchen_transparent',
    'emulatePrepare'        => true,
    'username'              => 'ris',
    'password'              => 'sec',
    'charset'               => 'utf8mb4',
    'queryCacheID'          => 'apcCache',
    'schemaCachingDuration' => 3600,
],
```
* Beispieldaten in die Datenbank importieren:
```bash
cat docs/schema.sql docs/beispieldaten.sql docs/triggers.sql | mysql -u ris -psec muenchen_transparent
```

### PHP-Konfiguration:
* Die Option "short_open_tag" muss auf "On" gestellt sein.
* Das Modul für curl muss installiert sein (`php5-curl`)

### Solr-Konfiguration
* Solr 5.5.1 [herunterladen](https://archive.apache.org/dist/lucene/solr/5.5.1/) und in einen Ordner mit dem Namen `solr` entpacken.
* `docs/solr_core/` nach `solr/server/solr/muenchen-transparent/` kopieren.
* solr kann dann mit `solr/bin/solr start` gestartet werden.

## OParl

Zum Zugriff auf die Daten gibt es eine [OParl](https://oparl.org)-Schnittstelle. Damit die API funktioniert, muss
`OPARL_10_ROOT` in `main.php` auf den gewünschten Wert gesetzt werden. Genauere Hinweise zur Implementierung finden
sich in `docs/oparl.md`.

## Tests

Als Testframework wird [codeception](http://codeception.com/) verwendet.

Zum lokalen Ausführen der Test muss ein 2. Server-Block in der nginx-Konfiguration angelegt werden. Dieser unterschiedet sich vom normalen Server-Block in drei Punkten:
* `server_name` muss `localhost` sein.
* `listen` muss auf `8080` gesetzt werden.
* `$yii_bootstrap` muss auf `index_codeception.php` gesetzt werden.

Die Tests können dann mit
```bash
vendor/bin/codeception run
```
ausgeführt werden.

Es ist zu beachten, dass die Tests durch PhpBrowser und nicht durch selenium ausgeführt werden. Deshalb können keine auf javascript basierenden Funktionen getestet werden.

## Code-Organisation

* __docs/__: Das Datenbankschema, die Konfiguration für nginx, solr, Fontello, travis, etc.
* __html/__: Statische Daten - vor allem die JS-Bibliotheken und (S)CSS-Dateien
* __protected/yiic.php__: Aufruf der Kommandozeilentools (entweder von der Shell wie z.B. "reindex_ba" oder als Cron-Job wie z.B. "update_ris_daily")
* __protected/commands/__: Definitionen der Kommantozeilentools
* __protected/components/__: Diverse (meist statische) Hilf-Funktionen
* __protected/config/__: Die Konfiguration. Insbesondere das Mapping der URLs auf die Controller-Funktionen und die Pfade der Kommandozeilenanwendungen.
* __protected/RISParser/__: Die Parser für das Scraping.
* __protected/models/__: Model
* __protected/controllers/__: Controller
* __protected/views/__: View

## Weitere Dokumentation
* [Icon-Font bearbeiten](docs/fontello/updating.txt)
* Eine Sammlung zu Dokumenten rund um München Transparent gibt es im [video-branch](https://github.com/codeformunich/Muenchen-Transparent/tree/video)

### pdf.js  Updaten:
* Neuste Pre-built Version von pdf.js herunterladen und in `html/pdfjs` entpacken
* `docs/pdfjs.patch` oder `docs/pdfjs.diff` darauf anwenden

### Eingesetzte Shell-Programme
* [Tesseract](https://code.google.com/p/tesseract-ocr/) für das automatische OCR. Wegen der besseren Erkennungsqualität kommt noch etwa 1-2mal montatlich eine zweite, manuelle OCR-Phase hinzu, basierend auf Nuance Omnipage.
* [Imagemagick](http://www.imagemagick.org/) zur Vorbereitung des OCRs.
* [Solr](http://lucene.apache.org/solr/) für die Volltextsuche.
* [PDFbox](http://pdfbox.apache.org) zur Text-Extraktion aus den PDFs.

### Eingesetzte PHP-Bibliotheken
* [Yii Framework](http://www.yiiframework.com/)
* [Zend Framework 2](http://framework.zend.com/)
* [Solarium](http://www.solarium-project.org/)
* [CSS2InlineStyles](https://github.com/tijsverkoyen/CssToInlineStyles) für die HTML-formatierten E-Mails.
* [sabre/dav](http://sabre.io/) für die Kalender-Synchronisation
* [Composer](https://getcomposer.org/)
* [Codeception](http://codeception.com/)

### Eingesetzte JS/CSS-Bibliotheken
* [Gulp](http://gulpjs.com/)
* [Sass](http://sass-lang.com/)
* [jQuery](http://www.jquery.com/)
* [Leaflet](http://leafletjs.com/) (mit dem Kartenmaterial von [Skobbler](http://www.skobbler.com/))
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
