/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*!40000 ALTER TABLE `antraege` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `antraege` VALUES (1,NULL,'stadtrat_antrag','2016-05-19 12:48:42',NULL,NULL,'',NULL,'',NULL,NULL,'','',1,'','','Antrag ohne Vorgang','','','',NULL,'',NULL,'2016-04-30 22:00:00','2016-04-30 22:00:00');
INSERT INTO `antraege` VALUES (2,1,'stadtrat_antrag','2016-05-19 12:49:58',NULL,NULL,'',NULL,'',NULL,NULL,'','',1,'','','Antrag mit verwandten Seiten','','','',NULL,'',NULL,'2016-05-01 22:00:00','2016-05-01 22:00:00');
INSERT INTO `antraege` VALUES (3,1,'stadtrat_antrag','2016-05-19 12:50:01',NULL,NULL,'',NULL,'',NULL,NULL,'','',1,'','','Ein verwandter Antrag','','','',NULL,'',NULL,'2016-05-02 22:00:00','2016-05-02 22:00:00');
INSERT INTO `antraege` VALUES (4,NULL,'stadtrat_antrag','2016-05-19 12:50:04',NULL,NULL,'',NULL,'',NULL,NULL,'','',1,'','','Antrag mit mehreren Dokumenten','','','',NULL,'',NULL,'2016-05-03 22:00:00','2016-05-03 22:00:00');
INSERT INTO `antraege` VALUES (5,NULL,'stadtrat_antrag','2016-05-19 12:51:54',NULL,NULL,'',NULL,'',NULL,NULL,'','',1,'','','Ein Antrag mit einem Dokument','','','',NULL,'',NULL,'2016-05-04 22:00:00','2016-05-04 22:00:00');
INSERT INTO `antraege` VALUES (6,NULL,'stadtrat_antrag','2016-04-30 20:01:10',NULL,NULL,'',NULL,'',NULL,NULL,'','',1,'','','Antrag mit Dokument mit vielen Eigenschaften','','','',NULL,'',NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `antraege` VALUES (7,NULL,'stadtrat_antrag','2016-05-17 20:57:54',NULL,'2015-01-01','Antragssteller','1999-04-01','','2026-01-01','2015-01-15','Referat für einen Antrag mit vielen Antraegen','Referent für einen Antrag mit vielen Antraegen',1,'1','Antrag','betreff','kurzinfo','status','lange','2038-01-19','StrIn. Dr. A. B. C.','2016-04-01','2016-05-02 17:53:08','2016-05-17 20:57:54');
INSERT INTO `antraege` VALUES (8,NULL,'ba_antrag','2016-04-30 20:14:03',1,NULL,'',NULL,'',NULL,NULL,'','',1,'','','','','','',NULL,'',NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
/*!40000 ALTER TABLE `antraege` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `antraege_history` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `antraege_history` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `antraege_orte` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `antraege_orte` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `antraege_personen` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `antraege_personen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `antraege_stadtraetInnen` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `antraege_stadtraetInnen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `antraege_tags` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `antraege_tags` VALUES (7,1,'2016-05-23 19:11:08',NULL);
/*!40000 ALTER TABLE `antraege_tags` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `antraege_vorlagen` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `antraege_vorlagen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `benutzerInnen` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `benutzerInnen` VALUES (47,'user@example.com',0,'2016-01-17 18:12:13',0,'$2y$10$NqowUOiQd3SNm8/zACCaguhyYpMxw8hX9pfxsvIrnXpI3/KHXfP4u',NULL,NULL,NULL,'2016-01-17 18:12:13');
/*!40000 ALTER TABLE `benutzerInnen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `benutzerInnen_vorgaenge_abos` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `benutzerInnen_vorgaenge_abos` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `bezirksausschuesse` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `bezirksausschuesse` VALUES (0,0,'Stadtrat','https://www.muenchen.de/rathaus/',11,NULL,'2016-09-29 12:31:50','2016-09-29 12:42:52');
INSERT INTO `bezirksausschuesse` VALUES (1,0,'BA mit Ausschuss mit Termin','http://www.ba1.muenchen.de',13,'[[11.575738191604614,48.13937283176814],[11.574804782867432,48.13723923356946],[11.576242446899414,48.13688123860767],[11.577283143997192,48.13884302035666],[11.575738191604614,48.13937283176814]]\n','2016-07-26 19:58:04','2016-07-26 19:58:05');
/*!40000 ALTER TABLE `bezirksausschuesse` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `bezirksausschuss_budget` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bezirksausschuss_budget` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `dokumente` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `dokumente` VALUES (0,NULL,NULL,NULL,NULL,NULL,NULL,'',0,'Dokument nur mit Titel','','2016-03-07 20:28:31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (1,NULL,3,NULL,NULL,1,NULL,'',0,'Ein verwandtes Dokument','Ein verwandtes Dokument','2016-01-23 15:50:18',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (2,NULL,2,NULL,NULL,NULL,NULL,'',0,'Das Dokument zum Antrag mit verwandten Seiten','Das Dokument zum Antrag mit verwandten Seiten','2016-01-23 15:52:08',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (3,'stadtrat_antrag',4,NULL,NULL,NULL,NULL,'',0,'Ein Dokument von mehreren in einem Antrag','','2016-03-07 20:18:22',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (4,'stadtrat_antrag',4,NULL,NULL,NULL,NULL,'',0,'Ein anderes Dokument von mehreren in einem Antrag','','2016-03-07 20:18:22',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (5,NULL,5,NULL,NULL,NULL,NULL,'',0,'Ein Dokument von einem Antrag mit einem Dokument','','2016-03-07 20:27:52',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (6,NULL,NULL,NULL,NULL,NULL,NULL,'',0,'Dokument ohne Antrag','','2016-03-07 20:32:58',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `dokumente` VALUES (7,NULL,6,4,1,2,NULL,'/media/testdokument.pdf',0,'Dokument (pdf) mit vielen Eigenschaften','Dokument viele Eigenschaften','2016-04-23 16:30:20','2016-04-21 22:00:00',NULL,NULL,NULL,NULL,10,'omnipage',NULL,'2016-05-02 17:53:08','2016-05-08 20:57:23');
INSERT INTO `dokumente` VALUES (8,NULL,NULL,NULL,NULL,NULL,NULL,'/media/testdokument.tiff',0,'Dokument (tiff) mit wenig Eigenschaften','','2016-04-23 16:30:20',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-08 20:57:28');
INSERT INTO `dokumente` VALUES (9,NULL,NULL,NULL,NULL,NULL,1,'http://example.org/rathausumschau/1-rathaus.pdf',0,'Rathausumschau','','2016-04-23 16:30:20',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-02 17:53:08','2016-05-09 16:09:15');
INSERT INTO `dokumente` VALUES (10,NULL,NULL,NULL,NULL,NULL,NULL,'/dev/nowhere',0,'unauffällig gelöschtes Dokument','','2016-05-09 16:08:46',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2016-05-09 16:08:46','2016-05-09 16:08:46');
/*!40000 ALTER TABLE `dokumente` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `fraktionen` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `fraktionen` VALUES (1,'Fraktion der Politiker',1,'https://www.example.org/fraktion-der-politiker','2016-05-02 17:53:08','2016-05-02 17:53:08');
INSERT INTO `fraktionen` VALUES (2,'Fraktion des Stadtrat',NULL,'','2016-05-02 17:53:08','2016-05-02 17:53:08');
/*!40000 ALTER TABLE `fraktionen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `gremien` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `gremien` VALUES (1,'2016-01-31 16:25:43',1,'Ausschuss mit Terminen','','Ausschuss','','2016-05-02 17:53:08','2016-05-02 17:53:09');
INSERT INTO `gremien` VALUES (2,'2016-05-08 10:11:31',NULL,'Ausschuss mit Mitgliedern','','','','2016-05-08 10:11:31','2016-05-08 10:11:31');
/*!40000 ALTER TABLE `gremien` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `gremien_history` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `gremien_history` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `metadaten` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `metadaten` VALUES ('anzahl_dokumente','147122');
INSERT INTO `metadaten` VALUES ('anzahl_dokumente_1w','346');
INSERT INTO `metadaten` VALUES ('anzahl_seiten','535039');
INSERT INTO `metadaten` VALUES ('anzahl_seiten_1w','1684');
INSERT INTO `metadaten` VALUES ('letzte_aktualisierun','2013-05-12 21:41:34');
INSERT INTO `metadaten` VALUES ('letzte_aktualisierung','2014-09-15 04:04:33');
/*!40000 ALTER TABLE `metadaten` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `orte_geo` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `orte_geo` VALUES (1,'Marienplatz',11.576006,48.137079,'manual',NULL,0,'','2016-10-09 14:12:24');
/*!40000 ALTER TABLE `orte_geo` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `personen` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `personen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `rathausumschau` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `rathausumschau` VALUES (1,'2016-04-23','http://example.org/rathausumschau/1-rathaus.pdf',2016,1);
/*!40000 ALTER TABLE `rathausumschau` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `rechtsdokument` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `rechtsdokument` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `referate` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `referate` VALUES (1,'Referat für städtische Aufgaben','aufg','Münchnerstr. 1','12345','München','aufg@example.com','089132456','http://aufg.example.com','Verantwortlich für Staädtische Aufgaben',1,'2016-05-02 17:53:09','2016-05-02 17:53:09');
INSERT INTO `referate` VALUES (7,'Referat für Arbeit und Wirtschaft','wirtschaft','Münchnerstr. 1','12345','München','aufg@example.com','089132456','http://aufg.example.com','',1,'2016-05-02 17:53:09','2016-05-02 17:53:09');
/*!40000 ALTER TABLE `referate` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `ris_aenderungen` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `ris_aenderungen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `stadtraetInnen` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stadtraetInnen` VALUES (1,NULL,0,'2014-05-01','Geboren am 31.05.1971 um 18:09:45\n\nQuery: `SELECT FROM_UNIXTIME(avg(unix_timestamp(geburtstag))) FROM stadtraetInnen WHERE geburtstag`','meine.email@gmail.com','https://example.com','Dr. Stadtrat, mit allen Eigenschaften','@StadtratmitallenEigenschaften','StadtratmitallenEigenschaften_1123410','Stadtrat mit allen Eigenschaften','maennlich','München','1971-05-31','Stadtrat','„Bürgernahe Steuersenkungen für Sicherheit und Freiheit“','~','2016-05-02 17:53:09','2016-06-30 14:44:25');
INSERT INTO `stadtraetInnen` VALUES (2,NULL,0,NULL,'',NULL,'','Stadträtin mit möglichst wenigen Eigenschaften',NULL,NULL,NULL,NULL,NULL,NULL,'','','','2016-05-02 17:53:09','2016-05-02 17:53:09');
INSERT INTO `stadtraetInnen` VALUES (3,NULL,1,NULL,'',NULL,'','Referent für Städtische Aufgaben',NULL,NULL,NULL,NULL,NULL,NULL,'','','','2016-05-02 17:53:09','2016-05-02 17:53:09');
INSERT INTO `stadtraetInnen` VALUES (4,NULL,0,NULL,'',NULL,'','Stadtrat in Gremium',NULL,NULL,NULL,NULL,NULL,NULL,'','','','2016-05-08 10:09:27','2016-05-08 10:09:27');
/*!40000 ALTER TABLE `stadtraetInnen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `stadtraetInnen_fraktionen` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stadtraetInnen_fraktionen` VALUES (1,1,1,'2','2000-01-01','2004-01-01','von 01.01.2000 bis 01.01.2004','Mitglied','2016-05-02 17:53:09','2016-05-02 17:53:09');
INSERT INTO `stadtraetInnen_fraktionen` VALUES (2,1,1,'3','2004-01-01',NULL,'seit 01.01.2014','Vorsitzender','2016-05-02 17:53:09','2016-06-24 08:58:33');
INSERT INTO `stadtraetInnen_fraktionen` VALUES (3,1,2,'2','2000-01-01','2004-01-01','von 01.01.2000 bis 01.01.2004','Mitglied','2016-05-02 17:53:09','2016-06-30 14:44:25');
/*!40000 ALTER TABLE `stadtraetInnen_fraktionen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `stadtraetInnen_gremien` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stadtraetInnen_gremien` VALUES (4,2,'2016-05-01','2016-05-02','Mitglied',1,'2016-05-08 10:10:02','2016-05-08 10:11:48');
/*!40000 ALTER TABLE `stadtraetInnen_gremien` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `stadtraetInnen_referate` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `stadtraetInnen_referate` VALUES (1,3,1,NULL,NULL,'2016-05-02 17:53:09','2016-05-02 17:53:09');
/*!40000 ALTER TABLE `stadtraetInnen_referate` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `statistik_datensaetze` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `statistik_datensaetze` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `strassen` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `strassen` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `tagesordnungspunkte` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tagesordnungspunkte` VALUES (1,NULL,'2016-04-23 16:28:45',NULL,'',NULL,4,'2016-04-23','Tagesorgdnungspunkt mit Dokument mit vielen Eigenschaften',NULL,NULL,0,NULL,NULL,'2016-05-02 17:53:09','2016-05-02 17:53:09');
/*!40000 ALTER TABLE `tagesordnungspunkte` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `tagesordnungspunkte_history` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tagesordnungspunkte_history` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tags` VALUES (1,'Fahrradfahrer',NULL,'2016-05-23 19:10:34',1,'2016-05-23 19:10:34','2016-05-23 19:10:51');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `termine` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `termine` VALUES (1,0,'2016-01-31 16:27:28',0,1,NULL,'2016-01-01 08:00:00',3,2,'Raum für einen Termin','','','','','','',0,'2016-05-02 17:53:09','2016-05-02 17:53:10');
INSERT INTO `termine` VALUES (2,0,'2016-01-31 16:27:28',0,1,NULL,'2016-02-01 08:00:00',NULL,NULL,'Raum für einen Termin','','','','','','',0,'2016-05-02 17:53:09','2016-05-02 17:53:10');
INSERT INTO `termine` VALUES (3,0,'2016-01-31 16:27:28',0,1,NULL,'2015-12-01 08:00:00',NULL,NULL,'Raum für einen Termin','','','','','','',0,'2016-05-02 17:53:09','2016-05-02 17:53:10');
INSERT INTO `termine` VALUES (4,0,'2016-04-23 16:27:45',0,1,NULL,'2016-04-11 22:00:00',NULL,NULL,'Ort','','','','','Termin zu Dokument mit vielen Eigenschaften','',0,'2016-05-02 17:53:09','2016-05-02 17:53:10');
INSERT INTO `termine` VALUES (5,0,'2016-09-26 15:07:51',0,NULL,NULL,'2000-04-03 22:00:00',NULL,NULL,'Raum des unbekannten Referats','','','','','Dateninkonsitent','Dateninkonsitent',0,'2016-09-26 15:07:51','2016-09-26 15:26:54');
/*!40000 ALTER TABLE `termine` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `termine_history` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `termine_history` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `texte` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `texte` ENABLE KEYS */;
commit;

/*!40000 ALTER TABLE `vorgaenge` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `vorgaenge` VALUES (1,NULL,NULL,'2016-05-02 17:53:10','2016-05-02 17:53:10');
INSERT INTO `vorgaenge` VALUES (2,NULL,'Vorgang mit Dokument mit vielen Eigenschaften','2016-05-02 17:53:10','2016-05-02 17:53:10');
/*!40000 ALTER TABLE `vorgaenge` ENABLE KEYS */;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

