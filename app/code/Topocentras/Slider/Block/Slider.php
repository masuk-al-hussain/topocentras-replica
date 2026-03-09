<?php
namespace Topocentras\Slider\Block;

use Magento\Framework\View\Element\Template;
use Topocentras\Slider\Model\ResourceModel\Slide\CollectionFactory;

class Slider extends Template
{
    protected $slideCollectionFactory;

    public function __construct(
        Template\Context $context,
        CollectionFactory $slideCollectionFactory,
        array $data = []
    ) {
        $this->slideCollectionFactory = $slideCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getSlides()
    {
        $collection = $this->slideCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1)
                   ->setOrder('sort_order', 'ASC');
        
        $slides = [];
        foreach ($collection as $slide) {
            $slides[] = [
                'image' => $this->getMediaUrl() . 'topocentras/slider/' . $slide->getImage(),
                'url' => $slide->getUrl() ?: '#',
                'alt' => $slide->getTitle()
            ];
        }
        
        return $slides;
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
