-- EcoRide - schema MySQL/MariaDB
-- DB: ecoride
-- Compatible with data.php (tables id_user, id_chauff)

CREATE DATABASE IF NOT EXISTS `ecoride`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `ecoride`;

-- Passagers
CREATE TABLE IF NOT EXISTS `id_user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(80) NOT NULL,
  `prenom` VARCHAR(80) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `cleConnexion` VARCHAR(32) NULL,
  `dateCreation` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_email` (`email`),
  UNIQUE KEY `uniq_user_key` (`cleConnexion`)
) ENGINE=InnoDB;

-- Chauffeurs
CREATE TABLE IF NOT EXISTS `id_chauff` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(80) NOT NULL,
  `prenom` VARCHAR(80) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `voiture` VARCHAR(120) NOT NULL,
  `imma` VARCHAR(32) NOT NULL,
  `vehiculeDateImmat` DATE NULL,
  `dateNaissance` VARCHAR(20) NULL,
  `cleConnexion` VARCHAR(32) NULL,
  `dateCreation` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_chauff_email` (`email`),
  UNIQUE KEY `uniq_chauff_imma` (`imma`),
  UNIQUE KEY `uniq_chauff_key` (`cleConnexion`)
) ENGINE=InnoDB;

