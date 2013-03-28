<?php

/**
 * Création du dossier de favicons
 */
if (!file_exists(Plugin::path() . 'favicons/')) {
    $res = mkdir(Plugin::path() . 'favicons/');
    if(!$res) {
        echo 'Impossible de créer le dossier pour stocker les favicons, vérifiez les droits sur le serveur';
    }
}