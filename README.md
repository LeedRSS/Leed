# Leed

fr [en](#leed-english-documentation) [es](#leed-span-documentation)

Leed (contraction de Light Feed) est un agrégateur [RSS](https://fr.wikipedia.org/wiki/Rss)/[ATOM](https://fr.wikipedia.org/wiki/Atom) minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.

Cet agrégateur peut s'installer sur votre propre serveur et fonctionne avec un système de tâches [cron](https://fr.wikipedia.org/wiki/Cron) afin de traiter les informations de manière transparente et de les afficher le plus rapidement possible lorsque vous vous y connectez.

- Application : Leed (Light Feed)
- Version : Branche de Développement
- Auteur : Valentin CARRUESCO aka Idleman (idleman@idleman.fr)
- Page du projet : http://projet.idleman.fr/leed
- Licence : [CC by-nc-sa](http://creativecommons.org/licenses/by-nc-sa/2.0/fr/)

Toutes les tâches de traitements de flux sont effectuées de manière invisible par une tâche programmée (cron), ainsi, l'utilisateur ne subit pas les lenteurs dues à la récupération et au traitement de chacun des flux suivis.

A noter que Leed est compatible toutes résolutions, sur pc, tablette et smartphone.

Leed est également compatible avec le format d'import/export [OPML](https://fr.wikipedia.org/wiki/OPML) ce qui le rend compatible avec les agrégateurs respectant ce standard.

### Pré-requis

- Serveur Apache conseillé (non testé sur les autres serveurs type Nginx…)
- PHP 5.3 minimum
- MySQL
- Un peu de bon sens :-)

### Installation

1. Récupérez le projet sur [idleman.fr](http://projet.idleman.fr/leed/?page=Téléchargement) ou sur la page [github](https://github.com/ldleman/Leed).
2. Placez le projet dans votre répertoire web et appliquez si nécessaire une permission _chmod 775_ (si vous êtes sur un hebergement ovh, préférez un _0755_ ou vous aurez une erreur 500) sur le dossier et son contenu.
3. Depuis votre navigateur, accédez à la page d'installation _install.php_ (ex : votre.domaine.fr/leed/install.php) et suivez les instructions.
4. Une fois l'installation terminée, supprimez le fichier _install.php_ par mesure de sécurité.
5. [Optionnel] Si vous souhaitez que les mises à jour de flux se fassent automatiquement, mettez en place un cron. Voir ci-après. Il est conseillé de ne pas mettre une fréquence trop rapide pour laisser le temps au script de s'exécuter.
6. Le script est installé, merci d'avoir choisi Leed, l'agrégateur RSS svelte :p

### Tâches programmées avec cron

On peut éditer les tâches programmées avec _crontab -e_. Il y a deux façons de mettre à jour les flux. Les exemples qui suivent mettent à jour toutes les heures.

1. En appelant directement Leed. Cette méthode a l'avantage d'être directe et de produire une sortie formatée pour la console mais requiert un accès local :
crontab
```Batchfile
0 * * * * cd (...)/leed && php action.php >> logs/cron.log 2>&1
```

1. En appelant Leed depuis le client web _wget_. Cette méthode nécessite un accès réseau mais a l'avantage de pouvoir être déclenchée à distance. Afin de contrôler l'accès, il est nécessaire de fournir le code de synchronisation :
```Batchfile
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
 Si vous n'avez pas accès a la commande _wget_ sur votre serveur, vous pouvez essayer son chemin complet _/usr/bin/wget_.

### Foire Aux Questions (F.A.Q.)

Vous pouvez retrouver la FAQ du projet ici : http://projet.idleman.fr/leed/?page=FAQ

### Plugins

Le dépot [Leed market](https://github.com/ldleman/Leed-market) contient tous les plugins à jour et approuvés officiellement pour le logiciel Leed.

### Bibliothèques utilisées

- Responsive / Cross browser : Initializr (http://www.initializr.com)
- Javascript : JQuery (http://www.jquery.com)
- Moteur template : RainTPL (http://www.raintpl.com)
- Parseur RSS : SimplePie (http://simplepie.org)


---------


# Leed (English documentation)

Leed (short for Light Feed) is an aggregator [RSS](https://fr.wikipedia.org/wiki/Rss)/[ATOM](https://fr.wikipedia.org/wiki/Atom) minimalist allowing consultation RSS rapid and non-intrusive.

This reader can be installed on your own server and works with a system of [cron](https://fr.wikipedia.org/wiki/Cron)  tasks to process information in a transparent manner and display quickly possible when you connect to.

- Application: Leed (Light Feed)
- Version : Branch Development
- Author : Valentin Carruesco aka Idleman ( idleman@idleman.fr )
- Project page: http://projet.idleman.fr/leed
- License: [CC by-nc -sa](http://creativecommons.org/licenses/by-nc-sa/2.0/fr/)

All tasks are performed treatments flows invisibly by a scheduled task (cron), so the user does not experience delays due to the recovery and processing of each of the monitored flow.

Note that Leed is compatible with all resolutions on pc, tablet and smartphone.

Leed is also compatible with [OPML](https://fr.wikipedia.org/wiki/OPML) import / export which makes it compatible with aggregators applying the standard .

### Prerequisites

- Recommended Apache server ( not tested on other types Nginx servers ...)
- PHP 5.3 minimum
- MySQL
- A little common sense :-)

### Installation

1. Retrieve the project [idleman.fr](http://projet.idleman.fr/leed/?page=Téléchargement) or on [github](https://github.com/ldleman/Leed).
2. Place the project in your web directory and if necessary apply a permission _chmod 775_ (if you're on a ovh hosting, prefer _0755_ or you will get a error 500) on the folder and its contents .
3. From your browser, go to the setup page _install.php_ (eg your.domaine.fr/leed/install.ph ) and follow the instructions.
4. Once the installation is complete , remove the _install.php_ for security file.
5. [Optional] If you want the updates to make stream automatically set up a cron. See below. It is advisable not to put too rapid frequency to allow time to run the script.
6. The script is installed, thank you for choosing Leed , slender RSS aggregator :p

### Scheduled tasks with cron

You can edit scheduled tasks with _crontab -e_. There are two ways to update feeds. The following examples update every hour.

1. Calling directly Leed. This method has the advantage of being direct and produce formatted output to the console but requires local access :
Crontab
```Batchfile
0 * * * * cd (...)/leed && php action.php >> logs/cron.log 2>&1
```

1. Leed calling from the web client _wget_. This method requires network access but has the advantage that it can be triggered remotely. To control access, it is necessary to provide the synchronization code :
```Batchfile
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
 If you do not have access to the _wget_ command on your server, you can try the full path _/usr/bin/wget_.

### Frequently Asked Questions (F.A.Q.)

You can find the project FAQ here : http://projet.idleman.fr/leed/?page=FAQ

### Plugins

The deposit [Leed-market](https://github.com/ldleman/Leed-market) contains all the plugins up to date and officially approved for Leed software.

### Libraries used

- Responsive / Cross browser: Initializr ( http://www.initializr.com )
- Javascript: JQuery ( http://www.jquery.com )
- Template Engine: RainTPL ( http://www.raintpl.com )
- RSS Parser : SimplePie ( http://simplepie.org )
