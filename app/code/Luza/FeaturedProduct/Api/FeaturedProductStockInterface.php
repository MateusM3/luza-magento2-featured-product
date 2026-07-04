<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Api;

/**
 * Provides the salable quantity of a product for the current sales channel (website stock).
 *
 * Wraps Magento MSI so consumers (ViewModel, future AJAX endpoint) don't repeat
 * the stock-resolution boilerplate. Fails soft: returns 0.0 when the product is
 * not salable/assigned or MSI raises a domain exception.
 */
interface FeaturedProductStockInterface
{
    /**
     * @param string $sku
     * @return float Salable quantity, or 0.0 when unavailable.
     */
    public function getSalableQty(string $sku): float;
}
