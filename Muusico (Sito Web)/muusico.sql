CREATE DATABASE IF NOT EXISTS muusico DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE muusico;

CREATE TABLE `carrello` (
  `codice` bigint(20) NOT NULL,
  `email_U` varchar(50) NOT NULL,
  `data_A` date DEFAULT NULL,
  `data_C` date DEFAULT NULL,
  `prezzoTot` int(11) DEFAULT NULL,
  `stato` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `carrello`
--

INSERT INTO `carrello` (`codice`, `email_U`, `data_A`, `data_C`, `prezzoTot`, `stato`) VALUES
(1, 'luca@gmail.com', '2024-12-22', '2024-12-25', 200, 1),
(3, 'luca@gmail.com', '2025-01-11', '2025-01-16', 552, 3),
(4, 'marco@gmail.com', '2025-01-26', '2025-01-29', 100, 3),
(5, 'mario.rossi@gmail.com', '2024-12-02', '2025-01-07', 1600, 1),
(8, 'chad@rhcp.com', '2024-12-09', '2024-12-13', 425, 1),
(9, 'chad@rhcp.com', '2024-12-19', '2024-12-24', 988, 1),
(10, 'mm@mm.com', '2025-01-25', '2025-01-30', 2799, 1),
(12, 'luca@gmail.com', '2025-02-08', '2025-02-13', 1896, 0),
(13, 'chad@rhcp.com', NULL, NULL, NULL, 0),
(15, 'luca@gmail.com', '2025-01-28', '2025-02-02', 1300, 3),
(18, 'marco@gmail.com', '2025-02-10', '2025-02-15', 200, 1),
(20, 'marco@gmail.com', '2025-02-10', '2025-02-15', 5247, 2),
(21, 'luca@gmail.com', '2025-02-10', '2025-02-15', 3498, 1),
(22, 'luca@gmail.com', '2025-02-10', '2025-02-15', 500, 3),
(23, 'luca@gmail.com', '2025-02-10', '2025-02-15', 1749, 3),
(26, 'marco@gmail.com', '2024-12-01', '2024-12-06', 25, 1),
(27, 'marco@gmail.com', '2025-01-25', '2025-01-31', 500, 1),
(28, 'marco@gmail.com', '2025-02-12', '2025-02-17', 1000, 1),
(32, 'luca@gmail.com', '2025-02-15', '2025-02-20', 500, 1),
(33, 'luca@gmail.com', '2025-02-15', '2025-02-20', 75, 2),
(34, 'luca@gmail.com', '2025-02-15', '2025-02-20', 25, 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `contiene`
--

CREATE TABLE `contiene` (
  `codice_C` bigint(20) NOT NULL,
  `codice_P` varchar(20) NOT NULL,
  `quantità_C` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `contiene`
--

INSERT INTO `contiene` (`codice_C`, `codice_P`, `quantità_C`) VALUES
(1, 'GT01', 2),
(4, 'GT01', 1),
(3, 'B01', 1),
(3, 'CBL2', 2),
(5, 'AMP01', 1),
(8, 'RDV01', 1),
(9, 'CBL2', 1),
(9, 'MA8', 1),
(9, 'GT04', 1),
(9, 'AMP5', 1),
(9, 'RICG5', 1),
(10, 'BMM01', 1),
(10, 'B54', 1),
(12, 'BMM01', 4),
(12, 'AMP5', 1),
(15, 'KBD02', 2),
(13, 'GT01', 2),
(18, 'GT01', 2),
(20, 'GT15', 3),
(21, 'GT15', 2),
(22, 'B01', 1),
(23, 'GT15', 1),
(26, 'CBL01', 1),
(27, 'GT03', 1),
(28, 'KBD01', 1),
(32, 'B01', 1),
(33, 'CBL01', 3),
(34, 'CBL01', 1),
(12, 'GT01', 1),
(12, 'RICG5', 1),
(12, 'CBL01', 2),
(12, 'AMP01', 2),
(12, 'B54', 2),
(12, 'B01', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotto`
--

CREATE TABLE `prodotto` (
  `nome` varchar(30) NOT NULL,
  `codice` varchar(10) NOT NULL,
  `email_V` varchar(20) NOT NULL,
  `categoria` enum('chitarre','bassi','batterie','cavi','keyboards','amplificatori','casse','corde','tracolle') NOT NULL,
  `colore` varchar(20) NOT NULL,
  `prezzo` float NOT NULL,
  `descrizione` varchar(100) NOT NULL,
  `quantitàM` int(11) NOT NULL,
  `rating` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotto`
--

INSERT INTO `prodotto` (`nome`, `codice`, `email_V`, `categoria`, `colore`, `prezzo`, `descrizione`, `quantitàM`, `rating`) VALUES
('Hot Rod DeVille', 'AMP01', 'info@fender.com', 'amplificatori', 'Nero', 1600, 'Suono caldo', 18, 5),
('THR 5 Mini', 'AMP5', 'info@yamaha.com', 'amplificatori', 'Giallo', 249, 'Piccolo ma potente', 34, 1),
('Jazz Bass', 'B01', 'info@fender.com', 'bassi', 'Nero', 500, 'Basso incredibile', 49, 5),
('Vintage 1954 Precision Bass', 'B54', 'info@fender.com', 'bassi', 'Sunburst', 2250, 'Precision Bass originale', 11, 5),
('Marcus Miller V7', 'BMM01', 'sara@gmail.com', 'bassi', 'Sunburst', 549, 'Modello Signature dalla leggenda Marcus Miller', 59, 4),
('Reference-TRS01', 'CBL01', 'info@reference.com', 'cavi', 'Nero', 25, 'Il miglior in circolazione in Italia', 99, NULL),
('Reference-Jack88', 'CBL2', 'info@reference.com', 'cavi', 'Nero', 26, 'Buono', 49, 5),
('Stratocaster', 'GT01', 'info@fender.com', 'chitarre', 'Nero', 100, 'Bella Chitarra. Famosissima. Tono spettacolare.', 98, 3.5),
('Telecaster', 'GT02', 'info@fender.com', 'chitarre', 'Rosso', 500, 'Bella anche questa', 0, 5),
('Jag Master', 'GT03', 'info@fender.com', 'chitarre', 'Sunburst', 500, 'Incredibile', 149, NULL),
('Larry Carlton H7', 'GT04', 'sara@gmail.com', 'chitarre', 'Rosso', 595, 'Suono incredibile MA viene spedito in 2 mesi', 14, 5),
('Revstar Professional', 'GT15', 'info@yamaha.com', 'chitarre', 'Verde', 1749, 'Evoca toni classici e strappalacrime da dual pickup stile P90', 3, 0),
('HS7 Bianco (Coppia)', 'HS7B', 'info@yamaha.com', 'casse', 'Bianco', 500, 'Ottime per chi inizia', 40, NULL),
('Jazz Chorus 40', 'JC40', 'info@roland.com', 'amplificatori', 'Nero', 1280, '2 canali con effetto chorus', 40, NULL),
('Rhodes', 'KBD01', 'info@fender.com', 'keyboards', 'Nero', 1000, 'Ha fatto la storia', 2, NULL),
('DX7', 'KBD02', 'info@yamaha.com', 'keyboards', 'Nero', 650, 'Suono 80s. Synth digitale.', 18, NULL),
('Juno 106', 'KDB03', 'info@roland.com', 'keyboards', 'Nero', 3000, 'Synth analogo. puro', 3, NULL),
('Vintage Maple', 'LDW01', 'william@gmail.com', 'batterie', 'Verde', 4699, 'Compra questo set di batteria e basta. Dai. Ti aspetto.', 3, NULL),
('70s Drums', 'LDW02', 'william@gmail.com', 'batterie', 'Bianco', 3000, 'Bella', 50, NULL),
('MA8 Micro Monitor', 'MA8', 'info@roland.com', 'casse', 'Bianco', 100, 'Semplice, affidabile', 14, 4),
('V Drums Batteria Elettronica', 'RDV01', 'info@roland.com', 'batterie', 'Bianco', 425, 'Include 16 kit con Bluetooth', 24, 3),
('RICG5 Cavo Strumenti', 'RICG5', 'info@roland.com', 'cavi', 'Nero', 18, 'Cavi placcati in oro', 129, 2),
('HS8 (Coppia)', 'SPK01', 'info@yamaha.com', 'casse', 'Nero', 600, 'Ottime per chi inizia', 80, NULL),
('10-46 Nickel Plated', 'STR01', 'info@fender.com', 'corde', 'Argento', 10, 'Ottima resa', 60, NULL),
('Corde Chitarra Classica', 'STR02', 'info@yamaha.com', 'corde', 'Nylon', 8, 'Suono naturale', 45, NULL),
('Tracolla Nera', 'STRAP01', 'info@fender.com', 'tracolle', 'Nero', 50, 'Utile', 100, NULL),
('Tracolla Fender Logo', 'STRAP02', 'info@fender.com', 'tracolle', 'Giallo', 100, 'Utilissima', 120, NULL),
('Boss Guitar Strap', 'STRAP03', 'info@roland.com', 'tracolle', 'Marrone', 50, 'Strap con logo Boss', 50, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `recensione`
--

CREATE TABLE `recensione` (
  `email_U` varchar(50) NOT NULL,
  `codice_P` varchar(10) NOT NULL,
  `testo` varchar(100) DEFAULT NULL,
  `punteggio` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `recensione`
--

INSERT INTO `recensione` (`email_U`, `codice_P`, `testo`, `punteggio`) VALUES
('luca@gmail.com', 'GT02', 'bellissima', 5),
('marco@gmail.com', 'GT01', 'mah. fa il suo dovereeoo', 2),
('mario.rossi@gmail.com', 'AMP01', 'il migliore!!!', 5),
('chad@rhcp.com', 'RDV01', 'Non suonano come una batteria vera', 3),
('chad@rhcp.com', 'GT04', 'Consegnata entro 5 giorni! Grazie Muusico!', 5),
('chad@rhcp.com', 'AMP5', 'A John non è piaciuto questo amplificatore', 1),
('chad@rhcp.com', 'RICG5', 'Meglio i reference', 2),
('chad@rhcp.com', 'CBL2', 'Cavo migliore del mondo! Wow!', 5),
('chad@rhcp.com', 'MA8', 'Mio figlio lo adora', 4),
('mm@mm.com', 'BMM01', 'Approvo questo basso', 4),
('mm@mm.com', 'B54', 'Un po meglio dal mio', 5),
('luca@gmail.com', 'B01', 'i <3 fender!', 5),
('luca@gmail.com', 'CBL2', 'Bello\r\n', 5),
('luca@gmail.com', 'GT01', 'Fantastica\r\n', 5);

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `nome` varchar(30) NOT NULL,
  `cognome` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(20) NOT NULL,
  `cf` varchar(30) DEFAULT NULL,
  `pi` varchar(30) DEFAULT NULL,
  `marca` varchar(20) DEFAULT NULL,
  `data_nascita` date DEFAULT NULL,
  `tipo` enum('A','C','V') NOT NULL,
  `block_status` tinyint(1) NOT NULL DEFAULT 0,
  `autorizzazione` tinyint(1) NOT NULL DEFAULT 0,
  `via` varchar(30) DEFAULT NULL,
  `città` varchar(20) DEFAULT NULL,
  `provincia` varchar(20) DEFAULT NULL,
  `cap` varchar(20) DEFAULT NULL,
  `nazione` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`nome`, `cognome`, `email`, `password`, `cf`, `pi`, `marca`, `data_nascita`, `tipo`, `block_status`, `autorizzazione`, `via`, `città`, `provincia`, `cap`, `nazione`) VALUES
('Admin', 'No. 3', 'admin@musico.com', '123', NULL, NULL, NULL, NULL, 'A', 0, 1, NULL, NULL, NULL, NULL, NULL),
('chad', 'smith', 'chad@rhcp.com', '123', '', NULL, NULL, NULL, 'C', 0, 1, '', '', '', '', ''),
('derin', 'donmez', 'dd@d.com', '123', 'dnmdmfmsdmnfdfnm', NULL, NULL, '1999-01-28', 'A', 0, 1, 'via1', 'citta2', 'prov3', '20202', 'ita'),
('delit', 'delitmi', 'delitmi@gmail.com', '123', '', '', '', NULL, 'C', 0, 0, '', '', '', '', ''),
('Leo', 'Fender', 'info@fender.com', '123', NULL, 'partitaivafender123', 'Fender', '1910-08-01', 'V', 0, 1, 'Fender St.', 'Fendertown', NULL, '12345', 'USA'),
('Giulio', 'Reference', 'info@reference.com', '123', '', 'POEIRYTJHJBJDHUY/&%&/&T(/Y(UO', 'Reference', NULL, 'V', 0, 1, '', '', '', '', ''),
('Ikutaro', 'Kakehashi', 'info@roland.com', '123', '', 'ROL1930', 'Roland', '1930-02-07', 'V', 0, 1, 'Via Kakehashi', 'Tokyo', 'TO', '100000', 'Giappone'),
('Katsuaki', 'Watanabe', 'info@yamaha.com', '123', NULL, 'PYHGSTRE&&%$(S76', 'Yamaha', NULL, 'V', 0, 1, NULL, NULL, NULL, NULL, NULL),
('Luca', 'Rossi', 'luca@gmail.com', '123', 'rsslcu12fhdafah', NULL, NULL, '2012-01-02', 'C', 0, 1, 'Via Luke 2', 'Lucca', 'LU', '55100', 'Italia'),
('marco', 'polo', 'marco@gmail.com', '123', 'plomrc84a123f93', '', '', '1984-04-18', 'C', 0, 1, 'Via Roma', 'Milano', 'MI', '20120', 'Italia'),
('mario', 'rossi', 'mario.rossi@gmail.com', '123', NULL, NULL, NULL, NULL, 'C', 1, 1, NULL, NULL, NULL, NULL, NULL),
('Marcus', 'Miller', 'mm@mm.com', '123', 'MLLMRC54ABC', '', '', '1954-03-15', 'C', 0, 1, 'Via Basso', 'Bergamo', 'BG', '24000', 'Italia'),
('Sara', 'Venditori', 'sara@gmail.com', '123', '', 'PIVAdiSara024', 'Sire', NULL, 'V', 0, 1, 'Via Sire', 'Siena', 'SI', '53100', 'Italia'),
('Simone', 'Piccirillo', 'simone@muusico.com', '123', 'PPPP', NULL, NULL, '2024-11-04', 'A', 0, 1, 'Milano 1', 'Milano', 'Milano', '20133', 'Italia'),
('William', 'Ludwig', 'william@gmail.com', '123', NULL, 'LUD1234', 'Ludwig', NULL, 'V', 0, 1, NULL, NULL, NULL, NULL, NULL);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `carrello`
--
ALTER TABLE `carrello`
  ADD PRIMARY KEY (`codice`),
  ADD KEY `email_U` (`email_U`);

--
-- Indici per le tabelle `contiene`
--
ALTER TABLE `contiene`
  ADD KEY `codice_C` (`codice_C`),
  ADD KEY `codice_P` (`codice_P`);

--
-- Indici per le tabelle `prodotto`
--
ALTER TABLE `prodotto`
  ADD PRIMARY KEY (`codice`),
  ADD KEY `email_V` (`email_V`);

--
-- Indici per le tabelle `recensione`
--
ALTER TABLE `recensione`
  ADD KEY `Codice_Prod` (`codice_P`),
  ADD KEY `User` (`email_U`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `carrello`
--
ALTER TABLE `carrello`
  MODIFY `codice` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `carrello`
--
ALTER TABLE `carrello`
  ADD CONSTRAINT `carrello_ibfk_1` FOREIGN KEY (`email_U`) REFERENCES `utente` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `contiene`
--
ALTER TABLE `contiene`
  ADD CONSTRAINT `contiene_ibfk_1` FOREIGN KEY (`codice_C`) REFERENCES `carrello` (`codice`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `contiene_ibfk_2` FOREIGN KEY (`codice_P`) REFERENCES `prodotto` (`codice`) ON DELETE CASCADE ON UPDATE CASCADE;
