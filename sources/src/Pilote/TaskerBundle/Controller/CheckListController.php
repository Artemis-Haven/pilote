<?php

namespace Pilote\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pilote\TaskerBundle\Entity\CheckList;
use Pilote\TaskerBundle\Form\CheckListType;

/**
 * CheckList controller.
 *
 */
class CheckListController extends Controller
{

    /**
     * Lists all CheckList entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PiloteTaskerBundle:CheckList')->findAll();

        return $this->render('PiloteTaskerBundle:CheckList:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new CheckList entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new CheckList();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('checklist_show', array('id' => $entity->getId())));
        }

        return $this->render('PiloteTaskerBundle:CheckList:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a CheckList entity.
    *
    * @param CheckList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(CheckList $entity)
    {
        $form = $this->createForm(new CheckListType(), $entity, array(
            'action' => $this->generateUrl('checklist_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new CheckList entity.
     *
     */
    public function newAction()
    {
        $entity = new CheckList();
        $form   = $this->createCreateForm($entity);

        return $this->render('PiloteTaskerBundle:CheckList:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CheckList entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:CheckList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CheckList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PiloteTaskerBundle:CheckList:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing CheckList entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:CheckList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CheckList entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PiloteTaskerBundle:CheckList:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a CheckList entity.
    *
    * @param CheckList $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(CheckList $entity)
    {
        $form = $this->createForm(new CheckListType(), $entity, array(
            'action' => $this->generateUrl('checklist_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing CheckList entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:CheckList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CheckList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('checklist_edit', array('id' => $id)));
        }

        return $this->render('PiloteTaskerBundle:CheckList:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a CheckList entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PiloteTaskerBundle:CheckList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find CheckList entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('checklist'));
    }

    /**
     * Creates a form to delete a CheckList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('checklist_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
