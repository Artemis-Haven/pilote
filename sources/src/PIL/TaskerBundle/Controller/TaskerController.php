<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use PIL\TaskerBundle\Entity\CheckListOption as CheckListOption;
use PIL\TaskerBundle\Entity\CheckList as CheckList;
use PIL\TaskerBundle\Entity\Task as Task;
use PIL\TaskerBundle\Entity\TList as TList;
use PIL\TaskerBundle\Entity\Step as Step;
use PIL\TaskerBundle\Entity\Domain as Domain;
use PIL\TaskerBundle\Entity\HasCommented as HasCommented;

class TaskerController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $boardList = $em->getRepository('PILTaskerBundle:Board')->findAll();

        return $this->render('PILTaskerBundle::index.html.twig', array(
            'boardList' => $boardList,
        ));
    }

    public function boardAction($boardId)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $em->getRepository('PILTaskerBundle:Board')->find($boardId);

        return $this->render('PILTaskerBundle::board.html.twig', array(
            'board' => $board,
        ));
    }

    public function createTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $tListId = $request->request->get('tListId');
            $tList = $em->getRepository('PILTaskerBundle:TList')->find($tListId);
            $newTask = new Task();
            $tList->addTask($newTask);

            $em->persist($newTask);
            $em->flush();

            $response = new Response(json_encode(array(
                'taskId' => $newTask->getId(), 
                'taskName' => $newTask->getName(), 
                'taskContent' => $newTask->getContent()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    public function deleteTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);

            $em->remove($task);
            $em->flush();
        }

        return new Response("");
    }


    public function renameTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $newTitle = $request->request->get('newTitle');
            $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);

            $task->setName($newTitle);
            $em->flush();

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }


    public function updateTaskContentAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $newContent = $request->request->get('newContent');
            $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);
            
            $response = new Response($task->getContent());

            $task->setContent($newContent);
            $em->flush();

            return $response;

        } else {

            return new Response("");
        }
    }


    public function getTaskDetailsAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);
            $tList = $task->getTList();
            $domain = $tList->getStep()->getDomain();

        return $this->render('PILTaskerBundle::taskDetails.html.twig', array(
            'task' => $task,
            'tList' => $tList,
            'domain' => $domain
        ));

        } else {
            return new Response("");
        }
    }

    public function createTListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $stepId = $request->request->get('stepId');
            $step = $em->getRepository('PILTaskerBundle:Step')->find($stepId);
            $newTList = new TList();
            $step->addTList($newTList);

            $em->persist($newTList);
            $em->flush();

            $response = new Response(json_encode(array(
                'tListId' => $newTList->getId(), 
                'tListName' => $newTList->getName(), 
                'tListDescription' => $newTList->getDescription(),
                'nbrOfLists' => sizeof($step->getTLists())
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    public function deleteTListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $tListId = $request->request->get('tListId');
            $tList = $em->getRepository('PILTaskerBundle:TList')->find($tListId);
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

            return $response;

        } else {

            return new Response("");
        }
    }


    public function renameTListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $tListId = $request->request->get('tListId');
            $newTitle = $request->request->get('newTitle');
            $tList = $em->getRepository('PILTaskerBundle:TList')->find($tListId);

            $tList->setName($newTitle);
            $em->flush();

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }

    public function createStepAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $domainId = $request->request->get('domainId');
            $domain = $em->getRepository('PILTaskerBundle:Domain')->find($domainId);
            $newStep = new Step();
            $domain->addStep($newStep);

            $em->persist($newStep);
            $em->flush();

            $response = new Response(json_encode(array(
                'stepId' => $newStep->getId(), 
                'stepName' => $newStep->getName()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    public function deleteStepAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $stepId = $request->request->get('stepId');
            $step = $em->getRepository('PILTaskerBundle:Step')->find($stepId);
            

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


    public function renameStepAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $stepId = $request->request->get('stepId');
            $newTitle = $request->request->get('newTitle');
            $step = $em->getRepository('PILTaskerBundle:Step')->find($stepId);

            $step->setName($newTitle);
            $em->flush();

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }


    public function createDomainAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $boardId = $request->request->get('boardId');
            $board = $em->getRepository('PILTaskerBundle:Board')->find($boardId);
            $newDomain = new Domain();
            $board->addDomain($newDomain);

            $em->persist($newDomain);
            $em->flush();

            $response = new Response(json_encode(array(
                'domainId' => $newDomain->getId(), 
                'domainName' => $newDomain->getName()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    public function deleteDomainAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $domainId = $request->request->get('domainId');
            $domain = $em->getRepository('PILTaskerBundle:Domain')->find($domainId);
            
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


    public function renameDomainAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $domainId = $request->request->get('domainId');
            $newTitle = $request->request->get('newTitle');
            $domain = $em->getRepository('PILTaskerBundle:Domain')->find($domainId);

            $domain->setName($newTitle);
            $em->flush();

            $response = new Response(json_encode(null));

            return $response;

        } else {

            return new Response("");
        }
    }


    public function createChecklistAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);
            $newChecklist = new CheckList();
            $task->addChecklist($newChecklist);

            $em->persist($newChecklist);
            $em->flush();

            $response = new Response(json_encode(array(
                'id' => $newChecklist->getId(), 
                'name' => $newChecklist->getName()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    public function renameChecklistAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $checklistId = $request->request->get('checklistId');
            $newName = $request->request->get('newName');
            $checklist = $em->getRepository('PILTaskerBundle:CheckList')->find($checklistId);

            $checklist->setName($newName);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }


    public function createChecklistOptionAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $checklistId = $request->request->get('checklistId');
            $checklist = $em->getRepository('PILTaskerBundle:CheckList')->find($checklistId);
            $newChecklistOption = new CheckListOption();
            $checklist->addChecklistOption($newChecklistOption);

            $em->persist($newChecklistOption);
            $em->flush();

            $response = new Response(json_encode(array(
                'id' => $newChecklistOption->getId(), 
                'optionText' => $newChecklistOption->getOptionText()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }


    public function renameChecklistOptionAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $checklistOptionId = $request->request->get('checklistOptionId');
            $newName = $request->request->get('newName');
            $checklistOption = $em->getRepository('PILTaskerBundle:CheckListOption')->find($checklistOptionId);

            $checklistOption->setOptionText($newName);
            $em->flush();

            return new Response(json_encode(null));

        } else {

            return new Response("");
        }
    }

    
    public function createCommentAction() {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();

            $taskId = $request->request->get('taskId');
            $content = $request->request->get('content');
            $task = $em->getRepository('PILTaskerBundle:Task')->find($taskId);
            $user = $this->container->get('security.context')->getToken()->getUser();
            $newComment = new HasCommented($user ,$task);
            $newComment->setComment($content);
            $task->addComment($newComment);

            $em->persist($newComment);
            $em->flush();

            $response = new Response(json_encode(array(
                'commentId' => $newComment->getId(), 
                'commentContent' => $newComment->getComment(), 
                'commentDate' => $newComment->getDate(), 
                'commentUser' => $user->getUsername(), 
                'commentTask' => $newComment->getTask()
                )));

            return $response;
            
        } else {
            return new Response("");
        }
    }

}
