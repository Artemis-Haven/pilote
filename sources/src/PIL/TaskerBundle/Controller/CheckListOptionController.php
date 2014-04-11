<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use PIL\TaskerBundle\Entity\CheckListOption;
use PIL\TaskerBundle\Form\CheckListOptionType;

/**
 * CheckListOption controller.
 *
 */
class CheckListOptionController extends Controller
{

    /**
     * Lists all CheckListOption entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PILTaskerBundle:CheckListOption')->findAll();

        return $this->render('PILTaskerBundle:CheckListOption:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new CheckListOption entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new CheckListOption();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('checklistoption_show', array('id' => $entity->getId())));
        }

        return $this->render('PILTaskerBundle:CheckListOption:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a CheckListOption entity.
    *
    * @param CheckListOption $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(CheckListOption $entity)
    {
        $form = $this->createForm(new CheckListOptionType(), $entity, array(
            'action' => $this->generateUrl('checklistoption_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new CheckListOption entity.
     *
     */
    public function newAction()
    {
        $entity = new CheckListOption();
        $form   = $this->createCreateForm($entity);

        return $this->render('PILTaskerBundle:CheckListOption:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CheckListOption entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:CheckListOption')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CheckListOption entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:CheckListOption:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing CheckListOption entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:CheckListOption')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CheckListOption entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:CheckListOption:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a CheckListOption entity.
    *
    * @param CheckListOption $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(CheckListOption $entity)
    {
        $form = $this->createForm(new CheckListOptionType(), $entity, array(
            'action' => $this->generateUrl('checklistoption_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing CheckListOption entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:CheckListOption')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CheckListOption entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('checklistoption_edit', array('id' => $id)));
        }

        return $this->render('PILTaskerBundle:CheckListOption:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a CheckListOption entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PILTaskerBundle:CheckListOption')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find CheckListOption entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('checklistoption'));
    }

    /**
     * Creates a form to delete a CheckListOption entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('checklistoption_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
