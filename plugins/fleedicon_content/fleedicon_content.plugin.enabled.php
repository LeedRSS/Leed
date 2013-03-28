<?php

/*
  @name Fleedicon_content
  @author gavrochelegnou <gavrochelegnou@trashmail.net>
  @licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
  @version 1.0.0
  @description Le plugin Fleedicon_content ajoute un favicon à gauche de chaque item lors de la lecture
 */

/**
 * Télécharge le Favicon en fonction de l'ID d'un flux
 * @param int $feed_id
 */
function fleedicon_content_plugin_getFavicon($feed_id) {

    /**
     * Chemin de l'icone pour ce flux
     */
    $iconPath = Plugin::path() . 'favicons/' . $feed_id . '.png';

    /**
     * S'il n'existe pas encore
     * il faut le télécharger
     */
    if (!file_exists($iconPath)) {

        /**
         * On récupère les infos du flux
         */
        $f = new Feed();
        $f = $f->getById($feed_id);

        /**
         * Et notamment le site web
         * Plus pertinent que l'URL du flux à cause notamment de feedburner
         */
        $url = parse_url($f->getWebsite());

        /**
         * Si l'URL est inexistante ou malformée on essaie 
         * quand même avec l'URL du flux
         */
        if (!$url) {
            $url = parse_url($f->getUrl());
        }

        /**
         * Si l'une des deux marche on essai d'appeler le service g.etfv.co
         */
        if ($url) {
            file_put_contents($iconPath, file_get_contents('http://g.etfv.co/' . $url['scheme'] . '://' . $url['host']));
        } else {
            /**
             * Sinon on utilise l'icône par défaut
             */
            copy(Plugin::path() . 'default.png', $iconPath);
        }
    }

    /**
     * Besoin de ça pour renseigner correctement le ALT
     */
    global $allFeeds;

    /**
     * Et l'image brute, sans CSS
     */
    echo '<img src="' . $iconPath . '" width="16" height="16" alt="' . htmlentities($allFeeds['idMap'][$feed_id]['name'], ENT_QUOTES) . '" />';
}

/**
 * Affiche un favicon en fonction d'un objet "event"
 * @param event $event
 */
function fleedicon_content_plugin_addFavicon(&$event) {
    echo fleedicon_content_plugin_getFavicon($event->getFeed());
}

/**
 * Affiche un favicon en fonction d'un tableau "feed"
 * @param type $feed
 */
function fleedicon_aside_plugin_addFavicon(&$feed) {
    echo fleedicon_content_plugin_getFavicon($feed['id']);
}


/**
 * Ajout de l'icone à coté de chaque item
 */
Plugin::addHook("event_pre_title", "fleedicon_content_plugin_addFavicon");

/**
 * Ajout de l'icone à coté du flux
 */
Plugin::addHook("menu_pre_feed_link", "fleedicon_aside_plugin_addFavicon");

