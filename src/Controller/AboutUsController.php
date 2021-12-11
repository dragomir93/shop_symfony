<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AboutUs;
use App\Services\CartService;
use Symfony\Component\HttpFoundation\Request;
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
        if($about_us == null) {
        $this->addFlash('error', "Ne možete videti stranicu O nama!Greška!");
        return $this->redirectToRoute("home");
        }
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
     * @Route("/admin/about/add", name="admin_about_add")
     */
    public function add(): Response
    {
        $about_us = $this->em->getRepository(AboutUs::class)->findAll();

        if (count($about_us) == 1) {
            $this->addFlash('error_admin', "Ne možete dodati više od jedne stranice za stranu O nama!");
            return $this->redirectToRoute("admin_about_us");
        }
    
        return $this->render('admin/about/create.html.twig');
    }

    /**
     * @Route("/admin/about/store", name="admin_about_store")
     */
    public function store(Request $request): Response
    {
        $about = new AboutUs(); 

        $title = $request->request->get('title');
        $about->setTitle($title);

        $content = $request->request->get('content');
        $about->setContent($content);

        $our_story = $request->request->get('our_story');
        $about->setOurStory($our_story);

        $about_us_text = $request->request->get('about_us_text');
        $about->setAboutUsText($about_us_text);

        $date = new \DateTime("now");
        $about->setUpdatedAt($date);
        $about->setCreatedAt($date);

        $this->em->persist($about);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste izmenili stranicu o nama!');
            
        return $this->redirectToRoute("admin_about_us");
    }

    /**
     * @Route("/admin/about/edit/{id}", name="admin_about_edit")
     */
    public function edit($id): Response
    {
        $about_us = $this->em->getRepository(AboutUs::class)->findBy(['id'=>$id]);
        
        return $this->render('admin/about/edit.html.twig', [
            'about_us' => $about_us[0],
        ]);
    }

    /**
     * @Route("/admin/about/update/{id}", name="admin_about_update")
     */
    public function update(Request $request): Response
    {
        $about = $this->em->find(AboutUs::class,$request->query->get('id'));

        $title = $request->request->get('title');
        $about->setTitle($title);

        $content = $request->request->get('content');
        $about->setContent($content);

        $our_story = $request->request->get('our_story');
        $about->setOurStory($our_story);

        $about_us_text = $request->request->get('about_us_text');
        $about->setAboutUsText($about_us_text);

        $date = new \DateTime("now");
        $about->setUpdatedAt($date);
        $about->setCreatedAt($date);

        $this->em->persist($about);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste izmenili stranicu o nama!');
            
        return $this->redirectToRoute("admin_about_us");
    }

    /**
     * @Route("/admin/about/delete/{id}", name="admin_about_delete")
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
