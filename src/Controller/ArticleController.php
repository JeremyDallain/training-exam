<?php

namespace App\Controller;

use DateTime;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{

    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(PaginatorInterface $paginator, Request $request, ArticleRepository $articleRepository)
    {
        // du plus au moins récent
        $datas = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        $articles = $paginator->paginate(
            $datas, // mes datas
            $request->query->getInt('page', 1), // numero de la page en cours
            5
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request)
    {

        $user = $this->getUser();

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($user)
                ->setCreatedAt(new DateTime());

            $this->em->persist($article);
            $this->em->flush();

            $this->addFlash('success', 'Article créé avec succés.');
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     */
    public function show($id, ArticleRepository $articleRepository)
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            $this->addFlash('danger', "L'article demandé n'existe pas.");
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="article_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id, ArticleRepository $articleRepository)
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            $this->addFlash('danger', "L'article demandé n'existe pas.");
            return $this->redirectToRoute('article_index');
        }

        if ($article->getUser() !== $this->getUser() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash('danger', "Vous n'etes pas le proprietaire de cet article, vous ne pouvez pas l'éditer !");
            return $this->redirectToRoute('article_index');
        }


        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Article modifié avec succés.');
            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"POST"})
     */
    public function delete(Request $request, $id, ArticleRepository $articleRepository)
    {

        $article = $articleRepository->find($id);

        if (!$article) {
            $this->addFlash('danger', "L'article demandé n'existe pas.");
            return $this->redirectToRoute('article_index');
        }

        if ($article->getUser() !== $this->getUser() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash('danger', "Vous n'êtes pas le proprietaire de cet article, vous ne pouvez pas le supprimer !");
            return $this->redirectToRoute('article_index');
        }

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $this->em->remove($article);
            $this->em->flush();
            $this->addFlash('success', 'Article supprimé avec succés.');
        } else {
            $this->addFlash('danger', "Suppression de l'article impossible.");
        }

        return $this->redirectToRoute('article_index');
    }
}
