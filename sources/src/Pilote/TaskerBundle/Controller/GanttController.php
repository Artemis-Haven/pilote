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

namespace Pilote\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pilote\TaskerBundle\Entity\DependencyLink as Link;

/**
 * Contrôleur des pages et des requêtes AJAX concernant le Diagramme de Gantt
 * et le Calendrier.
 */

class GanttController extends Controller {
    
    /**
     * Page du diagramme de Gantt d'un board
     * @param  int $boardId : L'id du board concerné
     */
    public function indexAction($boardId) {
        
        $em = $this->getDoctrine()->getManager();
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);
        $currentTasks = $this->getRequest()->query->get('currentTasks');
        $uuid = $this->getRequest()->query->get('uuid');
        $scale = $this->getRequest()->query->get('scale');

         /* Structure avec deux tableaux  */
        $ganttData = array(
            'data' => array(), 
            'links' => array(), 
            'totalTasksCount' => 0 );

        $ganttData = $this->getDataForBoard($board, $ganttData, $currentTasks, $uuid);
        
        return $this->render('PiloteTaskerBundle:GanttCalendar:gantt.html.twig', array(
            'board' => $board,
            'ganttData' => json_encode($ganttData),
            'currentTasks' => $currentTasks,
            'uuid' => $uuid,
            'scale' => $scale,
            'totalTasksCount' => $ganttData['totalTasksCount']
        ));
    }

    /**
     * Page du diagramme de Gantt de l'utilisateur courant, 
     * réunissant tous ses projets.
     */
    public function userGanttAction()
    {
        $em = $this->getDoctrine()->getManager();

        $currentTasks = $this->getRequest()->query->get('currentTasks');
        $scale = $this->getRequest()->query->get('scale');

         /* Structure avec deux tableaux  */
        $ganttData = array(
            'data' => array(), 
            'links' => array(), 
            'totalTasksCount' => 0 );
        foreach ($this->getUser()->getBoards() as $board) {
            $ganttData = $this->getDataForBoard($board, $ganttData, $currentTasks, $this->getUser()->getUuid(), true);
            $ganttData['data'][] = array (
                "id" => "b".$board->getId(),
                "text" => $board->getName(),
                "type" => "project",
                "open" => true,
            );
        }

        
        
        return $this->render('PiloteTaskerBundle:GanttCalendar:gantt.html.twig', array(
            'ganttData' => json_encode($ganttData),
            'currentTasks' => $currentTasks,
            'scale' => $scale,
            'totalTasksCount' => $ganttData['totalTasksCount']
        ));
    }

    /**
     * Renvoie les données permettant d'afficher le diagramme de Gantt d'un board, 
     * c'est à dire les données concernant les tâches, étapes et domaines de ce board
     * dans $ganttData['data'], et les données concernant les Liens de Dépendances dans
     * $ganttData['links'].
     * Trois filtres sont en paramètres d'entrée, selon que l'on veut filtrer juste les 
     * tâches en cours, juste les tâches d'une personne, ou si l'on veut générer le 
     * diagramme de Gantt de l'utilisateur courant.
     * 
     * @param  Board    $board        Le board concerné
     * @param  array()  $ganttData    Tableau de 2 tableaux 'data' et 'links' auxquels on veut
     * ajouter les données concernant le board courant.
     * @param  boolean  $currentTasks TRUE si l'on veut filtrer juste les tâches en cours, FALSE sinon
     * @param  string   $uuid         L'uuid d'un utilisateur si l'on veut filtrer juste ses tâches, NULL sinon
     * @param  boolean  $userGantt    TRUE si l'on veut afficher le Gantt d'un utilisateur, FALSE sinon
     * 
     * @return array()  $ganttData    Le même tableau $ganttData auquel on a ajouté les tâches et les
     * liens concernant ce board.
     */
    private function getDataForBoard($board, $ganttData, $currentTasks, $uuid, $userGantt = false)
    {
        $em = $this->getDoctrine()->getManager();
        $totalTasksCount = 0;

        foreach ($board->getDomains() as $domain) {
            $stepCount = 0;
            foreach ($domain->getSteps() as $step) {
                $taskCount = 0;
                foreach ($step->getTLists() as $tList) {
                    foreach ($tList->getTasks() as $task) {
                        if ($task->getStartDate() != null && $task->getEndDate() != null) {
                            $t = array (
                                "id" => "t".$task->getId(),
                                "text" => $task->getName(),
                                "parent" => "s".$step->getId(),
                                "start_date" => $task->getStartDate()->format("d-m-Y"),
                                "type" => "task",
                                "end_date" => $task->getEndDate()->modify('+1 day')->format("d-m-Y"),
                                "progress" => $task->getProgress()/100,
                            );
                            /* Test pour savoir si la tâche est filtrée ou non */
                            if ( !$this->isFiltered($currentTasks, $uuid, $task) ) {
                                $ganttData['data'][] = $t;
                                $taskCount++;
                                $totalTasksCount++;
                            }
                        } else if ($task->getEndDate() != null) {
                            $t = array (
                                "id" => "t".$task->getId(),
                                "text" => $task->getName(),
                                "parent" => "s".$step->getId(),
                                "start_date" => $task->getEndDate()->format("d-m-Y"),
                                "end_date" => $task->getEndDate()->format("d-m-Y"),
                                "type" => "myMilestone",
                            );
                            /* Test pour savoir si la tâche est filtrée ou non */
                            if ( !$this->isFiltered($currentTasks, $uuid, $task) ) {
                                $ganttData['data'][] = $t;
                                $taskCount++;
                                $totalTasksCount++;
                            }
                        }

                        $linkForTask = $em->getRepository('PiloteTaskerBundle:DependencyLink')->findBySource($task);
                        foreach ($linkForTask as $link) {
                            $ganttData['links'][] = array (
                                "id" => $link->getId(),
                                "source" => "t".$link->getSource()->getId(),
                                "target" => "t".$link->getTarget()->getId(),
                                "type" => $link->getType()
                            );
                        }
                    }    /* Fin pour chaque Task */
                }    /* Fin pour chaque List */
                if ($taskCount != 0) {
                    $ganttData['data'][] = array (
                        "id" => "s".$step->getId(),
                        "text" => $step->getName(),
                        "parent" => "d".$domain->getId(),
                        "type" => "project",
                        "open" => true,
                    );
                    $taskCount = 0;
                    $stepCount++;
                }
            }    /* Fin pour chaque step */
            if ($stepCount != 0) {
                $ganttData['data'][] = array (
                    "id" => "d".$domain->getId(),
                    "text" => $domain->getName(),
                    "type" => "project",
                    "open" => true,
                    "parent" => ($userGantt ? 'b'.$board->getId() : 'root')
                );
                $stepCount = 0;
            }
        }    /* Fin pour chaque domaine */

        $ganttData['totalTasksCount'] += $totalTasksCount;
        return $ganttData;
    }

    /**
     * Déplacement d'une tâche dans le diagramme de Gantt.
     * On modifie donc ses dates de début et de fin.
     * @param [POST] taskId : L'id de la tâche concernée
     * @param [POST] startDate : La nouvelle date de début de la tâche
     * @param [POST] endDate : La nouvelle date de fin de la tâche
     */
    public function moveTaskAction()
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
            return new Response(json_encode(null));

        } else {
            return new Response("");
        }
    }

    /**
     * Ajouter un Lien de Dépendance (DependencyLink) entre deux tâches.
     * Avant de la créer, on vérifie que l'on n'a pas de doublon.
     * @param [POST] source : L'id de la tâche source
     * @param [POST] target : L'id de la tâche cible
     * @param [POST] type : Le type du lien (début->fin, début->début, fin->début ou 
     * fin->fin). Il s'agit d'un paramètre géré par dHTMLXGantt.
     * @return [JSON] "exists" s'il s'agit d'un doublon, NULL sinon.
     */
    public function addLinkAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $link = new Link();
            $sourceId = $request->request->get('source');
            $source = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $sourceId);
            $targetId = $request->request->get('target');
            $target = $this->findOr404($em, 'PiloteTaskerBundle', 'Task', $targetId);
            $type = $request->request->get('type');

            if ($source != null && $target != null) {
                $duplicate = $em->getRepository('PiloteTaskerBundle:DependencyLink')->findOneBy(array(
                    'source' => $source, 
                    'target' => $target, 
                    'type'   => $type, 
                    ));
                if ($duplicate != null) {
                    return new Response(json_encode(array('exists' => true)));
                }
                $link->setTarget($target);
                $link->setSource($source);
                $link->setType($type);
            }

            $em->persist($link);
            $em->flush();
            return new Response(json_encode(null));

        } else {
            return new Response("");
        }
    }

    /**
     * Supprimer un Lien de Dépendance (DépendencyLink).
     * @param [POST] source : L'id de la tâche source
     * @param [POST] target : L'id de la tâche cible
     * @param [POST] type : Le type du lien 
     */
    public function deleteLinkAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();

            $sourceId = $request->request->get('source');
            $targetId = $request->request->get('target');
            $type = $request->request->get('type');

            if ($sourceId != null && $targetId != null) {
                $links = $em->getRepository('PiloteTaskerBundle:DependencyLink')->findBy(array(
                    'source' => $sourceId, 
                    'target' => $targetId, 
                    'type'   => $type, 
                    ));
                if ($links != null) {
                    foreach ($links as $link)
                        $em->remove($link);
                }
            }
            $em->flush();
            return new Response(json_encode(null));

        } else {
            return new Response("");
        }
    }
    
    /**
     * Page affichant le calendrier des tâches d'un board
     * @param  int $boardId : Le board concerné
     */
    public function calendarAction($boardId)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);
        $tasksArray = array();

        foreach ($board->getDomains() as $domain) {
            foreach ($domain->getSteps() as $step) {
                foreach ($step->getTLists() as $tList) {
                    foreach ($tList->getTasks() as $task) {
                        if ($task->getStartDate() != null && $task->getEndDate() != null) {
                            $tasksArray[] = array (
                                'id' => $task->getId(),
                                'title' => $task->getName(),
                                'start' => $task->getStartDate()->format("Y-m-d"),
                                'end' => $task->getEndDate()->modify('+1 day')->format("Y-m-d"),
                                'className' => 'task'
                            );
                        } else if ($task->getEndDate() != null) {
                            $tasksArray[] = array (
                                'id' => $task->getId(),
                                'title' => $task->getName(),
                                'start' => $task->getEndDate()->format("Y-m-d"),
                                'end' => $task->getEndDate()->format("Y-m-d"),
                                'className' => 'milestone'
                            );
                        }
                    }
                }
            }
        }

        return $this->render('PiloteTaskerBundle:GanttCalendar:calendar.html.twig', array(
            'board' => $board,
            'tasks' => json_encode($tasksArray),
        ));
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

    /**
     * Vérifie selon les paramètres d'entrée si la tâche passée en paramètre
     * doit être filtrée ou non.
     * Si $currentTasks est TRUE, on vérifie si la tâche est terminée.
     * Si $uuid n'est pas NULL, on vérifie si la tâche est assignée à
     * l'utilisateur ayant cet uuid.
     * @param  [type]  $currentTasks [description]
     * @param  [type]  $uuid         [description]
     * @param  [type]  $task         [description]
     * @return boolean               [description]
     */
    private function isFiltered($currentTasks, $uuid, $task)
    {
        if ($currentTasks) {
            if ($task->getProgress() == 100) {
                return true;
            }
            if ($task->getEndDate() < (new \DateTime())->modify('-1 day') ) {
                return true;
            }
            if ($task->getStartDate() != null && $task->getStartDate() > (new \DateTime())->modify('+1 day')) {
                return true;
            }
        }
        if ($uuid != null) {
            if ($task->getCreator() != null && $task->getCreator()->getUuid() == $uuid) {
                return false;
            }
            return true;
        }
        return false;
    }
}
