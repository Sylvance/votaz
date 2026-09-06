<?php
namespace App\Ai;

use App\Repository\OrderRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool('order_status', 'Finds the current status of an order')]
class OrderStatusTool
{
    public function __construct(
        private OrderRepository $orders,
    ) {
    }
    public function __invoke(int $orderNumber): string
    {
        // plain PHP: the model decides when to call this tool and
        // extracts the typed arguments from the conversation
        return $this->orders->find($orderNumber)->getStatus();
    }
}
