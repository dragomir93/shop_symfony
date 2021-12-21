<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Entity\Cart;
use App\Entity\Country;
use App\Entity\Orders;
use App\Entity\OrdersProducts;
use App\Entity\Payment;
use App\Entity\User;
use App\Services\CartService;
use App\Services\PDFService;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Constraint\Count;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class OrderCheckoutController extends AbstractController
{
    protected $em;

    protected $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->em = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/checkout/", name="order_checkout")
     */
    public function index(): Response
    {
        $user = new UserService();

        $cart_counter = new CartService($this->em,$this->security);
        
        $cart = $this->em->getRepository(Cart::class)->findBy(['user'=> $user->getUser($this->security)]);

        $countries = $this->em->getRepository(Country::class)->findAll();

        $payments = $this->em->getRepository(Payment::class)->findAll();
        
        foreach ($cart as $value) {
            $cart_update = $this->em->find(Cart::class,$value->getId());
            $cart_update->setIsApproved(true);
            $this->em->persist($cart_update);
            $this->em->flush();
        }

        return $this->render('order_checkout/index.html.twig', [
            'url'             => 'order_checkout',
            'cart_counter'    => $cart_counter->getCartCounter(),
            'cart'            => $cart,
            'countries'       => $countries,
            'payments'        => $payments,
        ]);
    }

    /**
     * @Route("/order/handle", name="order_handle")
     */
    public function handle(Request $request)
    {

        $user = new UserService();
        $orders = new Orders();

        $payment_id = $request->request->get('payment');
        $payment =$this->em->find(Payment::class,$payment_id);
        $orders->setPayment($payment);

        $total = $request->request->get('total');
        $orders->setTotal($total);

        $date = new \DateTime("now");

        $orders->setUpdatedAt($date);
        $orders->setCreatedAt($date);

        $city = $request->request->get('city');
        $orders->setCity($city);

        $postal_code = $request->request->get('postal_code');
        $orders->setPostalCode($postal_code);

        $number_of_flat = $request->request->get('number_of_flat');
        $orders->setNumberOfFlat($number_of_flat);

        $country_id = $request->request->get('country');
        $country = $this->em->find(Country::class,$country_id);
        $orders->setCountry($country);

        $users = $this->em->find(User::class,$user->getUser($this->security));
        $orders->setUser($users);

        $this->em->persist($orders);
        $this->em->flush();

        $cart = $this->em->getRepository(Cart::class)->findBy(['user'=> $user->getUser($this->security),'is_approved'=>1]);

        foreach ($cart as $value) {
            $order_products = new OrdersProducts();
            
            $article = $this->em->find(Articles::class,$value->getArticle()->getId());
            $order_products->setArticles($article);

            $order_products->setOrders($orders);

            $order_products->setQuantity($value->getQuantity());

            $order_products->setSize($value->getSize());

            $this->em->persist($order_products);
            $this->em->flush();

            $cart = new CartService($this->em,$this->security);
            $cart->clearCart();
        }

        $this->addFlash('sucess_order', 'Upešno ste se kreirali porudžbinu!');
            
        return $this->redirectToRoute("order_preview");
    }

    /**
     * @Route("/order_preview", name="order_preview")
     */
    public function preview(Request $request,MailerInterface $mailer): Response
    {
        $cart_counter = new CartService($this->em,$this->security);

        
        $orders = $this->em->getRepository(Orders::class)->getOrders($this->getUser($this->security));

        date_default_timezone_set("Europe/Belgrade");

        $email = (new TemplatedEmail())
        ->from(new Address('cokomoko.sb@gmail.com','Coko Moko'))
        ->to(new Address($orders[0]->getUser()->getEmail(),$orders[0]->getUser()->getName()))
        ->bcc('cokomoko.sb@gmail.com')
        ->replyTo('cokomoko.sb@gmail.com')
        ->subject('COKO MOKO- Porudžbina broj:'.$orders[0]->getId())
        ->htmlTemplate('email/index.html.twig')
        ->context([
            'orders' => $orders,
        ]);

        $mailer->send($email);

        return $this->render('order_checkout/order_preview.html.twig', [
         'url'             => 'order_preview',
         'cart_counter'    => $cart_counter->getCartCounter(),
         'orders'          => $orders,
        ]);
    
    }

     /**
     * @Route("/order_pdf", name="order_pdf")
     */
    public function orderPDF()
    {
        $pdf = new PDFService();

        $orders = $this->em->getRepository(Orders::class)->getOrders($this->getUser($this->security));

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('pdf/index.html.twig', [
            'orders'          => $orders,
        ]);

        $pdf->getPDF($html);
        
    }

    /**
     * @Route("/admin/orders/edit/{id}", name="admin_orders_edit")
     */
    public function edit($id): Response
    {
        $orders = $this->em->getRepository(Orders::class)->findBy(['id'=>$id]);

        $users = $this->em->getRepository(User::class)->findAll();
        $payments = $this->em->getRepository(Payment::class)->findAll();
        $countries = $this->em->getRepository(Country::class)->findAll();

        return $this->render('admin/orders/edit.html.twig', [
            'orders'   => $orders[0],
            'users'    => $users,
            'payments' => $payments,
            'countries'=> $countries,
        ]);
    }

     /**
     * @Route("/admin/orders/update/{id}", name="admin_orders_update")
     */
    public function update(Request $request): Response
    {
        $orders = $this->em->getRepository(Orders::class)->findBy(['id'=>$request->query->get('id')]);
        $orders = $orders[0];
       
        $total = $request->request->get('total');
        $orders->setTotal($total);

        $payment_id = $request->request->get('payment');
        $payment = $this->em->find(Payment::class,$payment_id);
        $orders->setPayment($payment);

        $city = $request->request->get('city');
        $orders->setCity($city);

        $postal_code = $request->request->get('postal_code');
        $orders->setPostalCode($postal_code);

        $country_id = $request->request->get('country');
        $country = $this->em->find(Country::class,$country_id);
        $orders->setCountry($country);

        $user_id = $request->request->get('user');
        $user = $this->em->find(User::class,$user_id);
        $orders->setUser($user);

        $date = new \DateTime("now");
        $orders->setUpdatedAt($date);

        $orders->setCreatedAt($date);

        $this->em->persist($orders);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste izmenili porudžbinu!');

        return $this->redirectToRoute("admin_show_orders");
    }

    /**
     * @Route("/admin/orders/detail/edit/{id}", name="admin_orders_detail_edit")
     */
    public function editOrdersDetail($id): Response
    {
        $order_products = $this->em->getRepository(OrdersProducts::class)->findBy(['id'=>$id]);
        $articles = $this->em->getRepository(Articles::class)->findAll();
        $sizes = ['velika','extra_velika','mala','srednja'];

        return $this->render('admin/orders/edit_order_products.html.twig', [
            'orders'   => $order_products[0],
            'articles' => $articles,
            'sizes'    => $sizes,
        ]);
    }

         /**
     * @Route("/admin/orders/detail/update/{id}", name="admin_orders_detail_update")
     */
    public function updateOrdersDetail(Request $request): Response
    {
        $orders_products = $this->em->getRepository(OrdersProducts::class)->findBy(['id'=>$request->query->get('id')]);
        $orders_products = $orders_products[0];
       
        $quantity = $request->request->get('quantity');
        $orders_products->setQuantity($quantity);

        $article_id = $request->request->get('article');
        $article = $this->em->find(Articles::class,$article_id);
        $orders_products->setArticles($article);

        $size = $request->request->get('size');
        $orders_products->setSize($size);

        $this->em->persist($orders_products);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste izmenili detalje porudžbine!');

        return $this->redirectToRoute("admin_orders");
    }

     /**
     * @Route("/admin/orders/delete/{id}", name="admin_orders_delete")
     */
    public function delete($id): Response
    {
        $orders = $this->em->getRepository(Orders::class)->findBy(['id'=>$id]);
        $order_products = $this->em->getRepository(OrdersProducts::class)->findBy(['orders'=>$id]);

        $this->em->remove($orders);
        $this->em->remove($order_products[0]);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste se obrisali željeni red!');

        return $this->redirectToRoute("admin_show_orders");
    }


    /**
     * @Route("/admin/orders/detail/delete/{id}", name="admin_orders_detail_delete")
    */
    public function deleteOrdersDetail($id): Response
    {
        $order_products = $this->em->getRepository(OrdersProducts::class)->findBy(['id'=>$id]);
        $this->em->remove($order_products[0]);
        $this->em->flush();

        $this->addFlash('sucess_admin', 'Upešno ste se obrisali željeni red!');

        return $this->redirectToRoute("admin_show_orders");
    }
}
