<?php

namespace Pilote\TaskerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Pilote\TaskerBundle\Entity\CheckListOption as CheckListOption;
use Pilote\TaskerBundle\Entity\CheckList as CheckList;
use Pilote\TaskerBundle\Entity\Task as Task;
use Pilote\TaskerBundle\Entity\TList as TList;
use Pilote\TaskerBundle\Entity\Step as Step;
use Pilote\TaskerBundle\Entity\Domain as Domain;
use Pilote\TaskerBundle\Entity\HasCommented as HasCommented;
use Pilote\MessageBundle\Entity\Thread;
use Pilote\MessageBundle\Entity\ThreadMetadata;
use Pilote\MessageBundle\Entity\Metadata;
use Pilote\TaskerBundle\Entity\Board;
use Pilote\TaskerBundle\Form\BoardType;


/**
 * Contrôleur principal des pages importantes :
 * - Page listant les projets
 * - Barres de navigation
 * - Board d'un projet
 * - Réglages d'un projet
 * - etc...
 */

class TaskerController extends Controller
{
    /**
     * Page principale de l'utilisateur connecté :
     * Affiche les différents projets (non archivés) de l'utilisateur courant
     */
    public function indexAction()
    {
        return $this->render('PiloteTaskerBundle::index.html.twig');
    }

    /**
     * Page de création d'un nouveau projet.
     * Si cette fonction est appelée avec une requête GET, le formulaire
     * est généré et est envoyé à la vue.
     * Si cette fonction est appelée avec une requête POST contenant des valeurs
     * dans les champs du formulaire, ces valeurs sont vérifiées. Si elles sont bonnes,
     * le formulaire est validé et l'utilisateur est renvoyé vers la page listant les projets.
     * Sinon il est renvoyé vers la page du formulaire avec ses valeurs erronées.
     */
    public function newBoardAction(Request $request)
    {
        $entity = new Board();        

        $form = $this->createForm(new BoardType(), $entity, array(
            'action' => $this->generateUrl('pilote_tasker_board_new'),
            'method' => 'POST',
            'attr' => array('class' => 'form' )
        ));
        $form->add('submit', 'submit', array('label' => 'Créer', 'attr' => array('class' => 'btn btn-success' )));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->addUser($this->getUser());
            $this->getUser()->addBoard($entity);

            $thread = new Thread($entity->getName());
            $metadata = new ThreadMetadata();
            $thread->addMetadata($metadata);
            $this->getUser()->addMetadata($metadata);
            $entity->setThread($thread);
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('pilote_projects'));
        }

        return $this->render('PiloteTaskerBundle:Main:newBoard.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Page de suppression d'un projet. Le projet ne sera pas supprimé mais
     * seulement désactivé.
     * @param  int $boardId L'identifiant du projet à désactiver.
     */
    public function deleteBoardAction($boardId)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);

        if (!$this->AccessGranted($board)) {
          throw $this->createAccessDeniedException('You are not allowed to access to this page.');
        }

        $board->setEnabled(false);
        $em->flush();

        return $this->redirect($this->generateUrl('pilote_projects'));
    }

    /**
     * Page de la vue générale d'un projet, avec les listes de tâches, etc.
     * @param  int $boardId L'identifiant du projet concerné
     */
    public function boardAction($boardId)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);
        if (!$this->AccessGranted($board)) {
          throw $this->createAccessDeniedException('You are not allowed to access to this page.');
        }

        return $this->render('PiloteTaskerBundle:Main:board.html.twig', array(
            'board' => $board,
        ));
    }

    /**
     * Page de réglages d'un projet.
     * Comme pour la page de création d'un projet, celle-ci contient un formulaire
     * permettant d'éditer le nom et la description d'un projet.
     * 
     * Si cette fonction est appelée avec une requête GET, le formulaire
     * est généré et est envoyé à la vue.
     * Si cette fonction est appelée avec une requête POST contenant des valeurs
     * dans les champs du formulaire, ces valeurs sont vérifiées. Si elles sont bonnes,
     * le formulaire est validé.
     * Dans tous les cas, l'utilisateur sera renvoyé vers la page de réglage du projet.
     * 
     * @param  int  $boardId L'identifiant du projet concerné.
     */
    public function settingsAction(Request $request, $boardId)
    {
        $em = $this->getDoctrine()->getManager();
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);
        if (!$this->AccessGranted($board)) {
          throw $this->createAccessDeniedException('You are not allowed to access to this page.');
        }

        $form = $this->createForm(new BoardType(), $board, array(
            'action' => $this->generateUrl('pilote_tasker_board_settings', array('boardId' => $board->getid())),
            'method' => 'POST',
            'attr' => array('class' => 'form' )
        ));
        $form->add('submit', 'submit', array('label' => 'Enregistrer les modifications', 'attr' => array('class' => 'btn btn-default' )));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->flush();
            return $this->forward('PiloteTaskerBundle:Tasker:board', array(
                'boardId'  => $boardId,
            ));
        }

        return $this->render('PiloteTaskerBundle:Main:settings.html.twig', array(
            'board' => $board,
            'form'  => $form->createView(),
        ));
    }
    
    /**
     * Fonction de recherche des utilisateurs potentiels à ajouter
     * à un projet. Elle prend en entrée l'identifiant d'un projet, 
     * cherche la liste de tous les utilisateurs quy n'y sont pas liés, 
     * et renvoit une liste en JSON de la forme suivante :
     *
     * [
     *   {"label":"user2","value":3},
     *   {"label":"user3","value":4},
     *   {"label":"user4","value":5}
     * ]
     * 
     * @param  int $boardId L'identifiant du projet concerné
     */
    public function searchUserAction($boardId) {
        $em = $this->getDoctrine()->getManager();

        $username = $this->container->get('request')->query->get('term');
        $board = $this->findOr404($em, 'PiloteTaskerBundle', 'Board', $boardId);
        $users = $em->getRepository('PiloteUserBundle:User')->findUsersForBoard($boardId, $username);

        $response = array();
        foreach ($users as $user) {
            $response[] = array('label' => $user->getUsername(), 'value' => $user->getId());
        }
        return new Response(json_encode($response));
    }

    /**
     * Fonction utilitaire vérifiant si l'utilisateur courant a
     * accès au projet passé en paramètre.
     * @param Board $board Le projet à vérifier
     * @return boolean         VRAI si l'utisateur a accès à ce
     * projet, faux sinon.
     */
    private function AccessGranted($board)
    {
        $boards = $this->getUser()->getBoards();
        foreach ($boards as $b)
            if($board==$b) return true;
        return false;
    }

    /**
     * Fonction utilitaire recherhant dans l'EntityManager une entity en
     * fonction de son Bundle, sa classe et son identifiant. Si l'entité
     * n'est pas trouvée, une exception est lancée avec un message d'erreur
     * personnalisé. En production, cela résulte en une page d'erreur 404.
     * 
     * @param  EntityManager $em     L'EntityManager courant
     * @param  string $bundle Le bundle souhaité (ex : "PiloteTaskerBundle")
     * @param  string $class  La classe de l'entité recherchée (ex : "Board")
     * @param  int $id        L'identifiant de l'entité recherchée
     * @return Object         Entitée recherchée, si elle existe.
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
