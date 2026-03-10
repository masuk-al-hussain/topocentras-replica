<?php
namespace Topocentras\PopularProducts\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class PopularProducts extends Template
{
    protected $productCollectionFactory;
    protected $imageHelper;
    protected $priceHelper;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    public function getPopularProducts()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                   ->addAttributeToFilter('status', 1)
                   ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
                   ->setPageSize(25)
                   ->setOrder('created_at', 'DESC');
        
        return $collection;
    }

    public function getProductImageUrl($product)
    {
        return $this->imageHelper->init($product, 'product_page_image_small')->getUrl();
    }

    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
