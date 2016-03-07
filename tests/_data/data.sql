-- phpMyAdmin SQL Dump
-- version 4.5.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 07. Mrz 2016 um 22:32
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

--
-- Daten für Tabelle `antraege`
--

INSERT INTO `antraege` (`id`, `vorgang_id`, `typ`, `datum_letzte_aenderung`, `ba_nr`, `gestellt_am`, `gestellt_von`, `erledigt_am`, `antrags_nr`, `bearbeitungsfrist`, `registriert_am`, `referat`, `referent`, `referat_id`, `wahlperiode`, `antrag_typ`, `betreff`, `kurzinfo`, `status`, `bearbeitung`, `fristverlaengerung`, `initiatorInnen`, `initiative_to_aufgenommen`) VALUES
(1, NULL, 'stadtrat_antrag', '2016-01-23 15:13:14', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Antrag ohne Vorgang', '', '', '', NULL, '', NULL),
(2, 1, 'stadtrat_antrag', '2016-01-23 15:26:49', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Antrag mit verwandten Seiten', '', '', '', NULL, '', NULL),
(3, 1, 'stadtrat_antrag', '2016-01-23 15:28:24', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Ein verwandter Antrag', '', '', '', NULL, '', NULL),
(4, NULL, 'stadtrat_antrag', '2016-03-07 20:16:45', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Antrag mit mehreren Dokumenten', '', '', '', NULL, '', NULL),
(5, NULL, 'stadtrat_antrag', '2016-03-07 20:27:15', NULL, NULL, '', NULL, '', NULL, NULL, '', '', NULL, '', '', 'Ein Antrag mit einem Dokument', '', '', '', NULL, '', NULL);

--
-- Daten für Tabelle `benutzerInnen`
--

INSERT INTO `benutzerInnen` (`id`, `email`, `email_bestaetigt`, `datum_angelegt`, `berechtigungen_flags`, `pwd_enc`, `pwd_change_date`, `pwd_change_code`, `einstellungen`, `datum_letzte_benachrichtigung`) VALUES
(47, 'user@example.com', 0, '2016-01-17 18:12:13', 0, '$2y$10$NqowUOiQd3SNm8/zACCaguhyYpMxw8hX9pfxsvIrnXpI3/KHXfP4u', NULL, NULL, NULL, '2016-01-17 18:12:13');

--
-- Daten für Tabelle `bezirksausschuesse`
--

INSERT INTO `bezirksausschuesse` (`ba_nr`, `ris_id`, `name`, `website`, `osm_init_zoom`, `osm_shape`) VALUES
(1, 0, 'BA mit Ausschuss mit Termin', NULL, NULL, NULL);

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

--
-- Daten für Tabelle `gremien`
--

INSERT INTO `gremien` (`id`, `datum_letzte_aenderung`, `ba_nr`, `name`, `kuerzel`, `gremientyp`, `referat`) VALUES
(1, '2016-01-31 16:25:43', 1, 'Ausschuss mit Terminen', '', '', '');

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

--
-- Daten für Tabelle `termine`
--

INSERT INTO `termine` (`id`, `typ`, `datum_letzte_aenderung`, `termin_reihe`, `gremium_id`, `ba_nr`, `termin`, `termin_prev_id`, `termin_next_id`, `sitzungsort`, `referat`, `referent`, `vorsitz`, `wahlperiode`, `status`, `sitzungsstand`, `abgesetzt`) VALUES
(1, 0, '2016-01-31 16:27:28', 0, 1, NULL, '2016-01-01 08:00:00', 3, 2, 'Raum für einen Termin', '', '', '', '', '', '', 0),
(2, 0, '2016-01-31 16:27:28', 0, 1, NULL, '2016-02-01 08:00:00', NULL, NULL, 'Raum für einen Termin', '', '', '', '', '', '', 0),
(3, 0, '2016-01-31 16:27:28', 0, 1, NULL, '2015-12-01 08:00:00', NULL, NULL, 'Raum für einen Termin', '', '', '', '', '', '', 0);

--
-- Daten für Tabelle `vorgaenge`
--

INSERT INTO `vorgaenge` (`id`, `typ`, `betreff`) VALUES
(1, NULL, NULL);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
