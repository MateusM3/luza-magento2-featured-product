<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class FeaturedProductData implements ArgumentInterface
{
    public function getName(): string
    {
        return 'Featured Product';
    }

    public function getPrice(): string
    {
        return 'R$ 100,99';
    }

    public function getStock(): int
    {
        return 50;
    }
}