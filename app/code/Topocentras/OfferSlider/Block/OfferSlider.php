<?php
namespace Topocentras\OfferSlider\Block;

use Magento\Framework\View\Element\Template;
use Topocentras\OfferSlider\Model\ResourceModel\OfferSlider\CollectionFactory as SliderCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Store\Model\StoreManagerInterface;

class OfferSlider extends Template
{
    protected $sliderCollectionFactory;
    protected $productRepository;
    protected $imageHelper;
    protected $priceHelper;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        SliderCollectionFactory $sliderCollectionFactory,
        ProductRepository $productRepository,
        ImageHelper $imageHelper,
        PriceHelper $priceHelper,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->sliderCollectionFactory = $sliderCollectionFactory;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getActiveSliders()
    {
        $collection = $this->sliderCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)
                   ->setOrder('sort_order', 'ASC');
        
        return $collection;
    }

    public function getSliderById($sliderId)
    {
        $collection = $this->sliderCollectionFactory->create();
        $collection->addFieldToFilter('slider_id', $sliderId);
        
        return $collection->getFirstItem();
    }

    public function getSliderProducts($slider)
    {
        $productSkus = $slider->getProductSkus();
        if (!$productSkus) {
            return [];
        }
        
        // Parse comma-separated SKUs
        $skuArray = array_map('trim', explode(',', $productSkus));
        $skuArray = array_filter($skuArray); // Remove empty values
        
        $products = [];
        foreach ($skuArray as $sku) {
            try {
                $product = $this->productRepository->get($sku);
                if ($product->getId()) {
                    $products[] = [
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'url' => $product->getProductUrl(),
                        'image' => $this->imageHelper->init($product, 'product_page_image_small')->getUrl(),
                        'price' => $this->priceHelper->currency($product->getPrice(), true, false),
                        'final_price' => $this->priceHelper->currency($product->getFinalPrice(), true, false),
                        'product' => $product
                    ];
                }
            } catch (\Exception $e) {
                // Skip invalid SKUs
                continue;
            }
        }
        
        return $products;
    }

    public function getBannerImageUrl($bannerImage)
    {
        if (!$bannerImage) {
            return '';
        }
        
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'topocentras/offerslider/' . $bannerImage;
    }

    public function getSliderId()
    {
        return $this->getData('slider_id');
    }
}
