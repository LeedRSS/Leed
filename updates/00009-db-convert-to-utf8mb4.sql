--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 18/01/2020
--#####			Version Leed : v1.8.5
--#####
--##### 		Feature(s) :
--#####			- Converti les tables vers l'encodage utf8mb4 pour gérer une palette de caractères bien plus grande
--#####
--######################################################################################################

ALTER DATABASE database_name CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci;
--# For each table:
ALTER TABLE `##MYSQL_PREFIX##configuration` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `##MYSQL_PREFIX##event` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `##MYSQL_PREFIX##feed` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `##MYSQL_PREFIX##folder` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `##MYSQL_PREFIX##user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
--# For each column:
ALTER TABLE `##MYSQL_PREFIX##configuration` CHANGE `key` `key` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##configuration` CHANGE `value` `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `creator` `creator` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `guid` `guid` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `content` `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `description` `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `link` `link` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##feed` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##feed` CHANGE `lastupdate` `lastupdate` VARCHAR(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##feed` CHANGE `description` `description` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##feed` CHANGE `website` `website` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##feed` CHANGE `url` `url` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##folder` CHANGE `name` `name` VARCHAR(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##user` CHANGE `login` `login` VARCHAR(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##user` CHANGE `password` `password` VARCHAR(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;
ALTER TABLE `##MYSQL_PREFIX##user` CHANGE `otpSecret` `otpSecret` VARCHAR(225) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
