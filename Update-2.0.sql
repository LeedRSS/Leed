/* #############
###  MISE À JOUR Base de données de Leed pour fonctionnement en v2.0

Conseils :
- Avant d'effectuer la mise à jour, sauvegardez votre BDD et exportez vos flux en OPML.
- Attention : "leed_" est à remplacer par votre préfix de table.
- Ce fichier est à supprimer après installation.

Description :
- Les requêtes suivantes sont a exécuter sur votre Base de données Leed avec phpMyAdmin par exemple

############### */
-- Mise à jour table FOLDER (Obligatoire)
ALTER TABLE `leed_feed` ADD `isverbose` INT(1) NOT NULL;

-- évolution pour les flux RSS défini verbeux qu'il faut ou ne faut pas afficher sur la page d'accueil.
INSERT INTO `leed_configuration` (`key`,`value`) VALUES ('optionFeedIsVerbose',1);