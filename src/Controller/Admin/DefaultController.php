<?php

namespace App\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN")
 *
 */
class DefaultController extends AbstractController
{
    /**
     *
     * @Route("/", methods="GET", name="admin_index")
     */
    public function index(Request $request): Response
    {
        return $this->render('admin/default/index.html.twig');
    }

}
