<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\ViewModel;

use Luza\FeaturedProduct\Api\FeaturedProductResolverInterface;
use Luza\FeaturedProduct\Api\FeaturedProductStockInterface;
use Luza\FeaturedProduct\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder as ImageUrlBuilder;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class FeaturedProductData implements ArgumentInterface
{
    private ?ProductInterface $product = null;

    private bool $resolved = false;

    public function __construct(
        private readonly FeaturedProductResolverInterface $resolver,
        private readonly FeaturedProductStockInterface $stock,
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly ImageUrlBuilder $imageUrlBuilder,
        private readonly Config $config,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    public function getProduct(): ?ProductInterface
    {
        if (!$this->resolved) {
            $this->product = $this->resolver->resolve();
            $this->resolved = true;
        }

        return $this->product;
    }

    public function hasProduct(): bool
    {
        return $this->getProduct() !== null;
    }

    public function getProductId(): ?int
    {
        $product = $this->getProduct();

        return $product ? (int) $product->getId() : null;
    }

    public function getName(): string
    {
        $product = $this->getProduct();

        return $product ? (string) $product->getName() : '';
    }

    public function getImageUrl(): string
    {
        $product = $this->getProduct();

        if (!$product instanceof Product) {
            return '';
        }

        return $this->imageUrlBuilder->getUrl(
            (string) $product->getData('image'),
            'product_base_image'
        );
    }

    public function getProductUrl(): string
    {
        $product = $this->getProduct();

        return $product instanceof Product ? $product->getProductUrl() : '';
    }

    public function getPrice(): string
    {
        $product = $this->getProduct();

        if (!$product instanceof SaleableInterface) {
            return '';
        }

        $amount = $product->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();

        return $this->priceCurrency->format((float) $amount, false);
    }

    public function getStock(): float
    {
        $product = $this->getProduct();

        if (empty($product) || !$product instanceof Product) {
            return 0.0;
        }

        return $this->stock->getSalableQty((string) $product->getSku());
    }

    /**
     * Config consumed by the Knockout stock component (merged into jsLayout by the Block).
     *
     * @return array{qty: float, updateUrl: string, interval: int, enabled: bool}
     */
    public function getStockComponentConfig(): array
    {
        return [
            'qty' => $this->getStock(),
            'updateUrl' => $this->urlBuilder->getUrl('featuredproduct/stock'),
            'interval' => $this->config->getStockUpdateInterval() * 1000,
            'enabled' => $this->config->isRealtimeStockEnabled(),
        ];
    }
}
