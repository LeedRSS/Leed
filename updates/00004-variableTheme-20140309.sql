--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 09/03/2014
--#####			Version Leed : v1.7
--#####
--##### 		Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 		Feature(s) :
--#####			- Ajout de la variable 'theme' remplaçant celle de 'constant.php'
--#####
--######################################################################################################

-- Mise à jour table event (Obligatoire)
INSERT INTO `##MYSQL_PREFIX##configuration` (`key`, `value`) VALUES ('theme','marigolds');
