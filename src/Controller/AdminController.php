<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index()
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('danger', "connectez vous pour acceder à cette partie");
        //     return $this->redirectToRoute('app_login');
        // }

        // if($this->isGranted("ROLE_ADMIN") === false) {
        //     $this->addFlash('danger', "vous n'avez pas le droit d'être ici");
        //     return $this->redirectToRoute('home');
        // }

        return $this->render('admin/index.html.twig');
    }
}
