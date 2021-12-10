<?php

namespace App\Services;

use App\Entity\Cart;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class CartService extends UserService {

    protected $em;
    protected $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }


    public function getCartCounter(): array 
    {
        $cart_counter = $this->em->getRepository(Cart::class)->countCart($this->getUser($this->security));

        return $cart_counter[0];
    }

    public function clearCart() {
        $cart = $this->em->getRepository(Cart::class)->findBy(['user'=> $this->getUser($this->security)]);
        $this->em->remove($cart[0]);
        $this->em->flush();
    }

}