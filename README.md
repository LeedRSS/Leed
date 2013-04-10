Leed
====

Leed (contraction de Light Feed) est un agrégateur [RSS](https://fr.wikipedia.org/wiki/Rss)/[ATOM](https://fr.wikipedia.org/wiki/Atom) libre et minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.

Cet agrégateur peut s'installer sur votre propre serveur et fonctionne avec un système de tâches [cron](https://fr.wikipedia.org/wiki/Cron) afin de traiter les informations de manière transparente et de les afficher le plus rapidement possible lorsque vous vous y connectez.

- Application : Leed (Light Feed)
- Version : 1.1 Beta
- Auteur : Valentin CARRUESCO aka Idleman (idleman@idleman.fr)
- Page du projet : http://projet.idleman.fr/leed
- Licence : [CC by-nc-sa](http://creativecommons.org/licenses/by-nc-sa/2.0/fr/)

Présentation
====

Leed (contraction de Light Feed) est un agrégateur RSS libre et minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.

Toutes les tâches de traitements de flux sont effectuées de manière invisible par une tâche programmée (cron), ainsi, l'utilisateur ne subit pas les lenteurs dues à la récupération et au traitement de chacuns des flux suivis.

A noter que Leed est compatible toutes résolutions, sur pc, tablette et smartphone.

Leed est également compatible avec le format d'import/export [OPML](https://fr.wikipedia.org/wiki/OPML) ce qui le rend compatible avec les agrégateurs respectant ce standard.

Pré-requis
====

- Serveur Apache conseillé (non testé sur les autres serveurs types Nginx…)
- PHP 5.3 minimum
- MySQL
- Un peu de bon sens :-)

Installation
====

1. Récupérez le projet sur [idleman.fr](http://projet.idleman.fr/leed/?page=Téléchargement) ou sur la page [github](https://github.com/ldleman/Leed).
2. Placez le projet dans votre repertoire web et appliquez si nécessaire une permission _chmod 775_ (si vous êtes sur un hebergement ovh, préférez un _0755_ ou vous aurez une erreur 500) sur le dossier et son contenu.
3. Depuis votre navigateur, accédez à la page d'installation _install.php_ (ex : votre.domaine.fr/leed/install.php) et suivez les instructions.
4. Une fois l'installation terminée, supprimez le fichier _install.php_ par mesure de sécurité.
5. [Optionnel] Si vous souhaitez que les mises a jour de flux se fassent automatiquement toutes les heures, mettez en place un cron (avec _crontab -e_ par exemple) :
``` crontab
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log ↩
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
Il est conseillé de ne pas mettre une fréquence trop rapide pour laisser le temps au script de s'exécuter. Si vous n'avez pas accès a la commande _wget_ sur votre serveur, vous pouvez essayer son chemin complet _/usr/bin/wget_.
6. Le script est installé, merci d'avoir choisis Leed, l'agrégateur RSS libre et svelte :p


Foire Aux Questions (F.A.Q.)
====

Vous pouvez retrouver la FAQ du projet ici : http://projet.idleman.fr/leed/?page=FAQ

Bibliothèques utilisées
==

- Responsive / Cross browser : Initializr (http://www.initializr.com)
- Javascript : JQuery (http://www.jquery.com)
- Moteur template : RainTPL (http://www.raintpl.com)
- Parseur RSS : SimplePie (http://simplepie.org)