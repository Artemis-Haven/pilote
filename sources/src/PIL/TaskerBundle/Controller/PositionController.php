<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use PIL\TaskerBundle\Entity\Task as Task;
use PIL\TaskerBundle\Entity\TList as TList;
use PIL\TaskerBundle\Entity\Step as Step;

class PositionController extends Controller
{
    public function moveTaskInListAction($taskId)
    {
        $em = $this->getDoctrine()->getManager();
        $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);
        $tList = $task->getTList();

        $response = new Response(json_encode(array(
                'taskId' => $task->getId(), /*
                'taskName' => $newTask->getName(), 
                'taskContent' => $newTask->getContent()*/
                )));
      
        return $response;
    }
    
    public function moveTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();
            
            // Récupération de la tâche en mouvement et de sa liste d'origine
            $movedTask = $em->getRepository('PILTaskerBundle:Task')->find($request->request->get('movedTaskId'));
            $oldList = $movedTask->getTList();
            
            $insertionPosition = 0;
            
            // Récupération de la tâche précédant la tâche en mouvement (au nouvel emplacement)
            if($request->request->get('upperTaskId') != -1) {
                // S'il y a bien une tâche : récupération de cette tâche et de la nouvelle liste
                $upperTask = $em->getRepository('PILTaskerBundle:Task')->find($request->request->get('upperTaskId'));
                $newList = $upperTask->getTList();
                $insertionPosition = $upperTask->getPosition() + 1;
            } else {
                // Si aucune tâche ne précède la tâche en mouvement : récupération de la nouvelle liste 
                $newList = $em->getRepository('PILTaskerBundle:TList')->find($request->request->get('newListId'));
            }
            
            // Si la tâche est déplacée dans une liste différente
            if ($oldList->getId() != $newList->getId())
            {
                foreach ($oldList->getTasks() as $t)
                {
                    if ($t->getPosition() > $movedTask->getPosition())
                        $t->setPosition($t->getPosition()-1);
                }
                foreach ($newList->getTasks() as $t)
                {
                    if ($t->getPosition() >= $insertionPosition)
                        $t->setPosition($t->getPosition()+1);
                }
                $newList->addTask($movedTask, $insertionPosition);
            } else { // Déplacement dans la même liste
                // Si la tâche est déplacée plus bas
                if ($movedTask->getPosition() < $insertionPosition)
                {
                    foreach ($oldList->getTasks() as $t)
                    {
                        if ($t->getPosition() > $movedTask->getPosition() && $t->getPosition() < $insertionPosition)
                            $t->setPosition($t->getPosition()-1);
                    }
                    $movedTask->setPosition($insertionPosition-1);
                } else { // Si la tâche est déplacée plus haut
                    foreach ($oldList->getTasks() as $t)
                    {
                        if ($t->getPosition() < $movedTask->getPosition() && $t->getPosition() >= $insertionPosition)
                            $t->setPosition($t->getPosition()+1);
                    }
                    $movedTask->setPosition($insertionPosition);
                }
            }
            $em->flush();
            return new Response(json_encode(null));
          
        } else {

            return new Response("Mauvais appel de la fonction moveTask. Bad Request.");
        }
        
    }
    
    public function moveListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            // Récupération de la liste en mouvement et de sa nouvelle position
            $movedList = $em->getRepository('PILTaskerBundle:TList')->find($request->request->get('movedListId'));
            // Récupération de l'étape correspondante
            $step = $movedList->getStep();
            
            // Récupération de la liste précédant la liste en mouvement (au nouvel emplacement)
            if($request->request->get('leftListId') != -1) {
                // S'il y a bien une liste : récupération de cette liste
                $leftList = $em->getRepository('PILTaskerBundle:TList')->find($request->request->get('leftListId'));
                $insertionPosition = $leftList->getPosition() + 1;
            } else {
                // Si aucune liste ne précède la liste en mouvement 
                $insertionPosition = 0;
            }
            
            // Si la liste est déplacée plus bas
            if ($movedList->getPosition() < $insertionPosition)
            {
                foreach ($step->getTLists() as $t)
                {
                    if ($t->getPosition() > $movedList->getPosition() && $t->getPosition() < $insertionPosition)
                        $t->setPosition($t->getPosition()-1);
                }
                $movedList->setPosition($insertionPosition-1);
            } else { // Si la tâche est déplacée plus haut
                foreach ($step->getTLists() as $t)
                {
                    if ($t->getPosition() < $movedList->getPosition() && $t->getPosition() >= $insertionPosition)
                        $t->setPosition($t->getPosition()+1);
                }
                $movedList->setPosition($insertionPosition);
            }
            
            $em->flush();
            return new Response(json_encode(null));
          
        } else {

            return new Response("Mauvais appel de la fonction moveTList. Bad Request.");
        }
        
    }
 
}
