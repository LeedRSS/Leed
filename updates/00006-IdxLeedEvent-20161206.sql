--######################################################################################################
--#####
--#####     MISE À JOUR Base de données de Leed
--#####			Date : 06/12/2016
--#####			Version Leed : v1.7
--#####
--##### 		Préfixe des tables : ##MYSQL_PREFIX## est remplacé automatiquement
--#####
--##### 		Feature(s) :
--#####			- Ajout d'index pour optimiser la lecture
--#####
--######################################################################################################

-- Mise à jour table event
CREATE INDEX indexguidfeed on `##MYSQL_PREFIX##event` (guid(60),feed);
