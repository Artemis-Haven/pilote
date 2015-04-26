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

namespace Pilote\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use \Pilote\MessageBundle\Entity\Thread;
use \Pilote\MessageBundle\Entity\ThreadMetadata;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('PiloteAdminBundle::index.html.twig', array(
            'messagesNb' =>  $em->getRepository('PiloteMessageBundle:Message')->count(),
            'boardsNb' =>  $em->getRepository('PiloteTaskerBundle:Board')->count(),
            'usersNb' =>  $em->getRepository('PiloteUserBundle:User')->count(),
            'tasksNb' =>  $em->getRepository('PiloteTaskerBundle:Task')->count()
        ));
    }
    
    public function usersAction()
    {
        $users = $this->get('fos_user.user_manager')->findUsers();
        return $this->render('PiloteAdminBundle::users.html.twig', array(
            'users' => $users
            ));
    }
    
    public function boardsAction()
    {
        $em = $this->getDoctrine()->getManager();
        return $this->render('PiloteAdminBundle::boards.html.twig', array(
            'boards' => $em->getRepository('PiloteTaskerBundle:Board')->findAll()
            ));
    }
    
    public function promoteUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PiloteUserBundle:User')->findOneByUuid($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // Supprimer l'éventuelle conversation d'aide avec les admins qui lui est liée
        foreach ($user->getMetadata() as $md) {
            if ($md->getThread()->getType() ==  Thread::ADMIN_THREAD) {
                $em->remove($md->getThread());
                break;
            }
        }

        // Lier l'utilisateur avec toutes les discussions d'aides avec les admins
        $allThreads = $em->getRepository('PiloteMessageBundle:Thread')->findAll();
        foreach ($allThreads as $thread) {
            if ($thread->getType() ==  Thread::ADMIN_THREAD) {
                $md = new ThreadMetadata($user, $thread);
                $em->persist($md);
            }
        }

        $user->addRole('ROLE_ADMIN');
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_users'));
    }
    
    public function demoteUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PiloteUserBundle:User')->findOneByUuid($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        
        // Supprimer le lien (metadata) entre l'utilisateur et les discussions avec les admins
        foreach ($user->getMetadata() as $md) {
            if ($md->getThread()->getType() ==  Thread::ADMIN_THREAD) {
                $em->remove($md);
            }
        }

        $user->removeRole('ROLE_ADMIN');
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_users'));
    }
    
    public function disableUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PiloteUserBundle:User')->findOneByUuid($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $user->setEnabled(false);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_users'));
    }
    
    public function enableUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PiloteUserBundle:User')->findOneByUuid($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $user->setEnabled(true);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_users'));
    }
    
    public function removeUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PiloteUserBundle:User')->findOneByUuid($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        // Supprimer l'utilisateur de tous les boards
        foreach ($user->getBoards() as $board) {
            $board->removeUser($user);
        }

        // Supprimer les liens d'assignation de l'utilisateur à des tâches
        foreach ($user->getTasks() as $task) {
            $user->removeTask($task);
        }

        // Supprimer tous les messages de l'utilisateur
        $messages = $em->getRepository('PiloteMessageBundle:Message')->findBy( array('sender' => $user) );
        foreach ($messages as $msg) {
            $em->remove($msg);
        }
        // Supprimer l'utilisateur de toutes ces conversations
        foreach ($user->getMetadata() as $md) {
            $em->remove($md);
        }

        $em->remove($user);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_users'));
    }
    
    public function newUserAction(Request $request)
    {
        $formFactory = $this->get('fos_user.registration.form.factory');
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager->updateUser($user);
            return $this->redirect($this->generateUrl('pilote_admin_users'));
        }

        return $this->render('PiloteAdminBundle::createUser.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    public function disableBoardAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $em->getRepository('PiloteTaskerBundle:Board')->find($id);
        if (!$board) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $board->setEnabled(false);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_boards'));
    }
    
    public function enableBoardAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $em->getRepository('PiloteTaskerBundle:Board')->find($id);
        if (!$board) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $board->setEnabled(true);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_boards'));
    }
    
    public function removeBoardAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $em->getRepository('PiloteTaskerBundle:Board')->find($id);
        if (!$board) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $em->remove($board);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_admin_boards'));
    }

}
