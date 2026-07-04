<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model;

use Luza\FeaturedProduct\Api\FeaturedProductStockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class FeaturedProductStock implements FeaturedProductStockInterface
{
    public function __construct(
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly StockResolverInterface $stockResolver,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getSalableQty(string $sku): float
    {
        if ($sku === '') {
            return 0.0;
        }

        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stockId = (int) $this->stockResolver
                ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)
                ->getStockId();

            return $this->getProductSalableQty->execute($sku, $stockId);
        } catch (\Throwable $e) {
            // Fail soft: SKU not assigned/salable, no stock for the channel, or a core MSI
            // TypeError for an unknown SKU. Stock display must never break the storefront.
            $this->logger->warning(
                'Luza_FeaturedProduct: could not resolve salable qty for SKU "' . $sku . '". ' . $e->getMessage()
            );

            return 0.0;
        }
    }
}
