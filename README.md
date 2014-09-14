[Ratsinformant](https://ratsinformant.de)
=========================================

Installation
------------------
Zuerst wird ein Webserver mit php und ein MySQL-Server benötigt, der Lese- und Schreibzugriff auf alle Dateien und Ordner hat.
Danach müssen die php-Abhängigkeiten mit Hilfe vom composer installiert werden. Wenn composer bereits installiert ist, reicht ein `composer install` im Terminal, ansonsten funktioniert auch die folgenden Befehle
```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

Jetzt muss php noch die die richtigen Werte und Pfade für den eigenen Sever erfahren. Dazu muss main.template.php in /protected/config/ in main.php umbenannt werden und die ursprünglichen Werte müssen durch die eigenene ersetzt werden.
Besonders wichtig sind die Einstellungen für die Verbindung zu MySQL: Dazu müssen alle Tabellen von /docs/schema3.sql in mysql importiert werden, wobei eine Datenbank "ris2" erstellt wird. Danach muss ein Nutzer mit Zugriff auf diese Datenbank erstellt werden, dessen Name und Passwort in main.php eingetragen wird.

to be continued...

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
