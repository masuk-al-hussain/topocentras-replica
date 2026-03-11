<?php
namespace Topocentras\OfferSlider\Block\Adminhtml\Slider;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class AddProductButton extends Template
{
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function getSliderId()
    {
        $slider = $this->registry->registry('offerslider_slider');
        return $slider ? $slider->getId() : $this->getRequest()->getParam('slider_id');
    }

    public function getAddProductUrl()
    {
        $sliderId = $this->getSliderId();
        if ($sliderId) {
            return $this->getUrl('offerslider/slide/edit', ['slider_id' => $sliderId]);
        }
        return '#';
    }

    protected function _toHtml()
    {
        $sliderId = $this->getSliderId();
        if (!$sliderId) {
            return '<div class="message message-notice notice"><div>' . __('Please save the slider first before adding products.') . '</div></div>';
        }

        $url = $this->getAddProductUrl();
        return '<div style="margin-bottom: 20px;">
            <button type="button" class="action-default scalable primary" onclick="window.location.href=\'' . $url . '\'">
                <span>' . __('Add Product') . '</span>
            </button>
        </div>';
    }
}
