<?php

namespace Jazzyweb\AulasMentor\NotasFrontendBundle\JAMNotasFrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('JAMNotasFrontendBundle:Default:index.html.twig');
    }
}
