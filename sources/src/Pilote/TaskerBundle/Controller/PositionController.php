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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Pilote\TaskerBundle\Entity\Task as Task;
use Pilote\TaskerBundle\Entity\TList as TList;
use Pilote\TaskerBundle\Entity\Step as Step;

/**
 * Contrôleur gérant les déplacements des tâches et des
 * listes de tâches dans la vue du Board.
 *
 * Ce sont toutes des requêtes AJAX.
 */

class PositionController extends Controller
{    
    /**
     * Déplacement d'une tâche.
     * @param [POST] movedTaskId : L'id de la tâche à déplacer
     * @param [POST] upperTaskId ! L'id de la tâche juste au-dessus du nouvel
     * emplacement de la tâche. Vaut -1 si la tâche est tout en haut de la liste.
     * @param [POST] newListId : L'id de la liste dans laquelle la tâche est déplacée, 
     * si upperTaskId = -1.
     */
    public function moveTaskAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            
            $em = $this->getDoctrine()->getManager();
            
            // Récupération de la tâche en mouvement et de sa liste d'origine
            $movedTask = $em->getRepository('PiloteTaskerBundle:Task')->find($request->request->get('movedTaskId'));
            $oldList = $movedTask->getTList();
            
            $insertionPosition = 0;
            
            // Récupération de la tâche précédant la tâche en mouvement (au nouvel emplacement)
            if($request->request->get('upperTaskId') != -1) {
                // S'il y a bien une tâche : récupération de cette tâche et de la nouvelle liste
                $upperTask = $em->getRepository('PiloteTaskerBundle:Task')->find($request->request->get('upperTaskId'));
                $newList = $upperTask->getTList();
                $insertionPosition = $upperTask->getPosition() + 1;
            } else {
                // Si aucune tâche ne précède la tâche en mouvement : récupération de la nouvelle liste 
                $newList = $em->getRepository('PiloteTaskerBundle:TList')->find($request->request->get('newListId'));
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

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('move-task', [
                'sender' => $this->getUser()->getUuid(),
                'taskId' => $movedTask->getId(),
                'upperTaskId' => $request->request->get('upperTaskId'),
                'tListId' => $movedTask->getTList()->getId(),
                'boardId' => $movedTask->getTList()->getStep()->getDomain()->getBoard()->getId()
            ]);

            return new Response(json_encode(null));
          
        } else {

            return new Response("Mauvais appel de la fonction moveTask. Bad Request.");
        }
        
    }
    
    /**
     * Déplacement d'une liste de tâches dans une étape.
     * @param [POST] movedListId : L'id de la liste à déplacer
     * @param [POST] leftListId : L'id de la liste à gauche de la liste à déplacer,
     * à son nouvel emplacement. Si leftListId = -1, alors la liste est déplacée
     * en première position.
     */
    public function moveListAction()
    {
        $request = $this->container->get('request');

        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getManager();
            // Récupération de la liste en mouvement et de sa nouvelle position
            $movedList = $em->getRepository('PiloteTaskerBundle:TList')->find($request->request->get('movedListId'));
            // Récupération de l'étape correspondante
            $step = $movedList->getStep();
            
            // Récupération de la liste précédant la liste en mouvement (au nouvel emplacement)
            if($request->request->get('leftListId') != -1) {
                // S'il y a bien une liste : récupération de cette liste
                $leftList = $em->getRepository('PiloteTaskerBundle:TList')->find($request->request->get('leftListId'));
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

            // Envoi vers Node.JS pour MàJ auto des autres clients
            $client = $this->get('elephantio_client.default');
            $client->send('move-tlist', [
                'sender' => $this->getUser()->getUuid(),
                'tListId' => $movedList->getId(),
                'leftListId' => $request->request->get('leftListId'),
                'stepId' => $movedList->getStep()->getId(),
                'boardId' => $movedList->getStep()->getDomain()->getBoard()->getId()
            ]);

            return new Response(json_encode(null));
          
        } else {

            return new Response("Mauvais appel de la fonction moveTList. Bad Request.");
        }
        
    }
 
}
