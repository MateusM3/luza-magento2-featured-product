<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Api;

use Magento\Catalog\Api\Data\ProductInterface;


interface FeaturedProductResolverInterface
{
    /**
     * Resolve the currently configured featured product.
     * @return ProductInterface|null Null when disabled, not configured, or the product no longer exists.
     */
    public function resolve(?int $storeId = null): ?ProductInterface;
}
