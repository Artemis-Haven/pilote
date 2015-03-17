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
