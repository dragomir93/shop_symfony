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

    /**
     * @Route("/admin/contacts/edit/{id}", name="admin_contacts_edit")
     */
    public function edit($id): Response
    {
        $contacts = $this->em->getRepository(Contact::class)->findBy(['id'=>$id]);
        
        return $this->render('admin/contacts/edit.html.twig', [
            'contacts' => $contacts[0],
        ]);
    }

    /**
     * @Route("/admin/contacts/update/{id}", name="admin_contacts_update")
     */
    public function update(Request $request): Response
    {
        $contact = $this->em->find(Contact::class,$request->attributes->get('id'));

        $name = $request->request->get('name');
        $contact->setName($name);

        $email = $request->request->get('email');
        $contact->setEmail($email);

        $phone = $request->request->get('phone');
        $contact->setPhone($phone);

        $subject = $request->request->get('subject');
        $contact->setSubject($subject);

        $message = $request->request->get('message');
        $contact->setMessage($message);

        $date = new \DateTime("now");
        $contact->setUpdatedAt($date);
        $contact->setCreatedAt($date);

        $this->em->persist($contact);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste izmenili stranicu kontakta!');
            
        return $this->redirectToRoute("admin_contacts");
    }

    /**
     * @Route("/admin/contacts/delete/{id}", name="admin_contacts_delete")
     */
    public function delete($id): Response
    {
        $contacts = $this->em->getRepository(Contact::class)->findBy(['id'=>$id]);
        $this->em->remove($contacts[0]);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste se obrisali željeni red!');

        return $this->redirectToRoute("admin_contacts");
    }
}
