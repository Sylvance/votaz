<?php
namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ProductSearch
{
    use DefaultActionTrait;
    #[LiveProp(writable: true)]
    public string $query = '';
    public function __construct(
        private ProductRepository $products,
    ) {
    }
    public function getProducts(): array
    {
        return $this->products->search($this->query);
    }
}
