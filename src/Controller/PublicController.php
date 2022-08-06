<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage blog contents in the public part of the site.
 *
 * @Route("/public")
 *
 */
class PublicController extends AbstractController
{
    /**
     * @Route("/", methods="GET", name="public_index")
     * @Cache(smaxage="10")
     *
     */
    public function index(Request $request): Response
    {                        
        return $this->render('default/public_index.html.twig');
    }

}
