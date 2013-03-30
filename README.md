Leed
====

Leed (contraction de Light Feed) est un agrégateur RSS & ATOM libre et minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.

Cet agrégateur peux s'installer sur votre propre serveur et fonctionne avec un système de CRON afin de traiter les informations de manière invisible et de les afficher le plus rapidement possible lorsque vous vous y connectez.

- Application : Leed (Light Feed)
- Version : 1.1 Beta
- Auteur : Valentin CARRUESCO aka Idleman (idleman@idleman.fr)
- Page du projet : http://projet.idleman.fr/leed
- Licence : CC by-nc-sa (http://creativecommons.org/licenses/by-nc-sa/2.0/fr/) 



Présentation
====

Leed (contraction de Light Feed) est un agrégatteur RSS libre et minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.
	
Toutes les tâches de traitements de flux sont effectuées de manière invisible par une tâche synchronisée (Cron), ainsi, l'utilisateur ne subit pas les lenteurs dues à la récupération et au traitement de chacuns des flux suivis.

A noter que Leed est compatible toutes résolutions, sur pc, tablettes et smartphone et fonctionne sous tous les navigateurs avec son skin par défaut.
	
Le script est également compatible avec les fichiers d'exports/imports OPML ce qui rend la migration de tous les agrégateurs réspectant le standard OPML simple et rapide.

Pré-requis
====

- Serveur Apache conseillé (Non testé sur les autres serveurs types Nginx ...)
- PHP 5.3 minimum (facultatif, conseillé)
- MySQL
- Un peu de bon sens :)


Installation
====

1. Récuperez le projet sur la page: http://projet.idleman.fr/leed/?page=Téléchargement ou sur notre page github: https://github.com/ldleman/Leed
2. Placez le projet dans votre repertoire web et appliquez une permission chmod 775 (nb si vous êtes sur un hebergement ovh, préférez un 0755 ou vous aurez une erreur 500) sur le dossier et son contenu
3. Depuis votre navigateur, accedez à la page d'installation install.php (ex : http://votre.domaine.fr/leed/install.php) et suivez les instructions.
4. Une fois l'installation terminée, supprimez le fichier install.php par mesure de sécurité
5. [Optionnel] Si vous souhaitez que les mises a jour de flux se fassent automatiquement, mettez en place un cron (sudo crontab -e pour ouvrir le fichier de cron) et placez y un appel vers la page http://votre.domaine.fr/leed/action.php?action=synchronize ex : 
		0 * * * * wget --no-check-certificate -q -O /var/www/leed/logsCron "http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
		Pour mettre à jour vos flux toutes les heures à la minute 0 (il est conseillé de ne pas mettre une fréquence trop rapide pour laisser le temps au script de s'executer).
		<Nb> : Si vous n'avez pas accès a la commande wget sur votre serveur, vous pouvez essayer la commande suivante : 
		0 * * * * /usr/bin/wget --no-check-certificate -O /var/www/leed/logsCron
		"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation" >
		/dev/null 2>&1
6. Le script est installé, merci d'avoir choisis Leed, l'agrégatteur RSS libre et svelte :p.


Questions courantes	(F.A.Q)
====

Vous pouvez retrouver la FAQ du projet ici : http://projet.idleman.fr/leed/?page=FAQ

Librairies utilisées
==

- Responsive / Cross browser : Initializr (http://www.initializr.com)
- Javascript : JQuery (http://www.jquery.com)
- Moteur template : RainTPL (http://www.raintpl.com)
- Parseur RSS : SimplePie (http://simplepie.org)