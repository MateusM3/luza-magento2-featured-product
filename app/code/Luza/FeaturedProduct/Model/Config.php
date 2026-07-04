<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'luza_featured_product/general/enabled';

    private const XML_PATH_SELECTION_TYPE = 'luza_featured_product/general/selection_type';

    private const XML_PATH_PRODUCT_SKU = 'luza_featured_product/general/product_sku';

    private const XML_PATH_PRODUCT_ID = 'luza_featured_product/general/product_id';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSelectionType(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELECTION_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getProductSku(?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_SKU,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getProductId(?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value ? (int) $value : null;
    }
}
