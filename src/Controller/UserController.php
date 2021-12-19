<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

     /**
     * @Route("/admin/users/add", name="admin_users_add")
     */
    public function add(): Response
    {
        return $this->render('admin/users/create.html.twig');
    }

    /**
     * @Route("/admin/users/store", name="admin_users_store")
     */
    public function store(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $name = $request->request->get('name');
        $user->setName($name);

        $email = $request->request->get('email');
        $user->setEmail($email);

        $adress = $request->request->get('adress');
        $user->setAdress($adress);

        $phone = $request->request->get('phone');
        $user->setPhone($phone);

        $password = $request->request->get('password');
        $hashedPassword = $passwordHasher->hashPassword($user,$password);
        $user->setPassword($hashedPassword);

        $user->setActive(1);

        $isAdmin = $request->request->get('is_admin');
        $user->setIsAdmin($isAdmin);

        if($isAdmin){
            $user->setRoles(['ROLE_ADMIN']);
        }

        $date = new \DateTime("now");
        $user->setUpdatedAt($date);

        $user->setCreatedAt($date);

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste dodali korisnika!');

        return $this->redirectToRoute("admin_show_users");
    }

     /**
     * @Route("/admin/users/edit/{id}", name="admin_users_edit")
     */
    public function edit($id): Response
    {
        $user = $this->em->getRepository(User::class)->findBy(['id'=>$id]);
        
        return $this->render('admin/users/edit.html.twig', [
            'user' => $user[0],
        ]);
    }

    /**
     * @Route("/admin/users/update/{id}", name="admin_users_update")
     */
    public function update(Request $request): Response
    {
        $user = $this->em->getRepository(User::class)->findBy(['id'=>$request->query->get('id')]);
        $user = $user[0];

        $name = $request->request->get('name');
        $user->setName($name);

        $email = $request->request->get('email');
        $user->setEmail($email);

        $adress = $request->request->get('adress');
        $user->setAdress($adress);

        $phone = $request->request->get('phone');
        $user->setPhone($phone);

        $user->setActive(1);

        $isAdmin = $request->request->get('is_admin');
        $user->setIsAdmin($isAdmin);

        if($isAdmin){
            $user->setRoles(['ROLE_ADMIN']);
        }

        $date = new \DateTime("now");
        $user->setUpdatedAt($date);

        $user->setCreatedAt($date);

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste izmenili korisnika!');

        return $this->redirectToRoute("admin_show_users");
    }

    /**
     * @Route("/admin/users/delete/{id}", name="admin_users_delete")
     */
    public function delete($id): Response
    {
        $users = $this->em->getRepository(User::class)->findBy(['id'=>$id]);
        $this->em->remove($users[0]);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste se obrisali željeni red!');

        return $this->redirectToRoute("admin_show_users");
    }

    /**
     * @Route("/admin/users/{id}/change_password", name="admin_users_change_password")
     */
    public function changePassword($id): Response
    {
        $users = $this->em->getRepository(User::class)->findBy(['id'=>$id]);
        
        return $this->render('admin/users/change_password.html.twig', [
            'users' => $users[0],
        ]);
    }

    /**
     * @Route("/admin/users/{id}/password_handler", name="admin_users_password_handler")
     */
    public function passwordHandler($id,Request $request,UserPasswordHasherInterface $passwordHasher): Response
    {
        $users = $this->em->getRepository(User::class)->findBy(['id'=>$id]);
        $users = $users[0];

        $password = $request->request->get('new_password');
        
        $hashedPassword = $passwordHasher->hashPassword($users,$password);
        $users->setPassword($hashedPassword);
        
        $this->em->persist($users);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste promenili password!');
        
        return $this->redirectToRoute("admin_show_users");

    }


}
