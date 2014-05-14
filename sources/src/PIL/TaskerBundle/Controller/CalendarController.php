<?php

namespace PIL\TaskerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CalendarController extends Controller {
    
    public function showCalendarAction()
    {
        return $this->render('PILTaskerBundle::calendar.html.twig');
    }
}