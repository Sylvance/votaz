<?php
namespace App\Service;

use App\Entity\Invoice;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
class InvoiceMailer
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }
    public function sendReceipt(Invoice $invoice): void
    {
        $email = (new TemplatedEmail())
            ->to($invoice->getCustomerEmail())
            ->subject('Your receipt from Acme')
            ->htmlTemplate('email/receipt.html.twig')
            ->context(['invoice' => $invoice]);
        $this->mailer->send($email);
    }
}
