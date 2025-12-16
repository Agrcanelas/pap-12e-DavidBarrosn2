-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27-Nov-2025
-- Servidor: MariaDB 10.4.32
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Base de dados: `humanicare`
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `humanicare`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `humanicare`;

-- --------------------------------------------------------
-- Tabela: utilizador
-- --------------------------------------------------------

CREATE TABLE `utilizador` (
  `utilizador_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(200) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(20) NOT NULL,
  `data_registo` DATE NOT NULL,
  PRIMARY KEY (`utilizador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Dados iniciais da tabela utilizador
-- --------------------------------------------------------

INSERT INTO `utilizador` (`utilizador_id`, `nome`, `email`, `senha`, `data_registo`) VALUES
(1, 'ze', 'ze@gmail.com', '1234', '2025-11-02');

-- --------------------------------------------------------
-- Tabela: evento
-- --------------------------------------------------------

CREATE TABLE `evento` (
  `evento_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(200) NOT NULL,
  `descricao` TEXT NOT NULL,
  `data_evento` DATE NOT NULL,
  `local_evento` VARCHAR(200) NOT NULL,
  `imagem` VARCHAR(255) DEFAULT NULL,
  `utilizador_id` INT(11) NOT NULL,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`evento_id`),
  KEY `fk_evento_utilizador` (`utilizador_id`),
  CONSTRAINT `fk_evento_utilizador`
    FOREIGN KEY (`utilizador_id`)
    REFERENCES `utilizador` (`utilizador_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
