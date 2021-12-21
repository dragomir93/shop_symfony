<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Images;
use App\Service\FileUploader;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            'cart_counter'  => $cart_counter->getCartCounter(),
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
            'cart_counter'  => $cart_counter->getCartCounter(),
        ]);
    }

    /**
    * @Route("/admin/article/add", name="admin_article_add")
    */
    public function add(): Response
    {
        return $this->render('admin/products/create.html.twig');
    }

    /**
    * @Route("/admin/article/store", name="admin_article_store")
    */
    public function store(Request $request, FileUploader $file_uploader): Response
    {

        $articles = new Articles();

        $title = $request->request->get('title');
        $articles->setTitle($title);

        $description = $request->request->get('description');
        $articles->setDescription($description);

        $extra_description = $request->request->get('extra_description');
        $articles->setExtraDescription($extra_description);

        $characteristics = $request->request->get('characteristics');
        $articles->setCharacteristics($characteristics);

        $price = $request->request->get('price');
        $articles->setPrice($price);

        $promotion = $request->request->get('promotion');
        $articles->setPromotion($promotion);

        $date = new \DateTime("now");
        $articles->setUpdatedAt($date);

        $articles->setCreatedAt($date);

        $this->em->persist($articles);
        $this->em->flush();

        $file = $request->files->get('thumbnail');
        $directoryPath = "images/uploads";

        if ($file) {
          $file_name = $file_uploader->upload($file);
          if (null !== $file_name) {
            $full_path = $directoryPath.'/'.$file_name;

            $image = new Images();

            $image->setPath($full_path);
            $image->setArticle($articles);

            $image->setUpdatedAt($date);
            $image->setCreatedAt($date);

            $this->em->persist($image);
            $this->em->flush();
          }
        }

        $this->addFlash('sucess_admin', 'Upešno ste se dodali artikl!');
        
        return $this->redirectToRoute("show_articles");
    }

    /**
    * @Route("/admin/article/edit/{id}", name="admin_article_edit")
    */
    public function edit($id): Response
    {
        $articles = $this->em->getRepository(Articles::class)->findBy(['id'=>$id]);
        
        return $this->render('admin/products/edit.html.twig', [
            'articles' => $articles[0],
        ]);
    }

    /**
    * @Route("/admin/article/update/{id}", name="admin_article_update")
    */
    public function update(Request $request, FileUploader $file_uploader): Response
    {
        $articles = $this->em->getRepository(Articles::class)->findBy(['id'=>$request->attributes->get('id')]);

        $articles = $articles[0];

        $title = $request->request->get('title');
        $articles->setTitle($title);

        $description = $request->request->get('description');
        $articles->setDescription($description);

        $extra_description = $request->request->get('extra_description');
        $articles->setExtraDescription($extra_description);

        $characteristics = $request->request->get('characteristics');
        $articles->setCharacteristics($characteristics);

        $price = $request->request->get('price');
        $articles->setPrice($price);

        $promotion = $request->request->get('promotion');
        $articles->setPromotion($promotion);

        $date = new \DateTime("now");
        $articles->setUpdatedAt($date);

        $articles->setCreatedAt($date);

        $this->em->persist($articles);
        $this->em->flush();

        $file = $request->files->get('thumbnail');
        $directoryPath = "images/uploads";

        if ($file != null) {
            $file_name = $file_uploader->upload($file);
            $full_path = $directoryPath.'/'.$file_name;

            $image = $this->em->getRepository(Images::class)->findBy(['article'=>$request->attributes->get('id')]);
            $image = $image[0];

            $image->setPath($full_path);
            $image->setArticle($articles);

            $image->setUpdatedAt($date);
            $image->setCreatedAt($date);

            $this->em->persist($image);
            $this->em->flush();
        }

        $this->addFlash('sucess_admin', 'Upešno ste izmenili artikl!');
        
        return $this->redirectToRoute("show_articles");
    }

    /**
    * @Route("/admin/article/delete/{id}", name="admin_article_delete")
    */
    public function delete($id): Response
    {
        $articles = $this->em->getRepository(Articles::class)->findBy(['id'=>$id]);
        $image = $this->em->getRepository(Images::class)->findBy(['article'=>$id]);
        $this->em->remove($image[0]);
        $this->em->remove($articles[0]);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste obrisali artikl!');

        return $this->redirectToRoute("show_articles");
    }
}
