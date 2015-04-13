# Installation

## Linux (Exemple avec Debian ou dérivés)

La plupart des commandes suivantes nécessitent les droits super-administrateur, on suposera donc que vous êtes connecté en tant que ```root```.
Tout d'abord, vérifions que le serveur est à jour :

```shell
apt-get update
apt-get upgrade
```

A présent installons les packages nécessaires :

```shell
apt-get install apache2 php5 mysql-server libapache2-mod-php5 php5-mysql curl php5-curl php5-ldap git php5-intl
```

Il faut aussi installer les packages _node_ et _npm_, mais dans Ubuntu par exemple il est disponible dans une version trop ancienne. Nous avons besoin de la version 0.10 minimum. Il faut donc passer par une méthode d'installation alternative :

### Première méthode d'installation de _node_ : via le dépôt GitHub

```shell
git clone https://github.com/joyent/node.git
cd node
./configure
make
make install
```

### Deuxième méthode : via un dépôt externe

```shell
curl -sL https://deb.nodesource.com/setup | sudo bash -
sudo apt-get install nodejs build-essential
```

Lançons à présent les services Apache et MySQL :

```shell
service apache2 start
service mysql start
```

Passons à l'installation du site proprement dite. On l'installera dans le dossier ```/var/www/```. On clone le dépôt GitHub et on lance le script d'installation. Pour utiliser la branche LDAP, il faut remplacer ```master``` par ```ldap``` :

```shell
cd /var/www/
git clone -b master https://github.com/Artemis-Haven/pilote.git
cd pilote/sources
php composer.phar self-update
php composer.phar install
```

Ce script vous demandera plusieurs informations afin de compléter le fichier de paramètres. A chaque fois, vous pouvez laisser la valeur par défaut en appuyant sur ```Entrée```.

* ```database_driver : (pdo_mysql)```
 * Laissez la valeur par défaut
* ```database_host : (127.0.0.1)```
 * Idem.
* ```database_port : (null)```
 * Idem.
* ```database_name : (pilote)```
 * Le nom que vous voulez donner à la base de données. Vous pouvez laisser par défaut.
* ```database_user : (root)```
 * Le nom d'utilisateur de la base de données.
* ```database_password : (null)```
 * Le mot de passe de la base de données.
* ```mailer_transport : (smtp)```
 * Laissez la valeur par défaut
* ```mailer_host : (127.0.0.1)```
 * Idem.
* ```mailer_user : (null)```
 * Idem.
* ```mailer_password : (null)```
 * Idem.
* ```locale : (fr)```
 * Sert à la localisation. Actuellement, seul le bundle gérant les utilisateurs est localisé.
* ```secret : (ThisTokenIsNotSoSecretChangeIt)```
 * Ce token sert à la génération des tokens CSRF. Changez cette variable par une chaîne de caractères de votre choix.
* ```debug_toolbar : (true)```
 * Laissez par défaut
* ```debug_redirects : (false)```
 * Idem.
* ```use_assetic_controller : (true)```
 * Idem
* ```router.request_context.base_url: (null)```
 * Cette variable sera ajoutée au début de chaque route dans les fichiers JS.
 * Si vous avez suivi ce tutoriel, il faut écrire ```/app.php```.
* ```notification_connexion_port: ('8010')```
 * Le port de votre serveur Node.JS. Il est définit dans le fichier ```sources/web/js/notifications/app.js```. Laisser par défaut.
* ```disable_registration: (false)```
 * Permet de désactiver les inscriptions sur le site. Cela peut être utile si vous voulez limiter les accès aux seuls utilisateurs provenant de l'annuaire LDAP.

Si vous avez choisi d'installer la branche LDAP de Pilote, vous devrez aussi renseigner les champs suivants :

* ```ldap_host: (    null)```
 * L'adresse IP de votre serveur LDAP.
* ```ldap_port: (    null)```
 * Le port du serveur.
* ```ldap_username: (null)```
* ```ldap_password: (null)```
* ```ldap_baseDN: (null)```
 * Exemple : ```OU=Utilisateurs,OU=Services,DC=entreprise```

Ensuite on crée la base de données :

```shell
sudo php app/console doctrine:database:create
sudo php app/console doctrine:schema:update --force
```

Ensuite on va s'occuper du serveur _node_. On va installer _socket.io_ pour les notifications en temps réel, et _pm2_ pour gérer le processus plus simplement sur un serveur de production. Puis on va démarrer le processus avec _pm2_.

```shell
cd web/js/notifications
npm install socket.io
cd ../../..
npm install pm2 -g
pm2 start web/js/notifications/app.js
```

Enfin, on génère le fichier de routes et on vide le cache :

```shell
php app/console fos:js-routing:dump
php app/console cache:clear --env=prod
chmod -R 777 app/logs app/cache app/sessions web/uploads
```

#### Configuration d'Apache

La dernière étape est la configuration d'Apache pour rediriger directement vers notre page d'accueil (en l'occurrence, dans ```sources/web/app.php```).

Créez un nouveau fichier de configuration :
```shell
> nano /etc/apache2/sites-available/pilote.conf
```
Et ajoutez le contenu suivant :
```shell
<VirtualHost *:80>
    ServerName projet-pilote.fr
    DocumentRoot "/var/www/pilote/sources/web"
    DirectoryIndex app.php
    <Directory "/var/www/pilote/sources/web">
        AllowOverride All
        Allow from All
    </Directory>
</VirtualHost>
```

Activez le nouveau VHost et redémarrez Apache :
```shell
> a2ensite pilote.conf
> a2enmod rewrite
> service apache2 restart
```



#### Créer un utilisateur depuis la console

Si vous avez désactivé la possibilité de s'inscrire sur le site (```disable_registration: true``` dans le fichier ```app/config/parameters.yml```), vous aurez besoin de créer un premier administrateur.

Pour créer un utilisateur :
```shell
> php app/console fos:user:create
Please choose a username: Admin
Please choose an email: admin@admin.com
Please choose a password:
Created user Admin
```

Pour promouvoir un utilisateur en administrateur :
```shell
> php app/console fos:user:promote
Please choose a username: Admin
Please choose a role: ROLE_ADMIN
Role "ROLE_ADMIN" has been added to user "Admin".
```

Pour rétrograder un administrateur en simple utilisateur :
```shell
> php app/console fos:user:demote
Please choose a username: Admin
Please choose a role: ROLE_ADMIN
Role "ROLE_ADMIN" has been removed from user "Admin".
```

----

## Maintenance

Voir la [page concernée](maintenance.md).
