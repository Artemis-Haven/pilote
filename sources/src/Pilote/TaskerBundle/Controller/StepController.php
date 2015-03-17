<?php

namespace Pilote\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pilote\TaskerBundle\Entity\Step;
use Pilote\TaskerBundle\Form\StepType;

/**
 * Step controller.
 *
 */
class StepController extends Controller
{

    /**
     * Lists all Step entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PiloteTaskerBundle:Step')->findAll();

        return $this->render('PiloteTaskerBundle:Step:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Step entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Step();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('crud_step_show', array('id' => $entity->getId())));
        }

        return $this->render('PiloteTaskerBundle:Step:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Step entity.
    *
    * @param Step $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Step $entity)
    {
        $form = $this->createForm(new StepType(), $entity, array(
            'action' => $this->generateUrl('crud_step_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Step entity.
     *
     */
    public function newAction()
    {
        $entity = new Step();
        $form   = $this->createCreateForm($entity);

        return $this->render('PiloteTaskerBundle:Step:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Step entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:Step')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Step entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PiloteTaskerBundle:Step:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Step entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:Step')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Step entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PiloteTaskerBundle:Step:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Step entity.
    *
    * @param Step $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Step $entity)
    {
        $form = $this->createForm(new StepType(), $entity, array(
            'action' => $this->generateUrl('crud_step_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Step entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PiloteTaskerBundle:Step')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Step entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('crud_step_edit', array('id' => $id)));
        }

        return $this->render('PiloteTaskerBundle:Step:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Step entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PiloteTaskerBundle:Step')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Step entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('crud_step'));
    }

    /**
     * Creates a form to delete a Step entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('crud_step_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
