<?php
namespace Topocentras\Slider\Block\Adminhtml\Slide\Edit;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getSlideId()
    {
        return $this->context->getRequest()->getParam('slide_id');
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
