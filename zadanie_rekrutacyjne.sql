-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Wrz 15, 2024 at 08:30 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zadanie_rekrutacyjne`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `faktury`
--

CREATE TABLE `faktury` (
  `id_faktury` int(11) NOT NULL,
  `numer` varchar(50) NOT NULL,
  `data_wystawienia` date NOT NULL,
  `termin_platnosci` date NOT NULL,
  `suma_brutto` decimal(8,2) NOT NULL,
  `id_przedsiebiorcy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faktury`
--

INSERT INTO `faktury` (`id_faktury`, `numer`, `data_wystawienia`, `termin_platnosci`, `suma_brutto`, `id_przedsiebiorcy`) VALUES
(1, 'FV/2023/09/00123', '2024-09-12', '2024-12-01', 160.00, 1),
(2, 'FV/2024/09/00345', '2024-10-03', '2024-11-21', 40.00, 3),
(3, 'FV/2024/11/08756', '2024-10-23', '2024-12-20', 60.00, 4),
(4, 'FV/2024/12/04432', '2024-08-25', '2024-11-10', 30.00, 1),
(5, 'FV/2024/09/00345', '2024-10-13', '2024-10-25', 80.00, 3),
(6, 'FV/20224/05/98765', '2024-05-12', '2024-11-01', 120.00, 1),
(7, 'FV/2024/03/92834', '2024-03-12', '2024-05-11', 160.00, 1),
(8, 'FV/2024/04/11111', '2024-04-15', '2024-04-23', 100.00, 4),
(9, 'FV/2024/04/98765', '2024-04-16', '2024-04-24', 140.00, 4);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `klienci`
--

CREATE TABLE `klienci` (
  `id_przedsiebiorcy` int(11) NOT NULL,
  `nazwa_przedsiebiorcy` varchar(50) NOT NULL,
  `numer_konta_bankowego` varchar(26) NOT NULL,
  `nip` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `klienci`
--

INSERT INTO `klienci` (`id_przedsiebiorcy`, `nazwa_przedsiebiorcy`, `numer_konta_bankowego`, `nip`) VALUES
(1, 'Przedsiebiorca A', '0000-1111', '9999'),
(2, 'Przedsiebiorca B', '0000-2222', '8888'),
(3, 'Przedsiebiorca C', '0000-3333', '7777'),
(4, 'Przedsiebiorca D', '0000-4444', '6666'),
(5, 'Przedsiebiorca E', '0000-5555', '5555');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `platnosci`
--

CREATE TABLE `platnosci` (
  `id_platnosci` int(11) NOT NULL,
  `tytul_platnosci` varchar(50) NOT NULL,
  `kwota` decimal(8,2) NOT NULL,
  `data_wplaty` date NOT NULL,
  `numer_konta_bankowego_wplaty` varchar(26) NOT NULL,
  `id_faktury` int(11) NOT NULL,
  `id_przedsiebiorcy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `platnosci`
--

INSERT INTO `platnosci` (`id_platnosci`, `tytul_platnosci`, `kwota`, `data_wplaty`, `numer_konta_bankowego_wplaty`, `id_faktury`, `id_przedsiebiorcy`) VALUES
(1, 'Zakup 1', 179.00, '2024-12-14', '1111-2222', 1, 1),
(2, 'Zakup 2', 30.00, '2024-10-22', '1111-2222', 4, 1),
(3, 'Zakup 1', 60.00, '2024-12-01', '1111-2222', 3, 4),
(5, 'Zakup 1', 40.00, '2024-10-01', '1111-2222', 2, 3),
(6, 'Zakup 2', 55.00, '2024-10-28', '1111-2222', 5, 3),
(7, 'Zakup 3', 130.00, '2024-10-25', '1111-2222', 6, 1),
(8, 'Zakup 4', 145.00, '2024-05-09', '1111-2222', 7, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pozycje_faktury`
--

CREATE TABLE `pozycje_faktury` (
  `id_pozycji_faktury` int(11) NOT NULL,
  `nazwa_produktu` varchar(50) NOT NULL,
  `ilosc` int(11) NOT NULL,
  `cena` decimal(8,2) NOT NULL,
  `id_faktury` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pozycje_faktury`
--

INSERT INTO `pozycje_faktury` (`id_pozycji_faktury`, `nazwa_produktu`, `ilosc`, `cena`, `id_faktury`) VALUES
(1, 'Produkt 1', 3, 20.00, 1),
(2, 'Produkt 1', 2, 20.00, 2),
(3, 'Produkt 2', 5, 10.00, 1),
(4, 'Produkt 3', 4, 15.00, 3),
(5, 'Produkt 4', 1, 30.00, 4),
(6, 'Produkt 1', 4, 20.00, 5),
(7, 'Produkt 1', 2, 60.00, 6),
(8, 'Produkt 1', 4, 40.00, 7),
(9, 'Produkt 5', 2, 50.00, 4),
(10, 'Produkt 6', 2, 70.00, 4);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `faktury`
--
ALTER TABLE `faktury`
  ADD PRIMARY KEY (`id_faktury`);

--
-- Indeksy dla tabeli `klienci`
--
ALTER TABLE `klienci`
  ADD PRIMARY KEY (`id_przedsiebiorcy`);

--
-- Indeksy dla tabeli `platnosci`
--
ALTER TABLE `platnosci`
  ADD PRIMARY KEY (`id_platnosci`);

--
-- Indeksy dla tabeli `pozycje_faktury`
--
ALTER TABLE `pozycje_faktury`
  ADD PRIMARY KEY (`id_pozycji_faktury`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `faktury`
--
ALTER TABLE `faktury`
  MODIFY `id_faktury` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `klienci`
--
ALTER TABLE `klienci`
  MODIFY `id_przedsiebiorcy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `platnosci`
--
ALTER TABLE `platnosci`
  MODIFY `id_platnosci` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pozycje_faktury`
--
ALTER TABLE `pozycje_faktury`
  MODIFY `id_pozycji_faktury` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
