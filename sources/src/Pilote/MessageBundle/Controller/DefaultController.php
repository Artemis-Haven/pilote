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

namespace Pilote\MessageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Pilote\MessageBundle\Entity\Thread;
use Pilote\MessageBundle\Entity\ThreadMetadata;
use Pilote\MessageBundle\Entity\Message;
use Pilote\UserBundle\Entity\Notification;

/**
 * Contrôleur de toutes les pages et actions (requêtes AJAX)
 * concernant la messagerie.
 */

class DefaultController extends Controller
{
	/**
	 * Page principale de la messagerie.
	 */
	public function indexAction()
	{
        return $this->render('PiloteMessageBundle::index.html.twig');
	}

	/**
	 * Page d'une discussion.
	 * @param  int    $id : L'id de la discussion à afficher
	 */
	public function threadAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		$request = $this->container->get('request');
		$thread = $this->findOr404($em, 'PiloteMessageBundle', 'Thread', $id);
		$isGranted = false;
		foreach ($thread->getMetadata() as $metadata) {
			if ($metadata->getParticipant() == $this->getUser()) {
				$metadata->setRead(true);
				$isGranted = true;
			}
		}
		if (!$isGranted) {
			return $this->redirect($this->generateUrl('pilote_message_index'));
		}
		$em->flush();

        return $this->render('PiloteMessageBundle::thread.html.twig', array(
            'thread' =>  $thread
        ));
	}

	/**
	 * Requête AJAX :
	 * Poster un message dans une discussion
	 * @param [POST] thread : L'id de la discussion
	 * @param [POST] message : Le contenu du message
	 */
	public function postAction()
	{
		$request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();          
            $threadId = $request->request->get('thread');
            $messageBody = $request->request->get('message');
            $thread = $this->findOr404($em, 'PiloteMessageBundle', 'Thread', $threadId);

			$previousSender = $em->getRepository('PiloteUserBundle:User')->findLastSenderForThread($threadId);
			$sameSender = ($previousSender == $this->getUser());

			$message = new Message();
			$message->setBody($messageBody);
			$this->sendMessage($em, $message, $thread);


            $client = $this->get('elephantio_client.default');
            $usersIds = array();
            $isGranted = false;
            foreach ($thread->getMetadata() as $md) {
            	if ($md->getParticipant() != $this->getUser()) {
            		$usersIds[] = $md->getParticipant()->getUuid();
            	} else {
            		$isGranted = true;
            	}
            }
			if (! $isGranted) {
				return $this->redirect($this->generateUrl('pilote_message_index'));
			}

            $title = "Nouveau message de ".$this->getUser();
            $content = "dans la discussion ".$thread->getTitle();
            if ($thread->getType() == Thread::ADMIN_THREAD) {
            	$content = "dans la discussion avec les administrateurs.";
            }
            $link = $this->generateUrl('pilote_message_thread', array('id' => $threadId));
            $notif = new Notification($this->getUser(), null, $title, $content, $link);
            $client->send('newMessage', [
                'htmlNotification' => $this->renderView('PiloteUserBundle:Notifications:notification.html.twig', 
                    array('notif' => $notif)),
                'htmlMessage' => $this->renderView('PiloteMessageBundle::message.html.twig', 
                    array('message' => $message,
                    	'sameSender' => $sameSender)),
                'users' => $usersIds,
                'threadId' => $threadId
            ]);


            $response = new Response(json_encode(array(
                'message' => $this->renderView('PiloteMessageBundle::message.html.twig', 
                                            array('message' => $message,
                                            	'sameSender' => $sameSender))
                )));

            return $response;

        } else {
            return new Response("");
        }
	}

	/**
	 * Envoyer un message dans une discussion.
	 * @param  $em      L'EntityManager
	 * @param  $message Le message à envoyer
	 * @param  $thread  La discussion concernée
	 * Cette fonction lie l'utilisateur au message, définit
	 * les dates, marque la discussion non lue pour les
	 * autres lecteurs, et envoie le message.
	 */
	private function sendMessage($em, $message, $thread)
	{
		$message->setSender($this->getUser());
		$message->setCreatedAt(new \DateTime('now'));
		$thread->addMessage($message);
		$thread->setLastMessageDate(new \DateTime('now'));
		foreach ($thread->getMetadata() as $metadata) {
			if ($metadata->getParticipant() != $this->getUser()) {
				$metadata->setRead(false);
			}
		}
		$em->persist($message);
		$em->flush();
	}

	/**
	 * Créer une discussion avec les administrateurs.
	 * Uniquement pour les non-administrateurs.
	 *
	 * Vérifie si une discussion entre l'utilisateur courant et
	 * les administrateurs existe déjà. Si oui, redirection vers celle-ci.
	 * Sinon, création d'une discussion.
	 */
	public function adminThreadAction()
	{
		$foundThread = false;
		$thread = null;
		foreach ($this->getUser()->getMetadata() as $metadata) {
			$thread = $metadata->getThread();
			if ($thread->getType() == Thread::ADMIN_THREAD) {
				$foundThread = true;
				break;
			}
		}
		if (! $foundThread) {
			$em = $this->getDoctrine()->getManager();
			$thread = new Thread("Discussion avec les administrateurs", $this->getUser());
			$thread->setType(Thread::ADMIN_THREAD);
			$md = new ThreadMetadata($this->getUser(), $thread);
			$em->persist($thread);
			$em->persist($md);
			$admins = $em->getRepository("PiloteUserBundle:User")->findByRole("ROLE_ADMIN");
			foreach ($admins as $admin) {
				$metadata = new ThreadMetadata($admin, $thread);
				$em->persist($metadata);
			}
			$em->flush();
		}
		return $this->redirect($this->generateUrl('pilote_message_thread', array('id' => $thread->getId())));
	}

	/**
	 * Création d'une nouvelle discussion.
	 * @param [POST] userId : L'id de l'utilisateur avec lequel
	 * l'utilisateur courant veut créer une conversation.
	 */
	public function newThreadAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$request = $this->container->get('request');
		$userId = $request->request->get('userId');

		$user = $this->findOr404($em, 'PiloteUserBundle', 'User', $userId);
		$thread = new Thread();
		$md1 = new ThreadMetadata($this->getUser(), $thread);
		$md2 = new ThreadMetadata($user, $thread);
		$em->persist($thread);
		$em->persist($md1);
		$em->persist($md2);
		$em->flush();

		return $this->redirect($this->generateUrl('pilote_message_thread', array('id' => $thread->getId())));
	}

	/**
	 * Requête AJAX :
	 * Recherche des utilisateurs potentiels pour démarrer une conversation.
	 * @param [POST] term : Le terme sur lequel faire la recherche de pseudo.
	 * @return [JSON] Tableau d'utilisateurs potentiels
	 */
	public function newThreadSearchUserAction()
	{
        $em = $this->getDoctrine()->getManager();

        $username = $this->container->get('request')->query->get('term');
        $users = $em->getRepository('PiloteUserBundle:User')->findAllOthers($this->getUser()->getId(), $username);

        $response = array();
        foreach ($users as $user) {
            $response[] = array('label' => $user->getUsername(), 'value' => $user->getId());
        }
        return new Response(json_encode($response));
	}

	/**
	 * Ajout d'un utilisateur à une discussion.
	 * @param [POST] addParticipantUserId : L'id de l'utilisateur que
	 * l'utilisateur courant veut ajouter à une conversation.
	 */
	public function addParticipantAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		
		$request = $this->container->get('request');
		$userId = $request->request->get('addParticipantUserId');

		$user = $this->findOr404($em, 'PiloteUserBundle', 'User', $userId);
		$thread = $this->findOr404($em, 'PiloteMessageBundle', 'Thread', $id);

		$isGranted = false;
		foreach ($thread->getMetadata() as $metadata) {
			if ($metadata->getParticipant() == $this->getUser()) {
				$isGranted = true;
				break;
			}
		}
		if (!$isGranted) {
			return $this->redirect($this->generateUrl('pilote_message_index'));
		}

		$md = new ThreadMetadata($user, $thread);
		$em->persist($md);
		$em->flush();

		return $this->redirect($this->generateUrl('pilote_message_thread', array('id' => $id)));
	}

	/**
	 * Requête AJAX :
	 * Recherche des utilisateurs potentiels à ajouter à une conversation.
	 * @param [POST] term : Le terme sur lequel faire la recherche de pseudo.
	 * @return [JSON] Tableau d'utilisateurs potentiels
	 */
	public function addParticipantSearchUserAction($threadId)
	{
        $em = $this->getDoctrine()->getManager();

        $username = $this->container->get('request')->query->get('term');
        $users = $em->getRepository('PiloteUserBundle:User')->findUsersForThread($threadId, $username);

        $response = array();
        foreach ($users as $user) {
            $response[] = array('label' => $user->getUsername(), 'value' => $user->getId());
        }
        return new Response(json_encode($response));
	}

	/**
	 * Supprimer l'utilisateur courant de la discussion.
	 * @param  $id : L'id de la discussion à quitter
	 */
	public function leaveThreadAction($id)
	{
        $em = $this->getDoctrine()->getManager();

		$thread = $this->findOr404($em, 'PiloteMessageBundle', 'Thread', $id);
		foreach ($thread->getMetadata() as $metadata) {
			if ($metadata->getParticipant() == $this->getUser()) {
				$em->remove($metadata);
				$thread->removeMetadata($metadata);
			}
		}
		if (count($thread->getMetadata()) == 0) {
			$em->remove($thread);
		}
		$em->flush();

		return $this->redirect($this->generateUrl('pilote_message_index'));
	}

	/**
	 * Fermer une discussion.
	 * @param  $id : L'id de la discussion à fermer
	 */
	public function closeThreadAction($id)
	{
        $em = $this->getDoctrine()->getManager();

		$thread = $this->findOr404($em, 'PiloteMessageBundle', 'Thread', $id);

		$isGranted = false;
		foreach ($thread->getMetadata() as $metadata) {
			if ($metadata->getParticipant() == $this->getUser()) {
				$isGranted = true;
				break;
			}
		}
		if (!$isGranted) {
			return $this->redirect($this->generateUrl('pilote_message_index'));
		}
		
		$em->remove($thread);
		$em->flush();

		return $this->redirect($this->generateUrl('pilote_message_index'));
	}

	/**
	 * Marquer toutes les discussions de l'utilisateur courant
	 * comme étant lues par lui-même.
	 */
	public function setAllReadAction()
	{
		$em = $this->getDoctrine()->getManager();

		foreach ($this->getUser()->getMetadata() as $md) {
			$md->setRead(true);
		}

		$em->flush();

		return $this->redirect($this->generateUrl('pilote_message_index'));
	}

	/**
	 * Trouver rapidement une entité par sonidentifiant, 
	 * ou bien renvoyer une erreur 404.
	 * @param  $em     L'EntityManager
	 * @param  $bundle Le bundle de la classe de l'entité
	 * @param  $class  La classe de l'entité
	 * @param  $id     L'id de l'entité
	 */
    private function findOr404($em, $bundle, $class, $id)
    {
        $entity = $em->getRepository($bundle.':'.$class)->find($id);
        if ($entity==null) {
            throw $this->createNotFoundException('Unable to find '.$class.' entity.');
        }
        return $entity;
    }
}
