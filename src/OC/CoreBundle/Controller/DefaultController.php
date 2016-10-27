<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('OCCoreBundle:Default:index.html.twig');
    }

    public function contactAction(Request $request){

        $session = $request->getSession();

        $session->getFlashBag()->add(
            'warning',
            'Message flash: La page de contact n\'est pas encore disponible. Merci de revenir plus tard.'
        );

        return $this->redirectToRoute('oc_core_homepage');
    }
}
