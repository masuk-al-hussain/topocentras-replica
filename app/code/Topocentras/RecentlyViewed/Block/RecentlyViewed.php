<?php
namespace Topocentras\RecentlyViewed\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

class RecentlyViewed extends Template
{
    const COOKIE_NAME = 'recently_viewed_products';
    const MAX_PRODUCTS = 5;

    protected $productCollectionFactory;
    protected $imageHelper;
    protected $priceHelper;
    protected $productVisibility;
    protected $registry;
    protected $cookieManager;
    protected $jsonSerializer;

    /**
     * Cache lifetime in seconds (null = no cache)
     */
    protected $_cacheLifetime = null;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        PriceHelper $priceHelper,
        Visibility $productVisibility,
        Registry $registry,
        CookieManagerInterface $cookieManager,
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->productVisibility = $productVisibility;
        $this->registry = $registry;
        $this->cookieManager = $cookieManager;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }

    /**
     * Get cache key informative items
     * Provide string array key to share specific info item with FPC
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        // Make cache unique per visitor by including cookie value
        $cookieValue = $this->cookieManager->getCookie(self::COOKIE_NAME);
        return [
            'RECENTLY_VIEWED_PRODUCTS',
            $this->_storeManager->getStore()->getId(),
            $cookieValue ?: 'empty'
        ];
    }

    /**
     * Get recently viewed product IDs from cookie
     */
    protected function getRecentlyViewedIds()
    {
        $cookie = $this->cookieManager->getCookie(self::COOKIE_NAME);
        if (!$cookie) {
            return [];
        }
        
        try {
            // Decode the URL-encoded cookie value
            $decodedCookie = urldecode($cookie);
            $ids = $this->jsonSerializer->unserialize($decodedCookie);
            return is_array($ids) ? $ids : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getRecentlyViewedProducts()
    {
        $productIds = $this->getRecentlyViewedIds();
        
        if (empty($productIds)) {
            return $this->productCollectionFactory->create()->addFieldToFilter('entity_id', 0);
        }
        
        $currentProduct = $this->registry->registry('current_product');
        
        // Remove current product from the list
        if ($currentProduct) {
            $productIds = array_filter($productIds, function($id) use ($currentProduct) {
                return $id != $currentProduct->getId();
            });
        }
        
        if (empty($productIds)) {
            return $this->productCollectionFactory->create()->addFieldToFilter('entity_id', 0);
        }
        
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                   ->addAttributeToFilter('entity_id', ['in' => $productIds])
                   ->addAttributeToFilter('status', 1)
                   ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                   ->setPageSize(self::MAX_PRODUCTS);
        
        // Maintain the order from cookie (most recent first)
        $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $productIds) . ')'));
        
        return $collection;
    }

    public function hasRecentlyViewedProducts()
    {
        return $this->getRecentlyViewedProducts()->getSize() > 0;
    }

    public function getCurrentProductId()
    {
        $currentProduct = $this->registry->registry('current_product');
        return $currentProduct ? $currentProduct->getId() : null;
    }

    public function getProductImageUrl($product, $imageId = 'product_page_image_small')
    {
        return $this->imageHelper->init($product, $imageId)->getUrl();
    }

    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    public function getProductUrl($product)
    {
        return $product->getProductUrl();
    }
}
