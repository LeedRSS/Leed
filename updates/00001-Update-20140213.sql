--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 13/02/2014
--#####			Feature(s) :
--#####				- Option pour cacher les flux souhaités sur la page d'accueil
--#####
--######################################################################################################

-- Mise à jour table FOLDER (Obligatoire)
ALTER TABLE `leed_feed` ADD `isverbose` INT(1) NOT NULL;

-- évolution pour les flux RSS défini verbeux qu'il faut ou ne faut pas afficher sur la page d'accueil.
INSERT INTO `leed_configuration` (`key`,`value`) VALUES ('optionFeedIsVerbose',1);