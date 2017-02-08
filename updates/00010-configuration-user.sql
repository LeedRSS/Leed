--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 08/02/2017
--#####			Version Leed : v2.0
--#####
--##### 		Feature(s) :
--#####			- Gestion de la configuration au niveau de l'utilisateur
--#####
--######################################################################################################

-- Mise à jour table user
DELETE FROM `##MYSQL_PREFIX##configuration` WHERE `key`='root';

