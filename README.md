#Projet Pilote : Application Web de gestion collaborative de projets
======
##Project Management Tool from Polytech students (French engineering school)
======

Ce site a été développé dans le cadre d'un Projet d'Ingénierie  du Logiciel de 4e année à **Polytech Tours**.
Il est principalement écrit en PHP et en Javascript et utilise le framework PHP **Symfony2** et **JQuery**.


##Installation

Sous Linux :

1. Ouvrez une console et, dans le dossier souhaité, tapez`git clone https://github.com/Artemis-Haven/pilote.git`
2. Entrez dans le dossier sources avec `cd pilote/sources`
3. (Si besoin, Editez le fichier php.ini pour allouer suffisament de mémoire pour php)
4. Installez les dépendances avec `php composer.phar install`
5. Pendant l'installation, remplir les champs demandés (ceux de `parameters.yml`)
6. Réglez les problèmes de cache en faisant `sudo chmod 777 -R app/logs/ app/cache/* && sudo php app/console cache:clear && sudo chmod 777 -R app/logs/ app/cache/*`
7. (Editez si besoin le fichier `app/config/parameters.yml` pour rajouter par exemple la ligne : `router.request_context.base_url: /Teamwork/pilote/sources/web/app_dev.php`

##Organisation du projet

Voici l'arborescence du site :

* `/sources/app` contenant plusieurs fichiers importants :
  * `config/config*.yml` configuration des services (*FosUserBundle*, *SwiftMailer*, *Doctrine*) ;
  * `config/parameters.yml` **paramètres spécifiques** à la machine sur laquelle est le site ;
  * `config/security.yml` paramètres sur le type d'encodage, les limitations d'accès au pages, les groupes d'utilisateurs, ... ;
  * `config/routing.yml` fichier de **routing principal** (il inclut les autres fichiers de routing secondaires) ;
  * `Resources/views` Les vues qui ne font pas partie d'un bundle (ici, pages d'accueil, de contact, squelette principal des pages dans `base.html.twig`, etc ...) ;
  * `AppKernel.php` fichier dans lequel on inclut les bundles ;
* `/sources/bin`
* `/sources/src` contenant nos bundles :
 * `PIL/TaskerBundle` Bundle principal avec
   * `Controllers` contenant les contrôleurs suivants :
     * `BoardController.php` Gère les pages du CRUD des entités *Board* ;
     * `DomainController.php` Gère les pages du CRUD des entités *Domain* ;
     * `StepController.php` Gère les pages du CRUD des entités *Step* ;
     * Etc ... pour *TList*, *Task*, *CheckList*, *CheckListOption* ;
     * `TaskerController.php` Gère les pages générales du site (index, etc ...) et les actions de la page principale du **Board** (notamment les requêtes AJAX) ;
   * `Entities` contenant les classes PHP de nos entités : *Board*, *Domain*, *Step*, *TList*, *Task*, *CheckList*, *CheckListOption* ;
   * `Form` contenant les classes PHP générant les formulaires (voir doc Symfony2) ;
   * `Resources/config/routing.yml` fichier de routing général du bundle ;
   * `Resources/config/routing/ajaxBoardRequest.yml` fichier de routing des requêtes AJAX de la page principale du Board ;
   * `Resources/config/routing/*.yml` fichiers de routing des pages CRUD des entités du bundle ;
   * `Resources/views` fichiers TWIG des vues du bundle ;
 * `PIL/AdminBundle` Bundle principal avec toutes les vues de la partie Admin, et les contrôleurs associés ;
 * `PIL/UserBundle` Bundle gérant les utilisateurs ;
   * `Entities/User.php` est notre classe utilisateur. Elle hérite de la classe abstraite *BaseUser* de **FosUserBundle**.
* `/sources/vendors` contenant différentes briques logicielles du framework Symfony2 ainsi que les différents bundles que nous avons rajoutés : *FosUserBundle* et *FosJsRouting* ;
* `/sources/web` contenant toutes les ressources qui ne sont pas dans un bundle : CSS, JS, images, etc ...
* `/composer.json` contenant la liste des dépendances de notre projet. Le fichier `composer.phar` va récupérer en ligne les fichiers manquants ou pas à jour du projet.
