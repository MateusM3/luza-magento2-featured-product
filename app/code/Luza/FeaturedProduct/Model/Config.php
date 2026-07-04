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

    private const XML_PATH_REALTIME_ENABLED = 'luza_featured_product/realtime_stock/enabled';

    private const XML_PATH_REALTIME_INTERVAL = 'luza_featured_product/realtime_stock/update_interval';

    private const DEFAULT_INTERVAL_SECONDS = 15;

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

    public function isRealtimeStockEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REALTIME_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Stock refresh interval in seconds (falls back to a safe default when unset/invalid).
     */
    public function getStockUpdateInterval(?int $storeId = null): int
    {
        $interval = (int) $this->scopeConfig->getValue(
            self::XML_PATH_REALTIME_INTERVAL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $interval > 0 ? $interval : self::DEFAULT_INTERVAL_SECONDS;
    }
}
