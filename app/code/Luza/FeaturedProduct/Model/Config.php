<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_ENABLED = 'luza_featured_product/general/enabled';

    private const XML_PATH_SELECTION_TYPE = 'luza_featured_product/general/selection_type';

    private const XML_PATH_PRODUCT_SKU = 'luza_featured_product/general/product_sku';

    private const XML_PATH_PRODUCT_ID ='luza_featured_product/general/product_id';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED
        );
    }

    public function getSelectionType(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SELECTION_TYPE
        );
    }

    public function getProductSku(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_SKU
        );
    }

    public function getProductId(): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_ID
        );

        return $value ? (int)$value : null;
    }
}