<?php

namespace Pilote\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pilote\TaskerBundle\Entity\DependencyLink as Link;

class GanttController extends Controller {
    
    public function indexAction($boardId) {
        
        $em = $this->getDoctrine()->getManager();
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);
        $currentTasks = $this->getRequest()->query->get('currentTasks');
        $scale = $this->getRequest()->query->get('scale');

         /* Structure avec deux tableaux  */
        $ganttData = array(
            'data' => array(), 
            'links' => array() );

        $ganttData = $this->getDataForBoard($board, $ganttData, $currentTasks, $uuid);
        
        return $this->render('PiloteTaskerBundle:GanttCalendar:gantt.html.twig', array(
            'board' => $board,
            'ganttData' => json_encode($ganttData),
            'currentTasks' => $currentTasks,
            'uuid' => $uuid,
            'scale' => $scale
        ));
    }

    public function userGanttAction()
    {
        $em = $this->getDoctrine()->getManager();

        $currentTasks = $this->getRequest()->query->get('currentTasks');
        $uuid = $this->getRequest()->query->get('uuid');
        $scale = $this->getRequest()->query->get('scale');

         /* Structure avec deux tableaux  */
        $ganttData = array(
            'data' => array(), 
            'links' => array() );
        foreach ($this->getUser()->getBoards() as $board) {
            $ganttData = $this->getDataForBoard($board, $ganttData, $currentTasks, $uuid, true);
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
            'scale' => $scale
        ));
    }


    private function getDataForBoard($board, $ganttData, $currentTasks, $uuid, $userGantt = false)
    {
        $em = $this->getDoctrine()->getManager();

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
        return $ganttData;
    }

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


    private function findOr404($em, $bundle, $class, $id)
    {
        $entity = $em->getRepository($bundle.':'.$class)->find($id);
        if ($entity==null) {
            throw $this->createNotFoundException('Unable to find '.$class.' entity.');
        }
        return $entity;
    }

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
