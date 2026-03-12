<?php
namespace Topocentras\RecentlyViewed\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class ProductViewTracker extends Template
{
    protected $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getCurrentProductId()
    {
        $product = $this->registry->registry('current_product');
        return $product ? $product->getId() : null;
    }
}
