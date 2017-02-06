--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 28/01/2017
--#####			Version Leed : v2.0
--#####
--##### 		Feature(s) :
--#####			- Renomme les tables event et feed pour l'utilisateur courant en vue du multi-utilisateurs
--#####			- place le sel sur la table du user au lieu de la configuration pour plus de 
--#####                 complexité et de sécurité
--#####
--######################################################################################################

-- Mise à jour table user
RENAME TABLE `##MYSQL_PREFIX##event` TO `##MYSQL_PREFIX####FIRST_USER_LOGIN##event`, `##MYSQL_PREFIX##feed` to `##MYSQL_PREFIX####FIRST_USER_LOGIN##feed`, `##MYSQL_PREFIX##folder` to `##MYSQL_PREFIX####FIRST_USER_LOGIN##folder`;
ALTER TABLE `##MYSQL_PREFIX##user` ADD salt VARCHAR(255) NOT NULL;

UPDATE `##MYSQL_PREFIX##user` `user`,
    ( SELECT `value`
    FROM `##MYSQL_PREFIX##configuration`
    WHERE `key`='cryptographicSalt' ) `configuration`
SET `salt`=`configuration`.value
WHERE `user`.`id`=1;
