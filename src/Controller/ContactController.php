<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ContactController extends AbstractController
{
    protected $em;

    protected $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }
    
    /**
     * @Route("/contact", name="contact")
     */
    public function index(): Response
    {

        $cart_counter = new CartService($this->em,$this->security);

        return $this->render('contact/index.html.twig', [
            'url'           => 'contact',
            'cart_counter'  => $cart_counter->getCartCounter()
        ]);
    }

    /**
     * @Route("/contact/handle", name="contact_handle")
     */
    public function handle(Request $request)
    {
        $contact = new Contact();
        
        $full_name = $request->request->get('full_name');
        $contact->setName($full_name);

        $email = $request->request->get('email');
        $contact->setEmail($email);

        $phone = $request->request->get('phone');
        $contact->setPhone($phone);

        $subject = $request->request->get('subject');
        $contact->setSubject($subject);

        $message = $request->request->get('message');
        $contact->setMessage($message);

        $date = new \DateTime("now");
        $contact->setCreatedAt($date);

        $contact->setUpdatedAt($date);
        
        $this->em->persist($contact);
        $this->em->flush();

        $this->addFlash('success', 'Upešno poslati podaci! Neko će vas kontaktirati.');
        
        return $this->redirectToRoute("contact");

    }
}
