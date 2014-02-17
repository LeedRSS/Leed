--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 13/02/2014
--#####
--#####				Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 			Feature(s) :
--#####				- Option pour cacher les flux souhaités sur la page d'accueil
--#####
--######################################################################################################

-- Mise à jour table FOLDER (Obligatoire)
ALTER TABLE `##MYSQL_PREFIX##feed` DROP `isverbose`;
ALTER TABLE `##MYSQL_PREFIX##feed` ADD `isverbose` INT(1) NOT NULL;

-- évolution pour les flux RSS défini verbeux qu'il faut ou ne faut pas afficher sur la page d'accueil.
DELETE FROM `##MYSQL_PREFIX##configuration` WHERE `key` = 'optionFeedIsVerbose';
INSERT INTO `##MYSQL_PREFIX##configuration` (`key`,`value`) VALUES ('optionFeedIsVerbose',1);