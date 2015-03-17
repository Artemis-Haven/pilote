<?php
namespace Pilote\UserBundle\Controller;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use Pilote\UserBundle\Entity\Picture;

class ProfileController extends BaseController
{
		public function showAction($id=-1)
		{
			$user;
			$current;

			if ($id==-1 || $this->getUser()->getUuid() == $id) {
				$user = $this->getUser();
				$current = true;
			} else {
				$em = $this->container->get('doctrine')->getManager();
				$user = $em->getRepository('PiloteUserBundle:User')->findOneByUuid($id);
				if (!$user) {
					throw $this->createNotFoundException('Unable to find User entity.');
				}
				$current = false;
			}
			return $this->render('PiloteUserBundle:Profile:show.html.twig', array(
				'user' => $user,
				'current' => $current
				));
		}

		public function editPictureAction()
		{
			$request = $this->container->get('request');

			$picture = $this->getUser()->getPicture();

			if ($picture == null) {
				$picture = new Picture();
			}

			$editForm = $this->createFormBuilder($picture)
				->add('file', 'file')
				->getForm();

			$editForm->handleRequest($request);

			return $this->render('PiloteUserBundle:Profile:edit_profile_picture.html.twig', array(
				'editForm' => $editForm->createView()
				));
		}

		public function checkNewPictureAction()
		{
			$picture = new Picture;
			$newForm = $this->createFormBuilder($picture)
				->add('file', 'file')
				->getForm();

			$newForm->handleRequest($this->container->get('request'));

			if($newForm->isValid())
			{
				$em = $this->getDoctrine()->getManager();
				$em->persist($picture);
				$this->getUser()->setPicture($picture);
				$em->flush();
			}
			return $this->redirect($this->generateUrl('pilote_profil_edit'));
		}

		public function checkEditPictureAction()
		{
			$picture = $this->getUser()->getPicture();
			$oldFile = $picture->getAbsolutePath();

			$editForm = $this->createFormBuilder($picture)
				->add('file', 'file')
				->getForm();

			$editForm->handleRequest($this->container->get('request'));

			if($editForm->isValid())
			{
				$picture->upload();
			}

			return $this->redirect($this->generateUrl('pilote_profil_edit'));
		}

		public function deletePictureAction()
		{
			$request = $this->container->get('request');

			$em = $this->getDoctrine()->getManager();
			$user = $this->getUser();
			$picture = $user->getPicture();

			if ($picture != null) {
				$path = $picture->getPath();
				$user->setPicture(null);
				$em->remove($picture);
				$em->flush();
			}

			return $this->redirect($this->generateUrl('pilote_profil_edit'));
		}
}

