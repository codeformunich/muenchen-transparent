-- MySQL dump 10.13  Distrib 5.6.28, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ristest
-- ------------------------------------------------------
-- Server version	5.6.28-0ubuntu0.15.10.1
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `antraege`
--

/*!40000 ALTER TABLE `antraege` DISABLE KEYS */;
INSERT INTO `antraege` VALUES (1,NULL,'stadtrat_antrag','2016-01-23 15:13:14',NULL,NULL,'',NULL,'',NULL,NULL,'','',NULL,'','','Antrag ohne Vorgang','','','',NULL,'',NULL);
INSERT INTO `antraege` VALUES (2,1,'stadtrat_antrag','2016-01-23 15:26:49',NULL,NULL,'',NULL,'',NULL,NULL,'','',NULL,'','','Antrag mit verwandten Seiten','','','',NULL,'',NULL);
INSERT INTO `antraege` VALUES (3,1,'stadtrat_antrag','2016-01-23 15:28:24',NULL,NULL,'',NULL,'',NULL,NULL,'','',NULL,'','','Ein verwandter Antrag','','','',NULL,'',NULL);
INSERT INTO `antraege` VALUES (4,NULL,'stadtrat_antrag','2016-03-07 20:16:45',NULL,NULL,'',NULL,'',NULL,NULL,'','',NULL,'','','Antrag mit mehreren Dokumenten','','','',NULL,'',NULL);
INSERT INTO `antraege` VALUES (5,NULL,'stadtrat_antrag','2016-03-07 20:27:15',NULL,NULL,'',NULL,'',NULL,NULL,'','',NULL,'','','Ein Antrag mit einem Dokument','','','',NULL,'',NULL);
/*!40000 ALTER TABLE `antraege` ENABLE KEYS */;

--
-- Dumping data for table `antraege_history`
--

/*!40000 ALTER TABLE `antraege_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `antraege_history` ENABLE KEYS */;

--
-- Dumping data for table `antraege_orte`
--

/*!40000 ALTER TABLE `antraege_orte` DISABLE KEYS */;
/*!40000 ALTER TABLE `antraege_orte` ENABLE KEYS */;

--
-- Dumping data for table `antraege_personen`
--

/*!40000 ALTER TABLE `antraege_personen` DISABLE KEYS */;
/*!40000 ALTER TABLE `antraege_personen` ENABLE KEYS */;

--
-- Dumping data for table `antraege_stadtraetInnen`
--

/*!40000 ALTER TABLE `antraege_stadtraetInnen` DISABLE KEYS */;
/*!40000 ALTER TABLE `antraege_stadtraetInnen` ENABLE KEYS */;

--
-- Dumping data for table `antraege_tags`
--

/*!40000 ALTER TABLE `antraege_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `antraege_tags` ENABLE KEYS */;

--
-- Dumping data for table `antraege_vorlagen`
--

/*!40000 ALTER TABLE `antraege_vorlagen` DISABLE KEYS */;
/*!40000 ALTER TABLE `antraege_vorlagen` ENABLE KEYS */;

--
-- Dumping data for table `benutzerInnen`
--

/*!40000 ALTER TABLE `benutzerInnen` DISABLE KEYS */;
INSERT INTO `benutzerInnen` VALUES (47,'user@example.com',0,'2016-01-17 18:12:13',0,'$2y$10$NqowUOiQd3SNm8/zACCaguhyYpMxw8hX9pfxsvIrnXpI3/KHXfP4u',NULL,NULL,NULL,'2016-01-17 18:12:13');
/*!40000 ALTER TABLE `benutzerInnen` ENABLE KEYS */;

--
-- Dumping data for table `benutzerInnen_vorgaenge_abos`
--

/*!40000 ALTER TABLE `benutzerInnen_vorgaenge_abos` DISABLE KEYS */;
/*!40000 ALTER TABLE `benutzerInnen_vorgaenge_abos` ENABLE KEYS */;

--
-- Dumping data for table `bezirksausschuesse`
--

/*!40000 ALTER TABLE `bezirksausschuesse` DISABLE KEYS */;
INSERT INTO `bezirksausschuesse` VALUES (1,0,'BA mit Ausschuss mit Termin',NULL,NULL,NULL);
/*!40000 ALTER TABLE `bezirksausschuesse` ENABLE KEYS */;

--
-- Dumping data for table `bezirksausschuss_budget`
--

/*!40000 ALTER TABLE `bezirksausschuss_budget` DISABLE KEYS */;
/*!40000 ALTER TABLE `bezirksausschuss_budget` ENABLE KEYS */;

--
-- Dumping data for table `dokumente`
--

/*!40000 ALTER TABLE `dokumente` DISABLE KEYS */;
INSERT INTO `dokumente` VALUES (0,NULL,NULL,NULL,NULL,NULL,NULL,'',0,'Dokument nur mit Titel','','2016-03-07 20:28:31',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `dokumente` VALUES (1,NULL,3,NULL,NULL,1,NULL,'',0,'Ein verwandtes Dokument','Ein verwandtes Dokument','2016-01-23 15:50:18',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `dokumente` VALUES (2,NULL,2,NULL,NULL,NULL,NULL,'',0,'Das Dokument zum Antrag mit verwandten Seiten','Das Dokument zum Antrag mit verwandten Seiten','2016-01-23 15:52:08',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `dokumente` VALUES (3,'stadtrat_antrag',4,NULL,NULL,NULL,NULL,'',0,'Ein Dokument von mehreren in einem Antrag','','2016-03-07 20:18:22',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `dokumente` VALUES (4,'stadtrat_antrag',4,NULL,NULL,NULL,NULL,'',0,'Ein anderes Dokument von mehreren in einem Antrag','','2016-03-07 20:18:22',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `dokumente` VALUES (5,NULL,5,NULL,NULL,NULL,NULL,'',0,'Ein Dokument von einem Antrag mit einem Dokument','','2016-03-07 20:27:52',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `dokumente` VALUES (6,NULL,NULL,NULL,NULL,NULL,NULL,'',0,'Dokument ohne Antrag','','2016-03-07 20:32:58',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `dokumente` ENABLE KEYS */;

--
-- Dumping data for table `fraktionen`
--

/*!40000 ALTER TABLE `fraktionen` DISABLE KEYS */;
INSERT INTO `fraktionen` VALUES (1,'Fraktion der Politiker',1,'https://www.example.org/fraktion-der-politiker');
INSERT INTO `fraktionen` VALUES (2,'Fraktion des Stadtrat',NULL,'');
/*!40000 ALTER TABLE `fraktionen` ENABLE KEYS */;

--
-- Dumping data for table `gremien`
--

/*!40000 ALTER TABLE `gremien` DISABLE KEYS */;
INSERT INTO `gremien` VALUES (1,'2016-01-31 16:25:43',1,'Ausschuss mit Terminen','','Ausschuss','');
/*!40000 ALTER TABLE `gremien` ENABLE KEYS */;

--
-- Dumping data for table `gremien_history`
--

/*!40000 ALTER TABLE `gremien_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `gremien_history` ENABLE KEYS */;

--
-- Dumping data for table `metadaten`
--

/*!40000 ALTER TABLE `metadaten` DISABLE KEYS */;
INSERT INTO `metadaten` VALUES ('anzahl_dokumente','147122');
INSERT INTO `metadaten` VALUES ('anzahl_dokumente_1w','346');
INSERT INTO `metadaten` VALUES ('anzahl_seiten','535039');
INSERT INTO `metadaten` VALUES ('anzahl_seiten_1w','1684');
INSERT INTO `metadaten` VALUES ('letzte_aktualisierun','2013-05-12 21:41:34');
INSERT INTO `metadaten` VALUES ('letzte_aktualisierung','2014-09-15 04:04:33');
/*!40000 ALTER TABLE `metadaten` ENABLE KEYS */;

--
-- Dumping data for table `orte_geo`
--

/*!40000 ALTER TABLE `orte_geo` DISABLE KEYS */;
/*!40000 ALTER TABLE `orte_geo` ENABLE KEYS */;

--
-- Dumping data for table `personen`
--

/*!40000 ALTER TABLE `personen` DISABLE KEYS */;
/*!40000 ALTER TABLE `personen` ENABLE KEYS */;

--
-- Dumping data for table `rathausumschau`
--

/*!40000 ALTER TABLE `rathausumschau` DISABLE KEYS */;
/*!40000 ALTER TABLE `rathausumschau` ENABLE KEYS */;

--
-- Dumping data for table `rechtsdokument`
--

/*!40000 ALTER TABLE `rechtsdokument` DISABLE KEYS */;
/*!40000 ALTER TABLE `rechtsdokument` ENABLE KEYS */;

--
-- Dumping data for table `referate`
--

/*!40000 ALTER TABLE `referate` DISABLE KEYS */;
/*!40000 ALTER TABLE `referate` ENABLE KEYS */;

--
-- Dumping data for table `ris_aenderungen`
--

/*!40000 ALTER TABLE `ris_aenderungen` DISABLE KEYS */;
/*!40000 ALTER TABLE `ris_aenderungen` ENABLE KEYS */;

--
-- Dumping data for table `stadtraetInnen`
--

/*!40000 ALTER TABLE `stadtraetInnen` DISABLE KEYS */;
INSERT INTO `stadtraetInnen` VALUES (1,NULL,0,'2014-05-01','Geboren am 31.05.1971 um 18:09:45\n\nQuery: `SELECT FROM_UNIXTIME(avg(unix_timestamp(geburtstag))) FROM stadtraetInnen WHERE geburtstag`','meine.email@gmail.com','https://example.com','Stadtrat mit allen Eigenschaften','@StadtratmitallenEigenschaften','StadtratmitallenEigenschaften_1123410','Stadtrat mit allen Eigenschaften','maennlich','München','1971-05-31','Stadtrat','„Bürgernahe Steuersenkungen für Sicherheit und Freiheit“','~');
INSERT INTO `stadtraetInnen` VALUES (2,NULL,0,NULL,'',NULL,'','Stadträtin mit möglichst wenigen Eigenschaften',NULL,NULL,NULL,NULL,NULL,NULL,'','','');
/*!40000 ALTER TABLE `stadtraetInnen` ENABLE KEYS */;

--
-- Dumping data for table `stadtraetInnen_fraktionen`
--

/*!40000 ALTER TABLE `stadtraetInnen_fraktionen` DISABLE KEYS */;
INSERT INTO `stadtraetInnen_fraktionen` VALUES (1,1,1,'[TODO]','2000-01-01','2004-01-01','von 01.01.2000 bis 01.01.2004','Mitglied');
INSERT INTO `stadtraetInnen_fraktionen` VALUES (2,1,1,'[TODO]','2004-01-01',NULL,'seit 01.01.2014','Vorsitzender');
/*!40000 ALTER TABLE `stadtraetInnen_fraktionen` ENABLE KEYS */;

--
-- Dumping data for table `stadtraetInnen_gremien`
--

/*!40000 ALTER TABLE `stadtraetInnen_gremien` DISABLE KEYS */;
/*!40000 ALTER TABLE `stadtraetInnen_gremien` ENABLE KEYS */;

--
-- Dumping data for table `stadtraetInnen_referate`
--

/*!40000 ALTER TABLE `stadtraetInnen_referate` DISABLE KEYS */;
/*!40000 ALTER TABLE `stadtraetInnen_referate` ENABLE KEYS */;

--
-- Dumping data for table `statistik_datensaetze`
--

/*!40000 ALTER TABLE `statistik_datensaetze` DISABLE KEYS */;
/*!40000 ALTER TABLE `statistik_datensaetze` ENABLE KEYS */;

--
-- Dumping data for table `strassen`
--

/*!40000 ALTER TABLE `strassen` DISABLE KEYS */;
/*!40000 ALTER TABLE `strassen` ENABLE KEYS */;

--
-- Dumping data for table `tagesordnungspunkte`
--

/*!40000 ALTER TABLE `tagesordnungspunkte` DISABLE KEYS */;
/*!40000 ALTER TABLE `tagesordnungspunkte` ENABLE KEYS */;

--
-- Dumping data for table `tagesordnungspunkte_history`
--

/*!40000 ALTER TABLE `tagesordnungspunkte_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `tagesordnungspunkte_history` ENABLE KEYS */;

--
-- Dumping data for table `tags`
--

/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;

--
-- Dumping data for table `termine`
--

/*!40000 ALTER TABLE `termine` DISABLE KEYS */;
INSERT INTO `termine` VALUES (1,0,'2016-01-31 16:27:28',0,1,NULL,'2016-01-01 08:00:00',3,2,'Raum für einen Termin','','','','','','',0);
INSERT INTO `termine` VALUES (2,0,'2016-01-31 16:27:28',0,1,NULL,'2016-02-01 08:00:00',NULL,NULL,'Raum für einen Termin','','','','','','',0);
INSERT INTO `termine` VALUES (3,0,'2016-01-31 16:27:28',0,1,NULL,'2015-12-01 08:00:00',NULL,NULL,'Raum für einen Termin','','','','','','',0);
/*!40000 ALTER TABLE `termine` ENABLE KEYS */;

--
-- Dumping data for table `termine_history`
--

/*!40000 ALTER TABLE `termine_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `termine_history` ENABLE KEYS */;

--
-- Dumping data for table `texte`
--

/*!40000 ALTER TABLE `texte` DISABLE KEYS */;
/*!40000 ALTER TABLE `texte` ENABLE KEYS */;

--
-- Dumping data for table `vorgaenge`
--

/*!40000 ALTER TABLE `vorgaenge` DISABLE KEYS */;
INSERT INTO `vorgaenge` VALUES (1,NULL,NULL);
/*!40000 ALTER TABLE `vorgaenge` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-04-08 20:51:46
