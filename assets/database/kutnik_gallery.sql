-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Lis 24, 2024 at 01:47 PM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kutnik_gallery`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tresc_komentarza` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `tresc_komentarza`) VALUES
(57, 57, 71, 'test'),
(58, 58, 72, 'wow'),
(59, 57, 72, 'yey');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `opis` text DEFAULT NULL,
  `data_stworzenia` timestamp NOT NULL DEFAULT current_timestamp(),
  `licznik_polubień` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `image_url`, `opis`, `data_stworzenia`, `licznik_polubień`) VALUES
(57, 71, 'http://localhost/Gallery_Project_BK/assets/uploads_posts/test_image.png', 'test description', '2024-11-24 12:44:02', 1),
(58, 72, 'http://localhost/Gallery_Project_BK/assets/uploads_posts/test_image.png', 'test2', '2024-11-24 12:44:30', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `user_id`) VALUES
(303, 57, 71);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `relacje_obserwacji`
--

CREATE TABLE `relacje_obserwacji` (
  `id_obserwujacy` int(11) NOT NULL,
  `id_obserwowany` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relacje_obserwacji`
--

INSERT INTO `relacje_obserwacji` (`id_obserwujacy`, `id_obserwowany`) VALUES
(71, 72),
(72, 71),
(72, 74);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(30) NOT NULL,
  `imie` varchar(40) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `email` varchar(40) NOT NULL,
  `plec` enum('mężczyzna','kobieta','','') NOT NULL,
  `biogram` varchar(150) DEFAULT NULL,
  `profile_img` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `imie`, `nazwisko`, `email`, `plec`, `biogram`, `profile_img`, `password`) VALUES
(71, 'test1', 'Test1', 'Test1', 'Test1@gmail.com', 'mężczyzna', 'test1', 'assets/uploads/test_image.png', '$2y$10$I.9.WAe.zUup9CG6i5KD5Ok8262CHLQH.8nCoT2q8etXf9jCLImmW'),
(72, 'test2', 'test2', 'test2', 'test2@gmail.com', 'kobieta', 'test2', 'assets/uploads/test_image.png', '$2y$10$j6.N6GwE1evxZYdzOhdDHuRe/BLM6HaAjxlzGVCkp0.6bgTKaWl7q'),
(73, 'test3', 'test3', 'test3', 'test3@gmail.com', 'mężczyzna', 'test3', 'assets/uploads/test_image.png', '$2y$10$aleQojQCOX8xyJmCXlHuLeoQXROnOOinKBdmZjXn2jqHJk9cQNVw6'),
(74, 'test4', 'test4', 'test4', 'test4@gmail.com', 'mężczyzna', 'test4', 'assets/uploads/test_image.png', '$2y$10$tI5A9v50Aj.x6eeS8/Sse.8f94q6MH1IVTa3CR10OYkAZMnACBzVu');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_id` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `relacje_obserwacji`
--
ALTER TABLE `relacje_obserwacji`
  ADD PRIMARY KEY (`id_obserwujacy`,`id_obserwowany`),
  ADD KEY `id_obserwowany` (`id_obserwowany`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`),
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `relacje_obserwacji`
--
ALTER TABLE `relacje_obserwacji`
  ADD CONSTRAINT `relacje_obserwacji_ibfk_1` FOREIGN KEY (`id_obserwujacy`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `relacje_obserwacji_ibfk_2` FOREIGN KEY (`id_obserwowany`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
