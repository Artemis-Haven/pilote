<?php

namespace Pilote\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pilote\TaskerBundle\Entity\TList;
use Pilote\TaskerBundle\Form\TListType;

/**
 * TList controller.
 *
 */
class TListController extends Controller
{

    /**
     * Lists all TList entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PiloteTaskerBundle:TList')->findAll();

        return $this->render('PiloteTaskerBundle:TList:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new TList entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new TList();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('crud_tlist_show', array('id' => $entity->getId())));
        }

        return $this->render('PiloteTaskerBundle:TList:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a TList entity.
    *
    * @param TList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(TList $entity)
    {
        $form = $this->createForm(new TListType(), $entity, array(
            'action' => $this->generateUrl('crud_tlist_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new TList entity.
     *
     */
    public function newAction()
    {
        $entity = new TList();
        $form   = $this->createCreateForm($entity);

        return $this->render('PiloteTaskerBundle:TList:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a TList entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:TList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PiloteTaskerBundle:TList:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing TList entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:TList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TList entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PiloteTaskerBundle:TList:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a TList entity.
    *
    * @param TList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(TList $entity)
    {
        $form = $this->createForm(new TListType(), $entity, array(
            'action' => $this->generateUrl('crud_tlist_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing TList entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:TList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('crud_tlist_edit', array('id' => $id)));
        }

        return $this->render('PiloteTaskerBundle:TList:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a TList entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PiloteTaskerBundle:TList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find TList entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('crud_tlist'));
    }

    /**
     * Creates a form to delete a TList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('crud_tlist_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
