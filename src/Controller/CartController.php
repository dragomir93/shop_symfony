<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Cart;
use App\Entity\User;
use App\Services\CartService;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CartController extends AbstractController
{
    protected $em;

    protected $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }

    /**
    * @Route("/cart", name="cart")
    */
    public function index(): Response
    { 
        $user = new UserService();

        $cart_counter = new CartService($this->em,$this->security);

        $cart = $this->em->getRepository(Cart::class)->findBy(['user'=>$user->getUser($this->security)]);

        return $this->render('cart/index.html.twig', [
            'url'             => 'cart',
            'cart_counter'    => $cart_counter->getCartCounter(),
            'cart'            => $cart,
        ]);
    }


    /**
    * @Route("/cart/add", name="cart_add")
    */
    public function add(Request $request): Response
    { 
        $user = new UserService(); 
        if ($user->getUser($this->security) != null) {

        $cart = new Cart();

        $quantity = $request->request->get('quantity');
        $cart->setQuantity($quantity);

        $size = $request->request->get('size');
        $cart->setSize($size);

        $article_id = $request->request->get('article_id');
        $article_id = (int) $article_id;

        $articles = $this->em->find(Articles::class,$article_id);

        $cart->setArticle($articles);
        
        $cart_find = $this->em->getRepository(Cart::class)->findBy(['user'=>$user->getUser($this->security),'article'=>$article_id]);

        if (count($cart_find) > 0) {

            $cart_find[0]->setQuantity($cart_find[0]->getQuantity() + (int) $request->request->get('quantity'));
            
            $date = new \DateTime("now");
            $cart->setUpdatedAt($date);

            $cart->setIsApproved(1);

            $this->em->persist($cart_find[0]);
            $this->em->flush();

            $this->addFlash('sucess_aded', 'Upešno ste se dodali artikl! Možete nastaviti sa kupovinom.');
            
            return $this->redirectToRoute("cart");
        }

        $users = $this->em->find(User::class,$user->getUser($this->security));
        $cart->setUser($users);

        $date = new \DateTime("now");
        $cart->setUpdatedAt($date);

        $cart->setIsApproved(false);

        $this->em->persist($cart);
        $this->em->flush();

        $this->addFlash('sucess_aded', 'Upešno ste se dodali artikl! Možete nastaviti sa kupovinom.');
        
        return $this->redirectToRoute("cart");
        
        } else {

        $referer = $request->headers->get('referer');

        $this->addFlash('error', 'Morate se ulogovati kako bi nastavili sa kupovinom.');

        return new RedirectResponse($referer);

        }
    }

    /**
    * @Route("/cart/update", name="cart_update")
    */
    public function update(Request $request): Response
    { 
        $cart = $this->em->find(Cart::class,$request->request->get('cart_id'));
        $cart->setQuantity((int) $request->request->get('quantity'));
        $this->em->persist($cart);
        $this->em->flush();
        
        return $this->redirectToRoute("cart");
    }
    
    /**
    * @Route("/cart/delete/{id}/", name="cart_delete")
    */
    public function delete($id): Response
    {
        $user = new UserService(); 

        $cart = $this->em->getRepository(Cart::class)->findBy(['user'=> $user->getUser($this->security),'article'=>$id]);
        $this->em->remove($cart[0]);
        $this->em->flush();

        return $this->redirectToRoute("cart");
    }
}
