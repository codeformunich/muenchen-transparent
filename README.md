[Ratsinformant](https://ratsinformant.de)
=========================================


Entwicklungs-Setup
------------------

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

Webserver-Konfiguration:
* DocumentRoot muss auf das html/-Verzeichnis gesetzt werden.
* Bei Apache regelt die html/.htaccess alles weitere. Bei nginx gibt es unter docs/nginx.conf eine Beispiel-Konfigurationsdatei
* Der Hostname des Webservers muss auch als RATSINFORMANT_BASE_URL bei protected/config/main.php gesetzt werden.

MariaDB/MySQL-Konfiguration
* Eine Datenbank und einen zugehörigen Nutzer anlegen. Hier im Beispiel: Datenbank "ratsinformant", Benutzer "ris", Passwort "sec"
* `cat docs/schema3.sql docs/init_data/1.sql docs/init_data/2_vorgaenge.sql docs/init_data/3_antraege.sql docs/init_data/4_termine.sql docs/init_data/5_dokumente.sql  | mysql -u ris -psec ratsinformant`
* Der zugehörige Abschnitt in der protected/config/main.php wäre dann:
```php
'db'           => array(
			'connectionString'      => 'mysql:host=127.0.0.1;dbname=ratsinformant',
			'emulatePrepare'        => true,
			'username'              => 'ris',
			'password'              => 'sec',
			'charset'               => 'utf8',
			'queryCacheID'          => 'apcCache',
			'schemaCachingDuration' => 3600,
		),
```

PHP-Konfiguration:
* Die Option "short_open_tag" muss auf "On" gestellt sein.

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

Eingesetzte Shell-Programme
---------------------------
* [Tesseract](https://code.google.com/p/tesseract-ocr/) für das automatische OCR. Wegen der besseren Erkennungsqualität kommt noch etwa 1-2mal montatlich eine zweite, manuelle OCR-Phase hinzu, basierend auf Nuance Omnipage.
* [Imagemagick](http://www.imagemagick.org/) zur Vorbereitung des OCRs.
* [Solr](http://lucene.apache.org/solr/) für die Volltextsuche.
* [PDFbox](pdfbox.apache.org) zur Text-Extraktion aus den PDFs.

Eingesetzte PHP-Bibliotheken
----------------------------
* [Yii Framework](http://www.yiiframework.com/)
* [Zend Framework 2](http://framework.zend.com/)
* [Solarium](http://www.solarium-project.org/) Zur Anbindung von SolR.
* [CSS2InlineStyles](https://github.com/tijsverkoyen/CssToInlineStyles) für die HTML-formatierten E-Mails.

Eingesetzte JS/CSS-Bibliotheken
-------------------------------
* [jQuery / jQueryUI](http://www.jquery.com/)
* [Leaflet](http://leafletjs.com/) für die Karten (mit dem Kartenmaterial von [Skobbler](http://www.skobbler.com/))
* [Modernizr](http://modernizr.com/)
* [Bootstrap](http://getbootstrap.com/)
* [Fontello](http://fontello.com/)
* ... To be continued
