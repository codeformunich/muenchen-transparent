SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `ris2` DEFAULT CHARACTER SET utf8 ;
USE `ris2` ;

-- -----------------------------------------------------
-- Table `ris2`.`bezirksausschuesse`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`bezirksausschuesse` (
  `ba_nr` SMALLINT(6) NOT NULL ,
  `name` VARCHAR(100) NULL DEFAULT NULL ,
  PRIMARY KEY (`ba_nr`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege` (
  `id` INT(11) NOT NULL ,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_vorlage_geheim') NOT NULL ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL COMMENT '0=Stadtrat' ,
  `gestellt_am` DATE NULL DEFAULT NULL ,
  `gestellt_von` TEXT NOT NULL ,
  `antrags_nr` VARCHAR(20) NOT NULL ,
  `bearbeitungsfrist` DATE NULL DEFAULT NULL ,
  `registriert_am` DATE NULL DEFAULT NULL ,
  `referat` VARCHAR(500) NOT NULL ,
  `referent` VARCHAR(200) NOT NULL ,
  `wahlperiode` VARCHAR(50) NOT NULL ,
  `antrag_typ` VARCHAR(50) NOT NULL ,
  `betreff` TEXT NOT NULL ,
  `kurzinfo` TEXT NOT NULL ,
  `status` VARCHAR(50) NOT NULL ,
  `bearbeitung` VARCHAR(100) NOT NULL ,
  `fristverlaengerung` DATE NULL DEFAULT NULL ,
  `initiatoren` TEXT NOT NULL ,
  `initiative_to_aufgenommen` DATE NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `registriert_am` (`registriert_am` ASC) ,
  INDEX `datum_letzte_aenderung` (`datum_letzte_aenderung` ASC) ,
  INDEX `fk_antraege_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `fk_antraege_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`gremien`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`gremien` (
  `id` INT(11) NOT NULL ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `kuerzel` VARCHAR(20) NOT NULL ,
  `gremientyp` VARCHAR(100) NOT NULL ,
  `referat` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_gremien_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `fk_gremien_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`termine`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`termine` (
  `id` INT(11) NOT NULL ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `termin_reihe` INT(11) NOT NULL DEFAULT '0' ,
  `gremium_id` INT(11) NULL DEFAULT NULL ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL ,
  `termin` TIMESTAMP NULL DEFAULT NULL ,
  `termin_prev_id` INT(11) NULL DEFAULT NULL ,
  `termin_next_id` INT(11) NULL DEFAULT NULL ,
  `sitzungsort` TEXT NOT NULL ,
  `referat` VARCHAR(200) NOT NULL ,
  `referent` VARCHAR(200) NOT NULL ,
  `vorsitz` VARCHAR(200) NOT NULL ,
  `wahlperiode` VARCHAR(20) NOT NULL ,
  `status` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `termin` (`termin` ASC) ,
  INDEX `termin_reihe` (`termin_reihe` ASC) ,
  INDEX `fk_termine_gremien1_idx` (`gremium_id` ASC) ,
  CONSTRAINT `fk_termine_gremien1`
    FOREIGN KEY (`gremium_id` )
    REFERENCES `ris2`.`gremien` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_ergebnisse`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_ergebnisse` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `antrag_id` INT(11) NULL DEFAULT NULL ,
  `gremium_name` VARCHAR(100) NOT NULL ,
  `gremium_id` INT(11) NULL ,
  `sitzungstermin_id` INT(11) NOT NULL ,
  `sitzungstermin_datum` DATE NOT NULL ,
  `beschluss_text` VARCHAR(500) NOT NULL ,
  `entscheidung` TEXT NULL ,
  `top_nr` VARCHAR(45) NULL ,
  `top_betreff` TEXT NULL ,
  `status` VARCHAR(200) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id` ASC) ,
  INDEX `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id` ASC) ,
  INDEX `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id` ASC) ,
  UNIQUE INDEX `ix_sitzung_antrag` (`antrag_id` ASC, `sitzungstermin_id` ASC) ,
  CONSTRAINT `fk_antraege_ergebnisse_termine1`
    FOREIGN KEY (`sitzungstermin_id` )
    REFERENCES `ris2`.`termine` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_gremien1`
    FOREIGN KEY (`gremium_id` )
    REFERENCES `ris2`.`gremien` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_antraege1`
    FOREIGN KEY (`antrag_id` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_dokumente`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_dokumente` (
  `id` INT(11) NOT NULL ,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','stadtrat_termin','ba_termin','stadtrat_beschluss') NULL DEFAULT NULL ,
  `antrag_id` INT(11) NULL DEFAULT NULL ,
  `termin_id` INT(11) NULL DEFAULT NULL ,
  `ergebnis_id` INT NULL DEFAULT NULL ,
  `url` VARCHAR(500) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `text_ocr_raw` MEDIUMTEXT NULL DEFAULT NULL ,
  `text_ocr_corrected` MEDIUMTEXT NULL DEFAULT NULL ,
  `text_ocr_garbage_seiten` TEXT NULL DEFAULT NULL ,
  `text_pdf` MEDIUMTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `antrag_id` (`antrag_id` ASC) ,
  INDEX `typ` (`typ` ASC) ,
  INDEX `fk_antraege_dokumente_termine1_idx` (`termin_id` ASC) ,
  INDEX `fk_antraege_dokumente_antraege_ergebnisse1_idx` (`ergebnis_id` ASC) ,
  CONSTRAINT `fk_antraege_dokumente_termine1`
    FOREIGN KEY (`termin_id` )
    REFERENCES `ris2`.`termine` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_dokumente_antraege1`
    FOREIGN KEY (`antrag_id` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_dokumente_antraege_ergebnisse1`
    FOREIGN KEY (`ergebnis_id` )
    REFERENCES `ris2`.`antraege_ergebnisse` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_history`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_history` (
  `id` MEDIUMINT(9) NOT NULL ,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative') NOT NULL ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL COMMENT '0=Stadtrat' ,
  `gestellt_am` DATE NULL DEFAULT NULL ,
  `gestellt_von` TEXT NOT NULL ,
  `antrags_nr` VARCHAR(20) NOT NULL ,
  `bearbeitungsfrist` DATE NULL DEFAULT NULL ,
  `registriert_am` DATE NULL DEFAULT NULL ,
  `referat` VARCHAR(500) NOT NULL ,
  `referent` VARCHAR(200) NOT NULL ,
  `wahlperiode` VARCHAR(50) NOT NULL ,
  `antrag_typ` VARCHAR(50) NOT NULL ,
  `betreff` TEXT NOT NULL ,
  `kurzinfo` TEXT NOT NULL ,
  `status` VARCHAR(50) NOT NULL ,
  `bearbeitung` VARCHAR(100) NOT NULL ,
  `fristverlaengerung` DATE NULL DEFAULT NULL ,
  `initiatoren` TEXT NOT NULL ,
  `initiative_to_aufgenommen` DATE NULL DEFAULT NULL ,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`) ,
  INDEX `registriert_am` (`registriert_am` ASC) ,
  INDEX `datum_letzte_aenderung` (`datum_letzte_aenderung` ASC) ,
  INDEX `fk_antraege_history_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `bezirksausschuss`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_vorlagen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_vorlagen` (
  `antrag1` INT(11) NOT NULL ,
  `antrag2` INT(11) NOT NULL ,
  `datum` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`antrag1`, `antrag2`) ,
  INDEX `fk_antraege_links_antraege1_idx` (`antrag1` ASC) ,
  INDEX `fk_antraege_links_antraege2_idx` (`antrag2` ASC) ,
  CONSTRAINT `fk_antraege_vorlagen_antraege1`
    FOREIGN KEY (`antrag2` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_vorlagen_antraege2`
    FOREIGN KEY (`antrag1` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`orte_geo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`orte_geo` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `ort` VARCHAR(100) NOT NULL ,
  `lat` DOUBLE NOT NULL ,
  `lon` DOUBLE NOT NULL ,
  `source` ENUM('auto','manual') NOT NULL ,
  `to_hide` TINYINT(4) NOT NULL ,
  `to_hide_kommentar` VARCHAR(200) NOT NULL ,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `ort` (`ort` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 37348
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_orte`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_orte` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `antrag_id` INT(11) NULL DEFAULT NULL ,
  `termin_id` INT(11) NULL DEFAULT NULL ,
  `dokument_id` INT(11) NOT NULL ,
  `ort_name` VARCHAR(100) NOT NULL ,
  `ort_id` SMALLINT(5) UNSIGNED NOT NULL ,
  `source` ENUM('text_parse','manual') NOT NULL ,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `dokument` (`dokument_id` ASC, `ort_id` ASC) ,
  INDEX `antrag` (`antrag_id` ASC) ,
  INDEX `ort_id` (`ort_id` ASC) ,
  INDEX `fk_antraege_orte_antraege_dokumente1_idx` (`dokument_id` ASC) ,
  INDEX `fk_antraege_orte_termine1_idx` (`termin_id` ASC) ,
  CONSTRAINT `fk_antraege_orte_antraege_dokumente1`
    FOREIGN KEY (`dokument_id` )
    REFERENCES `ris2`.`antraege_dokumente` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_termine1`
    FOREIGN KEY (`termin_id` )
    REFERENCES `ris2`.`termine` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_orte_geo1`
    FOREIGN KEY (`ort_id` )
    REFERENCES `ris2`.`orte_geo` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_orte_antraege1`
    FOREIGN KEY (`antrag_id` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 726823
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`stadtraetInnen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`stadtraetInnen` (
  `id` INT(11) NOT NULL ,
  `gewaehlt_am` DATE NULL DEFAULT NULL ,
  `bio` TEXT NOT NULL ,
  `web` VARCHAR(250) NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`personen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`personen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name_normalized` VARCHAR(50) NOT NULL ,
  `typ` ENUM('person','fraktion','sonstiges') NOT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `ris_stadtraetIn` INT(11) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `name_normalized` (`name_normalized` ASC) ,
  INDEX `fk_personen_stadtraete1_idx` (`ris_stadtraetIn` ASC) ,
  CONSTRAINT `fk_personen_stadtraete1`
    FOREIGN KEY (`ris_stadtraetIn` )
    REFERENCES `ris2`.`stadtraetInnen` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 266
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_personen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_personen` (
  `antrag_id` INT(11) NOT NULL ,
  `person_id` INT(11) NOT NULL ,
  `typ` ENUM('gestellt_von','initiator') NOT NULL DEFAULT 'gestellt_von' ,
  PRIMARY KEY (`antrag_id`, `person_id`) ,
  INDEX `person` (`person_id` ASC) ,
  INDEX `fk_antraege_personen_antraege1_idx` (`antrag_id` ASC) ,
  CONSTRAINT `fk_antraege_personen_ris_personen1`
    FOREIGN KEY (`person_id` )
    REFERENCES `ris2`.`personen` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_personen_antraege1`
    FOREIGN KEY (`antrag_id` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`fraktionen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`fraktionen` (
  `id` INT(11) NOT NULL ,
  `name` VARCHAR(50) NOT NULL ,
  `ba_nr` SMALLINT(6) NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_fraktionen_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `fk_fraktionen_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`gremien_history`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`gremien_history` (
  `id` INT(11) NOT NULL ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL ,
  `name` VARCHAR(100) NOT NULL ,
  `kuerzel` VARCHAR(20) NOT NULL ,
  `gremientyp` VARCHAR(100) NOT NULL ,
  `referat` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`) ,
  INDEX `fk_gremien_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `fk_gremien_bezirksausschuesse10`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`ris_aenderungen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`ris_aenderungen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `ris_id` INT(11) NOT NULL ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL ,
  `typ` ENUM('stadtrat_antrag','stadtrat_vorlage','ba_antrag','ba_initiative','ba_termin','stadtrat_termin','rathausumschau', 'stadtraetIn', 'ba_mitglied', 'stadtrat_gremium', 'ba_gremium', 'stadtrat_ergebnis') NOT NULL ,
  `datum` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `aenderungen` TEXT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `datum` (`datum` ASC) ,
  INDEX `antrag_id` (`ris_id` ASC) ,
  INDEX `ba_nr` (`ba_nr` ASC, `datum` ASC) ,
  INDEX `fk_ris_aenderungen_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `fk_ris_aenderungen_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 111094
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`stadtraetInnen_fraktionen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`stadtraetInnen_fraktionen` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `stadtraetIn_id` INT(11) NOT NULL ,
  `fraktion_id` INT(11) NOT NULL ,
  `wahlperiode` VARCHAR(30) NOT NULL ,
  `mitgliedschaft` TEXT NOT NULL ,
  `funktion` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_ris_stadtraete_fraktionen_ris_personen1_idx` (`stadtraetIn_id` ASC) ,
  INDEX `fk_stadtraete_fraktionen_fraktionen1_idx` (`fraktion_id` ASC) ,
  UNIQUE INDEX `uq` (`stadtraetIn_id` ASC, `fraktion_id` ASC, `wahlperiode` ASC) ,
  CONSTRAINT `fk_stadtraete_fraktionen_fraktionen2`
    FOREIGN KEY (`fraktion_id` )
    REFERENCES `ris2`.`fraktionen` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_stadtraetInnen_fraktionen`
    FOREIGN KEY (`stadtraetIn_id` )
    REFERENCES `ris2`.`stadtraetInnen` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`strassen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`strassen` (
  `id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `plz` VARCHAR(10) NOT NULL ,
  `osm_ref` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 5722
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`termine_history`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`termine_history` (
  `id` INT(11) NOT NULL ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `termin_reihe` INT(11) NOT NULL DEFAULT '0' ,
  `gremium_id` INT(11) NOT NULL ,
  `ba_nr` SMALLINT(6) NULL DEFAULT NULL ,
  `termin` TIMESTAMP NULL DEFAULT NULL ,
  `termin_prev_id` INT(11) NULL DEFAULT NULL ,
  `termin_next_id` INT(11) NULL DEFAULT NULL ,
  `sitzungsort` TEXT NOT NULL ,
  `referat` VARCHAR(200) NOT NULL ,
  `referent` VARCHAR(200) NOT NULL ,
  `vorsitz` VARCHAR(200) NOT NULL ,
  `wahlperiode` VARCHAR(20) NOT NULL ,
  `status` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`) ,
  INDEX `termin` (`termin` ASC) ,
  INDEX `termin_reihe` (`termin_reihe` ASC) ,
  INDEX `fk_termine_history_bezirksausschuesse1_idx` (`ba_nr` ASC) ,
  CONSTRAINT `fk_termine_history_bezirksausschuesse1`
    FOREIGN KEY (`ba_nr` )
    REFERENCES `ris2`.`bezirksausschuesse` (`ba_nr` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_stadtraetInnen`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_stadtraetInnen` (
  `antrag_id` INT(11) NOT NULL ,
  `stadtraetIn_id` INT(11) NOT NULL ,
  `gefunden_am` TIMESTAMP NULL ,
  INDEX `fk_table1_antraege1_idx` (`antrag_id` ASC) ,
  PRIMARY KEY (`antrag_id`, `stadtraetIn_id`) ,
  INDEX `fk_antraege_stadtraetInnen_stadtraetInnen1_idx` (`stadtraetIn_id` ASC) ,
  CONSTRAINT `fk_antraege_stadtraetInnen_stadtraetInnen1`
    FOREIGN KEY (`stadtraetIn_id` )
    REFERENCES `ris2`.`stadtraetInnen` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_stadtraetInnen_antraege1`
    FOREIGN KEY (`antrag_id` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `ris2`.`antraege_ergebnisse_history`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `ris2`.`antraege_ergebnisse_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `datum_letzte_aenderung` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `antrag_id` INT(11) NOT NULL ,
  `gremium_name` VARCHAR(100) NOT NULL ,
  `gremium_id` INT(11) NULL ,
  `sitzungstermin_id` INT(11) NOT NULL ,
  `sitzungstermin_datum` DATE NOT NULL ,
  `beschluss_text` VARCHAR(500) NOT NULL ,
  `top_nr` VARCHAR(45) NULL ,
  `top_betreff` TEXT NULL ,
  `status` VARCHAR(200) NULL ,
  `entscheidung` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`, `datum_letzte_aenderung`) ,
  INDEX `fk_antraege_ergebnisse_antraege1_idx` (`antrag_id` ASC) ,
  INDEX `fk_antraege_ergebnisse_termine1_idx` (`sitzungstermin_id` ASC) ,
  INDEX `fk_antraege_ergebnisse_gremien1_idx` (`gremium_id` ASC) ,
  CONSTRAINT `fk_antraege_ergebnisse_termine10`
    FOREIGN KEY (`sitzungstermin_id` )
    REFERENCES `ris2`.`termine` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_gremien10`
    FOREIGN KEY (`gremium_id` )
    REFERENCES `ris2`.`gremien` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_antraege_ergebnisse_antraege10`
    FOREIGN KEY (`antrag_id` )
    REFERENCES `ris2`.`antraege` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

USE `ris2` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
