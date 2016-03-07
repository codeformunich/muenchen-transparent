-- phpMyAdmin SQL Dump
-- version 4.5.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 07. Mrz 2016 um 22:28
-- Server-Version: 5.6.28-0ubuntu0.15.10.1
-- PHP-Version: 5.6.11-1ubuntu3.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `ristest`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege`
--

CREATE TABLE `antraege` (
  `id` int(11) NOT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_vorlage_geheim','bv_empfehlung') NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL COMMENT AS `0=Stadtrat`,
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
  `initiative_to_aufgenommen` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `antraege`
--

INSERT INTO `antraege` (`id`, `vorgang_id`, `typ`, `datum_letzte_aenderung`, `ba_nr`, `gestellt_am`, `gestellt_von`, `erledigt_am`, `antrags_nr`, `bearbeitungsfrist`, `registriert_am`, `referat`, `referent`, `referat_id`, `wahlperiode`, `antrag_typ`, `betreff`, `kurzinfo`, `status`, `bearbeitung`, `fristverlaengerung`, `initiatorInnen`, `initiative_to_aufgenommen`) VALUES
(1, NULL, 'stadtrat_antrag', '2016-01-23 15:13:14', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Antrag ohne Vorgang', '', '', '', NULL, '', NULL),
(2, 1, 'stadtrat_antrag', '2016-01-23 15:26:49', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Antrag mit verwandten Seiten', '', '', '', NULL, '', NULL),
(3, 1, 'stadtrat_antrag', '2016-01-23 15:28:24', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Ein verwandter Antrag', '', '', '', NULL, '', NULL),
(4, NULL, 'stadtrat_antrag', '2016-03-07 20:16:45', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Antrag mit mehreren Dokumenten', '', '', '', NULL, '', NULL),
(5, NULL, 'stadtrat_antrag', '2016-03-07 20:27:15', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Ein Antrag mit einem Dokument', '', '', '', NULL, '', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege_history`
--

CREATE TABLE `antraege_history` (
  `id` mediumint(9) NOT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative') NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL COMMENT AS `0=Stadtrat`,
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
  `initiative_to_aufgenommen` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege_orte`
--

CREATE TABLE `antraege_orte` (
  `id` int(11) NOT NULL,
  `antrag_id` int(11) DEFAULT NULL,
  `termin_id` int(11) DEFAULT NULL,
  `dokument_id` int(11) NOT NULL,
  `rathausumschau_id` mediumint(11) DEFAULT NULL,
  `ort_name` varchar(100) NOT NULL,
  `ort_id` smallint(5) UNSIGNED NOT NULL,
  `source` enum('text_parse','manual') NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege_personen`
--

CREATE TABLE `antraege_personen` (
  `antrag_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `typ` enum('gestellt_von','initiator') NOT NULL DEFAULT 'gestellt_von'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege_stadtraetInnen`
--

CREATE TABLE `antraege_stadtraetInnen` (
  `antrag_id` int(11) NOT NULL,
  `stadtraetIn_id` int(11) NOT NULL,
  `gefunden_am` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege_tags`
--

CREATE TABLE `antraege_tags` (
  `antrag_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `zugeordnet_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `zugeordnet_benutzerIn_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `antraege_vorlagen`
--

CREATE TABLE `antraege_vorlagen` (
  `antrag1` int(11) NOT NULL,
  `antrag2` int(11) NOT NULL,
  `datum` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `benutzerInnen`
--

CREATE TABLE `benutzerInnen` (
  `id` int(11) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `email_bestaetigt` tinyint(4) DEFAULT '0',
  `datum_angelegt` timestamp NULL DEFAULT NULL,
  `berechtigungen_flags` smallint(6) NOT NULL DEFAULT '0',
  `pwd_enc` varchar(100) DEFAULT NULL,
  `pwd_change_date` timestamp NULL DEFAULT NULL,
  `pwd_change_code` varchar(100) DEFAULT NULL,
  `einstellungen` blob,
  `datum_letzte_benachrichtigung` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `benutzerInnen`
--

INSERT INTO `benutzerInnen` (`id`, `email`, `email_bestaetigt`, `datum_angelegt`, `berechtigungen_flags`, `pwd_enc`, `pwd_change_date`, `pwd_change_code`, `einstellungen`, `datum_letzte_benachrichtigung`) VALUES
(47, 'user@example.com', 0, '2016-01-17 18:12:13', 0, '$2y$10$NqowUOiQd3SNm8/zACCaguhyYpMxw8hX9pfxsvIrnXpI3/KHXfP4u', NULL, NULL, NULL, '2016-01-17 18:12:13');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `benutzerInnen_vorgaenge_abos`
--

CREATE TABLE `benutzerInnen_vorgaenge_abos` (
  `benutzerInnen_id` int(11) NOT NULL,
  `vorgaenge_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `bezirksausschuesse`
--

CREATE TABLE `bezirksausschuesse` (
  `ba_nr` smallint(6) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `osm_init_zoom` tinyint(4) DEFAULT NULL,
  `osm_shape` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `bezirksausschuesse`
--

INSERT INTO `bezirksausschuesse` (`ba_nr`, `ris_id`, `name`, `website`, `osm_init_zoom`, `osm_shape`) VALUES
(1, 0, 'BA mit Ausschuss mit Termin', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `bezirksausschuss_budget`
--

CREATE TABLE `bezirksausschuss_budget` (
  `ba_nr` smallint(6) NOT NULL,
  `jahr` smallint(6) NOT NULL,
  `budget` int(11) DEFAULT NULL,
  `vorjahr_rest` int(11) DEFAULT NULL,
  `cache_aktuell` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dokumente`
--

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
  `highlight` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `dokumente`
--

INSERT INTO `dokumente` (`id`, `typ`, `antrag_id`, `termin_id`, `tagesordnungspunkt_id`, `vorgang_id`, `rathausumschau_id`, `url`, `deleted`, `name`, `name_title`, `datum`, `datum_dokument`, `text_ocr_raw`, `text_ocr_corrected`, `text_ocr_garbage_seiten`, `text_pdf`, `seiten_anzahl`, `ocr_von`, `highlight`) VALUES
(0, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 'Dokument nur mit Titel', '', '2016-03-07 20:28:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1, NULL, 3, NULL, NULL, 1, NULL, '', 0, 'Ein verwandtes Dokument', 'Ein verwandtes Dokument', '2016-01-23 15:50:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, NULL, 2, NULL, NULL, NULL, NULL, '', 0, 'Das Dokument zum Antrag mit verwandten Seiten', 'Das Dokument zum Antrag mit verwandten Seiten', '2016-01-23 15:52:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'stadtrat_antrag', 4, NULL, NULL, NULL, NULL, '', 0, 'Ein Dokument von mehreren in einem Antrag', '', '2016-03-07 20:18:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'stadtrat_antrag', 4, NULL, NULL, NULL, NULL, '', 0, 'Ein anderes Dokument von mehreren in einem Antrag', '', '2016-03-07 20:18:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, NULL, 5, NULL, NULL, NULL, NULL, '', 0, 'Ein Dokument von einem Antrag mit einem Dokument', '', '2016-03-07 20:27:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 'Dokument ohne Antrag', '', '2016-03-07 20:32:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fraktionen`
--

CREATE TABLE `fraktionen` (
  `id` int(11) NOT NULL,
  `name` varchar(70) NOT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `website` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gremien`
--

CREATE TABLE `gremien` (
  `id` int(11) NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `kuerzel` varchar(50) NOT NULL,
  `gremientyp` varchar(100) NOT NULL,
  `referat` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `gremien`
--

INSERT INTO `gremien` (`id`, `datum_letzte_aenderung`, `ba_nr`, `name`, `kuerzel`, `gremientyp`, `referat`) VALUES
(1, '2016-01-31 16:25:43', 1, 'Ausschuss mit Terminen', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gremien_history`
--

CREATE TABLE `gremien_history` (
  `id` int(11) NOT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_nr` smallint(6) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `kuerzel` varchar(20) NOT NULL,
  `gremientyp` varchar(100) NOT NULL,
  `referat` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `metadaten`
--

CREATE TABLE `metadaten` (
  `meta_key` varchar(25) NOT NULL,
  `meta_val` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `metadaten`
--

INSERT INTO `metadaten` (`meta_key`, `meta_val`) VALUES
('anzahl_dokumente', 0x313437313232),
('anzahl_dokumente_1w', 0x333436),
('anzahl_seiten', 0x353335303339),
('anzahl_seiten_1w', 0x31363834),
('letzte_aktualisierun', 0x323031332d30352d31322032313a34313a3334),
('letzte_aktualisierung', 0x323031342d30392d31352030343a30343a3333);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orte_geo`
--

CREATE TABLE `orte_geo` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `ort` varchar(100) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `source` enum('auto','manual') NOT NULL,
  `ba_nr` tinyint(4) DEFAULT NULL,
  `to_hide` tinyint(4) NOT NULL,
  `to_hide_kommentar` varchar(200) NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `personen`
--

CREATE TABLE `personen` (
  `id` int(11) NOT NULL,
  `name_normalized` varchar(100) NOT NULL,
  `typ` enum('person','fraktion','sonstiges') NOT NULL,
  `name` varchar(100) NOT NULL,
  `ris_stadtraetIn` int(11) DEFAULT NULL,
  `ris_fraktion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rathausumschau`
--

CREATE TABLE `rathausumschau` (
  `id` mediumint(11) NOT NULL,
  `datum` date DEFAULT NULL,
  `url` varchar(200) DEFAULT NULL,
  `jahr` smallint(6) NOT NULL,
  `nr` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rechtsdokument`
--

CREATE TABLE `rechtsdokument` (
  `id` int(11) NOT NULL,
  `titel` varchar(200) NOT NULL,
  `url_base` varchar(200) NOT NULL,
  `url_pdf` varchar(200) DEFAULT NULL,
  `url_html` varchar(200) NOT NULL,
  `str_beschluss` date DEFAULT NULL,
  `bekanntmachung` date DEFAULT NULL,
  `nr` varchar(45) DEFAULT NULL,
  `html` longtext,
  `css` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `referate`
--

CREATE TABLE `referate` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `urlpart` varchar(45) NOT NULL,
  `strasse` varchar(45) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefon` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `kurzbebeschreibung` varchar(200) DEFAULT NULL,
  `aktiv` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ris_aenderungen`
--

CREATE TABLE `ris_aenderungen` (
  `id` int(11) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `typ` enum('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','ba_termin','stadtrat_termin','rathausumschau','stadtraetIn','ba_mitglied','stadtrat_gremium','ba_gremium','stadtrat_ergebnis','stadtrat_fraktion','ba_ergebnis') NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aenderungen` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stadtraetInnen`
--

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
  `quellen` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stadtraetInnen_fraktionen`
--

CREATE TABLE `stadtraetInnen_fraktionen` (
  `id` int(11) NOT NULL,
  `stadtraetIn_id` int(11) NOT NULL,
  `fraktion_id` int(11) NOT NULL,
  `wahlperiode` varchar(30) NOT NULL,
  `datum_von` date DEFAULT NULL,
  `datum_bis` date DEFAULT NULL,
  `mitgliedschaft` mediumtext NOT NULL,
  `funktion` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stadtraetInnen_gremien`
--

CREATE TABLE `stadtraetInnen_gremien` (
  `stadtraetIn_id` int(11) NOT NULL,
  `gremium_id` int(11) NOT NULL,
  `datum_von` date NOT NULL,
  `datum_bis` date DEFAULT NULL,
  `funktion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stadtraetInnen_referate`
--

CREATE TABLE `stadtraetInnen_referate` (
  `id` int(11) NOT NULL,
  `stadtraetIn_id` int(11) NOT NULL,
  `referat_id` int(11) NOT NULL,
  `datum_von` date DEFAULT NULL,
  `datum_bis` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `statistik_datensaetze`
--

CREATE TABLE `statistik_datensaetze` (
  `id` mediumint(9) NOT NULL,
  `quelle` tinyint(4) NOT NULL,
  `indikator_gruppe` varchar(50) NOT NULL,
  `indikator_bezeichnung` varchar(100) NOT NULL,
  `indikator_auspraegung` varchar(100) NOT NULL,
  `indikator_wert` float DEFAULT NULL,
  `basiswert_1` float DEFAULT NULL,
  `basiswert_1_name` varchar(50) NOT NULL,
  `basiswert_2` float NOT NULL,
  `basiswert_2_name` varchar(50) NOT NULL,
  `basiswert_3` float DEFAULT NULL,
  `basiswert_3_name` varchar(50) NOT NULL,
  `basiswert_4` float DEFAULT NULL,
  `basiswert_4_name` varchar(50) NOT NULL,
  `basiswert_5` float DEFAULT NULL,
  `basiswert_5_name` varchar(50) NOT NULL,
  `jahr` smallint(6) NOT NULL,
  `gliederung` varchar(50) NOT NULL,
  `gliederung_nummer` mediumint(9) NOT NULL,
  `gliederung_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `strassen`
--

CREATE TABLE `strassen` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `plz` varchar(20) NOT NULL,
  `osm_ref` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tagesordnungspunkte`
--

CREATE TABLE `tagesordnungspunkte` (
  `id` int(11) NOT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `antrag_id` int(11) DEFAULT NULL,
  `gremium_name` varchar(100) NOT NULL,
  `gremium_id` int(11) DEFAULT NULL,
  `sitzungstermin_id` int(11) NOT NULL,
  `sitzungstermin_datum` date NOT NULL,
  `beschluss_text` varchar(500) NOT NULL,
  `entscheidung` mediumtext,
  `top_nr` varchar(45) DEFAULT NULL,
  `top_ueberschrift` tinyint(4) NOT NULL DEFAULT '0',
  `top_betreff` mediumtext,
  `status` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tagesordnungspunkte_history`
--

CREATE TABLE `tagesordnungspunkte_history` (
  `id` int(11) NOT NULL,
  `vorgang_id` int(11) DEFAULT NULL,
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `antrag_id` int(11) DEFAULT NULL,
  `gremium_name` varchar(100) NOT NULL,
  `gremium_id` int(11) DEFAULT NULL,
  `sitzungstermin_id` int(11) NOT NULL,
  `sitzungstermin_datum` date NOT NULL,
  `beschluss_text` varchar(500) NOT NULL,
  `top_nr` varchar(45) DEFAULT NULL,
  `top_ueberschrift` tinyint(4) NOT NULL DEFAULT '0',
  `top_betreff` mediumtext,
  `status` varchar(200) DEFAULT NULL,
  `entscheidung` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `angelegt_benutzerIn_id` int(11) DEFAULT NULL,
  `angelegt_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `termine`
--

CREATE TABLE `termine` (
  `id` int(11) NOT NULL,
  `typ` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `termin_reihe` int(11) NOT NULL DEFAULT '0',
  `gremium_id` int(11) DEFAULT NULL,
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
  `sitzungsstand` varchar(100) NOT NULL,
  `abgesetzt` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `termine`
--

INSERT INTO `termine` (`id`, `typ`, `datum_letzte_aenderung`, `termin_reihe`, `gremium_id`, `ba_nr`, `termin`, `termin_prev_id`, `termin_next_id`, `sitzungsort`, `referat`, `referent`, `vorsitz`, `wahlperiode`, `status`, `sitzungsstand`, `abgesetzt`) VALUES
(1, 0, '2016-01-31 16:27:28', 0, 1, NULL, '2016-01-01 08:00:00', 3, 2, 'Raum für einen Termin', '', '', '', '', '', '', 0),
(2, 0, '2016-01-31 16:27:28', 0, 1, NULL, '2016-02-01 08:00:00', NULL, NULL, 'Raum für einen Termin', '', '', '', '', '', '', 0),
(3, 0, '2016-01-31 16:27:28', 0, 1, NULL, '2015-12-01 08:00:00', NULL, NULL, 'Raum für einen Termin', '', '', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `termine_history`
--

CREATE TABLE `termine_history` (
  `id` int(11) NOT NULL,
  `typ` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `datum_letzte_aenderung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `termin_reihe` int(11) NOT NULL DEFAULT '0',
  `gremium_id` int(11) NOT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `termin` timestamp NULL DEFAULT NULL,
  `termin_prev_id` int(11) DEFAULT NULL,
  `termin_next_id` int(11) DEFAULT NULL,
  `sitzungsort` mediumtext NOT NULL,
  `referat` varchar(200) NOT NULL,
  `referent` varchar(200) NOT NULL,
  `vorsitz` varchar(200) NOT NULL,
  `wahlperiode` varchar(20) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `texte`
--

CREATE TABLE `texte` (
  `id` mediumint(9) NOT NULL,
  `typ` smallint(6) NOT NULL DEFAULT '0',
  `pos` smallint(6) NOT NULL DEFAULT '0',
  `text` longtext,
  `titel` varchar(180) DEFAULT NULL,
  `edit_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edit_benutzerIn_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vorgaenge`
--

CREATE TABLE `vorgaenge` (
  `id` int(11) NOT NULL,
  `typ` tinyint(4) DEFAULT NULL,
  `betreff` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `vorgaenge`
--

INSERT INTO `vorgaenge` (`id`, `typ`, `betreff`) VALUES
(1, NULL, NULL);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `antraege`
--
ALTER TABLE `antraege`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registriert_am` (`registriert_am`),
  ADD KEY `datum_letzte_aenderung` (`datum_letzte_aenderung`),
  ADD KEY `fk_antraege_bezirksausschuesse1_idx` (`ba_nr`),
  ADD KEY `ba_datum` (`ba_nr`,`datum_letzte_aenderung`),
  ADD KEY `antrags_nr` (`antrags_nr`),
  ADD KEY `fk_antraege_vorgaenge1_idx` (`vorgang_id`),
  ADD KEY `fk_antraege_referate1_idx` (`referat_id`);

--
-- Indizes für die Tabelle `antraege_history`
--
ALTER TABLE `antraege_history`
  ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  ADD KEY `registriert_am` (`registriert_am`),
  ADD KEY `datum_letzte_aenderung` (`datum_letzte_aenderung`),
  ADD KEY `fk_antraege_history_bezirksausschuesse1_idx` (`ba_nr`),
  ADD KEY `fk_antraege_history_vorgaenge1_idx` (`vorgang_id`),
  ADD KEY `fk_antraege_history_referate1_idx` (`referat_id`);

--
-- Indizes für die Tabelle `antraege_orte`
--
ALTER TABLE `antraege_orte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dokument` (`dokument_id`,`ort_id`),
  ADD KEY `antrag` (`antrag_id`),
  ADD KEY `ort_id` (`ort_id`),
  ADD KEY `fk_antraege_orte_antraege_dokumente1_idx` (`dokument_id`),
  ADD KEY `fk_antraege_orte_termine1_idx` (`termin_id`),
  ADD KEY `rathausumschau_id` (`rathausumschau_id`);

--
-- Indizes für die Tabelle `antraege_personen`
--
ALTER TABLE `antraege_personen`
  ADD PRIMARY KEY (`antrag_id`,`person_id`),
  ADD KEY `person` (`person_id`),
  ADD KEY `fk_antraege_personen_antraege1_idx` (`antrag_id`);

--
-- Indizes für die Tabelle `antraege_stadtraetInnen`
--
ALTER TABLE `antraege_stadtraetInnen`
  ADD PRIMARY KEY (`antrag_id`,`stadtraetIn_id`),
  ADD KEY `fk_table1_antraege1_idx` (`antrag_id`),
  ADD KEY `fk_antraege_stadtraetInnen_stadtraetInnen1_idx` (`stadtraetIn_id`);

--
-- Indizes für die Tabelle `antraege_tags`
--
ALTER TABLE `antraege_tags`
  ADD PRIMARY KEY (`antrag_id`,`tag_id`),
  ADD KEY `fk_antraege_tags_tags1_idx` (`tag_id`);

--
-- Indizes für die Tabelle `antraege_vorlagen`
--
ALTER TABLE `antraege_vorlagen`
  ADD PRIMARY KEY (`antrag1`,`antrag2`),
  ADD KEY `fk_antraege_links_antraege1_idx` (`antrag1`),
  ADD KEY `fk_antraege_links_antraege2_idx` (`antrag2`);

--
-- Indizes für die Tabelle `benutzerInnen`
--
ALTER TABLE `benutzerInnen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `benutzerInnen_vorgaenge_abos`
--
ALTER TABLE `benutzerInnen_vorgaenge_abos`
  ADD PRIMARY KEY (`benutzerInnen_id`,`vorgaenge_id`),
  ADD KEY `fk_benutzerInnen_has_vorgaenge_vorgaenge1_idx` (`vorgaenge_id`),
  ADD KEY `fk_benutzerInnen_has_vorgaenge_benutzerInnen1_idx` (`benutzerInnen_id`);

--
-- Indizes für die Tabelle `bezirksausschuesse`
--
ALTER TABLE `bezirksausschuesse`
  ADD PRIMARY KEY (`ba_nr`);

--
-- Indizes für die Tabelle `bezirksausschuss_budget`
--
ALTER TABLE `bezirksausschuss_budget`
  ADD PRIMARY KEY (`ba_nr`,`jahr`);

--
-- Indizes für die Tabelle `dokumente`
--
ALTER TABLE `dokumente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `antrag_id` (`antrag_id`),
  ADD KEY `typ` (`typ`),
  ADD KEY `fk_antraege_dokumente_termine1_idx` (`termin_id`),
  ADD KEY `fk_antraege_dokumente_antraege_ergebnisse1_idx` (`tagesordnungspunkt_id`),
  ADD KEY `datum` (`datum`),
  ADD KEY `fk_antraege_dokumente_vorgaenge1_idx` (`vorgang_id`),
  ADD KEY `highlight_dokument` (`highlight`),
  ADD KEY `url` (`url`(60)),
  ADD KEY `rathausumschau_id` (`rathausumschau_id`);

--
-- Indizes für die Tabelle `fraktionen`
--
ALTER TABLE `fraktionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fraktionen_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indizes für die Tabelle `gremien`
--
ALTER TABLE `gremien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gremien_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indizes für die Tabelle `gremien_history`
--
ALTER TABLE `gremien_history`
  ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  ADD KEY `fk_gremien_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indizes für die Tabelle `metadaten`
--
ALTER TABLE `metadaten`
  ADD PRIMARY KEY (`meta_key`);

--
-- Indizes für die Tabelle `orte_geo`
--
ALTER TABLE `orte_geo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ort` (`ort`);

--
-- Indizes für die Tabelle `personen`
--
ALTER TABLE `personen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_normalized` (`name_normalized`),
  ADD KEY `fk_personen_stadtraete1_idx` (`ris_stadtraetIn`),
  ADD KEY `fk_personen_fraktionen1_idx` (`ris_fraktion`);

--
-- Indizes für die Tabelle `rathausumschau`
--
ALTER TABLE `rathausumschau`
  ADD PRIMARY KEY (`id`),
  ADD KEY `datum` (`datum`);

--
-- Indizes für die Tabelle `rechtsdokument`
--
ALTER TABLE `rechtsdokument`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nr_UNIQUE` (`nr`);

--
-- Indizes für die Tabelle `referate`
--
ALTER TABLE `referate`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `urlpart_UNIQUE` (`urlpart`);

--
-- Indizes für die Tabelle `ris_aenderungen`
--
ALTER TABLE `ris_aenderungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `datum` (`datum`),
  ADD KEY `antrag_id` (`ris_id`),
  ADD KEY `ba_nr` (`ba_nr`,`datum`),
  ADD KEY `fk_ris_aenderungen_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indizes für die Tabelle `stadtraetInnen`
--
ALTER TABLE `stadtraetInnen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`benutzerIn_id`);

--
-- Indizes für die Tabelle `stadtraetInnen_fraktionen`
--
ALTER TABLE `stadtraetInnen_fraktionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ris_stadtraete_fraktionen_ris_personen1_idx` (`stadtraetIn_id`),
  ADD KEY `fk_stadtraete_fraktionen_fraktionen1_idx` (`fraktion_id`),
  ADD KEY `uq` (`stadtraetIn_id`,`fraktion_id`,`wahlperiode`);

--
-- Indizes für die Tabelle `stadtraetInnen_gremien`
--
ALTER TABLE `stadtraetInnen_gremien`
  ADD PRIMARY KEY (`stadtraetIn_id`,`gremium_id`,`datum_von`),
  ADD KEY `fk_stadtraetIn_gremien_mitgliedschaft_stadtraetInnen1_idx` (`stadtraetIn_id`),
  ADD KEY `fk_stadtraetIn_gremien_mitgliedschaft_gremien1_idx` (`gremium_id`);

--
-- Indizes für die Tabelle `stadtraetInnen_referate`
--
ALTER TABLE `stadtraetInnen_referate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stadtraetIn_id` (`stadtraetIn_id`),
  ADD KEY `fraktion_id` (`referat_id`);

--
-- Indizes für die Tabelle `statistik_datensaetze`
--
ALTER TABLE `statistik_datensaetze`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jahr` (`jahr`),
  ADD KEY `gliederung_nummer` (`gliederung_nummer`,`jahr`);

--
-- Indizes für die Tabelle `strassen`
--
ALTER TABLE `strassen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `tagesordnungspunkte`
--
ALTER TABLE `tagesordnungspunkte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ix_sitzung_antrag` (`antrag_id`,`sitzungstermin_id`),
  ADD KEY `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id`),
  ADD KEY `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id`),
  ADD KEY `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id`),
  ADD KEY `fk_antraege_ergebnisse_vorgaenge1_idx` (`vorgang_id`);

--
-- Indizes für die Tabelle `tagesordnungspunkte_history`
--
ALTER TABLE `tagesordnungspunkte_history`
  ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  ADD KEY `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id`),
  ADD KEY `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id`),
  ADD KEY `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id`),
  ADD KEY `fk_antraege_ergebnisse_history_vorgaenge1_idx` (`vorgang_id`);

--
-- Indizes für die Tabelle `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

--
-- Indizes für die Tabelle `termine`
--
ALTER TABLE `termine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `termin` (`termin`),
  ADD KEY `termin_reihe` (`termin_reihe`),
  ADD KEY `fk_termine_gremien1_idx` (`gremium_id`),
  ADD KEY `ba_nr` (`ba_nr`,`typ`);

--
-- Indizes für die Tabelle `termine_history`
--
ALTER TABLE `termine_history`
  ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`),
  ADD KEY `termin` (`termin`),
  ADD KEY `termin_reihe` (`termin_reihe`),
  ADD KEY `fk_termine_history_bezirksausschuesse1_idx` (`ba_nr`),
  ADD KEY `ba_nr` (`ba_nr`,`typ`);

--
-- Indizes für die Tabelle `texte`
--
ALTER TABLE `texte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pos` (`typ`,`pos`),
  ADD KEY `fk_texte_benutzerInnen1_idx` (`edit_benutzerIn_id`);

--
-- Indizes für die Tabelle `vorgaenge`
--
ALTER TABLE `vorgaenge`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `antraege_orte`
--
ALTER TABLE `antraege_orte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `benutzerInnen`
--
ALTER TABLE `benutzerInnen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;
--
-- AUTO_INCREMENT für Tabelle `orte_geo`
--
ALTER TABLE `orte_geo`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `personen`
--
ALTER TABLE `personen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `rathausumschau`
--
ALTER TABLE `rathausumschau`
  MODIFY `id` mediumint(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `rechtsdokument`
--
ALTER TABLE `rechtsdokument`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `referate`
--
ALTER TABLE `referate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `ris_aenderungen`
--
ALTER TABLE `ris_aenderungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `stadtraetInnen_fraktionen`
--
ALTER TABLE `stadtraetInnen_fraktionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `stadtraetInnen_referate`
--
ALTER TABLE `stadtraetInnen_referate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `statistik_datensaetze`
--
ALTER TABLE `statistik_datensaetze`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `strassen`
--
ALTER TABLE `strassen`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `tagesordnungspunkte`
--
ALTER TABLE `tagesordnungspunkte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `tagesordnungspunkte_history`
--
ALTER TABLE `tagesordnungspunkte_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `texte`
--
ALTER TABLE `texte`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `vorgaenge`
--
ALTER TABLE `vorgaenge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `antraege`
--
ALTER TABLE `antraege`
  ADD CONSTRAINT `fk_antraege_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_referate1` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `antraege_history`
--
ALTER TABLE `antraege_history`
  ADD CONSTRAINT `bezirksausschuss` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_history_referate1` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_history_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `antraege_orte`
--
ALTER TABLE `antraege_orte`
  ADD CONSTRAINT `antraege_orte_ibfk_1` FOREIGN KEY (`rathausumschau_id`) REFERENCES `rathausumschau` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_orte_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_orte_antraege_dokumente1` FOREIGN KEY (`dokument_id`) REFERENCES `dokumente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_orte_orte_geo1` FOREIGN KEY (`ort_id`) REFERENCES `orte_geo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_orte_termine1` FOREIGN KEY (`termin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `antraege_personen`
--
ALTER TABLE `antraege_personen`
  ADD CONSTRAINT `fk_antraege_personen_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_personen_ris_personen1` FOREIGN KEY (`person_id`) REFERENCES `personen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `antraege_stadtraetInnen`
--
ALTER TABLE `antraege_stadtraetInnen`
  ADD CONSTRAINT `fk_antraege_stadtraetInnen_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_stadtraetInnen_stadtraetInnen1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `antraege_tags`
--
ALTER TABLE `antraege_tags`
  ADD CONSTRAINT `fk_antraege_tags_tags1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `antraege_vorlagen`
--
ALTER TABLE `antraege_vorlagen`
  ADD CONSTRAINT `fk_antraege_vorlagen_antraege1` FOREIGN KEY (`antrag2`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_vorlagen_antraege2` FOREIGN KEY (`antrag1`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `benutzerInnen_vorgaenge_abos`
--
ALTER TABLE `benutzerInnen_vorgaenge_abos`
  ADD CONSTRAINT `fk_benutzerInnen_has_vorgaenge_benutzerInnen1` FOREIGN KEY (`benutzerInnen_id`) REFERENCES `benutzerInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_benutzerInnen_has_vorgaenge_vorgaenge1` FOREIGN KEY (`vorgaenge_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `bezirksausschuss_budget`
--
ALTER TABLE `bezirksausschuss_budget`
  ADD CONSTRAINT `fk_bezirksausschuss_budget_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `dokumente`
--
ALTER TABLE `dokumente`
  ADD CONSTRAINT `dokumente_ibfk_1` FOREIGN KEY (`rathausumschau_id`) REFERENCES `rathausumschau` (`id`),
  ADD CONSTRAINT `fk_antraege_dokumente_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_dokumente_termine1` FOREIGN KEY (`termin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_dokumente_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `fraktionen`
--
ALTER TABLE `fraktionen`
  ADD CONSTRAINT `fk_fraktionen_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `gremien`
--
ALTER TABLE `gremien`
  ADD CONSTRAINT `fk_gremien_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `gremien_history`
--
ALTER TABLE `gremien_history`
  ADD CONSTRAINT `fk_gremien_bezirksausschuesse10` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `personen`
--
ALTER TABLE `personen`
  ADD CONSTRAINT `fk_personen_fraktionen1` FOREIGN KEY (`ris_fraktion`) REFERENCES `fraktionen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_personen_stadtraete1` FOREIGN KEY (`ris_stadtraetIn`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `ris_aenderungen`
--
ALTER TABLE `ris_aenderungen`
  ADD CONSTRAINT `fk_ris_aenderungen_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `stadtraetInnen`
--
ALTER TABLE `stadtraetInnen`
  ADD CONSTRAINT `fr_stadtraetIn_benutzerIn` FOREIGN KEY (`benutzerIn_id`) REFERENCES `benutzerInnen` (`id`);

--
-- Constraints der Tabelle `stadtraetInnen_fraktionen`
--
ALTER TABLE `stadtraetInnen_fraktionen`
  ADD CONSTRAINT `fk_stadtraetInnen_fraktionen` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_stadtraete_fraktionen_fraktionen2` FOREIGN KEY (`fraktion_id`) REFERENCES `fraktionen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `stadtraetInnen_gremien`
--
ALTER TABLE `stadtraetInnen_gremien`
  ADD CONSTRAINT `fk_stadtraetIn_gremien_mitgliedschaft_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_stadtraetIn_gremien_mitgliedschaft_stadtraetInnen1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `stadtraetInnen_referate`
--
ALTER TABLE `stadtraetInnen_referate`
  ADD CONSTRAINT `stadtraetInnen_referate_ibfk_1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`),
  ADD CONSTRAINT `stadtraetInnen_referate_ibfk_2` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`);

--
-- Constraints der Tabelle `tagesordnungspunkte`
--
ALTER TABLE `tagesordnungspunkte`
  ADD CONSTRAINT `fk_antraege_ergebnisse_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_ergebnisse_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_ergebnisse_termine1` FOREIGN KEY (`sitzungstermin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_ergebnisse_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `tagesordnungspunkte_history`
--
ALTER TABLE `tagesordnungspunkte_history`
  ADD CONSTRAINT `fk_antraege_ergebnisse_antraege10` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_ergebnisse_gremien10` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_ergebnisse_history_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_antraege_ergebnisse_termine10` FOREIGN KEY (`sitzungstermin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `termine`
--
ALTER TABLE `termine`
  ADD CONSTRAINT `fk_termine_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `termine_history`
--
ALTER TABLE `termine_history`
  ADD CONSTRAINT `fk_termine_history_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `texte`
--
ALTER TABLE `texte`
  ADD CONSTRAINT `fk_texte_benutzerInnen1` FOREIGN KEY (`edit_benutzerIn_id`) REFERENCES `benutzerInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
