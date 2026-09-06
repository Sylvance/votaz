<?php
namespace App\Scheduler;

use App\Repository\InvoiceRepository;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: '1 day', from: '09:00')]
class SendInvoiceReminders
{
    public function __construct(
        private InvoiceRepository $invoices,
    ) {
    }
    public function __invoke(): void
    {
        foreach ($this->invoices->findOverdue() as $invoice) {
            // ... send the reminder
        }
    }
}
