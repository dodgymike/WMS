-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 22, 2012 at 06:45 PM
-- Server version: 5.5.20
-- PHP Version: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ctwug_noc`
--
CREATE DATABASE `ctwug_noc` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ctwug_noc`;

-- --------------------------------------------------------

--
-- Table structure for table `routerboard`
--

CREATE TABLE IF NOT EXISTS `routerboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `ros_serial` text NOT NULL,
  `last_check_in_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `routerboard_script_group`
--

CREATE TABLE IF NOT EXISTS `routerboard_script_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `routerboard_id` int(11) NOT NULL,
  `script_group_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `routerboard_id` (`routerboard_id`),
  KEY `script_group_id` (`script_group_id`),
  KEY `assigned_by` (`assigned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `routerboard_stats`
--

CREATE TABLE IF NOT EXISTS `routerboard_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `routerboard_id` int(11) NOT NULL,
  `stat_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `version` text NOT NULL,
  `cpu` text NOT NULL,
  `freq` text NOT NULL,
  `arch` text NOT NULL,
  `board` text NOT NULL,
  `fw` text NOT NULL,
  `ip` text NOT NULL,
  `ospf` text NOT NULL,
  `policy` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `routerboard_id` (`routerboard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `script`
--

CREATE TABLE IF NOT EXISTS `script` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `script_group_id` int(11) NOT NULL,
  `script_type_id` int(11) NOT NULL,
  `script_body` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `version` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `script_group_id` (`script_group_id`),
  KEY `script_type_id` (`script_type_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `script_group`
--

CREATE TABLE IF NOT EXISTS `script_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `script_type`
--

CREATE TABLE IF NOT EXISTS `script_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text CHARACTER SET latin1 NOT NULL,
  `password` text CHARACTER SET latin1 NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `routerboard_script_group`
--
ALTER TABLE `routerboard_script_group`
  ADD CONSTRAINT `routerboard_script_group_ibfk_1` FOREIGN KEY (`routerboard_id`) REFERENCES `routerboard` (`id`),
  ADD CONSTRAINT `routerboard_script_group_ibfk_2` FOREIGN KEY (`script_group_id`) REFERENCES `script_group` (`id`),
  ADD CONSTRAINT `routerboard_script_group_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `routerboard_stats`
--
ALTER TABLE `routerboard_stats`
  ADD CONSTRAINT `routerboard_stats_ibfk_1` FOREIGN KEY (`routerboard_id`) REFERENCES `routerboard` (`id`);

--
-- Constraints for table `script`
--
ALTER TABLE `script`
  ADD CONSTRAINT `script_ibfk_1` FOREIGN KEY (`script_group_id`) REFERENCES `routerboard_script_group` (`id`),
  ADD CONSTRAINT `script_ibfk_2` FOREIGN KEY (`script_type_id`) REFERENCES `script_type` (`id`),
  ADD CONSTRAINT `script_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);
