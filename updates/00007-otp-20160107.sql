--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 07/01/2017
--#####			Version Leed : v1.7
--#####
--##### 		Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 		Feature(s) :
--#####			- Champs pour le One Time Password des utilisateurs
--#####
--######################################################################################################

-- Mise à jour table user
ALTER TABLE `##MYSQL_PREFIX##user` ADD `otpSecret` VARCHAR(225);
