<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Application\Service\OrderProductService;
use App\Domain\Entity\Order;
use App\Domain\Repository\ProductRepositoryInterface;
use App\Domain\Service\EmailServiceInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class SymfonyEmailService implements EmailServiceInterface
{
    public function __construct(
        private readonly MailerInterface            $mailer,
        private readonly string                     $senderEmail,
        private readonly string                     $senderName,
        private readonly OrderProductService $orderProductService,
    ) {}

    private function createBaseEmail(Order $order, string $subject, string $template): TemplatedEmail
    {
        $productsMap = $this->orderProductService->getProductsForOrder($order);

        return new TemplatedEmail()
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($order->getClientEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'order'    => $order,
                'products' => $productsMap,
            ]);
    }

    /** @throws TransportExceptionInterface */
    public function sendOrderCreated(Order $order): void
    {
        $email = $this->createBaseEmail($order, 'La teva comanda ha estat creada', 'emails/order_created.html.twig');
        $this->mailer->send($email);
    }

    /** @throws TransportExceptionInterface */
    public function sendOrderDelivering(Order $order): void
    {
        $email = $this->createBaseEmail($order, 'La teva comanda ha estat enviada', 'emails/order_delivering.html.twig');
        $this->mailer->send($email);
    }

    /** @throws TransportExceptionInterface */
    public function sendOrderDelivered(Order $order): void
    {
        $email = $this->createBaseEmail($order, 'La teva comanda ha estat lliurada!', 'emails/order_delivered.html.twig');
        $this->mailer->send($email);
    }

    /** @throws TransportExceptionInterface */
    public function sendOrderPaid(Order $order): void
    {
        $email = $this->createBaseEmail($order, 'Pagament confirmat - La teva comanda està en procés', 'emails/order_paid.html.twig');
        $this->mailer->send($email);
    }

    /** @throws TransportExceptionInterface */
    public function sendOrderCancelled(Order $order): void
    {
        $email = $this->createBaseEmail($order, 'Comanda cancel·lada i reemborsament processat', 'emails/order_cancelled.html.twig');
        $this->mailer->send($email);
    }
}
