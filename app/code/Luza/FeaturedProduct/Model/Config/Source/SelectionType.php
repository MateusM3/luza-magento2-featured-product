<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SelectionType implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'sku',
                'label' => __('SKU')
            ],
            [
                'value' => 'product',
                'label' => __('Product')
            ]
        ];
    }
}