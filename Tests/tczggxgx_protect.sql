-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : dim. 18 jan. 2026 à 07:44
-- Version du serveur : 10.11.15-MariaDB
-- Version de PHP : 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tczggxgx_protect`
--

-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

CREATE TABLE `logs` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `logs`
--

INSERT INTO `logs` (`id_log`, `id_user`, `action`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Inscription réussie', '102.223.129.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 07:28:27'),
(2, 1, 'Connexion réussie', '102.223.129.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 07:28:42'),
(3, 2, 'Inscription réussie', '102.223.129.144', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-18 07:35:16');

-- --------------------------------------------------------

--
-- Structure de la table `proprietaires`
--

CREATE TABLE `proprietaires` (
  `id` int(11) NOT NULL,
  `id_proprietaire` int(11) DEFAULT NULL,
  `nom` varchar(100) NOT NULL,
  `post_nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `proprietaires`
--

INSERT INTO `proprietaires` (`id`, `id_proprietaire`, `nom`, `post_nom`, `prenom`, `telephone`, `created_at`) VALUES
(1, 1, 'user', 'user', 'geekgenius', '+243 ......', '2026-01-18 07:29:32');

-- --------------------------------------------------------

--
-- Structure de la table `rapports`
--

CREATE TABLE `rapports` (
  `id_rapport` int(11) NOT NULL,
  `id_vehicule` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nature_juridique` enum('Volé','Non volé') NOT NULL,
  `date_emission` date NOT NULL,
  `horodatage` datetime DEFAULT current_timestamp(),
  `title` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_user` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Agent','User') DEFAULT 'User',
  `email` varchar(150) DEFAULT NULL,
  `is_actif` tinyint(1) DEFAULT 0,
  `token` text DEFAULT NULL,
  `expire` int(11) DEFAULT 14,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_user`, `username`, `password_hash`, `role`, `email`, `is_actif`, `token`, `expire`, `created_at`) VALUES
(1, 'geekgenius', '$2y$10$v3i5F2Y8MI.4fvQCr7dZ..p4nIEgoI5IgIVGcQW5UERxtRFuE6G8G', 'User', 'gbelsalvador7@gmail.com', 1, '478f350bc0beb87131eacc207d7e429c9b5ddbdfbf8414962a0d58afeb2642ef', 14, '2026-01-18 07:28:27'),
(2, 'Admin', '$2y$10$v3i5F2Y8MI.4fvQCr7dZ..p4nIEgoI5IgIVGcQW5UERxtRFuE6G8G', 'User', 'gbelsalvador6@gmail.com', 0, NULL, 14, '2026-01-18 07:35:16');

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

CREATE TABLE `vehicules` (
  `id_vehicule` int(11) NOT NULL,
  `id_proprietaire` int(11) NOT NULL,
  `marque` varchar(100) DEFAULT NULL,
  `modele` varchar(100) DEFAULT NULL,
  `vin` char(17) NOT NULL,
  `plaque` varchar(20) NOT NULL,
  `boite_vitesse` varchar(50) DEFAULT NULL,
  `carburant` enum('Essence','Diesel') NOT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `traction` enum('Avant','Arrière') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `vehicules`
--

INSERT INTO `vehicules` (`id_vehicule`, `id_proprietaire`, `marque`, `modele`, `vin`, `plaque`, `boite_vitesse`, `carburant`, `pays`, `province`, `traction`, `created_at`) VALUES
(1, 1, 'toyta', 'rsm', 'VF1AB123456789012', 'ABC 123', 'Manuelle', 'Essence', 'RDC', 'Kinshasa', 'Avant', '2026-01-18 07:33:21');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `proprietaires`
--
ALTER TABLE `proprietaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proprietaire` (`id_proprietaire`);

--
-- Index pour la table `rapports`
--
ALTER TABLE `rapports`
  ADD PRIMARY KEY (`id_rapport`),
  ADD KEY `id_vehicule` (`id_vehicule`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD PRIMARY KEY (`id_vehicule`),
  ADD UNIQUE KEY `vin` (`vin`),
  ADD UNIQUE KEY `plaque` (`plaque`),
  ADD KEY `id_proprietaire` (`id_proprietaire`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `logs`
--
ALTER TABLE `logs`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `proprietaires`
--
ALTER TABLE `proprietaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `rapports`
--
ALTER TABLE `rapports`
  MODIFY `id_rapport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `vehicules`
--
ALTER TABLE `vehicules`
  MODIFY `id_vehicule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `proprietaires`
--
ALTER TABLE `proprietaires`
  ADD CONSTRAINT `proprietaires_ibfk_1` FOREIGN KEY (`id_proprietaire`) REFERENCES `utilisateurs` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `rapports`
--
ALTER TABLE `rapports`
  ADD CONSTRAINT `rapports_ibfk_1` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapports_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateurs` (`id_user`) ON DELETE SET NULL;

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`id_proprietaire`) REFERENCES `proprietaires` (`id_proprietaire`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
