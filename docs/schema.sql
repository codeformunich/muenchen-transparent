
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege` (
  `id` int(11) NOT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_vorlage_geheim','bv_empfehlung') NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL COMMENT '0=Stadtrat',
  `gestellt_am` date DEFAULT NULL,
  `gestellt_von` mediumtext NOT NULL,
  `erledigt_am` date DEFAULT NULL,
  `antrags_nr` varchar(20) NOT NULL,
  `bearbeitungsfrist` date DEFAULT NULL,
  `registriert_am` date DEFAULT NULL,
  `referat` varchar(500) NOT NULL,
  `referent` varchar(200) NOT NULL,
  `referat_id` int(11) DEFAULT NULL,
  `wahlperiode` varchar(50) NOT NULL,
  `antrag_typ` varchar(50) NOT NULL,
  `betreff` mediumtext NOT NULL,
  `kurzinfo` mediumtext NOT NULL,
  `status` varchar(50) NOT NULL,
  `bearbeitung` varchar(100) NOT NULL,
  `fristverlaengerung` date DEFAULT NULL,
  `initiatorInnen` mediumtext NOT NULL,
  `initiative_to_aufgenommen` date DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `registriert_am` (`registriert_am`),
  KEY `datum_letzte_aenderung` (`datum_letzte_aenderung`),
  KEY `fk_antraege_bezirksausschuesse1_idx` (`ba_nr`),
  KEY `ba_datum` (`ba_nr`,`datum_letzte_aenderung`),
  KEY `antrags_nr` (`antrags_nr`),
  KEY `fk_antraege_vorgaenge1_idx` (`vorgang_id`),
  KEY `fk_antraege_referate1_idx` (`referat_id`),
  CONSTRAINT `fk_antraege_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_referate1` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege_history` (
  `id` mediumint(9) NOT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative') NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL COMMENT '0=Stadtrat',
  `gestellt_am` date DEFAULT NULL,
  `gestellt_von` mediumtext NOT NULL,
  `erledigt_am` date DEFAULT NULL,
  `antrags_nr` varchar(20) NOT NULL,
  `bearbeitungsfrist` date DEFAULT NULL,
  `registriert_am` date DEFAULT NULL,
  `referat` varchar(500) NOT NULL,
  `referent` varchar(200) NOT NULL,
  `referat_id` int(11) DEFAULT NULL,
  `wahlperiode` varchar(50) NOT NULL,
  `antrag_typ` varchar(50) NOT NULL,
  `betreff` mediumtext NOT NULL,
  `kurzinfo` mediumtext NOT NULL,
  `status` varchar(50) NOT NULL,
  `bearbeitung` varchar(100) NOT NULL,
  `fristverlaengerung` date DEFAULT NULL,
  `initiatorInnen` mediumtext NOT NULL,
  `initiative_to_aufgenommen` date DEFAULT NULL,
  PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  KEY `registriert_am` (`registriert_am`),
  KEY `datum_letzte_aenderung` (`datum_letzte_aenderung`),
  KEY `fk_antraege_history_bezirksausschuesse1_idx` (`ba_nr`),
  KEY `fk_antraege_history_vorgaenge1_idx` (`vorgang_id`),
  KEY `fk_antraege_history_referate1_idx` (`referat_id`),
  CONSTRAINT `bezirksausschuss` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_history_referate1` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_history_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege_orte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `antrag_id` int(11) DEFAULT NULL,
  `termin_id` int(11) DEFAULT NULL,
  `dokument_id` int(11) NOT NULL,
  `rathausumschau_id` mediumint(11) DEFAULT NULL,
  `ort_name` varchar(100) NOT NULL,
  `ort_id` mediumint(8) unsigned NOT NULL,
  `source` enum('text_parse','manual') NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dokument` (`dokument_id`,`ort_id`),
  KEY `antrag` (`antrag_id`),
  KEY `ort_id` (`ort_id`),
  KEY `fk_antraege_orte_antraege_dokumente1_idx` (`dokument_id`),
  KEY `fk_antraege_orte_termine1_idx` (`termin_id`),
  KEY `rathausumschau_id` (`rathausumschau_id`),
  CONSTRAINT `antraege_orte_ibfk_1` FOREIGN KEY (`rathausumschau_id`) REFERENCES `rathausumschau` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_antraege_dokumente1` FOREIGN KEY (`dokument_id`) REFERENCES `dokumente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_orte_geo1` FOREIGN KEY (`ort_id`) REFERENCES `orte_geo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_termine1` FOREIGN KEY (`termin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege_personen` (
  `antrag_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `typ` enum('gestellt_von','initiator') NOT NULL DEFAULT 'gestellt_von',
  PRIMARY KEY (`antrag_id`,`person_id`),
  KEY `person` (`person_id`),
  KEY `fk_antraege_personen_antraege1_idx` (`antrag_id`),
  CONSTRAINT `fk_antraege_personen_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_personen_ris_personen1` FOREIGN KEY (`person_id`) REFERENCES `personen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege_stadtraetInnen` (
  `antrag_id` int(11) NOT NULL,
  `stadtraetIn_id` int(11) NOT NULL,
  `gefunden_am` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`antrag_id`,`stadtraetIn_id`),
  KEY `fk_table1_antraege1_idx` (`antrag_id`),
  KEY `fk_antraege_stadtraetInnen_stadtraetInnen1_idx` (`stadtraetIn_id`),
  CONSTRAINT `fk_antraege_stadtraetInnen_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_stadtraetInnen_stadtraetInnen1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege_tags` (
  `antrag_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `zugeordnet_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `zugeordnet_benutzerIn_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`antrag_id`,`tag_id`),
  KEY `fk_antraege_tags_tags1_idx` (`tag_id`),
  CONSTRAINT `fk_antraege_tags_tags1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antraege_vorlagen` (
  `antrag1` int(11) NOT NULL,
  `antrag2` int(11) NOT NULL,
  `datum` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`antrag1`,`antrag2`),
  KEY `fk_antraege_links_antraege1_idx` (`antrag1`),
  KEY `fk_antraege_links_antraege2_idx` (`antrag2`),
  CONSTRAINT `fk_antraege_vorlagen_antraege1` FOREIGN KEY (`antrag2`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_vorlagen_antraege2` FOREIGN KEY (`antrag1`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `benutzerInnen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(45) DEFAULT NULL,
  `email_bestaetigt` tinyint(4) DEFAULT '0',
  `datum_angelegt` timestamp NULL DEFAULT NULL,
  `berechtigungen_flags` smallint(6) NOT NULL DEFAULT '0',
  `pwd_enc` varchar(100) DEFAULT NULL,
  `pwd_change_date` timestamp NULL DEFAULT NULL,
  `pwd_change_code` varchar(100) DEFAULT NULL,
  `einstellungen` blob,
  `datum_letzte_benachrichtigung` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `benutzerInnen_vorgaenge_abos` (
  `benutzerInnen_id` int(11) NOT NULL,
  `vorgaenge_id` int(11) NOT NULL,
  PRIMARY KEY (`benutzerInnen_id`,`vorgaenge_id`),
  KEY `fk_benutzerInnen_has_vorgaenge_vorgaenge1_idx` (`vorgaenge_id`),
  KEY `fk_benutzerInnen_has_vorgaenge_benutzerInnen1_idx` (`benutzerInnen_id`),
  CONSTRAINT `fk_benutzerInnen_has_vorgaenge_benutzerInnen1` FOREIGN KEY (`benutzerInnen_id`) REFERENCES `benutzerInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_benutzerInnen_has_vorgaenge_vorgaenge1` FOREIGN KEY (`vorgaenge_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bezirksausschuesse` (
  `ba_nr` smallint(6) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `osm_init_zoom` tinyint(4) DEFAULT NULL,
  `osm_shape` mediumblob,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ba_nr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bezirksausschuss_budget` (
  `ba_nr` smallint(6) NOT NULL,
  `jahr` smallint(6) NOT NULL,
  `budget` int(11) DEFAULT NULL,
  `vorjahr_rest` int(11) DEFAULT NULL,
  `cache_aktuell` int(11) DEFAULT NULL,
  PRIMARY KEY (`ba_nr`,`jahr`),
  CONSTRAINT `fk_bezirksausschuss_budget_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dokumente` (
  `id` int(11) NOT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_termin','ba_termin','stadtrat_beschluss','bv_empfehlung','ba_beschluss','rathausumschau') DEFAULT NULL,
  `antrag_id` int(11) DEFAULT NULL,
  `termin_id` int(11) DEFAULT NULL,
  `tagesordnungspunkt_id` int(11) DEFAULT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `rathausumschau_id` mediumint(11) DEFAULT NULL,
  `url` varchar(500) NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(300) NOT NULL,
  `name_title` varchar(300) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datum_dokument` timestamp NULL DEFAULT NULL,
  `text_ocr_raw` longtext,
  `text_ocr_corrected` longtext,
  `text_ocr_garbage_seiten` mediumtext,
  `text_pdf` longtext,
  `seiten_anzahl` mediumint(9) DEFAULT NULL,
  `ocr_von` enum('','tesseract','omnipage') DEFAULT NULL,
  `highlight` timestamp NULL DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `antrag_id` (`antrag_id`),
  KEY `typ` (`typ`),
  KEY `fk_antraege_dokumente_termine1_idx` (`termin_id`),
  KEY `fk_antraege_dokumente_antraege_ergebnisse1_idx` (`tagesordnungspunkt_id`),
  KEY `datum` (`datum`),
  KEY `fk_antraege_dokumente_vorgaenge1_idx` (`vorgang_id`),
  KEY `highlight_dokument` (`highlight`),
  KEY `url` (`url`(60)),
  KEY `rathausumschau_id` (`rathausumschau_id`),
  CONSTRAINT `dokumente_ibfk_1` FOREIGN KEY (`rathausumschau_id`) REFERENCES `rathausumschau` (`id`),
  CONSTRAINT `fk_antraege_dokumente_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_dokumente_termine1` FOREIGN KEY (`termin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_dokumente_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fraktionen` (
  `id` int(11) NOT NULL,
  `name` varchar(70) NOT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `website` varchar(250) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fraktionen_bezirksausschuesse1_idx` (`ba_nr`),
  CONSTRAINT `fk_fraktionen_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gremien` (
  `id` int(11) NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `kuerzel` varchar(50) NOT NULL,
  `gremientyp` varchar(100) NOT NULL,
  `referat` varchar(100) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_gremien_bezirksausschuesse1_idx` (`ba_nr`),
  CONSTRAINT `fk_gremien_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gremien_history` (
  `id` int(11) NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `kuerzel` varchar(50) NOT NULL,
  `gremientyp` varchar(100) NOT NULL,
  `referat` varchar(100) NOT NULL,
  PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  KEY `fk_gremien_bezirksausschuesse1_idx` (`ba_nr`),
  CONSTRAINT `fk_gremien_bezirksausschuesse10` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metadaten` (
  `meta_key` varchar(25) NOT NULL,
  `meta_val` blob,
  PRIMARY KEY (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orte_geo` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ort` varchar(100) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `source` enum('auto','manual') NOT NULL,
  `ba_nr` tinyint(4) DEFAULT NULL,
  `to_hide` tinyint(4) NOT NULL,
  `to_hide_kommentar` varchar(200) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ort` (`ort`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_normalized` varchar(100) NOT NULL,
  `typ` enum('person','fraktion','sonstiges') NOT NULL,
  `name` varchar(100) NOT NULL,
  `ris_stadtraetIn` int(11) DEFAULT NULL,
  `ris_fraktion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_normalized` (`name_normalized`),
  KEY `fk_personen_stadtraete1_idx` (`ris_stadtraetIn`),
  KEY `fk_personen_fraktionen1_idx` (`ris_fraktion`),
  CONSTRAINT `fk_personen_fraktionen1` FOREIGN KEY (`ris_fraktion`) REFERENCES `fraktionen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_personen_stadtraete1` FOREIGN KEY (`ris_stadtraetIn`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rathausumschau` (
  `id` mediumint(11) NOT NULL AUTO_INCREMENT,
  `datum` date DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `jahr` smallint(6) NOT NULL,
  `nr` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rechtsdokument` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(200) NOT NULL,
  `url_base` varchar(200) NOT NULL,
  `url_pdf` varchar(200) DEFAULT NULL,
  `url_html` varchar(200) NOT NULL,
  `str_beschluss` date DEFAULT NULL,
  `bekanntmachung` date DEFAULT NULL,
  `nr` varchar(45) DEFAULT NULL,
  `html` longtext,
  `css` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nr_UNIQUE` (`nr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `urlpart` varchar(45) NOT NULL,
  `strasse` varchar(45) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefon` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `kurzbebeschreibung` varchar(200) DEFAULT NULL,
  `aktiv` tinyint(4) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `urlpart_UNIQUE` (`urlpart`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ris_aenderungen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ris_id` int(11) NOT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','ba_termin','stadtrat_termin','rathausumschau','stadtraetIn','ba_mitglied','stadtrat_gremium','ba_gremium','stadtrat_ergebnis','stadtrat_fraktion','ba_ergebnis') NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aenderungen` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`),
  KEY `antrag_id` (`ris_id`),
  KEY `ba_nr` (`ba_nr`,`datum`),
  KEY `fk_ris_aenderungen_bezirksausschuesse1_idx` (`ba_nr`),
  CONSTRAINT `fk_ris_aenderungen_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stadtraetInnen` (
  `id` int(11) NOT NULL,
  `benutzerIn_id` int(11) DEFAULT NULL,
  `referentIn` tinyint(4) NOT NULL DEFAULT '0',
  `gewaehlt_am` date DEFAULT NULL,
  `bio` mediumtext NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `web` varchar(250) NOT NULL,
  `name` varchar(100) NOT NULL,
  `twitter` varchar(45) DEFAULT NULL,
  `facebook` varchar(200) DEFAULT NULL,
  `abgeordnetenwatch` varchar(200) DEFAULT NULL,
  `geschlecht` enum('weiblich','maennlich','sonstiges') DEFAULT NULL,
  `kontaktdaten` text,
  `geburtstag` date DEFAULT NULL,
  `beruf` text NOT NULL,
  `beschreibung` text NOT NULL,
  `quellen` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id` (`benutzerIn_id`),
  CONSTRAINT `fr_stadtraetIn_benutzerIn` FOREIGN KEY (`benutzerIn_id`) REFERENCES `benutzerInnen` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stadtraetInnen_fraktionen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stadtraetIn_id` int(11) NOT NULL,
  `fraktion_id` int(11) NOT NULL,
  `wahlperiode` varchar(30) NOT NULL,
  `datum_von` date DEFAULT NULL,
  `datum_bis` date DEFAULT NULL,
  `mitgliedschaft` mediumtext NOT NULL,
  `funktion` mediumtext,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ris_stadtraete_fraktionen_ris_personen1_idx` (`stadtraetIn_id`),
  KEY `fk_stadtraete_fraktionen_fraktionen1_idx` (`fraktion_id`),
  KEY `uq` (`stadtraetIn_id`,`fraktion_id`,`wahlperiode`),
  CONSTRAINT `fk_stadtraetInnen_fraktionen` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_stadtraete_fraktionen_fraktionen2` FOREIGN KEY (`fraktion_id`) REFERENCES `fraktionen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stadtraetInnen_gremien` (
  `stadtraetIn_id` int(11) NOT NULL,
  `gremium_id` int(11) NOT NULL,
  `datum_von` date NOT NULL,
  `datum_bis` date DEFAULT NULL,
  `funktion` varchar(100) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stadtraetIn_id` (`stadtraetIn_id`,`gremium_id`,`datum_von`,`funktion`) USING BTREE,
  KEY `fk_stadtraetIn_gremien_mitgliedschaft_stadtraetInnen1_idx` (`stadtraetIn_id`),
  KEY `fk_stadtraetIn_gremien_mitgliedschaft_gremien1_idx` (`gremium_id`),
  CONSTRAINT `fk_stadtraetIn_gremien_mitgliedschaft_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_stadtraetIn_gremien_mitgliedschaft_stadtraetInnen1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stadtraetInnen_referate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stadtraetIn_id` int(11) NOT NULL,
  `referat_id` int(11) NOT NULL,
  `datum_von` date DEFAULT NULL,
  `datum_bis` date DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `stadtraetIn_id` (`stadtraetIn_id`),
  KEY `fraktion_id` (`referat_id`),
  CONSTRAINT `stadtraetInnen_referate_ibfk_1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`),
  CONSTRAINT `stadtraetInnen_referate_ibfk_2` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistik_datensaetze` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `quelle` tinyint(4) NOT NULL,
  `indikator_gruppe` varchar(100) NOT NULL,
  `indikator_bezeichnung` varchar(100) NOT NULL,
  `indikator_auspraegung` varchar(100) NOT NULL,
  `indikator_wert` float DEFAULT NULL,
  `basiswert_1` float DEFAULT NULL,
  `basiswert_1_name` varchar(100) NOT NULL,
  `basiswert_2` float DEFAULT NULL,
  `basiswert_2_name` varchar(100) NOT NULL,
  `basiswert_3` float DEFAULT NULL,
  `basiswert_3_name` varchar(100) NOT NULL,
  `basiswert_4` float DEFAULT NULL,
  `basiswert_4_name` varchar(100) NOT NULL,
  `basiswert_5` float DEFAULT NULL,
  `basiswert_5_name` varchar(100) NOT NULL,
  `jahr` smallint(6) NOT NULL,
  `gliederung` varchar(100) NOT NULL,
  `gliederung_nummer` mediumint(9) NOT NULL,
  `gliederung_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jahr` (`jahr`),
  KEY `gliederung_nummer` (`gliederung_nummer`,`jahr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `strassen` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `plz` varchar(20) NOT NULL,
  `osm_ref` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tagesordnungspunkte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vorgang_id` int(11) DEFAULT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `antrag_id` int(11) DEFAULT NULL,
  `gremium_name` varchar(100) NOT NULL,
  `gremium_id` int(11) DEFAULT NULL,
  `sitzungstermin_id` int(11) NOT NULL,
  `sitzungstermin_datum` date NOT NULL,
  `beschluss_text` varchar(500) NOT NULL,
  `entscheidung` mediumtext,
  `top_pos` int(11) NOT NULL DEFAULT '0',
  `top_id` int(11) DEFAULT NULL,
  `top_nr` varchar(45) DEFAULT NULL,
  `top_ueberschrift` tinyint(4) NOT NULL DEFAULT '0',
  `top_betreff` mediumtext,
  `status` varchar(200) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id`),
  KEY `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id`),
  KEY `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id`),
  KEY `fk_antraege_ergebnisse_vorgaenge1_idx` (`vorgang_id`),
  CONSTRAINT `fk_antraege_ergebnisse_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_termine1` FOREIGN KEY (`sitzungstermin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tagesordnungspunkte_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vorgang_id` int(11) DEFAULT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `antrag_id` int(11) DEFAULT NULL,
  `gremium_name` varchar(100) NOT NULL,
  `gremium_id` int(11) DEFAULT NULL,
  `sitzungstermin_id` int(11) NOT NULL,
  `sitzungstermin_datum` date NOT NULL,
  `beschluss_text` varchar(500) NOT NULL,
  `top_pos` int(11) NOT NULL DEFAULT '0',
  `top_id` int(11) DEFAULT NULL,
  `top_nr` varchar(45) DEFAULT NULL,
  `top_ueberschrift` tinyint(4) NOT NULL DEFAULT '0',
  `top_betreff` mediumtext,
  `status` varchar(200) DEFAULT NULL,
  `entscheidung` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  KEY `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id`),
  KEY `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id`),
  KEY `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id`),
  KEY `fk_antraege_ergebnisse_history_vorgaenge1_idx` (`vorgang_id`),
  CONSTRAINT `fk_antraege_ergebnisse_antraege10` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_gremien10` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_history_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_termine10` FOREIGN KEY (`sitzungstermin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `angelegt_benutzerIn_id` int(11) DEFAULT NULL,
  `angelegt_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed` tinyint(4) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termine` (
  `id` int(11) NOT NULL,
  `typ` smallint(5) unsigned NOT NULL DEFAULT '0',
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `termin_reihe` int(11) NOT NULL DEFAULT '0',
  `gremium_id` int(11) DEFAULT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `termin` timestamp NOT NULL DEFAULT '1999-12-31 23:00:00',
  `termin_prev_id` int(11) DEFAULT NULL,
  `termin_next_id` int(11) DEFAULT NULL,
  `sitzungsort` mediumtext NOT NULL,
  `referat` varchar(200) NOT NULL,
  `referent` varchar(200) NOT NULL,
  `vorsitz` varchar(200) NOT NULL,
  `wahlperiode` varchar(20) NOT NULL,
  `status` varchar(100) NOT NULL,
  `sitzungsstand` varchar(100) NOT NULL,
  `abgesetzt` tinyint(4) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `termin` (`termin`),
  KEY `termin_reihe` (`termin_reihe`),
  KEY `fk_termine_gremien1_idx` (`gremium_id`),
  KEY `ba_nr` (`ba_nr`,`typ`),
  CONSTRAINT `fk_termine_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `termine_history` (
  `id` int(11) NOT NULL,
  `typ` smallint(5) unsigned NOT NULL DEFAULT '0',
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `termin_reihe` int(11) NOT NULL DEFAULT '0',
  `gremium_id` int(11) NULL DEFAULT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `termin` timestamp NULL DEFAULT NULL,
  `termin_prev_id` int(11) DEFAULT NULL,
  `termin_next_id` int(11) DEFAULT NULL,
  `sitzungsort` mediumtext NOT NULL,
  `referat` varchar(200) NOT NULL,
  `referent` varchar(200) NOT NULL,
  `vorsitz` varchar(200) NOT NULL,
  `wahlperiode` varchar(20) NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  KEY `termin` (`termin`),
  KEY `termin_reihe` (`termin_reihe`),
  KEY `fk_termine_history_bezirksausschuesse1_idx` (`ba_nr`),
  KEY `ba_nr` (`ba_nr`,`typ`),
  CONSTRAINT `fk_termine_history_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `texte` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `typ` smallint(6) NOT NULL DEFAULT '0',
  `pos` smallint(6) NOT NULL DEFAULT '0',
  `text` longtext,
  `titel` varchar(180) DEFAULT NULL,
  `edit_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edit_benutzerIn_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos` (`typ`,`pos`),
  KEY `fk_texte_benutzerInnen1_idx` (`edit_benutzerIn_id`),
  CONSTRAINT `fk_texte_benutzerInnen1` FOREIGN KEY (`edit_benutzerIn_id`) REFERENCES `benutzerInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vorgaenge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typ` tinyint(4) DEFAULT NULL,
  `betreff` varchar(200) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

