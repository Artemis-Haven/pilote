<?php

namespace PIL\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PILAdminBundle:Default:index.html.twig');
    }
    
    public function inboxAction()
    {
        return $this->render('PILAdminBundle::inbox.html.twig');
    }
    
    public function usersAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('PILAdminBundle::users.html.twig', array('users' =>   $users));
    }
    
    public function bddAction()
    {
        return $this->render('PILAdminBundle::bdd.html.twig');
    }
    
    public function statsAction()
    {
        return $this->render('PILAdminBundle::stats.html.twig');
    }
    
    public function activityAction()
    {
        return $this->render('PILAdminBundle::activity.html.twig');
    }
}
