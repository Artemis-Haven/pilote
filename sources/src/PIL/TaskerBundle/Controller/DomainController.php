<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use PIL\TaskerBundle\Entity\Domain;
use PIL\TaskerBundle\Form\DomainType;

/**
 * Domain controller.
 *
 */
class DomainController extends Controller
{

    /**
     * Lists all Domain entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PILTaskerBundle:Domain')->findAll();

        return $this->render('PILTaskerBundle:Domain:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Domain entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Domain();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('crud_domain_show', array('id' => $entity->getId())));
        }

        return $this->render('PILTaskerBundle:Domain:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Domain entity.
    *
    * @param Domain $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Domain $entity)
    {
        $form = $this->createForm(new DomainType(), $entity, array(
            'action' => $this->generateUrl('crud_domain_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Domain entity.
     *
     */
    public function newAction()
    {
        $entity = new Domain();
        $form   = $this->createCreateForm($entity);

        return $this->render('PILTaskerBundle:Domain:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Domain entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Domain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:Domain:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Domain entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Domain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('PILTaskerBundle:Domain:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Domain entity.
    *
    * @param Domain $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Domain $entity)
    {
        $form = $this->createForm(new DomainType(), $entity, array(
            'action' => $this->generateUrl('crud_domain_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Domain entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PILTaskerBundle:Domain')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Domain entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('crud_domain_edit', array('id' => $id)));
        }

        return $this->render('PILTaskerBundle:Domain:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Domain entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PILTaskerBundle:Domain')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Domain entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('crud_domain'));
    }

    /**
     * Creates a form to delete a Domain entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('crud_domain_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
