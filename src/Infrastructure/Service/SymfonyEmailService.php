<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Entity\Order;
use App\Domain\Service\EmailServiceInterface;
use App\Domain\ValueObject\OrderStatus;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class SymfonyEmailService implements EmailServiceInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $senderEmail,
        private readonly string $senderName,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function sendOrderCreated(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($order->getClientEmail())
            ->subject('La teva comanda ha estat creada')
            ->htmlTemplate('emails/order_created.html.twig')
            ->context([
                'order'      => $order,
                'clientName' => $order->getClientName(),
                'orderId'    => $order->getId(),
                'paymentUrl' => $order->getPaymentUrl(),
                'lines'      => $order->getLines(),
                'total'      => $order->getTotalAmount(),
                'status'     => $order->getStatus()->value,
                'createdAt'  => $order->getCreatedAt()->format('d/m/Y H:i'),
            ]);

        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendOrderDelivered(Order $order): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($order->getClientEmail())
            ->subject('La teva comanda ha estat lliurada!')
            ->htmlTemplate('emails/order_delivered.html.twig')
            ->context([
                'clientName' => $order->getClientName(),
                'orderId'    => $order->getId(),
                'lines'      => $order->getLines(),
                'total'      => $order->getTotalAmount(),
                'createdAt'  => $order->getCreatedAt()->format('d/m/Y H:i'),
            ]);

        $this->mailer->send($email);
    }
}
