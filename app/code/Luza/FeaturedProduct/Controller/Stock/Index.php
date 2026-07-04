<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Controller\Stock;

use Luza\FeaturedProduct\Api\FeaturedProductResolverInterface;
use Luza\FeaturedProduct\Api\FeaturedProductStockInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Returns the current salable quantity of the featured product as JSON.
 *
 * Kept intentionally thin: all business logic lives in the service layer.
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly FeaturedProductResolverInterface $resolver,
        private readonly FeaturedProductStockInterface $stock,
        private readonly JsonFactory $resultJsonFactory
    ) {
    }

    public function execute(): ResultInterface
    {
        $product = $this->resolver->resolve();
        $qty = $product ? $this->stock->getSalableQty((string) $product->getSku()) : 0.0;

        return $this->resultJsonFactory->create()->setData(['qty' => $qty]);
    }
}
