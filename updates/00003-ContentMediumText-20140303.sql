--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 03/03/2014
--#####			Version Leed : v1.7
--#####
--#####				Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 			Feature(s) :
--#####				- Augmentation de la taille du champs pour permettre l'insertion de gros articles
--#####
--######################################################################################################

-- Mise à jour table event (Obligatoire)
ALTER TABLE `##MYSQL_PREFIX##event` CHANGE `content` `content` MEDIUMTEXT;
