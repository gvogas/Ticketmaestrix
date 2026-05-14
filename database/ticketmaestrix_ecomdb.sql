-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 14, 2026 at 07:21 PM
-- Server version: 11.8.5-MariaDB-log
-- PHP Version: 8.5.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticketmaestrix_ecomdb`
--

-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `ticketmaestrix_ecomdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE `ticketmaestrix_ecomdb`;

--
-- Table structure for table `authtoken`
--

CREATE TABLE `authtoken` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `token_hash` varchar(191) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `authtoken`
--

INSERT INTO `authtoken` (`id`, `user_id`, `token_hash`, `expires_at`) VALUES
(4, 0, '438f784a03807506a7efaca6d6e31a18eaffcb356348afb9ae1573c58ec0a6f5', '2026-05-13 19:48:54'),
(13, 6, '5abe45f60b2dc768f2841963e62239cc13f1669de39dcd4769310500039ac0f0', '2026-05-13 22:37:10'),
(14, 10, 'e13c565e6c8f2fe1fd1078862d2fdbc0ab7897da8f539022c4af9366cdbeccf6', '2026-05-14 00:58:16'),
(16, 11, 'dc8ac64f058374fcfd50649a6cde13a6854483e471aa24a1404e2ced3e8967c2', '2026-05-14 04:46:58'),
(18, 12, '7c0183aca40b1e8e28f7db5c4b0bd37705800d6d255f744c8922da7f16ace261', '2026-05-14 05:13:20'),
(20, 13, 'ce71d457bfae89c875c7b8097179d4c3ec1861129f559fb64f40e44854817ea8', '2026-05-14 05:56:04'),
(21, 14, '286c3edf420a3b42fab2b33e6956f1829a6cbcf6d96aa96adbf7b255a44a268f', '2026-05-14 08:21:30'),
(22, 15, '8a9978911ff384afd8f25e9801c48d303a70c3c6465c14236479d9a00b8809c2', '2026-05-14 20:09:48'),
(23, 16, '89def431b6ccfbcaaeccd92b5f5b349102d2214d2ed52663be9644e27d8ee600', '2026-05-14 21:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Concert'),
(2, 'Movies'),
(3, 'Sports'),
(4, 'Theater'),
(5, 'Raffle'),
(6, 'Festival'),
(7, 'Comedy');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `date` datetime NOT NULL,
  `venue_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `event_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date`, `venue_id`, `category_id`, `event_image`) VALUES
(1, 'Toronto Aurora Pop Night', 'Toronto Aurora Pop Night is a concert event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-18 17:00:00', 3, 1, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(2, 'Toronto Downtown Indie Bash', 'Toronto Downtown Indie Bash is a concert event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-05 19:00:00', 7, 1, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(3, 'Montreal Midnight Jazz Room', 'Montreal Midnight Jazz Room is a concert event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-22 20:00:00', 4, 1, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(4, 'Montreal Electric Skyline Rave', 'Montreal Electric Skyline Rave is a concert event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-12 21:00:00', 1, 1, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(5, 'Laval Noir Mystery Screening', 'Laval Noir Mystery Screening is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-15 21:00:00', 8, 2, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(6, 'Laval Sci-Fi Double Feature', 'Laval Sci-Fi Double Feature is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-08 10:00:00', 8, 2, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(7, 'Montreal City Hoops Showcase', 'Montreal City Hoops Showcase is a sports event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-15 19:30:00', 1, 3, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(8, 'Toronto Baseball Rivalry Day', 'Toronto Baseball Rivalry Day is a sports event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-04 13:00:00', 3, 3, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(9, 'Laval MMA Contender Card', 'Laval MMA Contender Card is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-10 18:00:00', 2, 3, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(10, 'Montreal Modern Drama Showcase', 'Montreal Modern Drama Showcase is a theater event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-20 19:00:00', 4, 4, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(11, 'Toronto Broadway Workshop Night', 'Toronto Broadway Workshop Night is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-14 19:30:00', 10, 4, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(12, 'Toronto Travel Dream Drawing', 'Toronto Travel Dream Drawing is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-30 12:00:00', 10, 5, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(13, 'Toronto Golden Hour Country Jam', 'Toronto Golden Hour Country Jam is a concert event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-15 14:00:00', 10, 1, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(14, 'Toronto Night Market Celebration', 'Toronto Night Market Celebration is a festival event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-20 11:00:00', 7, 6, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(15, 'Montreal Global Taste Fest', 'Montreal Global Taste Fest is a festival event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-03 12:00:00', 12, 6, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(16, 'Toronto Sketch Comedy Jam', 'Toronto Sketch Comedy Jam is a comedy event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-30 20:00:00', 10, 7, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(17, 'Toronto Street Food Weekend', 'Toronto Street Food Weekend is a festival event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-27 18:00:00', 10, 6, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(18, 'Laval Harbor Latin Beats', 'Laval Harbor Latin Beats is a concert event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-20 18:30:00', 2, 1, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(19, 'Montreal Animation Spotlight', 'Montreal Animation Spotlight is a movie event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-22 07:00:00', 12, 2, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(20, 'Toronto Rooftop Boxing Night', 'Toronto Rooftop Boxing Night is a sports event in Toronto, ON, Canada at Rogers Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-23 19:30:00', 5, 3, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(21, 'Laval One-Act Play Festival', 'Laval One-Act Play Festival is a theater event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-25 08:00:00', 8, 4, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(22, 'Montreal Tech Gear Giveaway', 'Montreal Tech Gear Giveaway is a raffle event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-26 20:30:00', 12, 5, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(23, 'Toronto Craft Beer Garden', 'Toronto Craft Beer Garden is a festival event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-28 09:00:00', 7, 6, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(24, 'Montreal Laugh Lab Live', 'Montreal Laugh Lab Live is a comedy event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-29 21:30:00', 4, 7, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(25, 'Laval Vinyl Revival Live', 'Laval Vinyl Revival Live is a concert event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-05-31 06:30:00', 8, 1, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(26, 'Toronto Documentary Directors Forum', 'Toronto Documentary Directors Forum is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-01 19:00:00', 10, 2, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(27, 'Montreal Summer Soccer Classic', 'Montreal Summer Soccer Classic is a sports event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-03 07:30:00', 6, 3, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(28, 'Laval Improvised Shakespeare', 'Laval Improvised Shakespeare is a theater event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-04 20:00:00', 8, 4, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(29, 'Toronto Scholarship Prize Draw', 'Toronto Scholarship Prize Draw is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-06 08:30:00', 10, 5, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(30, 'Montreal Waterfront Wine Walk', 'Montreal Waterfront Wine Walk is a festival event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-07 21:00:00', 6, 6, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(31, 'Laval Friday Stand-Up Showcase', 'Laval Friday Stand-Up Showcase is a comedy event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-09 09:30:00', 8, 7, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(32, 'Toronto Acoustic Rooftop Set', 'Toronto Acoustic Rooftop Set is a concert event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-10 18:30:00', 3, 1, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(33, 'Montreal Retro Horror Late Show', 'Montreal Retro Horror Late Show is a movie event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-12 07:00:00', 4, 2, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(34, 'Laval Skate Jam Invitational', 'Laval Skate Jam Invitational is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-13 19:30:00', 2, 3, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(35, 'Toronto Musical Preview Evening', 'Toronto Musical Preview Evening is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-15 08:00:00', 10, 4, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(36, 'Montreal Local Heroes Fundraiser', 'Montreal Local Heroes Fundraiser is a raffle event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-16 20:30:00', 12, 5, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(37, 'Laval Autumn Makers Fair', 'Laval Autumn Makers Fair is a festival event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-18 09:00:00', 9, 6, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(38, 'Toronto Comedy Cellar Selects', 'Toronto Comedy Cellar Selects is a comedy event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-19 21:30:00', 3, 7, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(39, 'Montreal Neon Synthwave Party', 'Montreal Neon Synthwave Party is a concert event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-21 06:30:00', 4, 1, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(40, 'Laval Family Matinee Weekend', 'Laval Family Matinee Weekend is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-22 19:00:00', 8, 2, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(41, 'Toronto Indoor Volleyball Cup', 'Toronto Indoor Volleyball Cup is a sports event in Toronto, ON, Canada at Coca-Cola Coliseum. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-24 07:30:00', 11, 3, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(42, 'Montreal Storytelling Stage', 'Montreal Storytelling Stage is a theater event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-25 20:00:00', 12, 4, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(43, 'Laval Holiday Basket Raffle', 'Laval Holiday Basket Raffle is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-27 08:30:00', 9, 5, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(44, 'Toronto Summer Culture Fest', 'Toronto Summer Culture Fest is a festival event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-28 21:00:00', 10, 6, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(45, 'Montreal Podcast Comedy Taping', 'Montreal Podcast Comedy Taping is a comedy event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-06-30 09:30:00', 12, 7, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(46, 'Laval Riverfront Blues Session', 'Laval Riverfront Blues Session is a concert event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-01 18:30:00', 2, 1, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(47, 'Toronto Indie Film Premiere', 'Toronto Indie Film Premiere is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-03 07:00:00', 10, 2, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(48, 'Laval Charity 5K Finals', 'Laval Charity 5K Finals is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-04 19:30:00', 2, 3, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(49, 'Montreal New Voices Theater', 'Montreal New Voices Theater is a theater event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-06 08:00:00', 4, 4, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(50, 'Toronto Community Prize Raffle', 'Toronto Community Prize Raffle is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-07 20:30:00', 10, 5, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(51, 'Laval Oktoberfest City Edition', 'Laval Oktoberfest City Edition is a festival event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-09 09:00:00', 2, 6, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(52, 'Montreal New Comics Spotlight', 'Montreal New Comics Spotlight is a comedy event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-10 21:30:00', 4, 7, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(53, 'Toronto Stadium Rock Tribute', 'Toronto Stadium Rock Tribute is a concert event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-12 06:30:00', 7, 1, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(54, 'Montreal Classic Cinema Brunch', 'Montreal Classic Cinema Brunch is a movie event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-13 19:00:00', 12, 2, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(55, 'Laval Hockey Night Classic', 'Laval Hockey Night Classic is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-15 07:30:00', 2, 3, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(56, 'Toronto Comedy Play Rehearsed', 'Toronto Comedy Play Rehearsed is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-16 20:00:00', 10, 4, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(57, 'Montreal Benefit Auction Night', 'Montreal Benefit Auction Night is a raffle event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-18 08:30:00', 12, 5, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(58, 'Laval Vegan Food Festival', 'Laval Vegan Food Festival is a festival event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-19 21:00:00', 9, 6, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(59, 'Toronto Crowd Work Night', 'Toronto Crowd Work Night is a comedy event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-21 09:30:00', 10, 7, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(60, 'Montreal Lo-Fi Garden Concert', 'Montreal Lo-Fi Garden Concert is a concert event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-22 18:30:00', 1, 1, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(61, 'Laval Action Movie Marathon', 'Laval Action Movie Marathon is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-24 07:00:00', 8, 2, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(62, 'Toronto Wrestling Main Event', 'Toronto Wrestling Main Event is a sports event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-25 19:30:00', 3, 3, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(63, 'Montreal Ballet and Stage Night', 'Montreal Ballet and Stage Night is a theater event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-27 08:00:00', 12, 4, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(64, 'Laval Sports Memorabilia Draw', 'Laval Sports Memorabilia Draw is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-28 20:30:00', 9, 5, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(65, 'Toronto BBQ Smokehouse Weekend', 'Toronto BBQ Smokehouse Weekend is a festival event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-30 09:00:00', 7, 6, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(66, 'Montreal Improv Battle Royale', 'Montreal Improv Battle Royale is a comedy event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-07-31 21:30:00', 12, 7, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(67, 'Laval K-Pop Dance Night', 'Laval K-Pop Dance Night is a concert event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-02 06:30:00', 8, 1, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(68, 'Toronto Rom-Com Night Out', 'Toronto Rom-Com Night Out is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-03 19:00:00', 10, 2, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(69, 'Montreal UFC Watch Party', 'Montreal UFC Watch Party is a sports event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-05 07:30:00', 1, 3, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(70, 'Laval Experimental Black Box', 'Laval Experimental Black Box is a theater event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-06 20:00:00', 8, 4, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(71, 'Toronto Concert Ticket Giveaway', 'Toronto Concert Ticket Giveaway is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-08 08:30:00', 10, 5, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(72, 'Montreal Maple Dessert Festival', 'Montreal Maple Dessert Festival is a festival event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-09 21:00:00', 12, 6, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(73, 'Laval Clean Comedy Hour', 'Laval Clean Comedy Hour is a comedy event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-11 09:30:00', 2, 7, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(74, 'Toronto Soul and R&B Lounge', 'Toronto Soul and R&B Lounge is a concert event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-12 18:30:00', 10, 1, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(75, 'Montreal Silent Film With Live Piano', 'Montreal Silent Film With Live Piano is a movie event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-14 07:00:00', 4, 2, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(76, 'Laval Tennis Open Showcase', 'Laval Tennis Open Showcase is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-15 19:30:00', 2, 3, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(77, 'Toronto Family Puppet Theater', 'Toronto Family Puppet Theater is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-17 08:00:00', 10, 4, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(78, 'Laval Weekend Getaway Raffle', 'Laval Weekend Getaway Raffle is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-18 20:30:00', 9, 5, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(79, 'Montreal Carnival Lights Night', 'Montreal Carnival Lights Night is a festival event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-20 09:00:00', 6, 6, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(80, 'Toronto Roast Battle Showcase', 'Toronto Roast Battle Showcase is a comedy event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-21 21:30:00', 3, 7, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(81, 'Laval Punk Warehouse Show', 'Laval Punk Warehouse Show is a concert event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-23 06:30:00', 2, 1, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(82, 'Montreal Thriller Preview Night', 'Montreal Thriller Preview Night is a movie event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-24 19:00:00', 12, 2, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(83, 'Toronto Lacrosse Night Live', 'Toronto Lacrosse Night Live is a sports event in Toronto, ON, Canada at Rogers Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-26 07:30:00', 5, 3, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(84, 'Montreal Poetry Performance Night', 'Montreal Poetry Performance Night is a theater event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-27 20:00:00', 4, 4, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(85, 'Laval Gaming Bundle Draw', 'Laval Gaming Bundle Draw is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-29 08:30:00', 9, 5, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(86, 'Toronto Artisan Pop-Up Fair', 'Toronto Artisan Pop-Up Fair is a festival event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-08-30 21:00:00', 10, 6, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(87, 'Montreal Late Night Laughs', 'Montreal Late Night Laughs is a comedy event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-01 09:30:00', 4, 7, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(88, 'Laval Orchestra Under Lights', 'Laval Orchestra Under Lights is a concert event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-02 18:30:00', 8, 1, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(89, 'Toronto Cult Classics After Dark', 'Toronto Cult Classics After Dark is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-04 07:00:00', 10, 2, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(90, 'Montreal Rugby Sevens Cup', 'Montreal Rugby Sevens Cup is a sports event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-05 19:30:00', 6, 3, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(91, 'Laval Opera Highlights Evening', 'Laval Opera Highlights Evening is a theater event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-07 08:00:00', 8, 4, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(92, 'Toronto Home Makeover Giveaway', 'Toronto Home Makeover Giveaway is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-08 20:30:00', 10, 5, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(93, 'Montreal Cultural Dance Festival', 'Montreal Cultural Dance Festival is a festival event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-10 09:00:00', 12, 6, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(94, 'Laval Student Comedy Night', 'Laval Student Comedy Night is a comedy event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-11 21:30:00', 8, 7, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(95, 'Toronto Reggae Summer Stage', 'Toronto Reggae Summer Stage is a concert event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-13 06:30:00', 3, 1, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(96, 'Montreal Foreign Film Showcase', 'Montreal Foreign Film Showcase is a movie event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-14 19:00:00', 4, 2, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(97, 'Laval Motocross Arena Jam', 'Laval Motocross Arena Jam is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-16 07:30:00', 2, 3, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(98, 'Toronto Community Theater Gala', 'Toronto Community Theater Gala is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-17 20:00:00', 10, 4, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(99, 'Montreal Food Lovers Prize Draw', 'Montreal Food Lovers Prize Draw is a raffle event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-19 08:30:00', 12, 5, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(100, 'Laval Seafood Pier Festival', 'Laval Seafood Pier Festival is a festival event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-20 21:00:00', 2, 6, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(101, 'Toronto Comedy Open Mic Finals', 'Toronto Comedy Open Mic Finals is a comedy event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-22 09:30:00', 10, 7, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(102, 'Montreal Folk Stories Live', 'Montreal Folk Stories Live is a concert event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-23 18:30:00', 4, 1, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(103, 'Laval Short Film Awards', 'Laval Short Film Awards is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-25 07:00:00', 8, 2, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(104, 'Toronto Monster Truck Rally', 'Toronto Monster Truck Rally is a sports event in Toronto, ON, Canada at Coca-Cola Coliseum. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-26 19:30:00', 11, 3, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(105, 'Montreal Mystery Dinner Theater', 'Montreal Mystery Dinner Theater is a theater event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-28 08:00:00', 12, 4, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(106, 'Laval Back-to-School Fundraiser', 'Laval Back-to-School Fundraiser is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-09-29 20:30:00', 9, 5, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(107, 'Toronto Coffee and Chocolate Fair', 'Toronto Coffee and Chocolate Fair is a festival event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-01 09:00:00', 7, 6, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(108, 'Laval Sitcom Writers Live', 'Laval Sitcom Writers Live is a comedy event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-02 21:30:00', 2, 7, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(109, 'Montreal Afrobeats Block Party', 'Montreal Afrobeats Block Party is a concert event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-04 06:30:00', 1, 1, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(110, 'Toronto Fantasy Saga Marathon', 'Toronto Fantasy Saga Marathon is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-05 19:00:00', 10, 2, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(111, 'Laval College Football Kickoff', 'Laval College Football Kickoff is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-07 07:30:00', 2, 3, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(112, 'Montreal Stage Combat Showcase', 'Montreal Stage Combat Showcase is a theater event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-08 20:00:00', 4, 4, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(113, 'Toronto Pet Rescue Prize Night', 'Toronto Pet Rescue Prize Night is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-10 08:30:00', 10, 5, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(114, 'Montreal Holiday Market Preview', 'Montreal Holiday Market Preview is a festival event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-11 21:00:00', 6, 6, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(115, 'Laval Character Comedy Showcase', 'Laval Character Comedy Showcase is a comedy event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-13 09:30:00', 8, 7, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(116, 'Toronto Metal Thunder Night', 'Toronto Metal Thunder Night is a concert event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-14 18:30:00', 7, 1, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(117, 'Montreal Superhero Fan Screening', 'Montreal Superhero Fan Screening is a movie event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-16 07:00:00', 12, 2, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(118, 'Laval Basketball Skills Challenge', 'Laval Basketball Skills Challenge is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-17 19:30:00', 2, 3, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(119, 'Toronto Children’s Theater Day', 'Toronto Children’s Theater Day is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-19 08:00:00', 10, 4, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(120, 'Montreal Art Lovers Silent Draw', 'Montreal Art Lovers Silent Draw is a raffle event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-20 20:30:00', 12, 5, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(121, 'Laval Taco and Salsa Fest', 'Laval Taco and Salsa Fest is a festival event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-22 09:00:00', 9, 6, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(122, 'Toronto Comedy Brunch Club', 'Toronto Comedy Brunch Club is a comedy event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-23 21:30:00', 3, 7, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(123, 'Montreal Piano Candlelight Evening', 'Montreal Piano Candlelight Evening is a concert event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-25 06:30:00', 4, 1, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(124, 'Laval Oscar Favorites Replay', 'Laval Oscar Favorites Replay is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-26 19:00:00', 8, 2, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(125, 'Toronto Pro Wrestling Fan Fest', 'Toronto Pro Wrestling Fan Fest is a sports event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-28 07:30:00', 3, 3, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(126, 'Montreal Cabaret Theater Night', 'Montreal Cabaret Theater Night is a theater event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-29 20:00:00', 12, 4, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(127, 'Laval Family Fun Prize Pack', 'Laval Family Fun Prize Pack is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-10-31 08:30:00', 9, 5, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(128, 'Toronto Food Truck Rally', 'Toronto Food Truck Rally is a festival event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-01 21:00:00', 10, 6, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(129, 'Montreal Stand-Up Under Lights', 'Montreal Stand-Up Under Lights is a comedy event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-03 09:30:00', 12, 7, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(130, 'Laval Disco Throwback Party', 'Laval Disco Throwback Party is a concert event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-04 18:30:00', 2, 1, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(131, 'Toronto Drive-In Movie Night', 'Toronto Drive-In Movie Night is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-06 07:00:00', 10, 2, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(132, 'Montreal Championship Fight Night', 'Montreal Championship Fight Night is a sports event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-07 19:30:00', 1, 3, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(133, 'Laval Dance Theater Fusion', 'Laval Dance Theater Fusion is a theater event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-09 08:00:00', 8, 4, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(134, 'Toronto Wellness Basket Raffle', 'Toronto Wellness Basket Raffle is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-10 20:30:00', 10, 5, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(135, 'Montreal Garden Flower Festival', 'Montreal Garden Flower Festival is a festival event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-12 09:00:00', 12, 6, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(136, 'Laval Two-Person Improv Night', 'Laval Two-Person Improv Night is a comedy event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-13 21:30:00', 2, 7, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(137, 'Toronto Hip-Hop Cypher Live', 'Toronto Hip-Hop Cypher Live is a concert event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-15 06:30:00', 10, 1, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80');
INSERT INTO `events` (`id`, `title`, `description`, `date`, `venue_id`, `category_id`, `event_image`) VALUES
(138, 'Laval Local Filmmaker Night', 'Laval Local Filmmaker Night is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-16 19:00:00', 8, 2, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(139, 'Montreal Marathon Finish Festival', 'Montreal Marathon Finish Festival is a sports event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-18 07:30:00', 6, 3, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(140, 'Toronto Historical Drama Night', 'Toronto Historical Drama Night is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-19 20:00:00', 10, 4, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(141, 'Laval Festival Pass Giveaway', 'Laval Festival Pass Giveaway is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-21 08:30:00', 9, 5, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(142, 'Montreal Book and Zine Fair', 'Montreal Book and Zine Fair is a festival event in Montreal, QC, Canada at Olympic Stadium. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-22 21:00:00', 6, 6, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(143, 'Toronto Dark Humor Showcase', 'Toronto Dark Humor Showcase is a comedy event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-24 09:30:00', 10, 7, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(144, 'Montreal Salsa Night Express', 'Montreal Salsa Night Express is a concert event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-25 18:30:00', 1, 1, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(145, 'Laval Adventure Film Weekend', 'Laval Adventure Film Weekend is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-27 07:00:00', 8, 2, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(146, 'Toronto Beach Volleyball Bash', 'Toronto Beach Volleyball Bash is a sports event in Toronto, ON, Canada at Rogers Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-28 19:30:00', 5, 3, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(147, 'Montreal Playwrights Lab Live', 'Montreal Playwrights Lab Live is a theater event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-11-30 08:00:00', 4, 4, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(148, 'Laval Cash Prize Community Draw', 'Laval Cash Prize Community Draw is a raffle event in Laval, QC, Canada at Palace Convention Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-01 20:30:00', 9, 5, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(149, 'Toronto Lantern Festival Walk', 'Toronto Lantern Festival Walk is a festival event in Toronto, ON, Canada at RBC Amphitheatre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-03 09:00:00', 7, 6, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(150, 'Montreal Bilingual Comedy Night', 'Montreal Bilingual Comedy Night is a comedy event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-04 21:30:00', 4, 7, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(151, 'Laval EDM Glow Festival', 'Laval EDM Glow Festival is a concert event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-06 06:30:00', 8, 1, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(152, 'Toronto Comedy Movie Rewind', 'Toronto Comedy Movie Rewind is a movie event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-07 19:00:00', 10, 2, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80'),
(153, 'Montreal Esports Arena Finals', 'Montreal Esports Arena Finals is a sports event in Montreal, QC, Canada at Bell Centre. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-09 07:30:00', 1, 3, 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80'),
(154, 'Laval Theater Tech Open House', 'Laval Theater Tech Open House is a theater event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-10 20:00:00', 8, 4, 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80'),
(155, 'Toronto Charity Car Seat Draw', 'Toronto Charity Car Seat Draw is a raffle event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-12 08:30:00', 10, 5, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80'),
(156, 'Montreal International Music Fair', 'Montreal International Music Fair is a festival event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-13 21:00:00', 12, 6, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80'),
(157, 'Laval Women in Comedy Night', 'Laval Women in Comedy Night is a comedy event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-15 09:30:00', 8, 7, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80'),
(158, 'Toronto Aurora Pop Night Encore 2', 'Toronto Aurora Pop Night Encore 2 is a concert event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-16 18:30:00', 3, 1, 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80'),
(159, 'Montreal Crime Drama Premiere', 'Montreal Crime Drama Premiere is a movie event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-18 07:00:00', 4, 2, 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80'),
(160, 'Laval Curling Cup Night', 'Laval Curling Cup Night is a sports event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-19 19:30:00', 2, 3, 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80'),
(161, 'Toronto Monologue Championship', 'Toronto Monologue Championship is a theater event in Toronto, ON, Canada at Meridian Hall. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-21 08:00:00', 10, 4, 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80'),
(162, 'Montreal Coffee Lovers Giveaway', 'Montreal Coffee Lovers Giveaway is a raffle event in Montreal, QC, Canada at Palais des congrès de Montréal. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-22 20:30:00', 12, 5, 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80'),
(163, 'Laval Urban Design Festival', 'Laval Urban Design Festival is a festival event in Laval, QC, Canada at Place Bell. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-24 09:00:00', 2, 6, 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80'),
(164, 'Toronto Comedy and Magic Mix', 'Toronto Comedy and Magic Mix is a comedy event in Toronto, ON, Canada at Scotiabank Arena. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-25 21:30:00', 3, 7, 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80'),
(165, 'Montreal Downtown Indie Bash Encore 2', 'Montreal Downtown Indie Bash Encore 2 is a concert event in Montreal, QC, Canada at MTELUS. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-27 06:30:00', 4, 1, 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1200&q=80'),
(166, 'Laval Holiday Movie Special', 'Laval Holiday Movie Special is a movie event in Laval, QC, Canada at Salle André-Mathieu. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-28 19:00:00', 8, 2, 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?auto=format&fit=crop&w=1200&q=80'),
(167, 'Toronto City Hoops Showcase Encore 2', 'Toronto City Hoops Showcase Encore 2 is a sports event in Toronto, ON, Canada at Coca-Cola Coliseum. This Ticketmaestrix listing uses a real Canadian venue, a Canadian address, and a working Unsplash image.', '2026-12-30 07:30:00', 11, 3, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `total_price` double NOT NULL,
  `status` tinyint(1) NOT NULL,
  `order_time` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `points_earned` int(11) DEFAULT 0,
  `points_spent` int(11) DEFAULT 0,
  `stripe_session_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `total_price`, `status`, `order_time`, `user_id`, `points_earned`, `points_spent`, `stripe_session_id`) VALUES
(1, 179.98, 1, '2026-04-10 14:30:00', 2, 0, 0, NULL),
(2, 299.99, 1, '2026-04-11 09:15:00', 3, 0, 0, NULL),
(3, 359.98, 1, '2026-04-11 16:45:00', 2, 0, 0, NULL),
(4, 25, 0, '2026-04-12 11:00:00', 4, 0, 0, NULL),
(5, 130, 1, '2026-04-12 18:20:00', 5, 0, 0, NULL),
(6, 89.99, 0, '2026-04-13 08:00:00', 3, 0, 0, NULL),
(12, 45, 1, '2026-05-14 04:18:20', 12, 4, 0, 'cs_test_a14jbqoIybc80DizOSiuv615rPLnjnC3fNsqECrftp2wOW5x6qTHPKJRzU'),
(13, 60.99, 1, '2026-05-14 04:21:24', 13, 6, 0, 'cs_test_b1EupUMu4ujfIsTwUob1Mt0gugWUtBp9q3nPK0ymqJ8k8T6D7ewNajfBtv'),
(14, 15.99, 1, '2026-05-14 04:45:02', 12, 1, 0, 'cs_test_a1IwGAAPr30rLiKAwIjar7KIXCAtHjcXwJLinkfO0QzMXCh48cKbB8Fpn8'),
(15, 0, 1, '2026-05-14 05:04:25', 13, 0, 0, ''),
(16, 74.69, 1, '2026-05-14 05:04:54', 12, 6, 5, 'cs_test_b1NDF4FbRH9PoHvvHV6oqFOgvrH7q3qIibk3pSKE7ZTnvetKervfwwrhi8'),
(17, 1126.98, 1, '2026-05-14 05:09:09', 12, 97, 0, 'cs_test_b1sf8GZsc6kKjddQDgbNqC7azjwVox4mepSnUZJlFM7G1XlazmK9Acpx4v'),
(18, 1322.48, 1, '2026-05-14 05:24:08', 12, 229, 0, 'cs_test_b1Gpbzti5mWmT9Y6LrDlFRtqFinRKX3hUUgNMWK0mG4XNBwXesdHJgPSw4'),
(19, 579.59, 1, '2026-05-14 05:25:32', 12, 100, 0, 'cs_test_b1WSzektqU2j28L9oXPHnxF0MJQSl3dO842FV4vZWQ85XdEMAEcDEZCRq7'),
(20, 48.3, 1, '2026-05-14 06:22:28', 14, 8, 0, 'cs_test_b1B3dsOF2AtUus3JpksRW8UMtKnTOCShYIWQm8jTStfSFF29iCfDuyCrEC'),
(21, 241.5, 1, '2026-05-14 18:10:43', 15, 42, 0, 'cs_test_b1qYVFl9VMpIWmvoRXzA1yWIJB89nXCKZkGDbSdaUEl4LQ4Cddt8ama4oi');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `quantity`, `order_id`, `ticket_id`) VALUES
(1, 1, 1, 1),
(2, 1, 1, 2),
(3, 1, 2, 28),
(4, 1, 3, 42),
(5, 1, 3, 43),
(6, 1, 4, 51),
(7, 1, 5, 59),
(8, 1, 5, 60),
(9, 1, 6, 3),
(10, 1, 12, 8),
(11, 1, 13, 19),
(12, 1, 13, 7),
(13, 1, 14, 21),
(14, 1, 15, 69),
(15, 1, 16, 10),
(16, 1, 17, 41),
(17, 1, 17, 6),
(18, 1, 17, 15),
(19, 1, 17, 46),
(20, 2, 17, 68),
(21, 1, 17, 55),
(22, 1, 17, 36),
(23, 1, 18, 45),
(24, 1, 18, 31),
(25, 1, 18, 40),
(26, 1, 18, 29),
(27, 1, 19, 5),
(28, 1, 19, 9),
(29, 1, 19, 14),
(30, 1, 19, 18),
(31, 1, 19, 38),
(32, 1, 19, 65),
(33, 1, 20, 93),
(34, 1, 21, 95),
(35, 1, 21, 75);

-- --------------------------------------------------------

--
-- Table structure for table `points_history`
--

CREATE TABLE `points_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `points_history`
--

INSERT INTO `points_history` (`id`, `user_id`, `order_id`, `amount`, `description`, `created_at`) VALUES
(1, 12, 12, 4, 'Earned 4 points (10%) from order #12', '2026-05-14 04:18:20'),
(2, 13, 13, 6, 'Earned 6 points (10%) from order #13', '2026-05-14 04:21:24'),
(3, 12, 14, 1, 'Earned 1 points (10%) from order #14', '2026-05-14 04:45:02'),
(4, 13, 15, 0, 'Earned 0 points (10%) from order #15', '2026-05-14 05:04:25'),
(5, 12, 16, -5, 'Spent 5 points on order #16', '2026-05-14 05:04:54'),
(6, 12, 16, 6, 'Earned 6 points (10%) from order #16', '2026-05-14 05:04:54'),
(7, 12, 17, 97, 'Earned 97 points (10%) from order #17', '2026-05-14 05:09:09'),
(8, 12, 18, 229, 'Earned 229 points (10%) from order #18', '2026-05-14 05:24:08'),
(9, 12, 19, 100, 'Earned 100 points (10%) from order #19', '2026-05-14 05:25:32'),
(10, 14, 20, 8, 'Earned 8 points (10%) from order #20', '2026-05-14 06:22:28'),
(11, 15, 21, 42, 'Earned 42 points (10%) from order #21', '2026-05-14 18:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `stripepending`
--

CREATE TABLE `stripepending` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `cart_json` longtext NOT NULL,
  `points_to_use` int(11) NOT NULL DEFAULT 0,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL,
  `stripe_session_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stripepending`
--

INSERT INTO `stripepending` (`id`, `user_id`, `cart_json`, `points_to_use`, `total`, `created_at`, `stripe_session_id`) VALUES
(1, 11, '[{\"ticket_id\":20,\"name\":\"The Midnight Chronicles\",\"image\":\"https:\\/\\/images.unsplash.com\\/photo-1440404653325-ab127d49abc1?w=800\",\"date\":\"2026-05-15 21:00:00\",\"price\":15.9900000000000002131628207280300557613372802734375,\"original_price\":null,\"on_sale\":false,\"quantity\":1,\"total\":15.9900000000000002131628207280300557613372802734375}]', 0, 15.99, '2026-05-14 02:47:18', 'cs_test_a1jleVnFjyjgucegd89IS2QYEtxpyFhF8uuaV4Py1CYrnA0xQZgKsfYNg1'),
(14, 13, '[{\"ticket_id\":20,\"name\":\"The Midnight Chronicles\",\"image\":\"https:\\/\\/images.unsplash.com\\/photo-1440404653325-ab127d49abc1?w=800\",\"date\":\"2026-05-15 21:00:00\",\"price\":15.9900000000000002131628207280300557613372802734375,\"original_price\":null,\"on_sale\":false,\"quantity\":1,\"total\":15.9900000000000002131628207280300557613372802734375}]', 0, 15.99, '2026-05-14 04:59:37', 'cs_test_a1hCFJxUqoLm9YqdSJWDKUxOKEbWwpAOPcZD6hK8Om1fTd6WRuYMYAuQrb'),
(15, 13, '[{\"ticket_id\":22,\"name\":\"The Midnight Chronicles\",\"image\":\"https:\\/\\/images.unsplash.com\\/photo-1440404653325-ab127d49abc1?w=800\",\"date\":\"2026-05-15 21:00:00\",\"price\":15.9900000000000002131628207280300557613372802734375,\"original_price\":null,\"on_sale\":false,\"quantity\":1,\"total\":15.9900000000000002131628207280300557613372802734375}]', 0, 15.99, '2026-05-14 04:59:52', 'cs_test_a1Wa9suPjF2ZLvHT5diFFUJvFWh1snk03EGRKYtGxLfBAJF4pCa47ZE8MX'),
(16, 13, '[{\"ticket_id\":22,\"name\":\"The Midnight Chronicles\",\"image\":\"https:\\/\\/images.unsplash.com\\/photo-1440404653325-ab127d49abc1?w=800\",\"date\":\"2026-05-15 21:00:00\",\"price\":15.9900000000000002131628207280300557613372802734375,\"original_price\":null,\"on_sale\":false,\"quantity\":1,\"total\":15.9900000000000002131628207280300557613372802734375}]', 0, 15.99, '2026-05-14 04:59:58', 'cs_test_a17nLzvW3sRBVZ5fGR86H4887tMjvHtj45FTc1hXTaODk11Cey6A2bO9Cd'),
(17, 13, '[{\"ticket_id\":44,\"name\":\"Hamilton - Broadway Musical\",\"image\":\"https:\\/\\/images.unsplash.com\\/photo-1507676184212-d03ab07a01bf?w=800\",\"date\":\"2026-05-20 19:00:00\",\"price\":179.990000000000009094947017729282379150390625,\"original_price\":null,\"on_sale\":false,\"quantity\":1,\"total\":179.990000000000009094947017729282379150390625}]', 0, 179.99, '2026-05-14 05:02:00', 'cs_test_a1PRplY8qj4VMGZr3WXLRBuDdRc7F3Y50dY952GzKtDUrVe4iT84OriVaf'),
(19, 13, '[{\"ticket_id\":45,\"name\":\"Hamilton - Broadway Musical\",\"image\":\"https:\\/\\/images.unsplash.com\\/photo-1507676184212-d03ab07a01bf?w=800\",\"date\":\"2026-05-20 19:00:00\",\"price\":249.990000000000009094947017729282379150390625,\"original_price\":null,\"on_sale\":false,\"quantity\":1,\"total\":249.990000000000009094947017729282379150390625}]', 0, 287.49, '2026-05-14 05:04:38', 'cs_test_b1wNSCwxRCGLpb8OlpMgdXajjKSKyAseUFhEkdwmYKZVK0lGi0G7C6JlZt');

-- --------------------------------------------------------

--
-- Table structure for table `tfatoken`
--

CREATE TABLE `tfatoken` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `token_hash` varchar(191) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `tfatoken`
--

INSERT INTO `tfatoken` (`id`, `user_id`, `token_hash`, `expires_at`) VALUES
(12, 11, '2e9adf602ecf922b69110ad3671cb4759306f810ec0d5e8aa36b51cb14634727', '2026-06-13 02:46:58'),
(17, 10, '908ad333f5d6d5ee3869127e79582b978992f0a5c9de6ed059c9e423daa21b9a', '2026-06-13 04:30:55'),
(18, 6, '151271686efb8cba583f8a0875d74c56e70221e995d24c19552caf44228e0dbe', '2026-06-13 04:31:03'),
(20, 9, 'bd48c64cadc037ccc627b2b268d39e216d1e3737cb1a0f788d69f2b036dfbc6c', '2026-06-13 04:43:43'),
(21, 13, 'ed91e9fe005bb2d968635a59db1673ef0547ab53706c40ba9e0eb263aa243bd2', '2026-06-13 04:45:48'),
(22, 12, 'b4e27e0c4ddf1e1ff88ade23d911d65a1f60058b07c4dedae25026ed2311e0f1', '2026-06-13 05:22:15'),
(23, 14, 'adc37d70f34a08e3898dc6cd1d8d002f2f9871e5c3b7dde20efef61b94095575', '2026-06-13 06:21:30'),
(24, 15, 'b9aecafff218cef839f52d286fec4c8e54dadabbfd3d88e2a480b8500c67dd31', '2026-06-13 18:09:48'),
(25, 16, '33b5558d90e29ef6612e33172012d47bfe054dd910724fdc3519fea51e82aa1f', '2026-06-13 19:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE `ticket` (
  `id` int(11) NOT NULL,
  `price` double NOT NULL,
  `seat` text NOT NULL,
  `row` text NOT NULL,
  `event_id` int(11) NOT NULL,
  `sale_type` varchar(191) DEFAULT NULL,
  `sale_amount` decimal(10,2) DEFAULT NULL,
  `sale_start` varchar(191) DEFAULT NULL,
  `sale_end` varchar(191) DEFAULT NULL,
  `sold` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket`
--

INSERT INTO `ticket` (`id`, `price`, `seat`, `row`, `event_id`, `sale_type`, `sale_amount`, `sale_start`, `sale_end`, `sold`) VALUES
(1, 89.99, '1', 'GA', 1, NULL, NULL, NULL, NULL, 1),
(2, 89.99, '2', 'GA', 1, NULL, NULL, NULL, NULL, 1),
(3, 89.99, '3', 'GA', 1, NULL, NULL, NULL, NULL, 0),
(4, 89.99, '4', 'GA', 1, NULL, NULL, NULL, NULL, 0),
(5, 149.99, '1', 'VIP', 1, NULL, NULL, NULL, NULL, 1),
(6, 149.99, '2', 'VIP', 1, NULL, NULL, NULL, NULL, 1),
(7, 45, '1', 'A', 2, NULL, NULL, NULL, NULL, 1),
(8, 45, '2', 'A', 2, NULL, NULL, NULL, NULL, 1),
(9, 45, '3', 'A', 2, NULL, NULL, NULL, NULL, 1),
(10, 65, '1', 'VIP', 2, NULL, NULL, NULL, NULL, 1),
(11, 75, '1', 'A', 3, NULL, NULL, NULL, NULL, 0),
(12, 75, '2', 'A', 3, NULL, NULL, NULL, NULL, 0),
(13, 75, '3', 'A', 3, NULL, NULL, NULL, NULL, 0),
(14, 120, '1', 'VIP', 3, NULL, NULL, NULL, NULL, 1),
(15, 120, '2', 'VIP', 3, NULL, NULL, NULL, NULL, 1),
(16, 55, '1', 'GA', 4, NULL, NULL, NULL, NULL, 0),
(17, 55, '2', 'GA', 4, NULL, NULL, NULL, NULL, 0),
(18, 99, '1', 'VIP', 4, NULL, NULL, NULL, NULL, 1),
(19, 15.99, '1', 'A', 5, NULL, NULL, NULL, NULL, 1),
(20, 15.99, '2', 'A', 5, NULL, NULL, NULL, NULL, 0),
(21, 15.99, '3', 'A', 5, NULL, NULL, NULL, NULL, 1),
(22, 15.99, '4', 'A', 5, NULL, NULL, NULL, NULL, 0),
(23, 22.99, '1', 'B', 5, NULL, NULL, NULL, NULL, 0),
(24, 22.99, '2', 'B', 5, NULL, NULL, NULL, NULL, 0),
(25, 12, '1', 'A', 6, NULL, NULL, NULL, NULL, 0),
(26, 12, '2', 'A', 6, NULL, NULL, NULL, NULL, 0),
(27, 12, '3', 'A', 6, NULL, NULL, NULL, NULL, 0),
(28, 299.99, '1', 'A', 7, NULL, NULL, NULL, NULL, 1),
(29, 299.99, '2', 'A', 7, NULL, NULL, NULL, NULL, 1),
(30, 450, '1', 'VIP', 7, NULL, NULL, NULL, NULL, 0),
(31, 450, '2', 'VIP', 7, NULL, NULL, NULL, NULL, 1),
(32, 199.99, '1', 'B', 7, NULL, NULL, NULL, NULL, 0),
(33, 199.99, '2', 'B', 7, NULL, NULL, NULL, NULL, 0),
(34, 85, '1', 'A', 8, NULL, NULL, NULL, NULL, 0),
(35, 85, '2', 'A', 8, NULL, NULL, NULL, NULL, 0),
(36, 85, '3', 'A', 8, NULL, NULL, NULL, NULL, 1),
(37, 55, '1', 'B', 8, NULL, NULL, NULL, NULL, 0),
(38, 55, '2', 'B', 8, NULL, NULL, NULL, NULL, 1),
(39, 150, '1', 'A', 9, NULL, NULL, NULL, NULL, 0),
(40, 150, '2', 'A', 9, NULL, NULL, NULL, NULL, 1),
(41, 250, '1', 'VIP', 9, NULL, NULL, NULL, NULL, 1),
(42, 179.99, '1', 'A', 10, NULL, NULL, NULL, NULL, 1),
(43, 179.99, '2', 'A', 10, NULL, NULL, NULL, NULL, 1),
(44, 179.99, '3', 'A', 10, NULL, NULL, NULL, NULL, 0),
(45, 249.99, '1', 'VIP', 10, NULL, NULL, NULL, NULL, 1),
(46, 249.99, '2', 'VIP', 10, NULL, NULL, NULL, NULL, 1),
(47, 0, '1', 'GA', 11, NULL, NULL, NULL, NULL, 0),
(48, 0, '2', 'GA', 11, NULL, NULL, NULL, NULL, 0),
(49, 0, '3', 'GA', 11, NULL, NULL, NULL, NULL, 0),
(50, 0, '4', 'GA', 11, NULL, NULL, NULL, NULL, 0),
(51, 25, '1', 'GA', 12, NULL, NULL, NULL, NULL, 0),
(52, 25, '2', 'GA', 12, NULL, NULL, NULL, NULL, 0),
(53, 25, '3', 'GA', 12, NULL, NULL, NULL, NULL, 0),
(54, 25, '4', 'GA', 12, NULL, NULL, NULL, NULL, 0),
(55, 25, '5', 'GA', 12, NULL, NULL, NULL, NULL, 1),
(56, 10, '1', 'GA', 13, NULL, NULL, NULL, NULL, 0),
(57, 10, '2', 'GA', 13, NULL, NULL, NULL, NULL, 0),
(58, 10, '3', 'GA', 13, NULL, NULL, NULL, NULL, 0),
(59, 65, '1', 'GA', 14, NULL, NULL, NULL, NULL, 1),
(60, 65, '2', 'GA', 14, NULL, NULL, NULL, NULL, 1),
(61, 95, '1', 'VIP', 14, NULL, NULL, NULL, NULL, 0),
(62, 40, '1', 'GA', 15, NULL, NULL, NULL, NULL, 0),
(63, 40, '2', 'GA', 15, NULL, NULL, NULL, NULL, 0),
(64, 70, '1', 'VIP', 15, NULL, NULL, NULL, NULL, 0),
(65, 35, '1', 'A', 16, NULL, NULL, NULL, NULL, 1),
(66, 35, '2', 'A', 16, NULL, NULL, NULL, NULL, 0),
(67, 35, '3', 'A', 16, NULL, NULL, NULL, NULL, 0),
(68, 50, '1', 'VIP', 16, NULL, NULL, NULL, NULL, 1),
(69, 0.01, 'Standing', 'General Admission', 17, 'fixed', 0.01, '2026-05-13T14:16', '2026-05-17T14:00', 1),
(71, 0, 'Standing', 'General Admission', 17, NULL, NULL, NULL, NULL, 0),
(72, 45, '1', 'GA', 18, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(73, 65, '1', 'A', 18, NULL, NULL, NULL, NULL, 0),
(74, 85, '1', 'B', 18, NULL, NULL, NULL, NULL, 0),
(75, 125, '1', 'VIP', 18, NULL, NULL, NULL, NULL, 1),
(76, 14.99, '2', 'GA', 19, NULL, NULL, NULL, NULL, 0),
(77, 18.99, '2', 'A', 19, NULL, NULL, NULL, NULL, 0),
(78, 24.99, '2', 'B', 19, NULL, NULL, NULL, NULL, 0),
(79, 34.99, '2', 'VIP', 19, NULL, NULL, NULL, NULL, 0),
(80, 55, '3', 'GA', 20, NULL, NULL, NULL, NULL, 0),
(81, 85, '3', 'A', 20, NULL, NULL, NULL, NULL, 0),
(82, 125, '3', 'B', 20, NULL, NULL, NULL, NULL, 0),
(83, 180, '3', 'VIP', 20, NULL, NULL, NULL, NULL, 0),
(84, 35, '4', 'GA', 21, NULL, NULL, NULL, NULL, 0),
(85, 55, '4', 'A', 21, NULL, NULL, NULL, NULL, 0),
(86, 75, '4', 'B', 21, NULL, NULL, NULL, NULL, 0),
(87, 110, '4', 'VIP', 21, NULL, NULL, NULL, NULL, 0),
(88, 10, '5', 'GA', 22, NULL, NULL, NULL, NULL, 0),
(89, 20, '5', 'A', 22, NULL, NULL, NULL, NULL, 0),
(90, 35, '5', 'B', 22, NULL, NULL, NULL, NULL, 0),
(91, 50, '5', 'VIP', 22, NULL, NULL, NULL, NULL, 0),
(92, 28, '6', 'GA', 23, NULL, NULL, NULL, NULL, 0),
(93, 42, '6', 'A', 23, NULL, NULL, NULL, NULL, 1),
(94, 60, '6', 'B', 23, NULL, NULL, NULL, NULL, 0),
(95, 90, '6', 'VIP', 23, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 1),
(96, 25, '7', 'GA', 24, NULL, NULL, NULL, NULL, 0),
(97, 35, '7', 'A', 24, NULL, NULL, NULL, NULL, 0),
(98, 45, '7', 'B', 24, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(99, 65, '7', 'VIP', 24, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(100, 45, '8', 'GA', 25, NULL, NULL, NULL, NULL, 0),
(101, 65, '8', 'A', 25, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(102, 85, '8', 'B', 25, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(103, 125, '8', 'VIP', 25, NULL, NULL, NULL, NULL, 0),
(104, 14.99, '9', 'GA', 26, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(105, 18.99, '9', 'A', 26, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(106, 24.99, '9', 'B', 26, NULL, NULL, NULL, NULL, 0),
(107, 34.99, '9', 'VIP', 26, NULL, NULL, NULL, NULL, 0),
(108, 55, '10', 'GA', 27, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(109, 85, '10', 'A', 27, NULL, NULL, NULL, NULL, 0),
(110, 125, '10', 'B', 27, NULL, NULL, NULL, NULL, 0),
(111, 180, '10', 'VIP', 27, NULL, NULL, NULL, NULL, 0),
(112, 35, '11', 'GA', 28, NULL, NULL, NULL, NULL, 0),
(113, 55, '11', 'A', 28, NULL, NULL, NULL, NULL, 0),
(114, 75, '11', 'B', 28, NULL, NULL, NULL, NULL, 0),
(115, 110, '11', 'VIP', 28, NULL, NULL, NULL, NULL, 0),
(116, 10, '12', 'GA', 29, NULL, NULL, NULL, NULL, 0),
(117, 20, '12', 'A', 29, NULL, NULL, NULL, NULL, 0),
(118, 35, '12', 'B', 29, NULL, NULL, NULL, NULL, 0),
(119, 50, '12', 'VIP', 29, NULL, NULL, NULL, NULL, 0),
(120, 28, '13', 'GA', 30, NULL, NULL, NULL, NULL, 0),
(121, 42, '13', 'A', 30, NULL, NULL, NULL, NULL, 0),
(122, 60, '13', 'B', 30, NULL, NULL, NULL, NULL, 0),
(123, 90, '13', 'VIP', 30, NULL, NULL, NULL, NULL, 0),
(124, 25, '14', 'GA', 31, NULL, NULL, NULL, NULL, 0),
(125, 35, '14', 'A', 31, NULL, NULL, NULL, NULL, 0),
(126, 45, '14', 'B', 31, NULL, NULL, NULL, NULL, 0),
(127, 65, '14', 'VIP', 31, NULL, NULL, NULL, NULL, 0),
(128, 45, '15', 'GA', 32, NULL, NULL, NULL, NULL, 0),
(129, 65, '15', 'A', 32, NULL, NULL, NULL, NULL, 0),
(130, 85, '15', 'B', 32, NULL, NULL, NULL, NULL, 0),
(131, 125, '15', 'VIP', 32, NULL, NULL, NULL, NULL, 0),
(132, 14.99, '16', 'GA', 33, NULL, NULL, NULL, NULL, 0),
(133, 18.99, '16', 'A', 33, NULL, NULL, NULL, NULL, 0),
(134, 24.99, '16', 'B', 33, NULL, NULL, NULL, NULL, 0),
(135, 34.99, '16', 'VIP', 33, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(136, 55, '17', 'GA', 34, NULL, NULL, NULL, NULL, 0),
(137, 85, '17', 'A', 34, NULL, NULL, NULL, NULL, 0),
(138, 125, '17', 'B', 34, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(139, 180, '17', 'VIP', 34, NULL, NULL, NULL, NULL, 0),
(140, 35, '18', 'GA', 35, NULL, NULL, NULL, NULL, 0),
(141, 55, '18', 'A', 35, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(142, 75, '18', 'B', 35, NULL, NULL, NULL, NULL, 0),
(143, 110, '18', 'VIP', 35, NULL, NULL, NULL, NULL, 0),
(144, 10, '19', 'GA', 36, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(145, 20, '19', 'A', 36, NULL, NULL, NULL, NULL, 0),
(146, 35, '19', 'B', 36, NULL, NULL, NULL, NULL, 0),
(147, 50, '19', 'VIP', 36, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(148, 28, '20', 'GA', 37, NULL, NULL, NULL, NULL, 0),
(149, 42, '20', 'A', 37, NULL, NULL, NULL, NULL, 0),
(150, 60, '20', 'B', 37, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(151, 90, '20', 'VIP', 37, NULL, NULL, NULL, NULL, 0),
(152, 25, '21', 'GA', 38, NULL, NULL, NULL, NULL, 0),
(153, 35, '21', 'A', 38, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(154, 45, '21', 'B', 38, NULL, NULL, NULL, NULL, 0),
(155, 65, '21', 'VIP', 38, NULL, NULL, NULL, NULL, 0),
(156, 45, '22', 'GA', 39, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(157, 65, '22', 'A', 39, NULL, NULL, NULL, NULL, 0),
(158, 85, '22', 'B', 39, NULL, NULL, NULL, NULL, 0),
(159, 125, '22', 'VIP', 39, NULL, NULL, NULL, NULL, 0),
(160, 14.99, '23', 'GA', 40, NULL, NULL, NULL, NULL, 0),
(161, 18.99, '23', 'A', 40, NULL, NULL, NULL, NULL, 0),
(162, 24.99, '23', 'B', 40, NULL, NULL, NULL, NULL, 0),
(163, 34.99, '23', 'VIP', 40, NULL, NULL, NULL, NULL, 0),
(164, 55, '24', 'GA', 41, NULL, NULL, NULL, NULL, 0),
(165, 85, '24', 'A', 41, NULL, NULL, NULL, NULL, 0),
(166, 125, '24', 'B', 41, NULL, NULL, NULL, NULL, 0),
(167, 180, '24', 'VIP', 41, NULL, NULL, NULL, NULL, 0),
(168, 35, '25', 'GA', 42, NULL, NULL, NULL, NULL, 0),
(169, 55, '25', 'A', 42, NULL, NULL, NULL, NULL, 0),
(170, 75, '25', 'B', 42, NULL, NULL, NULL, NULL, 0),
(171, 110, '25', 'VIP', 42, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(172, 10, '26', 'GA', 43, NULL, NULL, NULL, NULL, 0),
(173, 20, '26', 'A', 43, NULL, NULL, NULL, NULL, 0),
(174, 35, '26', 'B', 43, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(175, 50, '26', 'VIP', 43, NULL, NULL, NULL, NULL, 0),
(176, 28, '27', 'GA', 44, NULL, NULL, NULL, NULL, 0),
(177, 42, '27', 'A', 44, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(178, 60, '27', 'B', 44, NULL, NULL, NULL, NULL, 0),
(179, 90, '27', 'VIP', 44, NULL, NULL, NULL, NULL, 0),
(180, 25, '28', 'GA', 45, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(181, 35, '28', 'A', 45, NULL, NULL, NULL, NULL, 0),
(182, 45, '28', 'B', 45, NULL, NULL, NULL, NULL, 0),
(183, 65, '28', 'VIP', 45, NULL, NULL, NULL, NULL, 0),
(184, 45, '29', 'GA', 46, NULL, NULL, NULL, NULL, 0),
(185, 65, '29', 'A', 46, NULL, NULL, NULL, NULL, 0),
(186, 85, '29', 'B', 46, NULL, NULL, NULL, NULL, 0),
(187, 125, '29', 'VIP', 46, NULL, NULL, NULL, NULL, 0),
(188, 14.99, '30', 'GA', 47, NULL, NULL, NULL, NULL, 0),
(189, 18.99, '30', 'A', 47, NULL, NULL, NULL, NULL, 0),
(190, 24.99, '30', 'B', 47, NULL, NULL, NULL, NULL, 0),
(191, 34.99, '30', 'VIP', 47, NULL, NULL, NULL, NULL, 0),
(192, 55, '31', 'GA', 48, NULL, NULL, NULL, NULL, 0),
(193, 85, '31', 'A', 48, NULL, NULL, NULL, NULL, 0),
(194, 125, '31', 'B', 48, NULL, NULL, NULL, NULL, 0),
(195, 180, '31', 'VIP', 48, NULL, NULL, NULL, NULL, 0),
(196, 35, '32', 'GA', 49, NULL, NULL, NULL, NULL, 0),
(197, 55, '32', 'A', 49, NULL, NULL, NULL, NULL, 0),
(198, 75, '32', 'B', 49, NULL, NULL, NULL, NULL, 0),
(199, 110, '32', 'VIP', 49, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(200, 10, '33', 'GA', 50, NULL, NULL, NULL, NULL, 0),
(201, 20, '33', 'A', 50, NULL, NULL, NULL, NULL, 0),
(202, 35, '33', 'B', 50, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(203, 50, '33', 'VIP', 50, NULL, NULL, NULL, NULL, 0),
(204, 28, '34', 'GA', 51, NULL, NULL, NULL, NULL, 0),
(205, 42, '34', 'A', 51, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(206, 60, '34', 'B', 51, NULL, NULL, NULL, NULL, 0),
(207, 90, '34', 'VIP', 51, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(208, 25, '35', 'GA', 52, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(209, 35, '35', 'A', 52, NULL, NULL, NULL, NULL, 0),
(210, 45, '35', 'B', 52, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(211, 65, '35', 'VIP', 52, NULL, NULL, NULL, NULL, 0),
(212, 45, '36', 'GA', 53, NULL, NULL, NULL, NULL, 0),
(213, 65, '36', 'A', 53, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(214, 85, '36', 'B', 53, NULL, NULL, NULL, NULL, 0),
(215, 125, '36', 'VIP', 53, NULL, NULL, NULL, NULL, 0),
(216, 14.99, '37', 'GA', 54, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(217, 18.99, '37', 'A', 54, NULL, NULL, NULL, NULL, 0),
(218, 24.99, '37', 'B', 54, NULL, NULL, NULL, NULL, 0),
(219, 34.99, '37', 'VIP', 54, NULL, NULL, NULL, NULL, 0),
(220, 55, '38', 'GA', 55, NULL, NULL, NULL, NULL, 0),
(221, 85, '38', 'A', 55, NULL, NULL, NULL, NULL, 0),
(222, 125, '38', 'B', 55, NULL, NULL, NULL, NULL, 0),
(223, 180, '38', 'VIP', 55, NULL, NULL, NULL, NULL, 0),
(224, 35, '39', 'GA', 56, NULL, NULL, NULL, NULL, 0),
(225, 55, '39', 'A', 56, NULL, NULL, NULL, NULL, 0),
(226, 75, '39', 'B', 56, NULL, NULL, NULL, NULL, 0),
(227, 110, '39', 'VIP', 56, NULL, NULL, NULL, NULL, 0),
(228, 10, '40', 'GA', 57, NULL, NULL, NULL, NULL, 0),
(229, 20, '40', 'A', 57, NULL, NULL, NULL, NULL, 0),
(230, 35, '40', 'B', 57, NULL, NULL, NULL, NULL, 0),
(231, 50, '40', 'VIP', 57, NULL, NULL, NULL, NULL, 0),
(232, 28, '41', 'GA', 58, NULL, NULL, NULL, NULL, 0),
(233, 42, '41', 'A', 58, NULL, NULL, NULL, NULL, 0),
(234, 60, '41', 'B', 58, NULL, NULL, NULL, NULL, 0),
(235, 90, '41', 'VIP', 58, NULL, NULL, NULL, NULL, 0),
(236, 25, '42', 'GA', 59, NULL, NULL, NULL, NULL, 0),
(237, 35, '42', 'A', 59, NULL, NULL, NULL, NULL, 0),
(238, 45, '42', 'B', 59, NULL, NULL, NULL, NULL, 0),
(239, 65, '42', 'VIP', 59, NULL, NULL, NULL, NULL, 0),
(240, 45, '43', 'GA', 60, NULL, NULL, NULL, NULL, 0),
(241, 65, '43', 'A', 60, NULL, NULL, NULL, NULL, 0),
(242, 85, '43', 'B', 60, NULL, NULL, NULL, NULL, 0),
(243, 125, '43', 'VIP', 60, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(244, 14.99, '44', 'GA', 61, NULL, NULL, NULL, NULL, 0),
(245, 18.99, '44', 'A', 61, NULL, NULL, NULL, NULL, 0),
(246, 24.99, '44', 'B', 61, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(247, 34.99, '44', 'VIP', 61, NULL, NULL, NULL, NULL, 0),
(248, 55, '45', 'GA', 62, NULL, NULL, NULL, NULL, 0),
(249, 85, '45', 'A', 62, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(250, 125, '45', 'B', 62, NULL, NULL, NULL, NULL, 0),
(251, 180, '45', 'VIP', 62, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(252, 35, '46', 'GA', 63, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(253, 55, '46', 'A', 63, NULL, NULL, NULL, NULL, 0),
(254, 75, '46', 'B', 63, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(255, 110, '46', 'VIP', 63, NULL, NULL, NULL, NULL, 0),
(256, 10, '47', 'GA', 64, NULL, NULL, NULL, NULL, 0),
(257, 20, '47', 'A', 64, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(258, 35, '47', 'B', 64, NULL, NULL, NULL, NULL, 0),
(259, 50, '47', 'VIP', 64, NULL, NULL, NULL, NULL, 0),
(260, 28, '48', 'GA', 65, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(261, 42, '48', 'A', 65, NULL, NULL, NULL, NULL, 0),
(262, 60, '48', 'B', 65, NULL, NULL, NULL, NULL, 0),
(263, 90, '48', 'VIP', 65, NULL, NULL, NULL, NULL, 0),
(264, 25, '49', 'GA', 66, NULL, NULL, NULL, NULL, 0),
(265, 35, '49', 'A', 66, NULL, NULL, NULL, NULL, 0),
(266, 45, '49', 'B', 66, NULL, NULL, NULL, NULL, 0),
(267, 65, '49', 'VIP', 66, NULL, NULL, NULL, NULL, 0),
(268, 45, '50', 'GA', 67, NULL, NULL, NULL, NULL, 0),
(269, 65, '50', 'A', 67, NULL, NULL, NULL, NULL, 0),
(270, 85, '50', 'B', 67, NULL, NULL, NULL, NULL, 0),
(271, 125, '50', 'VIP', 67, NULL, NULL, NULL, NULL, 0),
(272, 14.99, '1', 'GA', 68, NULL, NULL, NULL, NULL, 0),
(273, 18.99, '1', 'A', 68, NULL, NULL, NULL, NULL, 0),
(274, 24.99, '1', 'B', 68, NULL, NULL, NULL, NULL, 0),
(275, 34.99, '1', 'VIP', 68, NULL, NULL, NULL, NULL, 0),
(276, 55, '2', 'GA', 69, NULL, NULL, NULL, NULL, 0),
(277, 85, '2', 'A', 69, NULL, NULL, NULL, NULL, 0),
(278, 125, '2', 'B', 69, NULL, NULL, NULL, NULL, 0),
(279, 180, '2', 'VIP', 69, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(280, 35, '3', 'GA', 70, NULL, NULL, NULL, NULL, 0),
(281, 55, '3', 'A', 70, NULL, NULL, NULL, NULL, 0),
(282, 75, '3', 'B', 70, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(283, 110, '3', 'VIP', 70, NULL, NULL, NULL, NULL, 0),
(284, 10, '4', 'GA', 71, NULL, NULL, NULL, NULL, 0),
(285, 20, '4', 'A', 71, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(286, 35, '4', 'B', 71, NULL, NULL, NULL, NULL, 0),
(287, 50, '4', 'VIP', 71, NULL, NULL, NULL, NULL, 0),
(288, 28, '5', 'GA', 72, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(289, 42, '5', 'A', 72, NULL, NULL, NULL, NULL, 0),
(290, 60, '5', 'B', 72, NULL, NULL, NULL, NULL, 0),
(291, 90, '5', 'VIP', 72, NULL, NULL, NULL, NULL, 0),
(292, 25, '6', 'GA', 73, NULL, NULL, NULL, NULL, 0),
(293, 35, '6', 'A', 73, NULL, NULL, NULL, NULL, 0),
(294, 45, '6', 'B', 73, NULL, NULL, NULL, NULL, 0),
(295, 65, '6', 'VIP', 73, NULL, NULL, NULL, NULL, 0),
(296, 45, '7', 'GA', 74, NULL, NULL, NULL, NULL, 0),
(297, 65, '7', 'A', 74, NULL, NULL, NULL, NULL, 0),
(298, 85, '7', 'B', 74, NULL, NULL, NULL, NULL, 0),
(299, 125, '7', 'VIP', 74, NULL, NULL, NULL, NULL, 0),
(300, 14.99, '8', 'GA', 75, NULL, NULL, NULL, NULL, 0),
(301, 18.99, '8', 'A', 75, NULL, NULL, NULL, NULL, 0),
(302, 24.99, '8', 'B', 75, NULL, NULL, NULL, NULL, 0),
(303, 34.99, '8', 'VIP', 75, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(304, 55, '9', 'GA', 76, NULL, NULL, NULL, NULL, 0),
(305, 85, '9', 'A', 76, NULL, NULL, NULL, NULL, 0),
(306, 125, '9', 'B', 76, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(307, 180, '9', 'VIP', 76, NULL, NULL, NULL, NULL, 0),
(308, 35, '10', 'GA', 77, NULL, NULL, NULL, NULL, 0),
(309, 55, '10', 'A', 77, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(310, 75, '10', 'B', 77, NULL, NULL, NULL, NULL, 0),
(311, 110, '10', 'VIP', 77, NULL, NULL, NULL, NULL, 0),
(312, 10, '11', 'GA', 78, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(313, 20, '11', 'A', 78, NULL, NULL, NULL, NULL, 0),
(314, 35, '11', 'B', 78, NULL, NULL, NULL, NULL, 0),
(315, 50, '11', 'VIP', 78, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(316, 28, '12', 'GA', 79, NULL, NULL, NULL, NULL, 0),
(317, 42, '12', 'A', 79, NULL, NULL, NULL, NULL, 0),
(318, 60, '12', 'B', 79, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(319, 90, '12', 'VIP', 79, NULL, NULL, NULL, NULL, 0),
(320, 25, '13', 'GA', 80, NULL, NULL, NULL, NULL, 0),
(321, 35, '13', 'A', 80, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(322, 45, '13', 'B', 80, NULL, NULL, NULL, NULL, 0),
(323, 65, '13', 'VIP', 80, NULL, NULL, NULL, NULL, 0),
(324, 45, '14', 'GA', 81, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(325, 65, '14', 'A', 81, NULL, NULL, NULL, NULL, 0),
(326, 85, '14', 'B', 81, NULL, NULL, NULL, NULL, 0),
(327, 125, '14', 'VIP', 81, NULL, NULL, NULL, NULL, 0),
(328, 14.99, '15', 'GA', 82, NULL, NULL, NULL, NULL, 0),
(329, 18.99, '15', 'A', 82, NULL, NULL, NULL, NULL, 0),
(330, 24.99, '15', 'B', 82, NULL, NULL, NULL, NULL, 0),
(331, 34.99, '15', 'VIP', 82, NULL, NULL, NULL, NULL, 0),
(332, 55, '16', 'GA', 83, NULL, NULL, NULL, NULL, 0),
(333, 85, '16', 'A', 83, NULL, NULL, NULL, NULL, 0),
(334, 125, '16', 'B', 83, NULL, NULL, NULL, NULL, 0),
(335, 180, '16', 'VIP', 83, NULL, NULL, NULL, NULL, 0),
(336, 35, '17', 'GA', 84, NULL, NULL, NULL, NULL, 0),
(337, 55, '17', 'A', 84, NULL, NULL, NULL, NULL, 0),
(338, 75, '17', 'B', 84, NULL, NULL, NULL, NULL, 0),
(339, 110, '17', 'VIP', 84, NULL, NULL, NULL, NULL, 0),
(340, 10, '18', 'GA', 85, NULL, NULL, NULL, NULL, 0),
(341, 20, '18', 'A', 85, NULL, NULL, NULL, NULL, 0),
(342, 35, '18', 'B', 85, NULL, NULL, NULL, NULL, 0),
(343, 50, '18', 'VIP', 85, NULL, NULL, NULL, NULL, 0),
(344, 28, '19', 'GA', 86, NULL, NULL, NULL, NULL, 0),
(345, 42, '19', 'A', 86, NULL, NULL, NULL, NULL, 0),
(346, 60, '19', 'B', 86, NULL, NULL, NULL, NULL, 0),
(347, 90, '19', 'VIP', 86, NULL, NULL, NULL, NULL, 0),
(348, 25, '20', 'GA', 87, NULL, NULL, NULL, NULL, 0),
(349, 35, '20', 'A', 87, NULL, NULL, NULL, NULL, 0),
(350, 45, '20', 'B', 87, NULL, NULL, NULL, NULL, 0),
(351, 65, '20', 'VIP', 87, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(352, 45, '21', 'GA', 88, NULL, NULL, NULL, NULL, 0),
(353, 65, '21', 'A', 88, NULL, NULL, NULL, NULL, 0),
(354, 85, '21', 'B', 88, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(355, 125, '21', 'VIP', 88, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(356, 14.99, '22', 'GA', 89, NULL, NULL, NULL, NULL, 0),
(357, 18.99, '22', 'A', 89, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(358, 24.99, '22', 'B', 89, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(359, 34.99, '22', 'VIP', 89, NULL, NULL, NULL, NULL, 0),
(360, 55, '23', 'GA', 90, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(361, 85, '23', 'A', 90, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(362, 125, '23', 'B', 90, NULL, NULL, NULL, NULL, 0),
(363, 180, '23', 'VIP', 90, NULL, NULL, NULL, NULL, 0),
(364, 35, '24', 'GA', 91, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(365, 55, '24', 'A', 91, NULL, NULL, NULL, NULL, 0),
(366, 75, '24', 'B', 91, NULL, NULL, NULL, NULL, 0),
(367, 110, '24', 'VIP', 91, NULL, NULL, NULL, NULL, 0),
(368, 10, '25', 'GA', 92, NULL, NULL, NULL, NULL, 0),
(369, 20, '25', 'A', 92, NULL, NULL, NULL, NULL, 0),
(370, 35, '25', 'B', 92, NULL, NULL, NULL, NULL, 0),
(371, 50, '25', 'VIP', 92, NULL, NULL, NULL, NULL, 0),
(372, 28, '26', 'GA', 93, NULL, NULL, NULL, NULL, 0),
(373, 42, '26', 'A', 93, NULL, NULL, NULL, NULL, 0),
(374, 60, '26', 'B', 93, NULL, NULL, NULL, NULL, 0),
(375, 90, '26', 'VIP', 93, NULL, NULL, NULL, NULL, 0),
(376, 25, '27', 'GA', 94, NULL, NULL, NULL, NULL, 0),
(377, 35, '27', 'A', 94, NULL, NULL, NULL, NULL, 0),
(378, 45, '27', 'B', 94, NULL, NULL, NULL, NULL, 0),
(379, 65, '27', 'VIP', 94, NULL, NULL, NULL, NULL, 0),
(380, 45, '28', 'GA', 95, NULL, NULL, NULL, NULL, 0),
(381, 65, '28', 'A', 95, NULL, NULL, NULL, NULL, 0),
(382, 85, '28', 'B', 95, NULL, NULL, NULL, NULL, 0),
(383, 125, '28', 'VIP', 95, NULL, NULL, NULL, NULL, 0),
(384, 14.99, '29', 'GA', 96, NULL, NULL, NULL, NULL, 0),
(385, 18.99, '29', 'A', 96, NULL, NULL, NULL, NULL, 0),
(386, 24.99, '29', 'B', 96, NULL, NULL, NULL, NULL, 0),
(387, 34.99, '29', 'VIP', 96, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(388, 55, '30', 'GA', 97, NULL, NULL, NULL, NULL, 0),
(389, 85, '30', 'A', 97, NULL, NULL, NULL, NULL, 0),
(390, 125, '30', 'B', 97, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(391, 180, '30', 'VIP', 97, NULL, NULL, NULL, NULL, 0),
(392, 35, '31', 'GA', 98, NULL, NULL, NULL, NULL, 0),
(393, 55, '31', 'A', 98, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(394, 75, '31', 'B', 98, NULL, NULL, NULL, NULL, 0),
(395, 110, '31', 'VIP', 98, NULL, NULL, NULL, NULL, 0),
(396, 10, '32', 'GA', 99, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(397, 20, '32', 'A', 99, NULL, NULL, NULL, NULL, 0),
(398, 35, '32', 'B', 99, NULL, NULL, NULL, NULL, 0),
(399, 50, '32', 'VIP', 99, NULL, NULL, NULL, NULL, 0),
(400, 28, '33', 'GA', 100, NULL, NULL, NULL, NULL, 0),
(401, 42, '33', 'A', 100, NULL, NULL, NULL, NULL, 0),
(402, 60, '33', 'B', 100, NULL, NULL, NULL, NULL, 0),
(403, 90, '33', 'VIP', 100, NULL, NULL, NULL, NULL, 0),
(404, 25, '34', 'GA', 101, NULL, NULL, NULL, NULL, 0),
(405, 35, '34', 'A', 101, NULL, NULL, NULL, NULL, 0),
(406, 45, '34', 'B', 101, NULL, NULL, NULL, NULL, 0),
(407, 65, '34', 'VIP', 101, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(408, 45, '35', 'GA', 102, NULL, NULL, NULL, NULL, 0),
(409, 65, '35', 'A', 102, NULL, NULL, NULL, NULL, 0),
(410, 85, '35', 'B', 102, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(411, 125, '35', 'VIP', 102, NULL, NULL, NULL, NULL, 0),
(412, 14.99, '36', 'GA', 103, NULL, NULL, NULL, NULL, 0),
(413, 18.99, '36', 'A', 103, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(414, 24.99, '36', 'B', 103, NULL, NULL, NULL, NULL, 0),
(415, 34.99, '36', 'VIP', 103, NULL, NULL, NULL, NULL, 0),
(416, 55, '37', 'GA', 104, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(417, 85, '37', 'A', 104, NULL, NULL, NULL, NULL, 0),
(418, 125, '37', 'B', 104, NULL, NULL, NULL, NULL, 0),
(419, 180, '37', 'VIP', 104, NULL, NULL, NULL, NULL, 0),
(420, 35, '38', 'GA', 105, NULL, NULL, NULL, NULL, 0),
(421, 55, '38', 'A', 105, NULL, NULL, NULL, NULL, 0),
(422, 75, '38', 'B', 105, NULL, NULL, NULL, NULL, 0),
(423, 110, '38', 'VIP', 105, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(424, 10, '39', 'GA', 106, NULL, NULL, NULL, NULL, 0),
(425, 20, '39', 'A', 106, NULL, NULL, NULL, NULL, 0),
(426, 35, '39', 'B', 106, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(427, 50, '39', 'VIP', 106, NULL, NULL, NULL, NULL, 0),
(428, 28, '40', 'GA', 107, NULL, NULL, NULL, NULL, 0),
(429, 42, '40', 'A', 107, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(430, 60, '40', 'B', 107, NULL, NULL, NULL, NULL, 0),
(431, 90, '40', 'VIP', 107, NULL, NULL, NULL, NULL, 0),
(432, 25, '41', 'GA', 108, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(433, 35, '41', 'A', 108, NULL, NULL, NULL, NULL, 0),
(434, 45, '41', 'B', 108, NULL, NULL, NULL, NULL, 0),
(435, 65, '41', 'VIP', 108, NULL, NULL, NULL, NULL, 0),
(436, 45, '42', 'GA', 109, NULL, NULL, NULL, NULL, 0),
(437, 65, '42', 'A', 109, NULL, NULL, NULL, NULL, 0),
(438, 85, '42', 'B', 109, NULL, NULL, NULL, NULL, 0),
(439, 125, '42', 'VIP', 109, NULL, NULL, NULL, NULL, 0),
(440, 14.99, '43', 'GA', 110, NULL, NULL, NULL, NULL, 0),
(441, 18.99, '43', 'A', 110, NULL, NULL, NULL, NULL, 0),
(442, 24.99, '43', 'B', 110, NULL, NULL, NULL, NULL, 0),
(443, 34.99, '43', 'VIP', 110, NULL, NULL, NULL, NULL, 0),
(444, 55, '44', 'GA', 111, NULL, NULL, NULL, NULL, 0),
(445, 85, '44', 'A', 111, NULL, NULL, NULL, NULL, 0),
(446, 125, '44', 'B', 111, NULL, NULL, NULL, NULL, 0),
(447, 180, '44', 'VIP', 111, NULL, NULL, NULL, NULL, 0),
(448, 35, '45', 'GA', 112, NULL, NULL, NULL, NULL, 0),
(449, 55, '45', 'A', 112, NULL, NULL, NULL, NULL, 0),
(450, 75, '45', 'B', 112, NULL, NULL, NULL, NULL, 0),
(451, 110, '45', 'VIP', 112, NULL, NULL, NULL, NULL, 0),
(452, 10, '46', 'GA', 113, NULL, NULL, NULL, NULL, 0),
(453, 20, '46', 'A', 113, NULL, NULL, NULL, NULL, 0),
(454, 35, '46', 'B', 113, NULL, NULL, NULL, NULL, 0),
(455, 50, '46', 'VIP', 113, NULL, NULL, NULL, NULL, 0),
(456, 28, '47', 'GA', 114, NULL, NULL, NULL, NULL, 0),
(457, 42, '47', 'A', 114, NULL, NULL, NULL, NULL, 0),
(458, 60, '47', 'B', 114, NULL, NULL, NULL, NULL, 0),
(459, 90, '47', 'VIP', 114, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(460, 25, '48', 'GA', 115, NULL, NULL, NULL, NULL, 0),
(461, 35, '48', 'A', 115, NULL, NULL, NULL, NULL, 0),
(462, 45, '48', 'B', 115, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(463, 65, '48', 'VIP', 115, NULL, NULL, NULL, NULL, 0),
(464, 45, '49', 'GA', 116, NULL, NULL, NULL, NULL, 0),
(465, 65, '49', 'A', 116, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(466, 85, '49', 'B', 116, NULL, NULL, NULL, NULL, 0),
(467, 125, '49', 'VIP', 116, NULL, NULL, NULL, NULL, 0),
(468, 14.99, '50', 'GA', 117, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(469, 18.99, '50', 'A', 117, NULL, NULL, NULL, NULL, 0),
(470, 24.99, '50', 'B', 117, NULL, NULL, NULL, NULL, 0),
(471, 34.99, '50', 'VIP', 117, NULL, NULL, NULL, NULL, 0),
(472, 55, '1', 'GA', 118, NULL, NULL, NULL, NULL, 0),
(473, 85, '1', 'A', 118, NULL, NULL, NULL, NULL, 0),
(474, 125, '1', 'B', 118, NULL, NULL, NULL, NULL, 0),
(475, 180, '1', 'VIP', 118, NULL, NULL, NULL, NULL, 0),
(476, 35, '2', 'GA', 119, NULL, NULL, NULL, NULL, 0),
(477, 55, '2', 'A', 119, NULL, NULL, NULL, NULL, 0),
(478, 75, '2', 'B', 119, NULL, NULL, NULL, NULL, 0),
(479, 110, '2', 'VIP', 119, NULL, NULL, NULL, NULL, 0),
(480, 10, '3', 'GA', 120, NULL, NULL, NULL, NULL, 0),
(481, 20, '3', 'A', 120, NULL, NULL, NULL, NULL, 0),
(482, 35, '3', 'B', 120, NULL, NULL, NULL, NULL, 0),
(483, 50, '3', 'VIP', 120, NULL, NULL, NULL, NULL, 0),
(484, 28, '4', 'GA', 121, NULL, NULL, NULL, NULL, 0),
(485, 42, '4', 'A', 121, NULL, NULL, NULL, NULL, 0),
(486, 60, '4', 'B', 121, NULL, NULL, NULL, NULL, 0),
(487, 90, '4', 'VIP', 121, NULL, NULL, NULL, NULL, 0),
(488, 25, '5', 'GA', 122, NULL, NULL, NULL, NULL, 0),
(489, 35, '5', 'A', 122, NULL, NULL, NULL, NULL, 0),
(490, 45, '5', 'B', 122, NULL, NULL, NULL, NULL, 0),
(491, 65, '5', 'VIP', 122, NULL, NULL, NULL, NULL, 0),
(492, 45, '6', 'GA', 123, NULL, NULL, NULL, NULL, 0),
(493, 65, '6', 'A', 123, NULL, NULL, NULL, NULL, 0),
(494, 85, '6', 'B', 123, NULL, NULL, NULL, NULL, 0),
(495, 125, '6', 'VIP', 123, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(496, 14.99, '7', 'GA', 124, NULL, NULL, NULL, NULL, 0),
(497, 18.99, '7', 'A', 124, NULL, NULL, NULL, NULL, 0),
(498, 24.99, '7', 'B', 124, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(499, 34.99, '7', 'VIP', 124, NULL, NULL, NULL, NULL, 0),
(500, 55, '8', 'GA', 125, NULL, NULL, NULL, NULL, 0),
(501, 85, '8', 'A', 125, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(502, 125, '8', 'B', 125, NULL, NULL, NULL, NULL, 0),
(503, 180, '8', 'VIP', 125, NULL, NULL, NULL, NULL, 0),
(504, 35, '9', 'GA', 126, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(505, 55, '9', 'A', 126, NULL, NULL, NULL, NULL, 0),
(506, 75, '9', 'B', 126, NULL, NULL, NULL, NULL, 0),
(507, 110, '9', 'VIP', 126, NULL, NULL, NULL, NULL, 0),
(508, 10, '10', 'GA', 127, NULL, NULL, NULL, NULL, 0),
(509, 20, '10', 'A', 127, NULL, NULL, NULL, NULL, 0),
(510, 35, '10', 'B', 127, NULL, NULL, NULL, NULL, 0),
(511, 50, '10', 'VIP', 127, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(512, 28, '11', 'GA', 128, NULL, NULL, NULL, NULL, 0),
(513, 42, '11', 'A', 128, NULL, NULL, NULL, NULL, 0),
(514, 60, '11', 'B', 128, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(515, 90, '11', 'VIP', 128, NULL, NULL, NULL, NULL, 0),
(516, 25, '12', 'GA', 129, NULL, NULL, NULL, NULL, 0),
(517, 35, '12', 'A', 129, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(518, 45, '12', 'B', 129, NULL, NULL, NULL, NULL, 0),
(519, 65, '12', 'VIP', 129, NULL, NULL, NULL, NULL, 0),
(520, 45, '13', 'GA', 130, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(521, 65, '13', 'A', 130, NULL, NULL, NULL, NULL, 0),
(522, 85, '13', 'B', 130, NULL, NULL, NULL, NULL, 0),
(523, 125, '13', 'VIP', 130, NULL, NULL, NULL, NULL, 0),
(524, 14.99, '14', 'GA', 131, NULL, NULL, NULL, NULL, 0),
(525, 18.99, '14', 'A', 131, NULL, NULL, NULL, NULL, 0),
(526, 24.99, '14', 'B', 131, NULL, NULL, NULL, NULL, 0),
(527, 34.99, '14', 'VIP', 131, NULL, NULL, NULL, NULL, 0),
(528, 55, '15', 'GA', 132, NULL, NULL, NULL, NULL, 0),
(529, 85, '15', 'A', 132, NULL, NULL, NULL, NULL, 0),
(530, 125, '15', 'B', 132, NULL, NULL, NULL, NULL, 0),
(531, 180, '15', 'VIP', 132, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(532, 35, '16', 'GA', 133, NULL, NULL, NULL, NULL, 0),
(533, 55, '16', 'A', 133, NULL, NULL, NULL, NULL, 0),
(534, 75, '16', 'B', 133, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(535, 110, '16', 'VIP', 133, NULL, NULL, NULL, NULL, 0),
(536, 10, '17', 'GA', 134, NULL, NULL, NULL, NULL, 0),
(537, 20, '17', 'A', 134, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(538, 35, '17', 'B', 134, NULL, NULL, NULL, NULL, 0),
(539, 50, '17', 'VIP', 134, NULL, NULL, NULL, NULL, 0),
(540, 28, '18', 'GA', 135, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(541, 42, '18', 'A', 135, NULL, NULL, NULL, NULL, 0),
(542, 60, '18', 'B', 135, NULL, NULL, NULL, NULL, 0),
(543, 90, '18', 'VIP', 135, NULL, NULL, NULL, NULL, 0),
(544, 25, '19', 'GA', 136, NULL, NULL, NULL, NULL, 0),
(545, 35, '19', 'A', 136, NULL, NULL, NULL, NULL, 0),
(546, 45, '19', 'B', 136, NULL, NULL, NULL, NULL, 0),
(547, 65, '19', 'VIP', 136, NULL, NULL, NULL, NULL, 0),
(548, 45, '20', 'GA', 137, NULL, NULL, NULL, NULL, 0),
(549, 65, '20', 'A', 137, NULL, NULL, NULL, NULL, 0),
(550, 85, '20', 'B', 137, NULL, NULL, NULL, NULL, 0),
(551, 125, '20', 'VIP', 137, NULL, NULL, NULL, NULL, 0),
(552, 14.99, '21', 'GA', 138, NULL, NULL, NULL, NULL, 0),
(553, 18.99, '21', 'A', 138, NULL, NULL, NULL, NULL, 0),
(554, 24.99, '21', 'B', 138, NULL, NULL, NULL, NULL, 0),
(555, 34.99, '21', 'VIP', 138, NULL, NULL, NULL, NULL, 0),
(556, 55, '22', 'GA', 139, NULL, NULL, NULL, NULL, 0),
(557, 85, '22', 'A', 139, NULL, NULL, NULL, NULL, 0),
(558, 125, '22', 'B', 139, NULL, NULL, NULL, NULL, 0),
(559, 180, '22', 'VIP', 139, NULL, NULL, NULL, NULL, 0),
(560, 35, '23', 'GA', 140, NULL, NULL, NULL, NULL, 0),
(561, 55, '23', 'A', 140, NULL, NULL, NULL, NULL, 0),
(562, 75, '23', 'B', 140, NULL, NULL, NULL, NULL, 0),
(563, 110, '23', 'VIP', 140, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(564, 10, '24', 'GA', 141, NULL, NULL, NULL, NULL, 0),
(565, 20, '24', 'A', 141, NULL, NULL, NULL, NULL, 0),
(566, 35, '24', 'B', 141, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(567, 50, '24', 'VIP', 141, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(568, 28, '25', 'GA', 142, NULL, NULL, NULL, NULL, 0),
(569, 42, '25', 'A', 142, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(570, 60, '25', 'B', 142, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(571, 90, '25', 'VIP', 142, NULL, NULL, NULL, NULL, 0),
(572, 25, '26', 'GA', 143, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(573, 35, '26', 'A', 143, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(574, 45, '26', 'B', 143, NULL, NULL, NULL, NULL, 0),
(575, 65, '26', 'VIP', 143, NULL, NULL, NULL, NULL, 0),
(576, 45, '27', 'GA', 144, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(577, 65, '27', 'A', 144, NULL, NULL, NULL, NULL, 0),
(578, 85, '27', 'B', 144, NULL, NULL, NULL, NULL, 0),
(579, 125, '27', 'VIP', 144, NULL, NULL, NULL, NULL, 0),
(580, 14.99, '28', 'GA', 145, NULL, NULL, NULL, NULL, 0),
(581, 18.99, '28', 'A', 145, NULL, NULL, NULL, NULL, 0),
(582, 24.99, '28', 'B', 145, NULL, NULL, NULL, NULL, 0),
(583, 34.99, '28', 'VIP', 145, NULL, NULL, NULL, NULL, 0),
(584, 55, '29', 'GA', 146, NULL, NULL, NULL, NULL, 0),
(585, 85, '29', 'A', 146, NULL, NULL, NULL, NULL, 0),
(586, 125, '29', 'B', 146, NULL, NULL, NULL, NULL, 0),
(587, 180, '29', 'VIP', 146, NULL, NULL, NULL, NULL, 0),
(588, 35, '30', 'GA', 147, NULL, NULL, NULL, NULL, 0),
(589, 55, '30', 'A', 147, NULL, NULL, NULL, NULL, 0),
(590, 75, '30', 'B', 147, NULL, NULL, NULL, NULL, 0),
(591, 110, '30', 'VIP', 147, NULL, NULL, NULL, NULL, 0),
(592, 10, '31', 'GA', 148, NULL, NULL, NULL, NULL, 0),
(593, 20, '31', 'A', 148, NULL, NULL, NULL, NULL, 0),
(594, 35, '31', 'B', 148, NULL, NULL, NULL, NULL, 0),
(595, 50, '31', 'VIP', 148, NULL, NULL, NULL, NULL, 0),
(596, 28, '32', 'GA', 149, NULL, NULL, NULL, NULL, 0),
(597, 42, '32', 'A', 149, NULL, NULL, NULL, NULL, 0),
(598, 60, '32', 'B', 149, NULL, NULL, NULL, NULL, 0),
(599, 90, '32', 'VIP', 149, NULL, NULL, NULL, NULL, 0),
(600, 25, '33', 'GA', 150, NULL, NULL, NULL, NULL, 0),
(601, 35, '33', 'A', 150, NULL, NULL, NULL, NULL, 0),
(602, 45, '33', 'B', 150, NULL, NULL, NULL, NULL, 0),
(603, 65, '33', 'VIP', 150, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(604, 45, '34', 'GA', 151, NULL, NULL, NULL, NULL, 0),
(605, 65, '34', 'A', 151, NULL, NULL, NULL, NULL, 0),
(606, 85, '34', 'B', 151, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(607, 125, '34', 'VIP', 151, NULL, NULL, NULL, NULL, 0),
(608, 14.99, '35', 'GA', 152, NULL, NULL, NULL, NULL, 0),
(609, 18.99, '35', 'A', 152, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(610, 24.99, '35', 'B', 152, NULL, NULL, NULL, NULL, 0),
(611, 34.99, '35', 'VIP', 152, NULL, NULL, NULL, NULL, 0),
(612, 55, '36', 'GA', 153, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(613, 85, '36', 'A', 153, NULL, NULL, NULL, NULL, 0),
(614, 125, '36', 'B', 153, NULL, NULL, NULL, NULL, 0),
(615, 180, '36', 'VIP', 153, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(616, 35, '37', 'GA', 154, NULL, NULL, NULL, NULL, 0),
(617, 55, '37', 'A', 154, NULL, NULL, NULL, NULL, 0),
(618, 75, '37', 'B', 154, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(619, 110, '37', 'VIP', 154, NULL, NULL, NULL, NULL, 0),
(620, 10, '38', 'GA', 155, NULL, NULL, NULL, NULL, 0),
(621, 20, '38', 'A', 155, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(622, 35, '38', 'B', 155, NULL, NULL, NULL, NULL, 0),
(623, 50, '38', 'VIP', 155, NULL, NULL, NULL, NULL, 0),
(624, 28, '39', 'GA', 156, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(625, 42, '39', 'A', 156, NULL, NULL, NULL, NULL, 0),
(626, 60, '39', 'B', 156, NULL, NULL, NULL, NULL, 0),
(627, 90, '39', 'VIP', 156, NULL, NULL, NULL, NULL, 0),
(628, 25, '40', 'GA', 157, NULL, NULL, NULL, NULL, 0),
(629, 35, '40', 'A', 157, NULL, NULL, NULL, NULL, 0),
(630, 45, '40', 'B', 157, NULL, NULL, NULL, NULL, 0),
(631, 65, '40', 'VIP', 157, NULL, NULL, NULL, NULL, 0),
(632, 45, '41', 'GA', 158, NULL, NULL, NULL, NULL, 0),
(633, 65, '41', 'A', 158, NULL, NULL, NULL, NULL, 0),
(634, 85, '41', 'B', 158, NULL, NULL, NULL, NULL, 0),
(635, 125, '41', 'VIP', 158, NULL, NULL, NULL, NULL, 0),
(636, 14.99, '42', 'GA', 159, NULL, NULL, NULL, NULL, 0),
(637, 18.99, '42', 'A', 159, NULL, NULL, NULL, NULL, 0),
(638, 24.99, '42', 'B', 159, NULL, NULL, NULL, NULL, 0),
(639, 34.99, '42', 'VIP', 159, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(640, 55, '43', 'GA', 160, NULL, NULL, NULL, NULL, 0),
(641, 85, '43', 'A', 160, NULL, NULL, NULL, NULL, 0),
(642, 125, '43', 'B', 160, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(643, 180, '43', 'VIP', 160, NULL, NULL, NULL, NULL, 0),
(644, 35, '44', 'GA', 161, NULL, NULL, NULL, NULL, 0),
(645, 55, '44', 'A', 161, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(646, 75, '44', 'B', 161, NULL, NULL, NULL, NULL, 0),
(647, 110, '44', 'VIP', 161, NULL, NULL, NULL, NULL, 0),
(648, 10, '45', 'GA', 162, 'percent', 15.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(649, 20, '45', 'A', 162, NULL, NULL, NULL, NULL, 0),
(650, 35, '45', 'B', 162, NULL, NULL, NULL, NULL, 0),
(651, 50, '45', 'VIP', 162, NULL, NULL, NULL, NULL, 0),
(652, 28, '46', 'GA', 163, NULL, NULL, NULL, NULL, 0),
(653, 42, '46', 'A', 163, NULL, NULL, NULL, NULL, 0),
(654, 60, '46', 'B', 163, NULL, NULL, NULL, NULL, 0),
(655, 90, '46', 'VIP', 163, NULL, NULL, NULL, NULL, 0),
(656, 25, '47', 'GA', 164, NULL, NULL, NULL, NULL, 0),
(657, 35, '47', 'A', 164, NULL, NULL, NULL, NULL, 0),
(658, 45, '47', 'B', 164, NULL, NULL, NULL, NULL, 0),
(659, 65, '47', 'VIP', 164, NULL, NULL, NULL, NULL, 0),
(660, 45, '48', 'GA', 165, NULL, NULL, NULL, NULL, 0),
(661, 65, '48', 'A', 165, NULL, NULL, NULL, NULL, 0),
(662, 85, '48', 'B', 165, NULL, NULL, NULL, NULL, 0),
(663, 125, '48', 'VIP', 165, NULL, NULL, NULL, NULL, 0),
(664, 14.99, '49', 'GA', 166, NULL, NULL, NULL, NULL, 0),
(665, 18.99, '49', 'A', 166, NULL, NULL, NULL, NULL, 0),
(666, 24.99, '49', 'B', 166, NULL, NULL, NULL, NULL, 0),
(667, 34.99, '49', 'VIP', 166, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(668, 55, '50', 'GA', 167, NULL, NULL, NULL, NULL, 0),
(669, 85, '50', 'A', 167, NULL, NULL, NULL, NULL, 0),
(670, 125, '50', 'B', 167, 'fixed', 5.00, '2026-05-14T00:00', '2026-12-31T23:59', 0),
(671, 180, '50', 'VIP', 167, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(225) NOT NULL,
  `password` text NOT NULL,
  `role` varchar(5) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `totp_secret` varchar(252) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `location` varchar(191) DEFAULT NULL,
  `bio` varchar(191) DEFAULT NULL,
  `avatar` varchar(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `points`, `totp_secret`, `birthday`, `location`, `bio`, `avatar`) VALUES
(1, 'John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0, 'TMIQKPBSDWP47NS6FSEHYCTJE6NEHC4D', NULL, NULL, NULL, NULL),
(2, 'Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0, '7JRH7AE7TSGRAJ2JCE5ENQLZVJ7EJZDT', NULL, NULL, NULL, NULL),
(3, 'Mike', 'Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 0, NULL, NULL, NULL, NULL, NULL),
(4, 'Sarah', 'Williams', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 0, NULL, NULL, NULL, NULL, NULL),
(5, 'Alex', 'Brown', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 0, NULL, NULL, NULL, NULL, NULL),
(6, 'Lucas', 'Coveyduck', 'lukecage7799@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0, 'CQOPGANENKAZI3BYEXFK7XM3VG2DTTYP', '2007-06-17', 'Montreal, QC, Canada', 'I am so blue', '/uploads/avatars/6_1778702451.png'),
(9, 'Lucas', 'Coveyduck', 'lcoveduck@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 0, 'OFYAHE2FQ3EXWBA3TBAHRGSU2OSFZZ2C', NULL, NULL, NULL, NULL),
(10, 'Groq', 'Yes', 'yes@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0, '6GOJ6HIRLJQE5DKZHWB6GGIVLGM46YS3', NULL, NULL, NULL, NULL),
(12, 'Yes', '', 'hello@yessing.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 432, 'UBJPD7U6Y5OFZKIEY2A2H7MHOSLG2OJP', NULL, '', '', '/uploads/avatars/12_1778733931.png'),
(13, 'Gork', '', 'gork@eee.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 6, 'SFXXZ6AIGXFNGQK3LHRZEG7HI2YAOZWB', NULL, '', '', '/uploads/avatars/13_1778732337.jpg'),
(14, 'fadwa', '', 'fadwa@anthony.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 8, 'YO5GOI4BHTBH2CGRES27K2VDCIT6ZRKQ', NULL, NULL, NULL, NULL),
(15, 'Hello', 'Yes', 'yess@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 42, 'VBIYUZNCFCOFC72TDKNBIWUKR6BSGQAZ', NULL, NULL, NULL, NULL),
(16, 'eew', 'fer', 'ger@ysr.omc', '$2y$12$2iwpAfRHZSF4mmk09uT8X.u.APHMmaMbSn7j.UfUANylvHmBOMOxW', 'user', 0, 'UZMDKOIQVQXRANQG6WBNCDVTO55LAIRK', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `image_url` text NOT NULL,
  `address` text NOT NULL,
  `capacity` int(11) NOT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`id`, `name`, `description`, `image_url`, `address`, `capacity`, `lat`, `lng`) VALUES
(1, 'Bell Centre', 'Real Montreal arena used for hockey, concerts, comedy, and major live events.', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80', '1909 Avenue des Canadiens-de-Montréal, Montreal, QC H3B 4C9, Canada', 21302, 45.4961, -73.5693),
(2, 'Place Bell', 'Real Laval arena and performance venue used for hockey, concerts, comedy, wrestling, and community events.', 'https://images.unsplash.com/photo-1507874457470-272b3c8d8ee2?auto=format&fit=crop&w=1200&q=80', '1950 Rue Claude-Gagné, Laval, QC H7N 0E4, Canada', 10000, 45.5566, -73.7212),
(3, 'Scotiabank Arena', 'Real Toronto arena used for basketball, hockey, concerts, comedy, and major touring events.', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1200&q=80', '40 Bay Street, Toronto, ON M5J 2X2, Canada', 19800, 43.6435, -79.3791),
(4, 'MTELUS', 'Real Montreal live-music venue used for concerts, comedy nights, club shows, and cultural events.', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80', '59 Rue Sainte-Catherine E, Montreal, QC H2X 1K5, Canada', 2300, 45.5106, -73.5634),
(5, 'Rogers Centre', 'Real Toronto stadium used for baseball, major concerts, sports showcases, and large live events.', 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80', '1 Blue Jays Way, Toronto, ON M5V 1J1, Canada', 49000, 43.6414, -79.3894),
(6, 'Olympic Stadium', 'Real Montreal stadium used for sports, exhibitions, festivals, and arena-scale events.', 'https://images.unsplash.com/photo-1499364615650-ec38552f4f34?auto=format&fit=crop&w=1200&q=80', '4545 Avenue Pierre-De Coubertin, Montreal, QC H1V 0B2, Canada', 56000, 45.558, -73.5519),
(7, 'RBC Amphitheatre', 'Real Toronto outdoor amphitheatre at Ontario Place used for summer concerts and festivals.', 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80', '909 Lake Shore Blvd W, Toronto, ON M6K 3L3, Canada', 16000, 43.6295, -79.415),
(8, 'Salle André-Mathieu', 'Real Laval seated concert hall used for music, comedy, theatre, and cultural events.', 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80', '475 Boulevard de l’Avenir, Laval, QC H7N 5H9, Canada', 827, 45.5606, -73.7183),
(9, 'Palace Convention Centre', 'Real Laval reception and convention hall used for galas, corporate events, family events, and festivals.', 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80', '1717 Boulevard Le Corbusier, Laval, QC H7S 2K7, Canada', 3000, 45.5682, -73.7482),
(10, 'Meridian Hall', 'Real Toronto theatre and concert hall used for stage shows, comedy, concerts, and cultural events.', 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=1200&q=80', '1 Front Street E, Toronto, ON M5E 1B2, Canada', 3191, 43.6467, -79.3763),
(11, 'Coca-Cola Coliseum', 'Real Toronto arena at Exhibition Place used for hockey, concerts, sports events, and trade shows.', 'https://images.unsplash.com/photo-1503095396549-807759245b35?auto=format&fit=crop&w=1200&q=80', '19 Nunavut Road, Toronto, ON M6K 3C3, Canada', 7797, 43.6334, -79.4186),
(12, 'Palais des congrès de Montréal', 'Real Montreal convention centre used for conferences, exhibitions, galas, markets, and large public events.', 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=1200&q=80', '1001 Place Jean-Paul-Riopelle, Montreal, QC H2Z 1H2, Canada', 10000, 45.5031, -73.5607);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authtoken`
--
ALTER TABLE `authtoken`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_foreignkey_authtoken_user` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_FK` (`venue_id`),
  ADD KEY `category_FK` (`category_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_stripe_session` (`stripe_session_id`),
  ADD KEY `users_FK` (`user_id`),
  ADD KEY `idx_orders_stripe_session` (`stripe_session_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_FK` (`order_id`),
  ADD KEY `tickets_FK` (`ticket_id`);

--
-- Indexes for table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `stripepending`
--
ALTER TABLE `stripepending`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stripepending_session` (`stripe_session_id`);

--
-- Indexes for table `tfatoken`
--
ALTER TABLE `tfatoken`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_foreignkey_tfatoken_user` (`user_id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_FK` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authtoken`
--
ALTER TABLE `authtoken`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stripepending`
--
ALTER TABLE `stripepending`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tfatoken`
--
ALTER TABLE `tfatoken`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=672;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `category_FK` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `venue_FK` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `users_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_FK` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_FK` FOREIGN KEY (`ticket_id`) REFERENCES `ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `points_history`
--
ALTER TABLE `points_history`
  ADD CONSTRAINT `points_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `points_history_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `event_FK` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
