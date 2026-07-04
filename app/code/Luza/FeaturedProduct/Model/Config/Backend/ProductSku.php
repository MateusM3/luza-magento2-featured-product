<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model\Config\Backend;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Validates that the configured Product SKU matches an existing product before saving the config.
 */
class ProductSku extends Value
{
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly ProductRepositoryInterface $productRepository,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $sku = trim((string) $this->getValue());

        if (empty($sku)) {
            throw new LocalizedException(
                __('The SKU field is empty.')
            );
        }

        try {
            $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(
                __('No product found with SKU "%1". Please enter a valid SKU.', $sku)
            );
        }

        return parent::beforeSave();
    }
}
