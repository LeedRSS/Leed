--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 28/01/2017
--#####			Version Leed : v2.0
--#####
--##### 		Feature(s) :
--#####			- Renomme les tables event et feed pour l'utilisateur courant en vue du multi-utilisateurs
--#####
--######################################################################################################

-- Mise à jour table user
RENAME TABLE `##MYSQL_PREFIX##event` TO `##MYSQL_PREFIX####FIRST_USER_LOGIN##event`, `##MYSQL_PREFIX##feed` to `##MYSQL_PREFIX####FIRST_USER_LOGIN##feed`, `##MYSQL_PREFIX##folder` to `##MYSQL_PREFIX####FIRST_USER_LOGIN##folder`;
