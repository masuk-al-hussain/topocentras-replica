<?php
namespace Topocentras\OfferSlider\Model\ResourceModel\OfferSlider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'slider_id';

    protected function _construct()
    {
        $this->_init(
            \Topocentras\OfferSlider\Model\OfferSlider::class,
            \Topocentras\OfferSlider\Model\ResourceModel\OfferSlider::class
        );
    }
}
