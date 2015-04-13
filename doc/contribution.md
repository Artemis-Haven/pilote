# Contribuer ou _forker_ le projet

## Présentation générale

Pilote est à l'origine un projet de groupe créé par 7 étudiants en 4ème année à l'École d'Ingénieurs Polytech Tours en 2014. Je l'ai ensuite repris l'année suivante pour mon Projet de Fin d'Études.

Dans la suite de ce document, je vais détailler le fonctionnement général de l'application web pour quelqu'un qui souhaiterait se plonger dans le code.

**Merci aux contributeurs du projet :**
- Hamza Ayoub
- Valentin Chareyre
- Sofian Hamou-Mamar
- Alain Krok
- Wenlong Li
- Rémi Patrizio
- Yamine Zaidou

## Technologies utilisées

Le framework PHP **Symfony2** a été utilisé pour la majeure partie. On respecte donc le modèle MVC. Le front-end est constitué de vues en HTML utilisant **TWIG**. Les animations en Javascript et les requêtes AJAX utilisent **jQuery**.

Les notifications en temps réel utilisent un serveur **Node.js** et **Socket.io**. Le lien entre la vue, le serveur Node.js et le serveur PHP est détaillé plus bas.

## Structure du projet

Pilote est constitué de 5 bundles :
- **PiloteMainBundle** : Il contient toutes les pages statiques et les templates généraux comme ceux de la barre de navigation par exemple. Il y a deux contrôleurs : un pour les pages et un pour les requêtes Ajax.
- **PiloteTaskerBundle** : Il contient les principales classes du modèle de l'application (*Board*, *Domain*, *Task*, *Checklist*, etc) et toutes les vues concernant l'affichage des projets (Board, Gantt, Calendrier). Il possède 4 contrôleurs : un pour les requêtes AJAX gérant le déplacement des tâches et des listes de tâches dans la vue du Board, un pour les autres requêtes AJAX, un pour le Gantt et le calendrier, et un dernier pour les pages.
- **PiloteAdminBundle** : Il contient les vues de la zone d'administration.
- **PiloteMessageBundle** : Il contient les classes du modèle concernant la messagerie (*Message*, *Thread*, *ThreadMetadata*), et toutes les vues associées à celle-ci.
- **PiloteUserBundle** : Il hérite de **FOSUserBundle**. Il gère ce qui est lié aux utilisateurs (classes *User*, *Notifications*, *Pictures*) et contient quelques vues surchargées des vues fournies par FOSUserBundle.

Pilote utilise plusieurs bundles installés automatiquement avec Composer :
- **FOSUserBundle** pour la gestion des utilisateurs.
- **FOSJSRoutingBundle** pour utiliser le système de routing de Symfony dans les fichiers Javascript.
- **NCElephantIOBundle** pour le lien entre le serveur PHP et le serveur Node.js. Il propose des fonctions simples pour envoyer des notifications depuis les contrôleurs Symfony au serveur Node.js.
- Enfin, **FR3DLdapBundle** pour utiliser un annuaire LDAP et permettre aux employés d'une entreprise de se connecter avec leurs identifiants.

## Envoi des notifications

Lorsqu'une action est effectuée dans une vue, une requête AJAX est envoyée au serveur PHP. Celui-ci effectue un traitement (dans un fichier ```Controller.php```) et envoie une notification au serveur Node.js grâce au Bundle ElephantIOBundle. Le serveur PHP est donc un client du serveur Node.js.

Voici un exemple de notification envoyée depuis un controller. Ici, on envoie la listes des utilisateurs potentiellement concernés par la notification :

```php
$client = $this->get('elephantio_client.default');
$client->send('simple-notification', [ 
    'html' => $monTexte,
    'users' => $usersIds
] );
```

Le serveur Node.js est constitué uniquement du fichier ```web/js/notifications/app.js```. Il reçoit une notification de connexion à chaque fois que quelqu'un charge une page. Il sait donc qui est connecté et sur quelle page il est. Lorsqu'il reçoit la notification du serveur PHP, il croise sa liste d'utilisateurs connectés et celle des utilisateurs concernés par la notification. Il va ensuite envoyer une nouvelle notification à ces clients.

Voici le code permettant d'effectuer ces actions :

```js
    socket.on('simple-notification', function (data) { 
        console.log('simple-notification');
        for (var i = 0; i < data.users.length; i++) {
            var clients = connectedClients.getSocketsForId(data.users[i]);
            for (var j = 0; j < clients.length; j++) {
                clients[j].emit('notification', data.html);
            };
        };  
    });
```

Le fichier ```web/js/notifications/notifs.js``` est inclus dans chaque page web et effectue le traitement côté client. Il suffit de récupérer la notification envoyée par le serveur Node.js et de répercuter ces informations dans la vue.

Voici le code concerné :

```js
    socket.on('notification', function (data) {
        displayNotification(data, true);
    });
```