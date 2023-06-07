# Leed

> Français - [English](#leed-english-documentation) - [Español](#leed-documentaci%C3%B3n-espa%C3%B1ola)

Leed (contraction de Light Feed) est un agrégateur [RSS](https://fr.wikipedia.org/wiki/Rss)/[ATOM](https://fr.wikipedia.org/wiki/Atom_Syndication_Format) minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.

Cet agrégateur peut s'installer sur votre propre serveur et fonctionne avec un système de tâches [cron](https://fr.wikipedia.org/wiki/Cron) afin de traiter les informations de manière transparente et de les afficher le plus rapidement possible lorsque vous vous y connectez.

- Application : Leed (Light Feed)
- Auteur : Valentin CARRUESCO aka **Idleman** (http://blog.idleman.fr)
- Contributeurs principaux : (par ordre alphabétique des pseudos)
  - Maël ILLOUZ aka **Cobalt74** (https://www.cobestran.com)
  - Christophe HENRY aka **Sbgodin** (http://sbgodin.fr)
  - Simon ALBERNY aka **Simounet** (https://www.simounet.net)
- Page du projet : https://github.com/LeedRSS/Leed
- Licence : [AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0.html)

Toutes les tâches de traitements de flux sont effectuées de manière invisible par une tâche programmée (cron), ainsi, l'utilisateur ne subit pas les lenteurs dues à la récupération et au traitement de chacun des flux suivis.

A noter que Leed est compatible toutes résolutions, sur pc, tablette et smartphone.

Leed est également compatible avec le format d'import/export [OPML](https://fr.wikipedia.org/wiki/OPML) ce qui le rend compatible avec les agrégateurs respectant ce standard.

### Pré-requis

- Serveur Apache conseillé (non testé sur les autres serveurs type Nginx…)
- PHP 7.2 minimum
- MySQL
- Un peu de bon sens :-)

### Installation

1. Récupérez le projet sur la page [github](https://github.com/LeedRSS/Leed/releases/latest).
2. Placez le projet dans votre répertoire web et appliquez si nécessaire une permission _chmod 775_ (si vous êtes sur un hebergement ovh, préférez un _0755_ ou vous aurez une erreur 500) sur le dossier et son contenu.
3. Depuis votre navigateur, accédez à la page d'installation _install.php_ (ex : votre.domaine.fr/leed/install.php) et suivez les instructions.
4. Une fois l'installation terminée, supprimez le fichier _install.php_ par mesure de sécurité.
5. [Optionnel] Si vous souhaitez que les mises à jour de flux se fassent automatiquement, mettez en place un cron. Voir ci-après. Il est conseillé de ne pas mettre une fréquence trop rapide pour laisser le temps au script de s'exécuter.
6. Le script est installé, merci d'avoir choisi Leed, l'agrégateur RSS svelte :p

### Tâches programmées avec cron

On peut éditer les tâches programmées avec _crontab -e_. Il y a deux façons de mettre à jour les flux. Les exemples qui suivent mettent à jour toutes les heures.

1. En appelant directement Leed. Cette méthode a l'avantage d'être directe et de produire une sortie formatée pour la console mais requiert un accès local :
```Batchfile
crontab
0 * * * * cd (...)/leed && php action.php >> logs/cron.log 2>&1
```
2. En appelant Leed depuis le client web _wget_. Cette méthode nécessite un accès réseau mais a l'avantage de pouvoir être déclenchée à distance. Afin de contrôler l'accès, il est nécessaire de fournir le code de synchronisation :
```Batchfile
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
 Si vous n'avez pas accès a la commande _wget_ sur votre serveur, vous pouvez essayer son chemin complet _/usr/bin/wget_.

### Plugins

Le dépot [Leed market](https://github.com/Leed-market) contient tous les plugins à jour et approuvés officiellement pour le logiciel Leed.

### Bibliothèques utilisées

- Responsive / Cross browser : Initializr (http://www.initializr.com)
- Javascript : JQuery (http://www.jquery.com)
- Moteur template : RainTPL (https://github.com/feulf/raintpl)
- Parseur RSS : SimplePie (http://simplepie.org)
- Php GD : LibGD (https://libgd.github.io/)


---------


# Leed (English documentation)

Leed (short for Light Feed) is a minimalist [RSS](https://en.wikipedia.org/wiki/Rss)/[ATOM](https://en.wikipedia.org/wiki/Atom_(web_standard)) aggregator which offers fast RSS consultation and non-intrusive features.

This reader can be installed on your own server and works with a system of [cron](https://en.wikipedia.org/wiki/Cron)  tasks to process information in a transparent manner and display the updates as quickly as possible when you connect to it.

- Application: Leed (Light Feed)
- Author: Valentin CARRUESCO aka **Idleman** (http://blog.idleman.fr)
- Main contributors: (alphabetical order of pseudos)
  - Maël ILLOUZ aka **Cobalt74** (https://www.cobestran.com)
  - Christophe HENRY aka **Sbgodin** (http://sbgodin.fr)
  - Simon ALBERNY aka **Simounet** (https://www.simounet.net)
- Project page: https://github.com/LeedRSS/Leed
- License: [AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0.html)

All tasks are performed in the background thanks to a scheduled task (cron), so the user does not experience delays due to the recovery and processing of each of the monitored feed.

Note that Leed is compatible with all resolutions on pc, tablet and smartphone.

Leed is also compatible with [OPML](https://en.wikipedia.org/wiki/OPML) import / export which makes it compatible with aggregators applying the standard.

### Prerequisites

- Recommended Apache server (not tested on other webservers such as Nginx…)
- PHP 7.2 minimum
- MySQL
- A little common sense :-)

### Installation

1. Retrieve the project archive at [github](https://github.com/LeedRSS/Leed/releases/latest).
2. Place the project in your web directory and if necessary apply a permission _chmod 775_ (if you're on a ovh hosting, prefer _0755_ or you will get an error 500) onto the folder and its contents .
3. From your browser, go to the setup page _install.php_ (eg your.domaine.fr/leed/install.ph ) and follow the instructions.
4. Once the installation is complete, remove the _install.php_ as a security measure.
5. [Optional] If you want the update process to run in the background, set up a crontask. See below for more info. It is advisable not to put too rapid frequency to allow time to run the script.
6. The script is installed, thank you for choosing Leed, slender RSS aggregator :p

### Scheduled tasks with cron

You can edit scheduled tasks with _crontab -e_. There are two ways to update feeds. The following examples update every hour.

1. Calling directly Leed. This method has the advantage of being direct and produce formatted output to the console but requires local access :
```Batchfile
Crontab
0 * * * * cd (...)/leed && php action.php >> logs/cron.log 2>&1
```
2. Leed calling from the web client _wget_. This method requires network access but has the advantage that it can be triggered remotely. To control access, it is necessary to provide the synchronization code :
```Batchfile
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
 If you do not have access to the _wget_ command on your server, you can try the full path _/usr/bin/wget_.

### Plugins

The [Leed-market](https://github.com/Leed-market) repository contains all the plugins up to date and officially approved for Leed software.

### Libraries used

- Responsive / Cross browser: Initializr ( http://www.initializr.com )
- Javascript: JQuery ( http://www.jquery.com )
- Template Engine: RainTPL ( https://github.com/feulf/raintpl )
- RSS Parser: SimplePie ( http://simplepie.org )
- Php GD : LibGD (https://libgd.github.io/)
 

---------


# Leed (Documentación española)

Leed (contracción de Light Feed) es un agregator [RSS](https://es.wikipedia.org/wiki/Rss)/[ATOM](https://es.wikipedia.org/wiki/Atom_(formato_de_redifusi%C3%B3n)) minimalista que permite leer sus RSS rapidamente y facilmente.

Se puede instalar este agregador sobre su servidor. Leed funciona con un sistema de tareas [cron](https://es.wikipedia.org/wiki/Cron_(Unix)) para procesar los RSS de manera transparente y mostrarse tan pronto como sea posible cuando se conecta.

- Script : Leed (Light Feed)
- Autor : Valentin CARRUESCO aka **Idleman** (http://blog.idleman.fr)
- Principales contribuyentes (por orden alfabético del pseudos):
  - Maël ILLOUZ aka **Cobalt74** (https://www.cobestran.com)
  - Christophe HENRY aka **Sbgodin** (http://sbgodin.fr)
  - Simon ALBERNY aka **Simounet** (https://www.simounet.net)
- Página del proyecto : https://github.com/LeedRSS/Leed
- Licencia : [AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0.html)

Todas las tareas de tratamiento de los RSS se efectuan de manera invisible gracias a una tarea sincronizada (Cron). Así, el usuario no debe sufrir los largos tiempos necesarios para recuperar y tratar los RSS.

Se debe notar que Leed es compatible con todas las resoluciones, sobre un ordenador, una tablet o un móvil y funciona con todos los navegadores.

El script también está compatible con los archivos de exportación/importación [OPML](https://es.wikipedia.org/wiki/OPML) para permitir una migración rápida y fácil a partir de todos los agregadores que respetan el formato [OPML](https://es.wikipedia.org/wiki/OPML).

### Prerrequisito

- Se recomienda Apache (non testé sur les autres serveurs type Nginx…)
- PHP versión 7.2 mínima
- MySQL
- Un poco de sentido común ;-)

### Instalación

1. Recuperar el proyecto sobre [github](https://github.com/LeedRSS/Leed/releases/latest).
2. Poner el proyecto en su directorio web y aplicar un permiso _chmod 775_ sobre el directorio y su contenido (si su _web host_ es OVH, aplicar un permiso _0755_ para no tener un error 500).
3. Desde el navegador, ir a la página de configuración _install.php_ (por ejemplo : http://su.sitio.fr/leed/install.php) y seguir las instrucciones.
4. Una vez terminada la instalación, suprimir el archivo _install.php_ por medida de seguridad.
5. [Opcional] Si desea que las actualizaciones sean automaticas, necesita una tarea cron. Véase más abajo. Es aconsejable no poner frecuencia demasiado rápida para que el script tenga tiempo para ejecutarse.
6. Se ha instalado el script, gracias por elegir Leed, delgado agregador RSS: p

### Tareas cron

Se puede modificar las tareas cron con _crontab -e_. Hay dos maneras de actualizar los RSS. Los ejemplos siguientes actualizan los RSS cada hora.

1. Llamando directamente Leed. Esta manera es directa y genera una salida formatada para el terminal, pero necesita un acceso local :
```Batchfile
crontab
0 * * * * cd (...)/leed && php action.php >> logs/cron.log 2>&1
```
2. Llamando directamente Leed desde el cliente web _wget_. Esta manera necesita un acceso a la red pero se puede utilizarla de manera remota. Para controlar el acceso, se necesita un código de sincronización :
```Batchfile
0 * * * * wget --no-check-certificate --quiet --output-document /var/www/leed/cron.log
"http://127.0.0.1/leed/action.php?action=synchronize&code=votre_code_synchronisation"
```
 Si no tiene _wget_ en su servido, puede intentar con el camino complejo _/usr/bin/wget_.

### Complementos

El repositorio [Leed market](https://github.com/Leed-market) contiene todos los complementos oficialemente aprobados para Leed.

### Bibliotecas usadas

- Responsive / Cross browser : Initializr (http://www.initializr.com)
- Javascript : JQuery (http://www.jquery.com)
- PHP Template : RainTPL (https://github.com/feulf/raintpl)
- RSS parser : SimplePie (http://simplepie.org)
- Php GD : LibGD (https://libgd.github.io/)
