<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPassType;
use App\Form\EditInfosType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * @Route("/profile", name="profile_")
 * @IsGranted("ROLE_USER")
 */
class ProfileController extends AbstractController
{

    protected $em;
    protected $encoder;
    protected $tokenStorage;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, TokenStorageInterface $tokenStorage) {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
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

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user, SessionInterface $sessionInterface)
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

            $this->tokenStorage->setToken(null);
            $sessionInterface->invalidate();

            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('success', "Vous avez bien supprimé votre propre compte");
        }

        return $this->redirectToRoute('home');
    }
}
