<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ArticleController extends AbstractController
{
    protected $em;

    protected $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/articles", name="articles")
     */
    public function index(): Response
    {

        $articles = $this->em->getRepository(Articles::class)->findAll();

        $cart_counter = new CartService($this->em,$this->security);
        
        return $this->render('article/index.html.twig', [
            'url'           => 'index',
            'articles'      => $articles,
            'cart_counter'  => $cart_counter->getCartCounter()
        ]);
    }

     /**
     * @Route("/article/{id}/", name="article_id")
     */
    public function getById($id): Response
    {
        $article = $this->em->getRepository(Articles::class)->findBy(['id'=>$id]);

        $cart_counter = new CartService($this->em,$this->security);

        return $this->render('article/article.html.twig', [
            'url'           => 'article_single',
            'article'       => $article,
            'cart_counter'  => $cart_counter->getCartCounter()
        ]);
    }
}
