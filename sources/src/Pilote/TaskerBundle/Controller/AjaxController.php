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

namespace Pilote\TaskerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Pilote\TaskerBundle\Entity\Domain;
use Pilote\TaskerBundle\Entity\Step;
use Pilote\TaskerBundle\Entity\TList;
use Pilote\TaskerBundle\Entity\Task;
use Pilote\TaskerBundle\Entity\HasCommented;
use Pilote\TaskerBundle\Entity\Document;
use Pilote\TaskerBundle\Entity\CheckList;
use Pilote\TaskerBundle\Entity\CheckListOption;
use Pilote\UserBundle\Entity\Notification;
use Pilote\MessageBundle\Entity\ThreadMetadata;

/**
 * Contrôleur de toutes les requêtes AJAX des pages du Board,
 * du détail d'une tâche, et de la page des réglages d'un Board.
 *
 * Seules les requêtes AJAX des déplacements des tâches et des listes
 * de tâches sont séparées, dans le fichier PositionController.php.
 *
 * Les requêtes AJAX concernant le diagramme de Gantt sont dans 
 * GanttController.php.
 */

class AjaxController extends Controller
{
    /**
     * Créer une tâche.
     * @param [POST] tListId : L'id de la liste dans laquelle ajouter la
     * nouvelle tâche
     * @return [JSON] Tableau avec le rendu HTML de l'aperçu de la tâche
     * et l'id de cette tâche.
     */
    public function createTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();
			$user = $this->container->get('security.context')->getToken()->getUser();
          
            $tListId = $request->request->get('tListId');
            $tList = $em->getRepository('PiloteTaskerBundle:TList')->find($tListId);
            $newTask = new Task();
            $tList->addTask($newTask, $tList->getMaxTaskPosition() + 1 );

            $em->persist($newTask);
            $em->flush();

            $view = $this->renderView('PiloteTaskerBundle:Main:taskThumbnail.html.twig', 
                    array('task' => $newTask));
            $response = new Response(json_encode(array(
                'taskThumbnail' => $view,
                'taskId' => $newTask->getId()
                )));

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('new-task', [
                'sender' => $this->getUser()->getUuid(),
                'taskThumbnail' => $view,
                'tListId' => $tList->getId(),
                'boardId' => $tList->getStep()->getDomain()->getBoard()->getId()
            ]);

            return $response;

        } else {
            return new Response("");
        }
    }

    /**
     * Supprimer une tâche.
     * @param [POST] taskId : L'id de la tâche à supprimer
     */
    public function deleteTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $task = $em->getRepository('PiloteTaskerBundle:Task')->find($taskId);

            $em->remove($task);
            $em->flush();

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('remove-task', [
                'sender' => $this->getUser()->getUuid(),
                'taskId' => $taskId,
                'boardId' => $task->getTList()->getStep()->getDomain()->getBoard()->getId()
            ]);
        }

        return new Response("");
    }

    /**
     * Renommer une tâche
     * @param [POST] taskId : L'id de la tâche à renommer
     * @param [POST] newTitle : Le nouveau titre de la tâche
     */
    public function renameTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $newTitle = $request->request->get('newTitle');
            $task = $em->getRepository('PiloteTaskerBundle:Task')->find($taskId);

            $task->setName($newTitle);
            $em->flush();

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('rename-task', [
                'taskId' => $task->getId(),
                'title' => $newTitle,
                'boardId' => $task->getTList()->getStep()->getDomain()->getBoard()->getId()
            ]);

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }


    /**
     * Modifier le contenu de la tâche.
     * @param [POST] taskId : L'id de la tâche à modifier
     * @param [POST] newContent : Le nouveau contenu
     */
    public function updateTaskContentAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $newContent = $request->request->get('newContent');
            $task = $em->getRepository('PiloteTaskerBundle:Task')->find($taskId);
            
            $response = new Response($task->getContent());

            $task->setContent($newContent);
            $em->flush();

            // Notifications
            $this->sendNotificationForTaskUpdate($task, $this->getUser());

            return $response;

        } else {

            return new Response("");
        }
    }

    /**
     * Afficher la fenêtre de détail d'une tâche.
     * @param [POST] taskId : L'id de la tâche à afficher
     * @return [HTML] Le contenu de la fenêtre de détail
     */
    public function getTaskDetailsAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $document = new Document();

            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $task = $em->getRepository('PiloteTaskerBundle:Task')->find($taskId);

            $form = $this->createFormBuilder($document)
            ->add('file', 'file')
            ->getForm();

        return $this->render('PiloteTaskerBundle:Main:taskDetails.html.twig', array(
            'task' => $task,
            'form'    => $form->createView(),
        ));

        } else {
            return new Response("");
        }
    }

    /**
     * Créer une liste de tâches.
     * @param [POST] stepId : L'id de l'étape dans laquelle ajouter la
     * nouvelle liste
     * @return [JSON] Tableau avec l'id de cette liste, le rendu HTML de la liste
     * et le nombre de listes dans cette étape.
     */
    public function createTListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $stepId = $request->request->get('stepId');
            $step = $em->getRepository('PiloteTaskerBundle:Step')->find($stepId);
            $newTList = new TList();
            $step->addTList($newTList, $step->getMaxTListPosition() + 1 );

            $em->persist($newTList);
            $em->flush();

            $view = $this->renderView('PiloteTaskerBundle:Main:tList.html.twig', 
                    array('tList' => $newTList));
            $response = new Response(json_encode(array(
                'tListId' => $newTList->getId(), 
                'tList' => $view, 
                'nbrOfLists' => sizeof($step->getTLists())
            )));

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('new-tlist', [
                'sender' => $this->getUser()->getUuid(),
                'tList' => $view,
                'stepId' => $step->getId(),
                'tListId' => $newTList->getId(),
                'boardId' => $step->getDomain()->getBoard()->getId()
            ]);

            return $response;
            
        } else {
            return new Response("");
        }
    }


    /**
     * Supprimer une liste de tâches.
     * @param [POST] tListId : L'id de la liste de tâches à supprimer
     */
    public function deleteTListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $tListId = $request->request->get('tListId');
            $tList = $em->getRepository('PiloteTaskerBundle:TList')->find($tListId);
            $step = $tList->getStep();

            foreach ($tList->getTasks() as $task) {
                $em->remove($task);
            }

            $em->remove($tList);
            $em->flush();

            $response = new Response(json_encode(array(
                'nbrOfLists' => sizeof($step->getTLists()),
                'stepId' => $step->getId()
                )));

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('remove-tlist', [
                'sender' => $this->getUser()->getUuid(),
                'tListId' => $tListId,
                'boardId' => $step->getDomain()->getBoard()->getId()
            ]);

            return $response;

        } else {

            return new Response("");
        }
    }


    /**
     * Renommer une liste de tâches
     * @param [POST] tListId : L'id de la liste à renommer
     * @param [POST] newTitle : Le nouveau titre de la liste
     */
    public function renameTListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $tListId = $request->request->get('tListId');
            $newTitle = $request->request->get('newTitle');
            $tList = $em->getRepository('PiloteTaskerBundle:TList')->find($tListId);

            $tList->setName($newTitle);
            $em->flush();

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('rename-tlist', [
                'tListId' => $tList->getId(),
                'title' => $newTitle,
                'boardId' => $tList->getStep()->getDomain()->getBoard()->getId()
            ]);

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }

    /**
     * Créer une étape.
     * @param [POST] domainId : L'id du domaine dans lequel ajouter la
     * nouvelle étape
     * @return [JSON] Tableau avec le rendu HTML de l'onglet de l'étape,
     * le rendu HTML du contenu de l'étape, et l'id de cette étape.
     */
    public function createStepAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $domainId = $request->request->get('domainId');
            $domain = $em->getRepository('PiloteTaskerBundle:Domain')->find($domainId);
            $newStep = new Step();
            $domain->addStep($newStep);

            $em->persist($newStep);
            $em->flush();

            $response = new Response(json_encode(array(
                'stepTab' => $this->renderView('PiloteTaskerBundle:Main:stepTab.html.twig', 
                                            array('step' => $newStep, 'activeStep' => true)),
                'stepContent' => $this->renderView('PiloteTaskerBundle:Main:step.html.twig', 
                                            array('step' => $newStep, 'activeStep' => true)),
                'stepId' => $newStep->getId()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    /**
     * Supprimer une étape.
     * @param [POST] stepId : L'id de l'étape à supprimer
     */
    public function deleteStepAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $stepId = $request->request->get('stepId');
            $step = $em->getRepository('PiloteTaskerBundle:Step')->find($stepId);
            

            foreach ($step->getTLists() as $tList) {
                foreach ($tList->getTasks() as $task) {
                    $em->remove($task);
                }
                $em->remove($tList);
            }

            $domain = $step->getDomain();
            $domain->removeStep($step);
            $em->remove($step);
            $em->flush();

            $otherSteps = $domain->getSteps();
            $newActiveStep = 0;
            if(sizeof($otherSteps) != 0)
                $newActiveStep = $otherSteps[0]->getId();

            $response = new Response(json_encode(array(
                'newActiveStep' => $newActiveStep
                )));

            return $response;

        } else {

            return new Response("");
        }
    }


    /**
     * Renommer une étape
     * @param [POST] stepId : L'id de l'étape à renommer
     * @param [POST] newTitle : Le nouveau titre de l'étape
     */
    public function renameStepAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $stepId = $request->request->get('stepId');
            $newTitle = $request->request->get('newTitle');
            $step = $em->getRepository('PiloteTaskerBundle:Step')->find($stepId);

            $step->setName($newTitle);
            $em->flush();

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('rename-step', [
                'stepId' => $step->getId(),
                'title' => $newTitle,
                'boardId' => $step->getDomain()->getBoard()->getId()
            ]);

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }


    /**
     * Créer un domaine.
     * @param [POST] boardId : L'id du board dans lequel ajouter le
     * nouveau domaine.
     * @return [JSON] Tableau avec l'id du domaine et le rendu HTML du domaine
     */
    public function createDomainAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $boardId = $request->request->get('boardId');
            $board = $em->getRepository('PiloteTaskerBundle:Board')->find($boardId);
            $newDomain = new Domain();
            $newStep = new Step();
            $newDomain->addStep($newStep);
            $board->addDomain($newDomain);

            $em->persist($newDomain);
            $em->flush();

            $response = new Response(json_encode(array(
                'domainId' => $newDomain->getId(), 
                'domain' => $this->renderView('PiloteTaskerBundle:Main:domain.html.twig', 
                    array('domain' => $newDomain, 
                        'board' => $board,
                        'activeDomain' => false))
            )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    /**
     * Supprimer un domaine.
     * @param [POST] domainId : L'id du domaine à supprimer
     */
    public function deleteDomainAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $domainId = $request->request->get('domainId');
            $domain = $em->getRepository('PiloteTaskerBundle:Domain')->find($domainId);
            
            foreach ($domain->getSteps() as $step) {
                foreach ($step->getTLists() as $tList) {
                    foreach ($tList->getTasks() as $task) {
                        $em->remove($task);
                    }
                    $em->remove($tList);
                }
                $em->remove($step);
            }

            $board = $domain->getBoard();
            $board->removeDomain($domain);
            $em->remove($domain);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }


    /**
     * Renommer un domaine
     * @param [POST] domainId : L'id du domaine à renommer
     * @param [POST] newTitle : Le nouveau titre du domaine
     */
    public function renameDomainAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $domainId = $request->request->get('domainId');
            $newTitle = $request->request->get('newTitle');
            $domain = $em->getRepository('PiloteTaskerBundle:Domain')->find($domainId);

            $domain->setName($newTitle);
            $em->flush();

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }

    /**
     * Créer une liste de cases à cocher (Checklist).
     * @param [POST] taskId : L'id de la tâche dans laquelle créer une checklist.
     * @return [JSON] Tableau contenant l'id de la checklist et le rendu HTML de 
     * celle-ci.
     */
    public function createChecklistAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $task = $em->getRepository('PiloteTaskerBundle:Task')->find($taskId);
            $newChecklist = new CheckList();
            $task->addChecklist($newChecklist);

            $em->persist($newChecklist);
            $em->flush();

            // Notifications
            $this->sendNotificationForTaskUpdate($task, $this->getUser(), 'a créé une Checklist pour la tâche');

            $response = new Response(json_encode(array(
                'id' => $newChecklist->getId(), 
                'checkList' => $this->renderView('PiloteTaskerBundle:Main:checkList.html.twig', 
                    array('checkList' => $newChecklist))
            )));
            return $response;
        } else {
            return new Response("");
        }
    }


    /**
     * Renommer une checklist
     * @param [POST] checklistId : L'id de la checklist à renommer
     * @param [POST] newName : Le nouveau titre de la checklist
     */
    public function renameChecklistAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $checklistId = $request->request->get('checklistId');
            $newName = $request->request->get('newName');
            $checklist = $em->getRepository('PiloteTaskerBundle:CheckList')->find($checklistId);

            $checklist->setName($newName);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }

    /**
     * Supprimer une checklist.
     * @param [POST] checklistId : L'id de la checklist à supprimer
     */
    public function deleteChecklistAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            $checklist = $this->findOr404($em, 'PiloteTaskerBundle', 'CheckList', $request->request->get('checklistId'));

            $em->remove($checklist);
            $em->flush();

            // Notifications
            $this->sendNotificationForTaskUpdate($checklist->getTask(), $this->getUser(), 
                'a supprimé une Checklist pour la tâche');

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }



    /**
     * Créer une case à cocher (ChecklistOption).
     * @param [POST] checklistId : L'id de la checklist dans laquelle créer une option.
     * @return [JSON] Tableau contenant l'id de l'option et le rendu HTML de 
     * celle-ci.
     */
    public function createChecklistOptionAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $checklistId = $request->request->get('checklistId');
            $checklist = $em->getRepository('PiloteTaskerBundle:CheckList')->find($checklistId);
            $newChecklistOption = new CheckListOption();
            $checklist->addChecklistOption($newChecklistOption);

            $em->persist($newChecklistOption);
            $em->flush();

            $response = new Response(json_encode(array(
                'id' => $newChecklistOption->getId(), 
                'checkListOption' => $this->renderView('PiloteTaskerBundle:Main:checkListOption.html.twig', 
                    array('option' => $newChecklistOption))
            )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    /**
     * Renommer une option de checklist
     * @param [POST] checklistOptionId : L'id de l'option à renommer
     * @param [POST] newName : Le nouveau titre de l'option
     */
    public function renameChecklistOptionAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $checklistOptionId = $request->request->get('checklistOptionId');
            $newName = $request->request->get('newName');
            $checklistOption = $em->getRepository('PiloteTaskerBundle:CheckListOption')->find($checklistOptionId);

            $checklistOption->setOptionText($newName);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }


    /**
     * Activer / Désactiver une option de checklist
     * @param [POST] checklistOptionId : L'id de l'option concernée
     * @param [POST] value : La nouvelle valeur de l'option
     * @param [POST] newName : Le nouveau titre de l'option
     */
    public function toggleChecklistOptionAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();
            $value = $request->request->get('value');
            $optionId = $request->request->get('checklistOptionId');
            $option = $this->findOr404($em, 'PiloteTaskerBundle', 'CheckListOption', $optionId);

            $option->setChecked($value == "true" ? true : false);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }

    /**
     * Supprimer une option de checklist.
     * @param [POST] optionId : L'id de l'option à supprimer
     */
    public function deleteChecklistOptionAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            $option = $this->findOr404($em, 'PiloteTaskerBundle', 'CheckListOption', $request->request->get('optionId'));

            $em->remove($option);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }

    /**
     * Créer un commentaire pour une tâche.
     * @param [POST] taskId : L'id de la tâche concernée
     * @param [POST] content : Le contenu du commentaire
     * @return [JSON] Tableau contenant l'id du nouveau commentaire
     * et le rendu HTML de celui-ci.
     */
    public function createCommentAction() {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $content = $request->request->get('content');
            $task = $em->getRepository('PiloteTaskerBundle:Task')->find($taskId);
            $user = $this->container->get('security.context')->getToken()->getUser();
            $newComment = new HasCommented($user ,$task);
            $newComment->setComment($content);
            $task->addComment($newComment);

            $em->persist($newComment);
            $em->flush();

            // Notifications
            $receivers = array();
            if ($task->getCreator() != null && $task->getCreator() != $user) {
                $receivers[] = $task->getCreator();
            }
            foreach ($task->getComments() as $comm) {
                if (!in_array($comm->getUser(), $receivers) && $comm->getUser() != $user) {
                    $receivers[] = $comm->getUser();
                }
            }
            $notifTitle = $user.' a commenté la tâche <em>'.$task.'</em>';
            $notifContent = ' du projet <em>'.
                        $task->getTList()->getStep()->getDomain()->getBoard().'</em>.';
            $link = $this->generateUrl('pilote_tasker_board', array(
                'boardId' => $task->getTList()->getStep()->getDomain()->getBoard()->getId()));
            
            $this->sendNotifications($user, $receivers, $notifTitle, $notifContent, $link);
            // Fin des Notifs

            $response = new Response(json_encode(array(
                'commentId' => $newComment->getId(), 
                'comment' => $this->renderView('PiloteTaskerBundle:Main:comment.html.twig', 
                                            array('comment' => $newComment))
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }

    /**
     * Supprimer un commentaire.
     * @param [POST] commentId : L'id du commentaire à supprimer
     */
    public function deleteCommentAction() {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $commentId = $request->request->get('commentId');
            $comment = $this->findOr404($em, 'PiloteTaskerBundle', 'HasCommented', $commentId);
            
            $task = $comment->getTask();
            $task->removeComment($comment);
            $em->remove($comment);
            $em->flush();

            return new Response(json_encode(null));            
        } else {
            return new Response("");
        }
    }

    /**
     * Supprimer un utilisateur du board (=projet).
     * @param [POST] userId : l'id de l'utilisateur à supprimer
     * @param [POST] boardId : l'id du board concerné
     */
    public function removeUserAction() {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $user = $this->findOr404($em, 'PiloteUserBundle', 'User', $request->request->get('userId'));

            $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $request->request->get('boardId'));
            
            if (!$board->getUsers()->contains($user)) {
                throw new HttpException(400, 
                    "The user '".$user->getUsername()."' isn't related to this board.");
            }

            // Supprimer les liens d'assignation de l'utilisateur à des tâches
            foreach ($user->getTasks() as $task) {
                if ($task->getTList()->getStep()->getDomain()->getBoard() == $board) {
                    $user->removeTask($task);
                }
            }

            // Supprimer le lien entre l'utilisateur et la conversation de groupe
            $user->getBoards()->removeElement($board);
            $board->getUsers()->removeElement($user);
            $thread = $board->getThread();
            foreach ($thread->getMetadata() as $metadata) {
                if ($metadata->getParticipant() == $user) {
                    $em->remove($metadata);
                    $em->flush();
                    break;
                }
            }

            $em->flush();

            // Notifications
            if ($this->getUser() != $user) {
                $notifTitle = $this->getUser().' vous a supprimé du projet <em>'.$board.'</em>.';                
                $this->sendNotifications($this->getUser(), array($user), $notifTitle, "", "#");
            }
            // Fin des notifs

            return new Response(json_encode(null));

        } else {
            return new Response("");
        }
    }

    /**
     * Ajouter un utilisateur à un board (=projet)
     * @param [POST] userId : L'id de l'utilisateur à ajouter
     * @param [int] $boardId : L'id du board concerné
     */
    public function addUserAction($boardId) {
        $request = $this->container->get('request');

        $em = $this->getDoctrine()->getManager();

        $user = $this->findOr404($em, 'PiloteUserBundle', 'User', $request->request->get('userId'));
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);

        $user->getBoards()->add($board);
        $board->getUsers()->add($user);
        $metadata = new ThreadMetadata();
        $board->getThread()->addMetadata($metadata);
        $user->addMetadata($metadata);
        
        $em->flush();

        // Notifications
        if ($this->getUser() != $user) {
            $notifTitle = $this->getUser().' vous a intégré au projet <em>'.$board.'</em>.';
            $link = $this->generateUrl('pilote_tasker_board', array('boardId' => $boardId));
            $this->sendNotifications($this->getUser(), array($user), $notifTitle, "", $link);
        }

        return $this->redirect($this->generateUrl('pilote_tasker_board_settings', array('boardId' => $boardId)));
    }

    /**
     * Assigner un utilisateur à une tâche.
     * @param [POST] memberId : l'id de l'utilisateur concerné
     * @param [POST] taskId : l'id de la tâche concerné
     */
    public function assignAction() {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
            
            $memberId = $request->request->get('memberId');

            if ($task->getCreator() != null && $memberId == $task->getCreator()->getId()) {
                return new Response(json_encode(array(
                    'name' => $task->getCreator()->__toString(), 
                    'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
                )));
            }
            if ($task->getCreator() != null) {
                $oldOwner = $task->getCreator();
                $oldOwner->removeTask($task);
            }
            
            if ($memberId=='') {
                $em->flush();
                return new Response(json_encode(array(
                    'name' => " Assigner à... ", 
                    'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
                )));

            } else {
                $user = $this->findOr404($em, 'PiloteUserBundle', 'User', $memberId);

                $user->addTask($task);
                $em->flush();

                // Notifications
                $this->sendNotificationForTaskUpdate($task, $this->getUser(), "vous a assigné à la tâche");
                return new Response(json_encode(array(
                    'name' => $user->__toString(), 
                    'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
                )));
            }

        } else {
            return new Response("");
        }
    }

    
    /**
     * Définir un label pour une tâche.
     * @param [POST] label : le label à définir
     * @param [POST] taskId : l'id de la tâche concerné
     */
    public function labelAction() {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
            
            $task->setLabel($request->request->get('label'));

            $em->flush();

            return new Response(json_encode(""));
        } else {
            return new Response("");
        }
    }

    /**
     * Activer et afficher / Désactiver et masquer 
     * la barre de progression sur une tâche.
     * @param [POST] taskId : L'id de la tâche concernée
     * @param [POST] activte : Booléan indiquant si l'on veut activer ou désactiver la progression
     * @return [JSON] Tableau contenant le rendu HTML de la zone de l'aperçu de la tâche résumant
     * ses différentes caractéristiques.
     */
    public function activateProgressAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
            
            /* Pour être sûr de bien récupérer un booléen : 
               tout autre valeur que TRUE renverra FALSE */
            $activate = ($request->request->get('activate') == "true");

            $task->setProgressActivated($activate);
            if (!$activate) {
                $task->setProgress(0);
            }

            $em->flush();

            return new Response(json_encode(array(
                'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
            )));

        } else {
            return new Response("");
        }
    }

    /**
     * Modifier la progression d'une tâche.
     * @param [POST] taskId : L'id de la tâche concernée
     * @param [POST] value : La nouvelle valeur de la progression
     * @return [JSON] Tableau contenant le rendu HTML de la zone de l'aperçu de la tâche résumant
     * ses différentes caractéristiques.
     */
    public function updateProgressAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
            
            $value = $request->request->get('value');

            if (in_array($value, array(0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100))) 
            {
                $task->setProgress($value);
                $em->flush();

                // Notifications
                $this->sendNotificationForTaskUpdate($task, $this->getUser());

                return new Response(json_encode(array(
                    'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
                )));

            } else {
                return new Response("");
            }

        } else {
            return new Response("");
        }
    }

    /**
     * Upload d'un fichier joint à une tâche.
     * La fenêtre de détail d'une tâche contient un formulaire d'upload
     * de fichier. Lorsque celui-ci est validé, cette fonction est appelée.
     * @param [POST] taskId : L'id de la tâche concernée
     * @return [JSON] Tableau contenant le rendu HTML de l'aperçu du document,
     * et le rendu HTML de la zone de l'aperçu de la tâche résumant
     * ses différentes caractéristiques.
     */
    public function fileUploadAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $document = new Document();

            $form = $this->createFormBuilder($document)
                ->add('file', 'file')
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
                $task->setDocument($document);
                $document->setTask($task);
                $em->flush();

                // Notifications
                $this->sendNotificationForTaskUpdate($task, $this->getUser(), "a ajouté un fichier à la tâche");

                return new Response(json_encode(array(
                    'documentThumbnail' => $this->renderView('PiloteTaskerBundle:Main:documentThumbnail.html.twig', 
                        array('document' => $document, 
                            'form' => $form->createView(),
                            'task' => $task)
                    ),
                    'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
                )));
            }

            return new Response(var_export($this->getErrorMessages($form), true));

        } else {
            return new Response("");
        }
    }

    /**
     * Suppression d'un fichier joint à une tâche.
     * @param [POST] taskId : L'id de la tâche concernée
     * @return [JSON] Tableau contenant le rendu HTML de l'aperçu du document,
     * et le rendu HTML de la zone de l'aperçu de la tâche résumant
     * ses différentes caractéristiques.
     */
    public function deleteFileAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
            $doc = $task->getDocument();

            if ($doc != null) {
                $path = $doc->getPath();
                $task->setDocument(null);
                $em->remove($doc);
                $em->flush();
                $doc = null;
            }

            $form = $this->createFormBuilder(new Document())
                ->add('file', 'file')
                ->getForm();

            return new Response(json_encode(array(
                'documentThumbnail' => $this->renderView('PiloteTaskerBundle:Main:documentThumbnail.html.twig', 
                    array('document' => $doc, 'form' => $form->createView())
                ),
                'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
            )));

        } else {
            return new Response("");
        }
    }

    /**
     * Définir une date de début et/ou une date de fin pour une tâche.
     * Note : Dans la vue, il n'est pas possible de ne définir qu'une
     * date de début.
     * @param [POST] taskId : L'id de la tâche concernée
     * @param [POST] startDate : La date de début
     * @param [POST] endDate : La date de fin
     * @return [JSON] Tableau contenant le rendu HTML de la zone de l'aperçu 
     * de la tâche résumant ses différentes caractéristiques.
     */
    public function setDatesAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $task = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $request->request->get('taskId'));
            
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');

            if ($startDate != null) {
                $start = \DateTime::createFromFormat('j/m/Y', $startDate);
                $task->setStartDate($start);
            } else {
                $task->setStartDate(null);
            }
            if ($endDate != null) {
                $end = \DateTime::createFromFormat('j/m/Y', $endDate);
                $task->setEndDate($end);
            } else {
                $task->setEndDate(null);
            }
            $em->flush();

            // Notifications
            $this->sendNotificationForTaskUpdate($task, $this->getUser(), "a modifié les dates de la tâche");

            return new Response(json_encode(array(
                'infos' => $this->renderView('PiloteTaskerBundle:Main:taskInfos.html.twig', array('task' => $task))
            )));

        } else {
            return new Response("");
        }
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

    private function getErrorMessages(\Symfony\Component\Form\Form $form) 
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }

    /**
     * Envoyer des notifications au serveur Node lors de la modification
     * des éléments d'une tâche.
     * @param  Task   $task    La tâche concernée
     * @param  User   $sender  L'utilisateur à l'origine de la notification
     * @param  string $message Un message précisant la modification effectuée (facultatif)
     * Des notifications seront envoyées aux personnes concernées, à savoir la personne
     * assignée à la tâche, sauf s'il s'agit de $sender.
     */
    private function sendNotificationForTaskUpdate($task, $sender, $message = 'a modifié la tâche')
    {
        $receivers = array();
        if ($task->getCreator() != null && $task->getCreator() != $sender) {
            $receivers[] = $task->getCreator();
        }
        $notifTitle = $sender.' '.$message.' <em>'.$task.'</em>';
        $notifContent = ' du projet <em>'.$task->getTList()->getStep()->getDomain()->getBoard().'</em>.';
        $link = $this->generateUrl('pilote_tasker_board', array(
            'boardId' => $task->getTList()->getStep()->getDomain()->getBoard()->getId()));
        $this->sendNotifications($sender, $receivers, $notifTitle, $notifContent, $link);
    }

    /**
     * Envoyer des notifications au serveur Node avec quelques 
     * paramètres prédéfinis :
     * - Les notifications seront de type "simple-notification".
     * - Elles afficheront une notification visible par l'utilisateur,
     * et seront stockées dans le menu des notifications.
     * @param  User        $sender    L'utilisateur à l'origine de la notification
     * @param  Array(User) $receivers Les utilisateurs concernés par la notification
     * @param  string      $title     Le titre de la notification
     * @param  string      $content   Le contenu de la notification
     * @param  string      $link      Le lien vers lequel la notification va renvoyer
     */
    private function sendNotifications($sender, $receivers, $title, $content, $link)
    {
        if (sizeof($receivers) > 0) {
            $em = $this->getDoctrine()->getManager();
            $usersIds = array();
            $notif = null;
            foreach ($receivers as $user) {
                $notif = new Notification($sender, $user, $title, $content, $link);
                $em->persist($notif);
                $usersIds[] = $user->getUuid();
            }
            $em->flush();
            $client = $this->get('elephantio_client.default');
            $client->send('simple-notification', [
                'html' => $this->renderView('PiloteUserBundle:Notifications:notification.html.twig', 
                    array('notif' => $notif)),
                'users' => $usersIds,
            ]);
        }
    }
}
