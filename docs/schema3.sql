-- --------------------------------------------------------

--
-- Table structure for table `antraege`
--

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
  `initiative_to_aufgenommen` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `antraege_history`
--

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
  `initiative_to_aufgenommen` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `antraege_orte`
--

CREATE TABLE `antraege_orte` (
  `id` int(11) NOT NULL,
  `antrag_id` int(11) DEFAULT NULL,
  `termin_id` int(11) DEFAULT NULL,
  `dokument_id` int(11) NOT NULL,
  `rathausumschau_id` mediumint(11) DEFAULT NULL,
  `ort_name` varchar(100) NOT NULL,
  `ort_id` smallint(5) unsigned NOT NULL,
  `source` enum('text_parse','manual') NOT NULL,
  `datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `antraege_personen`
--

CREATE TABLE `antraege_personen` (
  `antrag_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `typ` enum('gestellt_von','initiator') NOT NULL DEFAULT 'gestellt_von'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `antraege_stadtraetInnen`
--

CREATE TABLE `antraege_stadtraetInnen` (
  `antrag_id` int(11) NOT NULL,
  `stadtraetIn_id` int(11) NOT NULL,
  `gefunden_am` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `antraege_tags`
--

CREATE TABLE `antraege_tags` (
  `antrag_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `zugeordnet_datum` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `zugeordnet_benutzerIn_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `antraege_vorlagen`
--

CREATE TABLE `antraege_vorlagen` (
  `antrag1` int(11) NOT NULL,
  `antrag2` int(11) NOT NULL,
  `datum` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `benutzerInnen`
--

CREATE TABLE `benutzerInnen` (
  `id` int(11) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `email_bestaetigt` tinyint(4) DEFAULT '0',
  `datum_angelegt` datetime DEFAULT NULL,
  `pwd_enc` varchar(100) DEFAULT NULL,
  `pwd_change_date` timestamp NULL DEFAULT NULL,
  `pwd_change_code` varchar(100) DEFAULT NULL,
  `einstellungen` blob,
  `datum_letzte_benachrichtigung` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `benutzerInnen_vorgaenge_abos`
--

CREATE TABLE `benutzerInnen_vorgaenge_abos` (
  `benutzerInnen_id` int(11) NOT NULL,
  `vorgaenge_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bezirksausschuesse`
--

CREATE TABLE `bezirksausschuesse` (
  `ba_nr` smallint(6) NOT NULL,
  `ris_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `osm_init_zoom` tinyint(4) DEFAULT NULL,
  `osm_shape` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bezirksausschuss_budget`
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
-- Table structure for table `dokumente`
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
  `name` varchar(300) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `fraktionen`
--

CREATE TABLE `fraktionen` (
  `id` int(11) NOT NULL,
  `name` varchar(70) NOT NULL,
  `ba_nr` smallint(6) DEFAULT NULL,
  `website` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `gremien`
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

-- --------------------------------------------------------

--
-- Table structure for table `gremien_history`
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
-- Table structure for table `metadaten`
--

CREATE TABLE `metadaten` (
  `meta_key` varchar(25) NOT NULL,
  `meta_val` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `orte_geo`
--

CREATE TABLE `orte_geo` (
  `id` smallint(5) unsigned NOT NULL,
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
-- Table structure for table `personen`
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
-- Table structure for table `rathausumschau`
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
-- Table structure for table `rechtsdokument`
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
-- Table structure for table `referate`
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
-- Table structure for table `ris_aenderungen`
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
-- Table structure for table `stadtraetInnen`
--

CREATE TABLE `stadtraetInnen` (
  `id` int(11) NOT NULL,
  `benutzerIn_id` int(11) DEFAULT NULL,
  `referentIn` tinyint(4) NOT NULL DEFAULT '0',
  `gewaehlt_am` date DEFAULT NULL,
  `bio` mediumtext NOT NULL,
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
-- Table structure for table `stadtraetInnen_fraktionen`
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
-- Table structure for table `stadtraetInnen_gremien`
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
-- Table structure for table `stadtraetInnen_referate`
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
-- Table structure for table `strassen`
--

CREATE TABLE `strassen` (
  `id` smallint(5) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `plz` varchar(10) NOT NULL,
  `osm_ref` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tagesordnungspunkte`
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
-- Table structure for table `tagesordnungspunkte_history`
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
-- Table structure for table `tags`
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
-- Table structure for table `termine`
--

CREATE TABLE `termine` (
  `id` int(11) NOT NULL,
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
  `abgesetzt` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `termine_history`
--

CREATE TABLE `termine_history` (
  `id` int(11) NOT NULL,
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
-- Table structure for table `texte`
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
-- Table structure for table `vorgaenge`
--

CREATE TABLE `vorgaenge` (
  `id` int(11) NOT NULL,
  `typ` tinyint(4) DEFAULT NULL,
  `betreff` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antraege`
--
ALTER TABLE `antraege`
ADD PRIMARY KEY (`id`), ADD KEY `registriert_am` (`registriert_am`), ADD KEY `datum_letzte_aenderung` (`datum_letzte_aenderung`), ADD KEY `fk_antraege_bezirksausschuesse1_idx` (`ba_nr`), ADD KEY `ba_datum` (`ba_nr`,`datum_letzte_aenderung`), ADD KEY `antrags_nr` (`antrags_nr`), ADD KEY `fk_antraege_vorgaenge1_idx` (`vorgang_id`), ADD KEY `fk_antraege_referate1_idx` (`referat_id`);

--
-- Indexes for table `antraege_history`
--
ALTER TABLE `antraege_history`
ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`), ADD KEY `registriert_am` (`registriert_am`), ADD KEY `datum_letzte_aenderung` (`datum_letzte_aenderung`), ADD KEY `fk_antraege_history_bezirksausschuesse1_idx` (`ba_nr`), ADD KEY `fk_antraege_history_vorgaenge1_idx` (`vorgang_id`), ADD KEY `fk_antraege_history_referate1_idx` (`referat_id`);

--
-- Indexes for table `antraege_orte`
--
ALTER TABLE `antraege_orte`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `dokument` (`dokument_id`,`ort_id`), ADD KEY `antrag` (`antrag_id`), ADD KEY `ort_id` (`ort_id`), ADD KEY `fk_antraege_orte_antraege_dokumente1_idx` (`dokument_id`), ADD KEY `fk_antraege_orte_termine1_idx` (`termin_id`), ADD KEY `rathausumschau_id` (`rathausumschau_id`);

--
-- Indexes for table `antraege_personen`
--
ALTER TABLE `antraege_personen`
ADD PRIMARY KEY (`antrag_id`,`person_id`), ADD KEY `person` (`person_id`), ADD KEY `fk_antraege_personen_antraege1_idx` (`antrag_id`);

--
-- Indexes for table `antraege_stadtraetInnen`
--
ALTER TABLE `antraege_stadtraetInnen`
ADD PRIMARY KEY (`antrag_id`,`stadtraetIn_id`), ADD KEY `fk_table1_antraege1_idx` (`antrag_id`), ADD KEY `fk_antraege_stadtraetInnen_stadtraetInnen1_idx` (`stadtraetIn_id`);

--
-- Indexes for table `antraege_tags`
--
ALTER TABLE `antraege_tags`
ADD PRIMARY KEY (`antrag_id`,`tag_id`), ADD KEY `fk_antraege_tags_tags1_idx` (`tag_id`);

--
-- Indexes for table `antraege_vorlagen`
--
ALTER TABLE `antraege_vorlagen`
ADD PRIMARY KEY (`antrag1`,`antrag2`), ADD KEY `fk_antraege_links_antraege1_idx` (`antrag1`), ADD KEY `fk_antraege_links_antraege2_idx` (`antrag2`);

--
-- Indexes for table `benutzerInnen`
--
ALTER TABLE `benutzerInnen`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `benutzerInnen_vorgaenge_abos`
--
ALTER TABLE `benutzerInnen_vorgaenge_abos`
ADD PRIMARY KEY (`benutzerInnen_id`,`vorgaenge_id`), ADD KEY `fk_benutzerInnen_has_vorgaenge_vorgaenge1_idx` (`vorgaenge_id`), ADD KEY `fk_benutzerInnen_has_vorgaenge_benutzerInnen1_idx` (`benutzerInnen_id`);

--
-- Indexes for table `bezirksausschuesse`
--
ALTER TABLE `bezirksausschuesse`
ADD PRIMARY KEY (`ba_nr`);

--
-- Indexes for table `bezirksausschuss_budget`
--
ALTER TABLE `bezirksausschuss_budget`
ADD PRIMARY KEY (`ba_nr`,`jahr`);

--
-- Indexes for table `dokumente`
--
ALTER TABLE `dokumente`
ADD PRIMARY KEY (`id`), ADD KEY `antrag_id` (`antrag_id`), ADD KEY `typ` (`typ`), ADD KEY `fk_antraege_dokumente_termine1_idx` (`termin_id`), ADD KEY `fk_antraege_dokumente_antraege_ergebnisse1_idx` (`tagesordnungspunkt_id`), ADD KEY `datum` (`datum`), ADD KEY `fk_antraege_dokumente_vorgaenge1_idx` (`vorgang_id`), ADD KEY `highlight_dokument` (`highlight`), ADD KEY `url` (`url`(60)), ADD KEY `rathausumschau_id` (`rathausumschau_id`);

--
-- Indexes for table `fraktionen`
--
ALTER TABLE `fraktionen`
ADD PRIMARY KEY (`id`), ADD KEY `fk_fraktionen_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indexes for table `gremien`
--
ALTER TABLE `gremien`
ADD PRIMARY KEY (`id`), ADD KEY `fk_gremien_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indexes for table `gremien_history`
--
ALTER TABLE `gremien_history`
ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`), ADD KEY `fk_gremien_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indexes for table `metadaten`
--
ALTER TABLE `metadaten`
ADD PRIMARY KEY (`meta_key`);

--
-- Indexes for table `orte_geo`
--
ALTER TABLE `orte_geo`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `ort` (`ort`);

--
-- Indexes for table `personen`
--
ALTER TABLE `personen`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name_normalized` (`name_normalized`), ADD KEY `fk_personen_stadtraete1_idx` (`ris_stadtraetIn`), ADD KEY `fk_personen_fraktionen1_idx` (`ris_fraktion`);

--
-- Indexes for table `rathausumschau`
--
ALTER TABLE `rathausumschau`
ADD PRIMARY KEY (`id`), ADD KEY `datum` (`datum`);

--
-- Indexes for table `rechtsdokument`
--
ALTER TABLE `rechtsdokument`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `nr_UNIQUE` (`nr`);

--
-- Indexes for table `referate`
--
ALTER TABLE `referate`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `urlpart_UNIQUE` (`urlpart`);

--
-- Indexes for table `ris_aenderungen`
--
ALTER TABLE `ris_aenderungen`
ADD PRIMARY KEY (`id`), ADD KEY `datum` (`datum`), ADD KEY `antrag_id` (`ris_id`), ADD KEY `ba_nr` (`ba_nr`,`datum`), ADD KEY `fk_ris_aenderungen_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indexes for table `stadtraetInnen`
--
ALTER TABLE `stadtraetInnen`
ADD PRIMARY KEY (`id`), ADD KEY `id` (`benutzerIn_id`);

--
-- Indexes for table `stadtraetInnen_fraktionen`
--
ALTER TABLE `stadtraetInnen_fraktionen`
ADD PRIMARY KEY (`id`), ADD KEY `fk_ris_stadtraete_fraktionen_ris_personen1_idx` (`stadtraetIn_id`), ADD KEY `fk_stadtraete_fraktionen_fraktionen1_idx` (`fraktion_id`), ADD KEY `uq` (`stadtraetIn_id`,`fraktion_id`,`wahlperiode`);

--
-- Indexes for table `stadtraetInnen_gremien`
--
ALTER TABLE `stadtraetInnen_gremien`
ADD PRIMARY KEY (`stadtraetIn_id`,`gremium_id`,`datum_von`), ADD KEY `fk_stadtraetIn_gremien_mitgliedschaft_stadtraetInnen1_idx` (`stadtraetIn_id`), ADD KEY `fk_stadtraetIn_gremien_mitgliedschaft_gremien1_idx` (`gremium_id`);

--
-- Indexes for table `stadtraetInnen_referate`
--
ALTER TABLE `stadtraetInnen_referate`
ADD PRIMARY KEY (`id`), ADD KEY `stadtraetIn_id` (`stadtraetIn_id`), ADD KEY `fraktion_id` (`referat_id`);

--
-- Indexes for table `strassen`
--
ALTER TABLE `strassen`
ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tagesordnungspunkte`
--
ALTER TABLE `tagesordnungspunkte`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `ix_sitzung_antrag` (`antrag_id`,`sitzungstermin_id`), ADD KEY `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id`), ADD KEY `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id`), ADD KEY `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id`), ADD KEY `fk_antraege_ergebnisse_vorgaenge1_idx` (`vorgang_id`);

--
-- Indexes for table `tagesordnungspunkte_history`
--
ALTER TABLE `tagesordnungspunkte_history`
ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`), ADD KEY `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id`), ADD KEY `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id`), ADD KEY `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id`), ADD KEY `fk_antraege_ergebnisse_history_vorgaenge1_idx` (`vorgang_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name_UNIQUE` (`name`);

--
-- Indexes for table `termine`
--
ALTER TABLE `termine`
ADD PRIMARY KEY (`id`), ADD KEY `termin` (`termin`), ADD KEY `termin_reihe` (`termin_reihe`), ADD KEY `fk_termine_gremien1_idx` (`gremium_id`);

--
-- Indexes for table `termine_history`
--
ALTER TABLE `termine_history`
ADD PRIMARY KEY (`id`,`datum_letzte_aenderung`), ADD KEY `termin` (`termin`), ADD KEY `termin_reihe` (`termin_reihe`), ADD KEY `fk_termine_history_bezirksausschuesse1_idx` (`ba_nr`);

--
-- Indexes for table `texte`
--
ALTER TABLE `texte`
ADD PRIMARY KEY (`id`), ADD KEY `pos` (`typ`,`pos`), ADD KEY `fk_texte_benutzerInnen1_idx` (`edit_benutzerIn_id`);

--
-- Indexes for table `vorgaenge`
--
ALTER TABLE `vorgaenge`
ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antraege_orte`
--
ALTER TABLE `antraege_orte`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `benutzerInnen`
--
ALTER TABLE `benutzerInnen`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `orte_geo`
--
ALTER TABLE `orte_geo`
MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `personen`
--
ALTER TABLE `personen`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rathausumschau`
--
ALTER TABLE `rathausumschau`
MODIFY `id` mediumint(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `rechtsdokument`
--
ALTER TABLE `rechtsdokument`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `referate`
--
ALTER TABLE `referate`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ris_aenderungen`
--
ALTER TABLE `ris_aenderungen`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `stadtraetInnen_fraktionen`
--
ALTER TABLE `stadtraetInnen_fraktionen`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `stadtraetInnen_referate`
--
ALTER TABLE `stadtraetInnen_referate`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `strassen`
--
ALTER TABLE `strassen`
MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tagesordnungspunkte`
--
ALTER TABLE `tagesordnungspunkte`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tagesordnungspunkte_history`
--
ALTER TABLE `tagesordnungspunkte_history`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `texte`
--
ALTER TABLE `texte`
MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `vorgaenge`
--
ALTER TABLE `vorgaenge`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `antraege`
--
ALTER TABLE `antraege`
ADD CONSTRAINT `fk_antraege_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_referate1` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `antraege_history`
--
ALTER TABLE `antraege_history`
ADD CONSTRAINT `bezirksausschuss` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_history_referate1` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_history_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `antraege_orte`
--
ALTER TABLE `antraege_orte`
ADD CONSTRAINT `antraege_orte_ibfk_1` FOREIGN KEY (`rathausumschau_id`) REFERENCES `rathausumschau` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_orte_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_orte_antraege_dokumente1` FOREIGN KEY (`dokument_id`) REFERENCES `dokumente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_orte_orte_geo1` FOREIGN KEY (`ort_id`) REFERENCES `orte_geo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_orte_termine1` FOREIGN KEY (`termin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `antraege_personen`
--
ALTER TABLE `antraege_personen`
ADD CONSTRAINT `fk_antraege_personen_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_personen_ris_personen1` FOREIGN KEY (`person_id`) REFERENCES `personen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `antraege_stadtraetInnen`
--
ALTER TABLE `antraege_stadtraetInnen`
ADD CONSTRAINT `fk_antraege_stadtraetInnen_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_stadtraetInnen_stadtraetInnen1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `antraege_tags`
--
ALTER TABLE `antraege_tags`
ADD CONSTRAINT `fk_antraege_tags_tags1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `antraege_vorlagen`
--
ALTER TABLE `antraege_vorlagen`
ADD CONSTRAINT `fk_antraege_vorlagen_antraege1` FOREIGN KEY (`antrag2`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_vorlagen_antraege2` FOREIGN KEY (`antrag1`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `benutzerInnen_vorgaenge_abos`
--
ALTER TABLE `benutzerInnen_vorgaenge_abos`
ADD CONSTRAINT `fk_benutzerInnen_has_vorgaenge_benutzerInnen1` FOREIGN KEY (`benutzerInnen_id`) REFERENCES `benutzerInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_benutzerInnen_has_vorgaenge_vorgaenge1` FOREIGN KEY (`vorgaenge_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bezirksausschuss_budget`
--
ALTER TABLE `bezirksausschuss_budget`
ADD CONSTRAINT `fk_bezirksausschuss_budget_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `dokumente`
--
ALTER TABLE `dokumente`
ADD CONSTRAINT `dokumente_ibfk_1` FOREIGN KEY (`rathausumschau_id`) REFERENCES `rathausumschau` (`id`),
ADD CONSTRAINT `fk_antraege_dokumente_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_dokumente_termine1` FOREIGN KEY (`termin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_dokumente_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `fraktionen`
--
ALTER TABLE `fraktionen`
ADD CONSTRAINT `fk_fraktionen_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `gremien`
--
ALTER TABLE `gremien`
ADD CONSTRAINT `fk_gremien_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `gremien_history`
--
ALTER TABLE `gremien_history`
ADD CONSTRAINT `fk_gremien_bezirksausschuesse10` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `personen`
--
ALTER TABLE `personen`
ADD CONSTRAINT `fk_personen_fraktionen1` FOREIGN KEY (`ris_fraktion`) REFERENCES `fraktionen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_personen_stadtraete1` FOREIGN KEY (`ris_stadtraetIn`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `ris_aenderungen`
--
ALTER TABLE `ris_aenderungen`
ADD CONSTRAINT `fk_ris_aenderungen_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `stadtraetInnen`
--
ALTER TABLE `stadtraetInnen`
ADD CONSTRAINT `fr_stadtraetIn_benutzerIn` FOREIGN KEY (`benutzerIn_id`) REFERENCES `ris_aenderungen` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `stadtraetInnen_fraktionen`
--
ALTER TABLE `stadtraetInnen_fraktionen`
ADD CONSTRAINT `fk_stadtraetInnen_fraktionen` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_stadtraete_fraktionen_fraktionen2` FOREIGN KEY (`fraktion_id`) REFERENCES `fraktionen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `stadtraetInnen_gremien`
--
ALTER TABLE `stadtraetInnen_gremien`
ADD CONSTRAINT `fk_stadtraetIn_gremien_mitgliedschaft_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_stadtraetIn_gremien_mitgliedschaft_stadtraetInnen1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `stadtraetInnen_referate`
--
ALTER TABLE `stadtraetInnen_referate`
ADD CONSTRAINT `stadtraetInnen_referate_ibfk_1` FOREIGN KEY (`stadtraetIn_id`) REFERENCES `stadtraetInnen` (`id`),
ADD CONSTRAINT `stadtraetInnen_referate_ibfk_2` FOREIGN KEY (`referat_id`) REFERENCES `referate` (`id`);

--
-- Constraints for table `tagesordnungspunkte`
--
ALTER TABLE `tagesordnungspunkte`
ADD CONSTRAINT `fk_antraege_ergebnisse_antraege1` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_ergebnisse_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_ergebnisse_termine1` FOREIGN KEY (`sitzungstermin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_ergebnisse_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `tagesordnungspunkte_history`
--
ALTER TABLE `tagesordnungspunkte_history`
ADD CONSTRAINT `fk_antraege_ergebnisse_antraege10` FOREIGN KEY (`antrag_id`) REFERENCES `antraege` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_ergebnisse_gremien10` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_ergebnisse_history_vorgaenge1` FOREIGN KEY (`vorgang_id`) REFERENCES `vorgaenge` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_antraege_ergebnisse_termine10` FOREIGN KEY (`sitzungstermin_id`) REFERENCES `termine` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `termine`
--
ALTER TABLE `termine`
ADD CONSTRAINT `fk_termine_gremien1` FOREIGN KEY (`gremium_id`) REFERENCES `gremien` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `termine_history`
--
ALTER TABLE `termine_history`
ADD CONSTRAINT `fk_termine_history_bezirksausschuesse1` FOREIGN KEY (`ba_nr`) REFERENCES `bezirksausschuesse` (`ba_nr`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `texte`
--
ALTER TABLE `texte`
ADD CONSTRAINT `fk_texte_benutzerInnen1` FOREIGN KEY (`edit_benutzerIn_id`) REFERENCES `benutzerInnen` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
