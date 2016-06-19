## Implemtierungshinweise zur OParl-Schnittstelle

Um die Kompatibilität zu späteren OParl-Versionen zu gewährleisten, haben die Datein für OParl 1.0 eine `10` im Namen.

### Dateistruktur

OParl ist fast vollständig in nur drei Dateien Implementiert:
 * __protected/controllers/Oparl10Controller.php__: Weiterleitung der Anfragen, "Formatierung" der Ausgabe sowie einige
 Hilfsunktionen
 * __protected/components/OParl10Object.php__: Alle Objekte
 * __protected/components/OParl10List.php__: Die externen Objektlisten

### Tests

Alle Bestandteile der API werden mit Hilfe einer eigenen Codeception-Suite ausführlich getestet. Zur Vereinfachung des
Testens gibt die Funktion `$I->getOParl([url-affix])` (implementiert in `tests/_support/OparlTester.php`), die ein
OParl-Objekt abruft und das Objekt durch `$I->getPrettyResponse()` (pretty-printed json),
`$I->getUglyResponse()` (komprimiertes json) und `$I->getTree()` (PHP-Array) bereitstellt. Letztere sowie einige weitere Hilfsfunktionen
gibt es in `tests/_support/Helper/Oparl.php`. Es ist zu beachten, dass dabei alle Umlaute bereits dekodiert sind.

`$I->getOParl([url-affix])` kümmert sich um die Einhaltung einiger allgemeiner Kriterien, wie z.B. HTTP-Header und
Statuscodes, und überprüft, ob die Antwort des Server der in `tests/_data/oparl[url-affix].json` gespeicherten
entspricht. Um die erwarteten Anworten zu aktualisieren wird `--env updatejson` an den Aufruf von Codeception angehängt.