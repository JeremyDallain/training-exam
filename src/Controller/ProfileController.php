<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPassType;
use App\Form\EditInfosType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/profile", name="profile_")
 */
class ProfileController extends AbstractController
{

    protected $em;
    protected $encoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder) {
        $this->em = $em;
        $this->encoder = $encoder;
    }
    
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('profile/index.html.twig');
    }

    /**
     * @Route("/edit/infos", name="edit_infos")
     */
    public function editInfos(Request $request)
    {  
        /**
         * @var User
         */
        $user = $this->getUser();

        $form = $this->createForm(EditInfosType::class, $user);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Profil mis à jour');
            return $this->redirectToRoute('profile_home');
        }

        return $this->render('profile/edit_infos.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/pass", name="edit_pass")
     */
    public function editPass(Request $request)
    {  
        /**
         * @var User
         */
        $user = $this->getUser();
        $form = $this->createForm(EditPassType::class);        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            if ($data['pass1'] !== $data['pass2']) {
                $this->addFlash('danger', "Les mots de passe doivent correspondre.");
                return $this->redirectToRoute('profile_edit_pass');
            }

            $user->setPassword($this->encoder->encodePassword($user, $data['pass1']));
            $this->em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour');
            return $this->redirectToRoute('profile_home');
        }

        return $this->render('profile/edit_pass.html.twig', [
            'form' => $form->createView()
        ]);
    }


}
