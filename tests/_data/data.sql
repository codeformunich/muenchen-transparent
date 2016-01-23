
-- Beispiel-Nutzer: user@example.com password: 1234

INSERT INTO `benutzerInnen` (`id`, `email`, `email_bestaetigt`, `datum_angelegt`, `berechtigungen_flags`, `pwd_enc`, `pwd_change_date`, `pwd_change_code`, `einstellungen`, `datum_letzte_benachrichtigung`) VALUES
(47, 'user@example.com', 0, '2016-01-17 19:12:13', 0, '$2y$10$NqowUOiQd3SNm8/zACCaguhyYpMxw8hX9pfxsvIrnXpI3/KHXfP4u', NULL, NULL, NULL, '2016-01-17 19:12:13');

-- Metadaten für die Startseite

INSERT INTO `metadaten` (`meta_key`, `meta_val`) VALUES
('anzahl_dokumente', 0x313437313232),
('anzahl_dokumente_1w', 0x333436),
('anzahl_seiten', 0x353335303339),
('anzahl_seiten_1w', 0x31363834),
('letzte_aktualisierun', 0x323031332d30352d31322032313a34313a3334),
('letzte_aktualisierung', 0x323031342d30392d31352030343a30343a3333);
