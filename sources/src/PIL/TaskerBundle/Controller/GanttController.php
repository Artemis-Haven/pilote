<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GanttController extends Controller {
    
    public function indexAction($boardId) {
        
        $em = $this->getDoctrine()->getManager();
        $board = $em->getRepository('PILTaskerBundle:Board')->find($boardId);
        /* Tableau pour stock le Info de task */
        $data = array();
        foreach ($board->getDomains() as $domain) {
            foreach ($domain->getSteps() as $step) {
                foreach ($step->getTLists() as $tList) {
                    foreach ($tList->getTasks() as $task) {
                        if ($task->getStartDate() != null && $task->getEndDate() != null) {
                            $newData = array (
                                "id" => $task->getId(),
                                "text" => $task->getName(),
                                "start_date" => $task->getStartDate()->format("d-m-Y"),
                                "duration" => $task->getEndDate()->diff($task->getStartDate())->d,
                                "order" => 10,
                                "progress" => 0.5,
                            );
                            $data[] = $newData;
                        }
                        /* Fin pour chaque Task */
                    }
                    /* Fin pour chaque List */
                }
            /* Fin pour chaque step */
            }
        /* Fin pour chaque domaine */
        }
        /* Tableau pour stock le relation entre les tasks */
        $links = array();
        
         /* Structure avec deux tableaus  */
        $ganttData = array(
            'data' => $data, 
            'links' => $links );
        
        return $this->render('PILTaskerBundle::gantt.html.twig', array(
            'board' => $board,
            'ganttData' => $ganttData,
        ));
    }
    
    public function ganttAction($boardId,$domaineId) {
        
        $em = $this->getDoctrine()->getManager();
        $board = $em->getRepository('PILTaskerBundle:Board')->find($boardId);
        $domain = $em->getRepository('PILTaskerBundle:Domain')->find($domaineId);
        /* Tableau pour stock le Info de task */
        $data = array();
            foreach ($domain->getSteps() as $step) {
                foreach ($step->getTLists() as $tList) {
                    foreach ($tList->getTasks() as $task) {
                        if ($task->getStartDate() != null && $task->getEndDate() != null) {
                            $newData = array (
                                "id" => $task->getId(),
                                "text" => $task->getName(),
                                "start_date" => $task->getStartDate()->format("d-m-Y"),
                                "duration" => $task->getEndDate()->diff($task->getStartDate())->d,
                                "order" => 10,
                                "progress" => 0.5,
                            );
                            $data[] = $newData;
                        }
                        /* Fin pour chaque Task */
                    }
                    /* Fin pour chaque List */
                }
            /* Fin pour chaque step */
            }
            
        /* Tableau pour stock le relation entre les tasks */
        $links = array();
        
         /* Structure avec deux tableaus  */
        $ganttData = array(
            'data' => $data, 
            'links' => $links );
        
        return $this->render('PILTaskerBundle::gantt_domain.html.twig', array(
            'board' => $board,
            'ganttData' => $ganttData,
        ));
    }
}
