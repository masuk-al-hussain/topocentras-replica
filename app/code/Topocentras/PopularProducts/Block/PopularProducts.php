<?php
namespace Topocentras\PopularProducts\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PopularProducts extends Template
{
    const XML_PATH_ENABLED = 'topocentras_popular_products/general/enabled';
    const XML_PATH_PRODUCT_SKUS = 'topocentras_popular_products/general/product_skus';
    const XML_PATH_SLIDER_TITLE = 'topocentras_popular_products/general/slider_title';

    protected $productCollectionFactory;
    protected $imageHelper;
    protected $priceHelper;
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        PriceHelper $priceHelper,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSliderTitle()
    {
        $title = $this->scopeConfig->getValue(
            self::XML_PATH_SLIDER_TITLE,
            ScopeInterface::SCOPE_STORE
        );
        return $title ?: __('Populiaru dabar');
    }

    public function getPopularProducts()
    {
        // Get SKU list from system configuration
        $skuString = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_SKUS,
            ScopeInterface::SCOPE_STORE
        );
        
        // If no SKUs configured, return empty collection
        if (empty($skuString)) {
            return $this->productCollectionFactory->create()->addFieldToFilter('sku', '');
        }
        
        // Convert comma-separated string to array
        $skuList = array_map('trim', explode(',', $skuString));
        $skuList = array_filter($skuList); // Remove empty values
        
        if (empty($skuList)) {
            return $this->productCollectionFactory->create()->addFieldToFilter('sku', '');
        }
        
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                   ->addAttributeToFilter('status', 1)
                   ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
                   ->addAttributeToFilter('sku', ['in' => $skuList]);
        
        // Maintain the order of SKUs as provided
        $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.sku, "' . implode('","', $skuList) . '")'));
        
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
