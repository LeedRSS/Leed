/* #############
###  MISE À JOUR Base de données de Leed pour fonctionnement en v1.5

Conseils :
- Avant d'effectuer la mise à jour, sauvegardez votre BDD et exportez vos flux en OPML.
- Attention : "leed_" est à remplacer par votre préfix de table.
- Ce fichier est à supprimer après installation.

Description :
- Les requêtes suivantes sont a exécuter sur votre Base de données Leed avec phpMyAdmin par exemple

############### */

-- Mise à jour index Table des Event
ALTER TABLE `leed_event` ADD KEY `indexfeed` (`feed`);
ALTER TABLE `leed_event` ADD KEY `indexunread` (`unread`);
ALTER TABLE `leed_event` ADD KEY `indexfavorite` (`favorite`);

-- Mise à jour index Table des Feed
ALTER TABLE `leed_feed` ADD KEY `indexfolder` (`folder`);

-- Mise à jour table Event pour la synchronisation des flux (OBLIGATOIRE)
ALTER TABLE `leed_event` ADD `syncId` INT UNSIGNED NOT NULL;