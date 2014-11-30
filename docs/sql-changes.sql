ALTER TABLE `ris2`.`antraege_ergebnisse` RENAME TO  `ris2`.`tagesordnungspunkte`;
ALTER TABLE `ris2`.`antraege_ergebnisse_history`  RENAME TO  `ris2`.`tagesordnungspunkte_history`;
ALTER TABLE `ris2`.`antraege_dokumente`  RENAME TO  `ris2`.`dokumente`;

ALTER TABLE ris2`.`dokumente` CHANGE COLUMN `ergebnis_id` `tagesordnungspunkt_id`;
