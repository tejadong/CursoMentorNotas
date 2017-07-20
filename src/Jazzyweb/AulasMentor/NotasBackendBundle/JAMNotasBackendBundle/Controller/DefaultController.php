<?php

namespace Jazzyweb\AulasMentor\NotasBackendBundle\JAMNotasBackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('JAMNotasBackendBundle:Default:index.html.twig');
    }
}
