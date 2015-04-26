<!--

Copyright (C) 2015 Hamza Ayoub, Valentin Chareyre, Sofian Hamou-Mamar, 
Alain Krok, Wenlong Li, Rémi Patrizio, Yamine Zaidou

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

class DefaultController extends Controller
{
	public function indexAction()
	{
		return $this->render('PiloteMainBundle::accueil.html.twig');
	}

	public function aproposAction()
	{
		return $this->render('PiloteMainBundle::apropos.html.twig');
	}

	public function contactAction()
	{
		return $this->render('PiloteMainBundle::contact.html.twig');
	}

    /**
     * Barre de navigation en haut de chaque fenêtre.
     * Elle est différente si l'utilisateur est connecté ou pas.
     * Cette fonction est appelée depuis le template '::base.html.twig'.
     * Si l'utilisateur courant est dans un board particulier, la vue contient
     * un menu spécial avec le nom de ce board.
     * @param  int $boardId   Identifiant du board courant
     * @param  string $boardName Nom du board courant
     */
    public function navbarAction($boardId=null, $boardName=null, $admin=null)
    {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            return $this->render('PiloteMainBundle::authentNavbar.html.twig', array(
                'boardId'   => $boardId,
                'boardName' => $boardName,
                'admin' => $admin,
                'notifications' => $em->getRepository('PiloteUserBundle:Notification')->findLastFives($this->getUser())
            ));
        } else {
            return $this->render('PiloteMainBundle::nonAuthentNavbar.html.twig');
        }
    }
}
