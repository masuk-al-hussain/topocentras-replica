<?php
namespace Topocentras\OfferSlider\Block\Adminhtml\Slider\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class GenericButton
{
    protected $context;
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    public function getSliderId()
    {
        $slider = $this->registry->registry('offerslider_slider');
        return $slider ? $slider->getId() : null;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
