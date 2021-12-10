<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomeController extends AbstractController
{
    protected $em;

    protected $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }

    
    /**
     * @Route("/home", name="home")
     */
    public function index(): Response
    {
        $articles = $this->em->getRepository(Articles::class)->findLimitedResults(5);

        $cart_counter = new CartService($this->em,$this->security);

        return $this->render('home/index.html.twig', [
            'url'           => 'home',
            'articles'      => $articles,
            'cart_counter'  => $cart_counter->getCartCounter()
        ]);
    }
}
