<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class ProductList implements OptionSourceInterface
{
    public function __construct(
        private readonly CollectionFactory $productCollectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        $options = [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ]
        ];

        $collection = $this->productCollectionFactory->create();

        $collection->addAttributeToSelect([
            'name',
            'sku'
        ]);

        $collection->setPageSize(200);

        foreach ($collection as $product) {
            $options[] = [
                'value' => $product->getId(),
                'label' => sprintf(
                    '%s (%s)',
                    $product->getName(),
                    $product->getSku()
                )
            ];
        }

        return $options;
    }
}