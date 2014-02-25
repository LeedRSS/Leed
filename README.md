Leed
====

Leed (contraction de Light Feed) est un agrégateur [RSS](https://fr.wikipedia.org/wiki/Rss)/[ATOM](https://fr.wikipedia.org/wiki/Atom) minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.

Cet agrégateur peut s'installer sur votre propre serveur et fonctionne avec un système de tâches [cron](https://fr.wikipedia.org/wiki/Cron) afin de traiter les informations de manière transparente et de les afficher le plus rapidement possible lorsque vous vous y connectez.

- Application : Leed (Light Feed)
- Version : Branche Stable
- Auteur : Valentin CARRUESCO aka Idleman (idleman@idleman.fr)
- Page du projet : http://projet.idleman.fr/leed
- Licence : [CC by-nc-sa](http://creativecommons.org/licenses/by-nc-sa/2.0/fr/)

Toutes les tâches de traitements de flux sont effectuées de manière invisible par une tâche programmée (cron), ainsi, l'utilisateur ne subit pas les lenteurs dues à la récupération et au traitement de chacuns des flux suivis.

A noter que Leed est compatible toutes résolutions, sur pc, tablette et smartphone.

Leed est également compatible avec le format d'import/export [OPML](https://fr.wikipedia.org/wiki/OPML) ce qui le rend compatible avec les agrégateurs respectant ce standard.

Pré-requis
====

- Serveur Apache conseillé (non testé sur les autres serveurs type Nginx…)
- PHP 5.3 minimum
- MySQL
- Un peu de bon sens :-)

Installation
====

1. Récupérez le projet sur [idleman.fr](http://projet.idleman.fr/leed/?page=Téléchargement) ou sur la page [github](https://github.com/ldleman/Leed).
2. Placez le projet dans votre répertoire web et appliquez si nécessaire une permission _chmod 775_ (si vous êtes sur un hebergement ovh, préférez un _0755_ ou vous aurez une erreur 500) sur le dossier et son contenu.
3. Depuis votre navigateur, accédez à la page d'installation _install.php_ (ex : votre.domaine.fr/leed/install.php) et suivez les instructions.
4. Une fois l'installation terminée, supprimez le fichier _install.php_ par mesure de sécurité.
5. [Optionnel] Si vous souhaitez que les mises à jour de flux se fassent automatiquement, mettez en place un cron. Voir ci-après. Il est conseillé de ne pas mettre une fréquence trop rapide pour laisser le temps au script de s'exécuter.
6. Le script est installé, merci d'avoir choisi Leed, l'agrégateur RSS svelte :p

Tâches programmées avec cron
====

On peut éditer les tâches programmées avec _crontab -e_. Il y a deux façons de mettre à jour les flux. Les exemples qui suivent mettent à jour toutes les heures.

1. En appelant directement Leed. Cette méthode a l'avantage d'être directe et de produire une sortie formatée pour la console mais requiert un accès local :
``` crontab
0 * * * * cd (...)/leed && php action.php >> logs/cron.log 2>&1
```

1. En appelant Leed depuis le client web _wget_. Cette méthode nécessite un accès réseau mais a l'avantage de pouvoir être déclenchée à distance. Afin de contrôler l'accès, il est nécessaire de fournir le code de synchronisation :
```
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
 Si vous n'avez pas accès a la commande _wget_ sur votre serveur, vous pouvez essayer son chemin complet _/usr/bin/wget_.

Foire Aux Questions (F.A.Q.)
====

Vous pouvez retrouver la FAQ du projet ici : http://projet.idleman.fr/leed/?page=FAQ

Plugins
====
Le dépot [Leed market](https://github.com/ldleman/Leed-market) contient tous les plugins à jour et approuvés officiellement pour le logiciel Leed.

Bibliothèques utilisées
==

- Responsive / Cross browser : Initializr (http://www.initializr.com)
- Javascript : JQuery (http://www.jquery.com)
- Moteur template : RainTPL (http://www.raintpl.com)
- Parseur RSS : SimplePie (http://simplepie.org)
