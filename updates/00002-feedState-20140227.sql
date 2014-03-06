--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 27/02/2014
--#####			Version Leed : v1.7
--#####
--#####				Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 			Feature(s) :
--#####				- Option pour stocker si le flux à eu des erreurs lors de la dernière synchro.
--#####
--######################################################################################################

-- Mise à jour table FEED (Obligatoire)
ALTER TABLE `##MYSQL_PREFIX##feed` DROP `lastSyncInError`;
ALTER TABLE `##MYSQL_PREFIX##feed` ADD `lastSyncInError` INT(1) DEFAULT 0 NOT NULL;
