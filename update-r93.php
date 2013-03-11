<?php
error_reporting(E_ALL);
@set_time_limit(0);

/**
 *  met à jour la BDD de la rev92 à la rev 93
 *  Leed doit déjà être installé
 *  auteur : alefburzmali
 */

ob_start();
require 'constant.php';

// connexion
$mysql = new MySQLi(MYSQL_HOST,MYSQL_LOGIN,MYSQL_MDP,MYSQL_BDD);

$tables = array(
    'c' => MYSQL_PREFIX.'configuration',
    'e' => MYSQL_PREFIX.'event',
    'f' => MYSQL_PREFIX.'feed',
    'd' => MYSQL_PREFIX.'folder',
    'u' => MYSQL_PREFIX.'user',
);

// on convertit toutes les tables
foreach ($tables as $tb)
{
    echo '<br>conversion de la structure de la table '.$tb.' ... ';
    ob_flush(); flush();
    if (!$mysql->query('ALTER TABLE '.$tb.' CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci, ENGINE=InnoDB'))
    {
        echo 'erreur !<br>';
        print_r($mysql->error_list);
        ob_flush(); flush();
        die(1);
    }
    echo 'ok';
}

// on passe la connexion en utf-8 pour traiter les données
$mysql->query('SET NAMES utf8');

// maintenant on va récupérer toutes les tables pour faire html_entity_decode
function convert($table, $champs)
{
    global $mysql;
    echo '<br>conversion des données de la table '.$table.' ... ';
    ob_flush(); flush();

    $res = $mysql->query('SELECT * FROM '.$table);
    if ($res)
    {
        while ($row = $res->fetch_assoc())
        {
            $sql = 'UPDATE '.$table.' SET ';
            $first = true;
            foreach ($champs as $c)
            {
                $row[$c] = html_entity_decode($row[$c]);
                $sql .= ($first?'':', '). $c .'="'.$mysql->real_escape_string($row[$c]).'"';
                $first = false;
            }
            $sql .= ' WHERE id = '.$row['id'];
            if (!$mysql->query($sql))
            {
                echo 'erreur champ '.$row['id'].'<br>';
                print_r($mysql->error_list);

                echo '<br>on continue ... ';
                ob_flush(); flush();
            }
        }
        echo 'ok';
        $res->free();
    }
}

// evenements
convert($tables['e'], array('title','creator','content','description'));
// feed
convert($tables['f'], array('name','description'));
// folder
convert($tables['d'], array('name'));

echo '<br>Conversion terminée';
