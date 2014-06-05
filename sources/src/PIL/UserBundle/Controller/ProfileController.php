<?php
namespace PIL\UserBundle\Controller;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use PIL\UskerBundle\Entity\User as User;

class ProfileController extends BaseController
{
    public function showAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();   
        $em = $this->container->get('doctrine')->getManager();
      /*$taches = $em->getRepository('PILTaskerBundle:Task')->findAll();
      */
      	$taches = $user->getTasks();
      
        // Ou null si aucun article n'a été trouvé avec l'id $id
      
        return $this->container->get('templating')->renderResponse('PILUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user,'taches' => $taches));
    }
    
    /**
     * Edit the user
     */
    public function editAction(Request $request)
    {
        $response = parent::editAction($request);
        return $response;
    }
}

