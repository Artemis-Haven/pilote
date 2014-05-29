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
        $task = $task->getTList();

        $response = new Response(json_encode(array(
                'taskId' => $task->getId(), /*
                'taskName' => $newTask->getName(), 
                'taskContent' => $newTask->getContent()*/
                )));
      
        return $response;
    }
 
}
