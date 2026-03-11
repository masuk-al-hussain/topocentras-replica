<?php
namespace Topocentras\OfferSlider\Model;

use Magento\Framework\Model\AbstractModel;

class OfferSlider extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Topocentras\OfferSlider\Model\ResourceModel\OfferSlider::class);
    }
}
