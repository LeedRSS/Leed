--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 06/03/2014
--#####			Version Leed : v1.7
--#####
--#####				Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 			Feature(s) :
--#####				- Pliage des articles - issues : #87
--#####
--######################################################################################################

-- insertion du paramétrage par défaut
INSERT INTO `##MYSQL_PREFIX##configuration` (`key`, `value`) VALUES ('articleDisplayMode','summary');
-- suppression des anciennes variables
DELETE FROM `##MYSQL_PREFIX##configuration` WHERE (`key` = 'articleDisplayContent');
DELETE FROM `##MYSQL_PREFIX##configuration` WHERE (`key` = 'articleView');