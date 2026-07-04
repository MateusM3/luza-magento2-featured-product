<?php

declare(strict_types=1);

namespace Luza\FeaturedProduct\Block;

use Luza\FeaturedProduct\ViewModel\FeaturedProductData;
use Magento\Framework\View\Element\Template;

class FeaturedProduct extends Template
{
    private const STOCK_COMPONENT = 'featuredProductStock';

    /**
     * Merge the featured product's dynamic data (qty, url, interval) into the
     * Knockout component config declared in the layout's jsLayout argument.
     *
     * @return string
     */
    public function getJsLayout()
    {
        $viewModel = $this->getData('view_model');

        if ($viewModel instanceof FeaturedProductData
            && isset($this->jsLayout['components'][self::STOCK_COMPONENT])
        ) {
            $this->jsLayout['components'][self::STOCK_COMPONENT]['config'] = array_replace(
                $this->jsLayout['components'][self::STOCK_COMPONENT]['config'] ?? [],
                $viewModel->getStockComponentConfig()
            );
        }

        return parent::getJsLayout();
    }
}
