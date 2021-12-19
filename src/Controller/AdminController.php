<?php

namespace App\Controller;

use App\Entity\AboutUs;
use App\Entity\Articles;
use App\Entity\Contact;
use App\Entity\Orders;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class AdminController extends AbstractController
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $articles = $this->em->getRepository(Articles::class)->findAll();
        $orders =   $this->em->getRepository(Orders::class)->findAll();
        $users =    $this->em->getRepository(User::class)->findAll();
        $contacts =    $this->em->getRepository(Contact::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'articles' => $articles,
            'orders'   => $orders,
            'users'    => $users,
            'contacts' => $contacts,

        ]);
    }

    /**
     * @Route("/admin_about_us", name="admin_about_us")
     */
    public function about(): Response{
        $about_us = $this->em->getRepository(AboutUs::class)->findLimitedResults(1);
        if($about_us != null){
            $about_us = $about_us[0];
        }
        return $this->render('admin/about/index.html.twig', [
            'about_us' => $about_us,
        ]);
    }

     /**
     * @Route("/admin_contacts", name="admin_contacts")
     */
    public function contacts(): Response{
        $contacts = $this->em->getRepository(Contact::class)->findAll();

        return $this->render('admin/contacts/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

     /**
     * @Route("/showArticles", name="show_articles")
     */
    public function showArticles(): Response{
        $articles = $this->em->getRepository(Articles::class)->findAll();
        return $this->render('admin/products/index.html.twig',[
            'articles'=>$articles,
        ]);
    }

         /**
     * @Route("/admin/showUsers", name="admin_show_users")
     */
    public function showUsers(): Response{
        $users = $this->em->getRepository(User::class)->findAll();
        return $this->render('admin/users/index.html.twig',[
            'users'=>$users,
        ]);
    }
}
