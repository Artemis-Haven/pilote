# Maintenance

## Comment mettre à jour l'application ?

Tout d'abord assurez-vous d'être connecté en tant que _root_ et placez vous dans le dossier ```sources``` de Pilote :
```shell
sudo su
cd /var/www/pilote/sources/
```

Exécutez les commandes suivantes, afin de récupérer les dernières sources, relancer le serveur de notifications avec les nouvelles sources, mettre à jour la base de données et vider le cache (cela permet aux nouveautés d'être réellement visibles en ligne).
```shell
git pull
pm2 delete app.js
pm2 start web/js/notifications/app.js
sudo php app/console fos:js-routing:dump
php app/console doctrine:schema:update --force
php app/console cache:clear --env=prod
chmod -R 777 app/cache app/logs app/sessions
```

## Que faire après un redémarrage ?

Tout d'abord assurez-vous d'être connecté en tant que _root_ et placez vous dans le dossier ```sources``` de Pilote :
```shell
sudo su
cd /var/www/pilote/sources/
```

Lancez le serveur Node à l'aide du gestionnaire de processus PM2 :
```shell
pm2 start web/js/notifications/app.js
```
