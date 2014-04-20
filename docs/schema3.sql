SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `bezirksausschuesse`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bezirksausschuesse` (
  `ba_nr` SMALLINT(6) NOT NULL,
  `name` VARCHAR(100) NULL DEFAULT NULL,
  `website` VARCHAR(200) NULL DEFAULT NULL,
  `osm_init_zoom` TINYINT(4) NULL DEFAULT NULL,
  `osm_shape` BLOB NULL DEFAULT NULL,
  PRIMARY KEY (`ba_nr`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege` (
  `id` INT(11) NOT NULL,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_vorlage_geheim','bv_empfehlung') NOT NULL,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL COMMENT '0=Stadtrat',
  `gestellt_am` DATE NULL DEFAULT NULL,
  `gestellt_von` TEXT NOT NULL,
  `erledigt_am` DATE NULL,
  `antrags_nr` VARCHAR(20) NOT NULL,
  `bearbeitungsfrist` DATE NULL DEFAULT NULL,
  `registriert_am` DATE NULL DEFAULT NULL,
  `referat` VARCHAR(500) NOT NULL,
  `referent` VARCHAR(200) NOT NULL,
  `wahlperiode` VARCHAR(50) NOT NULL,
  `antrag_typ` VARCHAR(50) NOT NULL,
  `betreff` TEXT NOT NULL,
  `kurzinfo` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `bearbeitung` VARCHAR(100) NOT NULL,
  `fristverlaengerung` DATE NULL DEFAULT NULL,
  `initiatorInnen` TEXT NOT NULL,
  `initiative_to_aufgenommen` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `registriert_am` (`registriert_am` ASC),
  INDEX `datum_letzte_aenderung` (`datum_letzte_aenderung` ASC),
  INDEX `fk_antraege_bezirksausschuesse1_idx` (`ba_nr` ASC),
  INDEX `ba_datum` (`ba_nr` ASC, `datum_letzte_aenderung` ASC),
  CONSTRAINT `fk_antraege_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `gremien`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `gremien` (
  `id` INT(11) NOT NULL,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `kuerzel` VARCHAR(20) NOT NULL,
  `gremientyp` VARCHAR(100) NOT NULL,
  `referat` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_gremien_bezirksausschuesse1_idx` (`ba_nr` ASC),
  CONSTRAINT `fk_gremien_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `termine`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `termine` (
  `id` INT(11) NOT NULL,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `termin_reihe` INT(11) NOT NULL DEFAULT '0',
  `gremium_id` INT(11) NULL DEFAULT NULL,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL,
  `termin` TIMESTAMP NULL DEFAULT NULL,
  `termin_prev_id` INT(11) NULL DEFAULT NULL,
  `termin_next_id` INT(11) NULL DEFAULT NULL,
  `sitzungsort` TEXT NOT NULL,
  `referat` VARCHAR(200) NOT NULL,
  `referent` VARCHAR(200) NOT NULL,
  `vorsitz` VARCHAR(200) NOT NULL,
  `wahlperiode` VARCHAR(20) NOT NULL,
  `status` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `termin` (`termin` ASC),
  INDEX `termin_reihe` (`termin_reihe` ASC),
  INDEX `fk_termine_gremien1_idx` (`gremium_id` ASC),
  CONSTRAINT `fk_termine_gremien1`
    FOREIGN KEY (`gremium_id`)
    REFERENCES `gremien` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_ergebnisse`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_ergebnisse` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `antrag_id` INT(11) NULL DEFAULT NULL,
  `gremium_name` VARCHAR(100) NOT NULL,
  `gremium_id` INT(11) NULL DEFAULT NULL,
  `sitzungstermin_id` INT(11) NOT NULL,
  `sitzungstermin_datum` DATE NOT NULL,
  `beschluss_text` VARCHAR(500) NOT NULL,
  `entscheidung` TEXT NULL DEFAULT NULL,
  `top_nr` VARCHAR(45) NULL DEFAULT NULL,
  `top_betreff` TEXT NULL DEFAULT NULL,
  `status` VARCHAR(200) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `ix_sitzung_antrag` (`antrag_id` ASC, `sitzungstermin_id` ASC),
  INDEX `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id` ASC),
  INDEX `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id` ASC),
  INDEX `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id` ASC),
  CONSTRAINT `fk_antraege_ergebnisse_antraege1`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_gremien1`
    FOREIGN KEY (`gremium_id`)
    REFERENCES `gremien` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_termine1`
    FOREIGN KEY (`sitzungstermin_id`)
    REFERENCES `termine` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 20406
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_dokumente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_dokumente` (
  `id` INT(11) NOT NULL,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_termin','ba_termin','stadtrat_beschluss') NULL DEFAULT NULL,
  `antrag_id` INT(11) NULL DEFAULT NULL,
  `termin_id` INT(11) NULL DEFAULT NULL,
  `ergebnis_id` INT(11) NULL DEFAULT NULL,
  `url` VARCHAR(500) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text_ocr_raw` MEDIUMTEXT NULL DEFAULT NULL,
  `text_ocr_corrected` MEDIUMTEXT NULL DEFAULT NULL,
  `text_ocr_garbage_seiten` TEXT NULL DEFAULT NULL,
  `text_pdf` MEDIUMTEXT NULL DEFAULT NULL,
  `seiten_anzahl` MEDIUMINT(9) NULL DEFAULT NULL,
  `ocr_von` ENUM('', 'tesseract', 'omnipage') NULL,
  PRIMARY KEY (`id`),
  INDEX `antrag_id` (`antrag_id` ASC),
  INDEX `typ` (`typ` ASC),
  INDEX `fk_antraege_dokumente_termine1_idx` (`termin_id` ASC),
  INDEX `fk_antraege_dokumente_antraege_ergebnisse1_idx` (`ergebnis_id` ASC),
  CONSTRAINT `fk_antraege_dokumente_antraege1`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_dokumente_antraege_ergebnisse1`
    FOREIGN KEY (`ergebnis_id`)
    REFERENCES `antraege_ergebnisse` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_dokumente_termine1`
    FOREIGN KEY (`termin_id`)
    REFERENCES `termine` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_history` (
  `id` MEDIUMINT(9) NOT NULL,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative') NOT NULL,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL COMMENT '0=Stadtrat',
  `gestellt_am` DATE NULL DEFAULT NULL,
  `gestellt_von` TEXT NOT NULL,
  `erledigt_am` DATE NULL,
  `antrags_nr` VARCHAR(20) NOT NULL,
  `bearbeitungsfrist` DATE NULL DEFAULT NULL,
  `registriert_am` DATE NULL DEFAULT NULL,
  `referat` VARCHAR(500) NOT NULL,
  `referent` VARCHAR(200) NOT NULL,
  `wahlperiode` VARCHAR(50) NOT NULL,
  `antrag_typ` VARCHAR(50) NOT NULL,
  `betreff` TEXT NOT NULL,
  `kurzinfo` TEXT NOT NULL,
  `status` VARCHAR(50) NOT NULL,
  `bearbeitung` VARCHAR(100) NOT NULL,
  `fristverlaengerung` DATE NULL DEFAULT NULL,
  `initiatoren` TEXT NOT NULL,
  `initiative_to_aufgenommen` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`),
  INDEX `registriert_am` (`registriert_am` ASC),
  INDEX `datum_letzte_aenderung` (`datum_letzte_aenderung` ASC),
  INDEX `fk_antraege_history_bezirksausschuesse1_idx` (`ba_nr` ASC),
  CONSTRAINT `bezirksausschuss`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_vorlagen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_vorlagen` (
  `antrag1` INT(11) NOT NULL,
  `antrag2` INT(11) NOT NULL,
  `datum` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`antrag1`, `antrag2`),
  INDEX `fk_antraege_links_antraege1_idx` (`antrag1` ASC),
  INDEX `fk_antraege_links_antraege2_idx` (`antrag2` ASC),
  CONSTRAINT `fk_antraege_vorlagen_antraege1`
    FOREIGN KEY (`antrag2`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_vorlagen_antraege2`
    FOREIGN KEY (`antrag1`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `orte_geo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `orte_geo` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ort` VARCHAR(100) NOT NULL,
  `lat` DOUBLE NOT NULL,
  `lon` DOUBLE NOT NULL,
  `source` ENUM('auto','manual') NOT NULL,
  `ba_nr` TINYINT NULL DEFAULT NULL,
  `to_hide` TINYINT(4) NOT NULL,
  `to_hide_kommentar` VARCHAR(200) NOT NULL,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `ort` (`ort` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 38671
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_orte`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_orte` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `antrag_id` INT(11) NULL DEFAULT NULL,
  `termin_id` INT(11) NULL DEFAULT NULL,
  `dokument_id` INT(11) NOT NULL,
  `ort_name` VARCHAR(100) NOT NULL,
  `ort_id` SMALLINT(5) UNSIGNED NOT NULL,
  `source` ENUM('text_parse','manual') NOT NULL,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `dokument` (`dokument_id` ASC, `ort_id` ASC),
  INDEX `antrag` (`antrag_id` ASC),
  INDEX `ort_id` (`ort_id` ASC),
  INDEX `fk_antraege_orte_antraege_dokumente1_idx` (`dokument_id` ASC),
  INDEX `fk_antraege_orte_termine1_idx` (`termin_id` ASC),
  CONSTRAINT `fk_antraege_orte_antraege1`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_antraege_dokumente1`
    FOREIGN KEY (`dokument_id`)
    REFERENCES `antraege_dokumente` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_orte_geo1`
    FOREIGN KEY (`ort_id`)
    REFERENCES `orte_geo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_termine1`
    FOREIGN KEY (`termin_id`)
    REFERENCES `termine` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 743699
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `fraktionen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `fraktionen` (
  `id` INT(11) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_fraktionen_bezirksausschuesse1_idx` (`ba_nr` ASC),
  CONSTRAINT `fk_fraktionen_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `stadtraetInnen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `stadtraetInnen` (
  `id` INT(11) NOT NULL,
  `gewaehlt_am` DATE NULL DEFAULT NULL,
  `bio` TEXT NOT NULL,
  `web` VARCHAR(250) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `twitter` VARCHAR(45) NULL,
  `facebook` VARCHAR(200) NULL,
  `abgeordnetenwatch` VARCHAR(200) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `personen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `personen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name_normalized` VARCHAR(50) NOT NULL,
  `typ` ENUM('person','fraktion','sonstiges') NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `ris_stadtraetIn` INT(11) NULL DEFAULT NULL,
  `ris_fraktion` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_normalized` (`name_normalized` ASC),
  INDEX `fk_personen_stadtraete1_idx` (`ris_stadtraetIn` ASC),
  INDEX `fk_personen_fraktionen1_idx` (`ris_fraktion` ASC),
  CONSTRAINT `fk_personen_fraktionen1`
    FOREIGN KEY (`ris_fraktion`)
    REFERENCES `fraktionen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_personen_stadtraete1`
    FOREIGN KEY (`ris_stadtraetIn`)
    REFERENCES `stadtraetInnen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 267
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_personen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_personen` (
  `antrag_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  `typ` ENUM('gestellt_von','initiator') NOT NULL DEFAULT 'gestellt_von',
  PRIMARY KEY (`antrag_id`, `person_id`),
  INDEX `person` (`person_id` ASC),
  INDEX `fk_antraege_personen_antraege1_idx` (`antrag_id` ASC),
  CONSTRAINT `fk_antraege_personen_antraege1`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_personen_ris_personen1`
    FOREIGN KEY (`person_id`)
    REFERENCES `personen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `gremien_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `gremien_history` (
  `id` INT(11) NOT NULL,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `kuerzel` VARCHAR(20) NOT NULL,
  `gremientyp` VARCHAR(100) NOT NULL,
  `referat` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`),
  INDEX `fk_gremien_bezirksausschuesse1_idx` (`ba_nr` ASC),
  CONSTRAINT `fk_gremien_bezirksausschuesse10`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `ris_aenderungen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ris_aenderungen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ris_id` INT(11) NOT NULL,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','ba_termin','stadtrat_termin','rathausumschau','stadtraetIn','ba_mitglied','stadtrat_gremium','ba_gremium','stadtrat_ergebnis','stadtrat_fraktion') NOT NULL,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aenderungen` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `datum` (`datum` ASC),
  INDEX `antrag_id` (`ris_id` ASC),
  INDEX `ba_nr` (`ba_nr` ASC, `datum` ASC),
  INDEX `fk_ris_aenderungen_bezirksausschuesse1_idx` (`ba_nr` ASC),
  CONSTRAINT `fk_ris_aenderungen_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 139928
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `stadtraetInnen_fraktionen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `stadtraetInnen_fraktionen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `stadtraetIn_id` INT(11) NOT NULL,
  `fraktion_id` INT(11) NOT NULL,
  `wahlperiode` VARCHAR(30) NOT NULL,
  `datum_von` DATE NULL DEFAULT NULL,
  `datum_bis` DATE NULL DEFAULT NULL,
  `mitgliedschaft` TEXT NOT NULL,
  `funktion` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ris_stadtraete_fraktionen_ris_personen1_idx` (`stadtraetIn_id` ASC),
  INDEX `fk_stadtraete_fraktionen_fraktionen1_idx` (`fraktion_id` ASC),
  INDEX `uq` (`stadtraetIn_id` ASC, `fraktion_id` ASC, `wahlperiode` ASC),
  CONSTRAINT `fk_stadtraete_fraktionen_fraktionen2`
    FOREIGN KEY (`fraktion_id`)
    REFERENCES `fraktionen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_stadtraetInnen_fraktionen`
    FOREIGN KEY (`stadtraetIn_id`)
    REFERENCES `stadtraetInnen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 322
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `strassen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `strassen` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `plz` VARCHAR(10) NOT NULL,
  `osm_ref` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 5722
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `termine_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `termine_history` (
  `id` INT(11) NOT NULL,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `termin_reihe` INT(11) NOT NULL DEFAULT '0',
  `gremium_id` INT(11) NOT NULL,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL,
  `termin` TIMESTAMP NULL DEFAULT NULL,
  `termin_prev_id` INT(11) NULL DEFAULT NULL,
  `termin_next_id` INT(11) NULL DEFAULT NULL,
  `sitzungsort` TEXT NOT NULL,
  `referat` VARCHAR(200) NOT NULL,
  `referent` VARCHAR(200) NOT NULL,
  `vorsitz` VARCHAR(200) NOT NULL,
  `wahlperiode` VARCHAR(20) NOT NULL,
  `status` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`),
  INDEX `termin` (`termin` ASC),
  INDEX `termin_reihe` (`termin_reihe` ASC),
  INDEX `fk_termine_history_bezirksausschuesse1_idx` (`ba_nr` ASC),
  CONSTRAINT `fk_termine_history_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr`)
    REFERENCES `bezirksausschuesse` (`ba_nr`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_stadtraetInnen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_stadtraetInnen` (
  `antrag_id` INT(11) NOT NULL,
  `stadtraetIn_id` INT(11) NOT NULL,
  `gefunden_am` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`antrag_id`, `stadtraetIn_id`),
  INDEX `fk_table1_antraege1_idx` (`antrag_id` ASC),
  INDEX `fk_antraege_stadtraetInnen_stadtraetInnen1_idx` (`stadtraetIn_id` ASC),
  CONSTRAINT `fk_antraege_stadtraetInnen_antraege1`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_stadtraetInnen_stadtraetInnen1`
    FOREIGN KEY (`stadtraetIn_id`)
    REFERENCES `stadtraetInnen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_ergebnisse_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_ergebnisse_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `antrag_id` INT(11) NOT NULL,
  `gremium_name` VARCHAR(100) NOT NULL,
  `gremium_id` INT(11) NULL DEFAULT NULL,
  `sitzungstermin_id` INT(11) NOT NULL,
  `sitzungstermin_datum` DATE NOT NULL,
  `beschluss_text` VARCHAR(500) NOT NULL,
  `top_nr` VARCHAR(45) NULL DEFAULT NULL,
  `top_betreff` TEXT NULL DEFAULT NULL,
  `status` VARCHAR(200) NULL DEFAULT NULL,
  `entscheidung` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`),
  INDEX `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id` ASC),
  INDEX `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id` ASC),
  INDEX `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id` ASC),
  CONSTRAINT `fk_antraege_ergebnisse_antraege10`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_gremien10`
    FOREIGN KEY (`gremium_id`)
    REFERENCES `gremien` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_termine10`
    FOREIGN KEY (`sitzungstermin_id`)
    REFERENCES `termine` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `benutzerInnen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `benutzerInnen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(45) NULL DEFAULT NULL,
  `email_bestaetigt` TINYINT(4) NULL DEFAULT '0',
  `datum_angelegt` DATETIME NULL DEFAULT NULL,
  `pwd_enc` VARCHAR(100) NULL DEFAULT NULL,
  `pwd_change_date` TIMESTAMP NULL DEFAULT NULL,
  `pwd_change_code` VARCHAR(100) NULL DEFAULT NULL,
  `einstellungen` BLOB NULL DEFAULT NULL,
  `datum_letzte_benachrichtigung` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `antraege_abos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `antraege_abos` (
  `antrag_id` INT(11) NOT NULL,
  `benutzerIn_id` INT(11) NOT NULL,
  PRIMARY KEY (`antrag_id`, `benutzerIn_id`),
  INDEX `fk_antraege_has_benutzerInnen_benutzerInnen1_idx` (`benutzerIn_id` ASC),
  INDEX `fk_antraege_has_benutzerInnen_antraege1_idx` (`antrag_id` ASC),
  CONSTRAINT `fk_antraege_has_benutzerInnen_antraege1`
    FOREIGN KEY (`antrag_id`)
    REFERENCES `antraege` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_has_benutzerInnen_benutzerInnen1`
    FOREIGN KEY (`benutzerIn_id`)
    REFERENCES `benutzerInnen` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `metadaten`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `metadaten` (
  `meta_key` VARCHAR(25) NOT NULL,
  `meta_val` BLOB NULL,
  PRIMARY KEY (`meta_key`))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
