<?php

namespace PIL\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
  public function indexAction()
  {
    return $this->render('::accueil.html.twig');
  }
  
  public function aproposAction()
  {
    return $this->render('::apropos.html.twig');
  }
  
  public function contactAction()
  {
    return $this->render('::contact.html.twig');
  }
}
