<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    protected $em;

    protected $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(): Response
    {
        $cart_counter = new CartService($this->em,$this->security);

        return $this->render('user/index.html.twig', [
            'url' => 'login',
            'cart_counter'  => $cart_counter->getCartCounter()
        ]);
        
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function registration(): Response
    {
        return $this->render('user/registration.html.twig', [
            'url' => 'registration',
        ]);
    }

    /**
     * @Route("/registration/handle", name="registration_handle")
     */
    public function handle(Request $request,UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $name = $request->request->get('name');
        $user->setName($name);

        $adress = $request->request->get('adress');
        $user->setAdress($adress);

        $phone = $request->request->get('phone');
        $user->setPhone($phone);

        $email = $request->request->get('email');
        $user->setEmail($email);

        $password = $request->request->get('password');
        $user->setPassword($encoder->encodePassword($user, $password));

        $date = new \DateTime("now");
        $user->setCreatedAt($date);

        $user->setUpdatedAt($date);

        $user->setActive(1);

        $user->setIsAdmin(0);
        
        $this->em->persist($user);
        $this->em->flush();

        
        $this->addFlash('success_login', 'Upešno ste se registrovali! Možete se ulogovati.');
        
        return $this->redirectToRoute("login");
     
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout(): void
    {
       
    }
}
