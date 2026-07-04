<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Observer;

use Luza\FeaturedProduct\Model\Config;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class SyncFeaturedSku implements ObserverInterface
{
    private const CONFIG_PATH_PRODUCT_SKU = 'luza_featured_product/general/product_sku';

    public function __construct(
        private readonly Config $config,
        private readonly WriterInterface $configWriter,
        private readonly TypeListInterface $cacheTypeList
    ) {
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getData('product');

        $oldSku = (string) $product->getOrigData('sku');
        $newSku = (string) $product->getSku();

        if (empty($oldSku) || $oldSku === $newSku) {
            return;
        }

        if ($this->config->getProductSku() !== $oldSku) {
            return;
        }

        $this->configWriter->save(self::CONFIG_PATH_PRODUCT_SKU, $newSku);
        $this->cacheTypeList->cleanType(ConfigCache::TYPE_IDENTIFIER);
    }
}
