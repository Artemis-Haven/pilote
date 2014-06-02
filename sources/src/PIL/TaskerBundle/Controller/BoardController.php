<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use PIL\TaskerBundle\Entity\Board;
use PIL\TaskerBundle\Form\BoardType;

/**
 * Board controller.
 *
 */
class BoardController extends Controller
{

    /**
     * Lists all Board entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PILTaskerBundle:Board')->findAll();

        return $this->render('PILTaskerBundle:Board:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Board entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Board();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('piltasker_accueil'));
        }

        return $this->render('PILTaskerBundle:Board:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Board entity.
    *
    * @param Board $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Board $entity)
    {
        $form = $this->createForm(new BoardType(), $entity, array(
            'action' => $this->generateUrl('board_create'),
            'method' => 'POST',
            'attr' => array('class' => 'form' )
        ));
        
        $form->add('submit', 'submit', array('label' => 'Créer', 'attr' => array('class' => 'btn btn-success' )));

        return $form;
    }

    /**
     * Displays a form to create a new Board entity.
     *
     */
    public function newAction()
    {
        $entity = new Board();
        $form   = $this->createCreateForm($entity);

        return $this->render('PILTaskerBundle:Board:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Board entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Board')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:Board:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Board entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Board')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:Board:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Board entity.
    *
    * @param Board $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Board $entity)
    {
        $form = $this->createForm(new BoardType(), $entity, array(
            'action' => $this->generateUrl('board_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Valider', 'attr' => array('class' => 'btn btn-success' )));

        return $form;
    }
    /**
     * Edits an existing Board entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Board')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('piltasker_accueil'));
        }

        return $this->render('PILTaskerBundle:Board:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Check before deleting a Board entity.
     *
     */
    public function deleteCheckAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Board')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Board entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:Board:delete_check.html.twig', array(
            'board'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Board entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PILTaskerBundle:Board')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Board entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('piltasker_accueil'));
    }

    /**
     * Creates a form to delete a Board entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('board_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Supprimer', 'attr' => array('class' => 'btn btn-danger' )))
            ->getForm()
        ;
    }
}
