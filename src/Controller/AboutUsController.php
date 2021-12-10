<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AboutUs;
use App\Services\CartService;
use Symfony\Component\Security\Core\Security;

class AboutUsController extends AbstractController
{
    protected $em;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/about/us", name="about_us")
     */
    public function index(EntityManagerInterface $entityManager,Security $security): Response
    {
        $about_us = $entityManager->getRepository(AboutUs::class)->findAll();

        $title = $about_us[0]->getTitle();

        $cart = new CartService($entityManager,$security);

        return $this->render('about_us/index.html.twig', [
            'url'           => 'about_us',
            'title'         => $title,
            'about_us'      => $about_us,
            'cart_counter'  => $cart->getCartCounter()
        ]);
    }

    /**
     * @Route("/about_add", name="about_add")
     */
    public function add(): Response{
        $about_us = $this->em->getRepository(AboutUs::class)->findLimitedResults(1);
        
        return $this->render('admin/about/add.html.twig', [
            'about_us' => $about_us[0],
        ]);
    }

    /**
     * @Route("/about/edit/{id}", name="about_edit")
     */
    public function edit($id): Response{
        $about_us = $this->em->getRepository(AboutUs::class)->findBy(['id'=>$id]);
        
        return $this->render('admin/about/edit.html.twig', [
            'about_us' => $about_us[0],
        ]);
    }

    /**
     * @Route("/about/delete/{id}", name="about_delete")
     */
    public function delete($id): Response
    {
        $about = $this->em->getRepository(AboutUs::class)->findBy(['id'=>$id]);
        $this->em->remove($about[0]);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste se obrisali željeni red!');

        return $this->redirectToRoute("admin_about_us");
    }
}
