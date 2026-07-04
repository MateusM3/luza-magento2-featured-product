<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model;

use Luza\FeaturedProduct\Api\FeaturedProductResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class FeaturedProductResolver implements FeaturedProductResolverInterface
{
    public const TYPE_SKU = 'sku';

    public const TYPE_PRODUCT = 'product';

    public function __construct(
        private readonly Config $config,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function resolve(?int $storeId = null): ?ProductInterface
    {
        if (!$this->config->isEnabled($storeId)) {
            return null;
        }

        try {
            return match ($this->config->getSelectionType($storeId)) {
                self::TYPE_SKU => $this->resolveBySku($storeId),
                self::TYPE_PRODUCT => $this->resolveById($storeId),
                default => null,
            };
        } catch (NoSuchEntityException $e) {
            $this->logger->warning(
                'Luza_FeaturedProduct: configured product could not be resolved. ' . $e->getMessage()
            );

            return null;
        }
    }

    private function resolveBySku(?int $storeId): ?ProductInterface
    {
        $sku = $this->config->getProductSku($storeId);

        if(empty($sku)) {
            return null;
        }

        return $this->productRepository->get($sku, false, $storeId);
    }

    private function resolveById(?int $storeId): ?ProductInterface
    {
        $productId = $this->config->getProductId($storeId);

        if(empty($productId)) {
            return null;
        }

        return $this->productRepository->getById($productId, false, $storeId);
    }
}
