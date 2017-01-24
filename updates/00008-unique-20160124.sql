--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 24/01/2017
--#####			Version Leed : v1.7
--#####
--##### 		Feature(s) :
--#####			- Rend unique le login d'un utilisateur
--#####			- Le champ `key` des configurations est maintenant un varchar 255
--#####			- Rend unique la colonne `key` des paramètres de configuration
--#####
--######################################################################################################

-- Mise à jour table user
ALTER TABLE `##MYSQL_PREFIX##user` ADD CONSTRAINT `uniquelogin` UNIQUE (login);

ALTER TABLE `##MYSQL_PREFIX##configuration` MODIFY `key` VARCHAR(255) NOT NULL;
CREATE TABLE `##MYSQL_PREFIX##configuration_new` LIKE `##MYSQL_PREFIX##configuration`;
ALTER TABLE `##MYSQL_PREFIX##configuration_new` ADD UNIQUE `uniquekey` (`key`);
INSERT INTO `##MYSQL_PREFIX##configuration_new`
    SELECT * FROM `##MYSQL_PREFIX##configuration`
        GROUP BY (`key`);
RENAME TABLE `##MYSQL_PREFIX##configuration` TO `##MYSQL_PREFIX##configuration_old`, `##MYSQL_PREFIX##configuration_new` to `##MYSQL_PREFIX##configuration`;
-- DROP TABLE `##MYSQL_PREFIX##configuration_old`;
