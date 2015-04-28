<!--

Copyright (C) 2015 Rémi Patrizio

________________________________

This file is part of Pilote.

    Pilote is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Pilote is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Pilote.  If not, see <http://www.gnu.org/licenses/>.

-->

<?php

namespace Pilote\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur gérant les requêtes AJAX communes à toutes les pages.
 * Pour l'instant, il ne s'agit que des requêtes concernant le système
 * de notifications.
 */

class AjaxController extends Controller
{
    /**
     * Requête AJAX :
     * Renvoie les 5 notifications suivantes à afficher dans le menu
     * des notifications de la barre de menu.
     * @param [POST] lastNotifId : l'Id de la plus ancienne notification
     * actuellement affichée dans le menu
     * @return [HTML] Le rendu HTML des 5 notifications suivantes
     */
    public function loadNextNotificationsAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            $lastNotifId = $request->request->get('lastNotifId');
            $notif = $em->getRepository('PiloteUserBundle:Notification')->find($lastNotifId);
            $notifications = $em->getRepository('PiloteUserBundle:Notification')->findNextFives($notif, $this->getUser());

            $view = array();
            foreach ($notifications as $notif) {
                $view[] = $this->renderView('PiloteUserBundle:Notifications:notification.html.twig', 
                                            array('notif' => $notif));
            }

            $response = new Response(json_encode(implode($view)));

            return $response;

        } else {
            return new Response("");
        }
    }

    /**
     * Requête AJAX :
     * Définit toutes les notifications de l'utilisateur
     * courant comme étant lues.
     */
    public function setAllNotificationsReadAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            foreach ($this->getUser()->getNotifications() as $notif) {
                $notif->setRead(true);
            }
            $em->flush();

            $response = new Response(json_encode(""));

            return $response;

        } else {
            return new Response("");
        }
    }

    /**
     * Requête AJAX :
     * Supprime toutes les notifications de l'utilisateur courant.
     */
    public function removeAllNotificationsAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            foreach ($this->getUser()->getNotifications() as $notif) {
                $em->remove($notif);
            }
            $em->flush();

            $response = new Response(json_encode(""));

            return $response;

        } else {
            return new Response("");
        }
    }

    /**
     * Requête AJAX :
     * Surpprime une notification.
     * @param [POST] id : L'id de la notification à supprimer
     */
    public function removeNotificationAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            $notifId = $request->request->get('id');
            $notif = $em->getRepository('PiloteUserBundle:Notification')->find($notifId);
            $em->remove($notif);
            $em->flush();

            $response = new Response(json_encode(""));

            return $response;

        } else {
            return new Response("");
        }
    }
}
